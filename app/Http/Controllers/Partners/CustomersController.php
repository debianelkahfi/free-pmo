<?php

namespace App\Http\Controllers\Partners;

use App\Entities\Partners\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    /**
     * Display a listing of the customer.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $editableCustomer = null;
        $customers = Customer::where(function ($query) {
            $query->where('name', 'like', '%'.request('q').'%');
        })->paginate(25);

        if (in_array(request('action'), ['edit', 'delete']) && request('id') != null) {
            $editableCustomer = Customer::find(request('id'));
        }

        return view('customers.index', compact('customers', 'editableCustomer'));
    }

    /**
     * Store a newly created customer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newCustomerData = $this->validate($request, [
            'name' => 'required|max:60',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|max:255',
            'pic' => 'nullable|max:255',
            'address' => 'nullable|max:255',
            'notes' => 'nullable|max:255',
        ]);

        Customer::create($newCustomerData);

        flash(trans('customer.created'), 'success');

        return redirect()->route('customers.index');
    }

    /**
     * Update the specified customer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\Partners\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $customerData = $this->validate($request, [
            'name' => 'required|max:60',
            'email' => 'nullable|email|unique:customers,email,'.$customer->id,
            'phone' => 'nullable|max:255',
            'pic' => 'nullable|max:255',
            'address' => 'nullable|max:255',
            'notes' => 'nullable|max:255',
            'is_active' => 'required|boolean',
        ]);

        $routeParam = request()->only('page', 'q');

        $customer = $customer->update($customerData);

        flash(trans('customer.updated'), 'success');
        return redirect()->route('customers.index', $routeParam);
    }

    /**
     * Remove the specified customer from storage.
     *
     * @param  \App\Entities\Partners\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        // TODO: user cannot delete customer that has been used in other table
        $this->validate(request(), [
            'customer_id' => 'required',
        ]);

        $routeParam = request()->only('page', 'q');

        if (request('customer_id') == $customer->id && $customer->delete()) {
            flash(trans('customer.deleted'), 'warning');
            return redirect()->route('customers.index', $routeParam);
        }

        flash(trans('customer.undeleted'), 'danger');
        return back();
    }
}