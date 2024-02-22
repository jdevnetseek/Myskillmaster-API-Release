<?php

namespace App\Http\Controllers\V1\Auth\AccountSettings\Address;

use App\Models\User;
use App\Enums\AddressType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;

class DeliveryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Handles the request for getting the delivery address of the user.
     *
     * @todo Add Unit Test
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        /** @var User */
        $user = $request->user();

        /**
         * We will do a first or new so that the response would return a null
         * property when there is no data was found.
         */
        $address = $user->deliveryAddress()->firstOrNew([]);

        return AddressResource::make($address->load('country'));
    }

    /**
     * Handles the request for setting the delivery address of the user.
     *
     * @todo Add Unit Test
     *
     * @param AddressRequest $request
     * @return void
     */
    public function store(AddressRequest $request)
    {
        /** @var User */
        $user = $request->user();

        $address = $user->deliveryAddress()->firstOrNew([]);
        $address->fill($request->validated());
        $address->type = AddressType::DELIVERY;
        $address->save();

        return AddressResource::make($address->load('country'));
    }
}
