<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_template_id' => ['nullable', 'exists:service_templates,id'],
            'assigned_to' => ['required', 'exists:users,id'],
            'helpers' => 'nullable|array',
            'helpers.*' => 'exists:users,id',
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'due_date' => [
                'nullable', 
                'date', 
                'after_or_equal:today'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'A work order title is required.',
            'title.min' => 'The title must be at least 3 characters.',
            'description.required' => 'Please provide a description of the work order.',
            'description.min' => 'The description must be at least 10 characters.',
            'assigned_to.required' => 'Please select a worker to assign this work order to.',
            'assigned_to.exists' => 'The selected worker is invalid.',
            'priority.required' => 'Please select a priority level.',
            'priority.in' => 'The selected priority is invalid.',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
            'service_template_id.exists' => 'The selected service template is invalid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('due_date') && empty($this->input('due_date'))) {
            $this->request->remove('due_date');
        }
    }
}