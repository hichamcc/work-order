<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index()
    {
        $customers = Customer::latest()->paginate(15);
        
        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'is_default' => 'boolean'
        ]);

        $customer = Customer::create($validated);
        
        if ($request->has('is_default') && $request->is_default) {
            $customer->setAsDefault();
        }

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        $customer->load(['workOrders' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('customers')->ignore($customer->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'is_default' => 'boolean'
        ]);

        $customer->update($validated);
    

        if ($request->has('is_default') && $request->is_default ) {
            //dd('has');
            $customer->setAsDefault();
        }

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has work orders
        if ($customer->workOrders()->count() > 0) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Cannot delete customer with existing work orders.');
        }

        // Don't allow deleting the default customer if it's the only one
        if ($customer->is_default && Customer::count() == 1) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Cannot delete the only customer.');
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Set customer as default.
     */
    public function setDefault(Customer $customer)
    {
        $customer->setAsDefault();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer set as default successfully.');
    }
}