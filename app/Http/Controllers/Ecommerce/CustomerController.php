<?php

namespace App\Http\Controllers\Ecommerce;

use App\Models\Ecommerce\Customer;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Ecommerce\StoreCustomerRequest;
use App\Http\Requests\Ecommerce\UpdateCustomerRequest;


class CustomerController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
