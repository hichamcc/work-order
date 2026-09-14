<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WorkOrderPhotoController extends Controller
{
    /**
     * Largest image accepted, in kilobytes.
     */
    const MAX_SIZE_KB = 5120;

    /**
     * Attach photos to the job itself, rather than to a checklist item.
     *
     * Mechanics use these to show the parts they changed and the state of the
     * work, so they are taken while the job is being done.
     */
    public function store(Request $request, WorkOrder $workOrder)
    {
        $this->authoriseWorker($workOrder);

        if ($workOrder->status === 'completed') {
            return back()->with('error', 'This work order is completed; photos can no longer be added.');
        }

        $validated = $request->validate([
            'photos' => ['required', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,heic,heif,webp', 'max:'.self::MAX_SIZE_KB],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'photos.required' => 'Please choose at least one photo.',
            'photos.*.image' => 'Only image files can be uploaded.',
            'photos.*.max' => 'Each photo must be 5 MB or smaller.',
            'photos.max' => 'You can upload up to 10 photos at a time.',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['photos'] as $photo) {
                $path = $photo->store('work-orders/'.$workOrder->id, 'public');

                $workOrder->photos()->create([
                    'checklist_item_id' => null,
                    'file_path' => $path,
                    'file_name' => $photo->getClientOriginalName(),
                    'mime_type' => $photo->getClientMimeType(),
                    'file_size' => $photo->getSize(),
                    'description' => $validated['description'] ?? null,
                    'uploaded_by' => auth()->id(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Could not upload the photos: '.$e->getMessage());
        }

        $count = count($validated['photos']);

        return back()->with('success', $count.' '.($count === 1 ? 'photo' : 'photos').' added.');
    }

    /**
     * Remove a photo. Mechanics may delete their own; admins may delete any.
     */
    public function destroy(WorkOrder $workOrder, WorkOrderPhoto $photo)
    {
        if ($photo->work_order_id !== $workOrder->id) {
            abort(404);
        }

        $user = auth()->user();

        if (! $user->isAdmin()) {
            $this->authoriseWorker($workOrder);

            if ($photo->uploaded_by !== $user->id) {
                abort(403, 'You can only delete photos you uploaded.');
            }

            if ($workOrder->status === 'completed') {
                return back()->with('error', 'This work order is completed; photos can no longer be removed.');
            }
        }

        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return back()->with('success', 'Photo removed.');
    }

    /**
     * Only the assigned mechanic and their helpers may touch a job's photos.
     */
    protected function authoriseWorker(WorkOrder $workOrder): void
    {
        if ($workOrder->assigned_to !== auth()->id()
            && ! $workOrder->helpers->contains('id', auth()->id())) {
            abort(403, 'This work order is not assigned to you.');
        }
    }
}
