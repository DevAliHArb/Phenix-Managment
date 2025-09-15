<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserAddressController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user)
    {

        $addresses = $user->userAddresses;
    
    if ($addresses->isEmpty()) {
        return $this->errorResponse('You have no available addresses.', 404);
    }
        return $this->showAll($addresses);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, User $user)
    {
        // Validate the incoming request data
        $rules = [
            'title' => 'required|string',
            'address' => 'required|string',
            'country' => 'required|string',
            'city' => 'required|string',
            'postalcode' => 'required|string',
        ];

        $this->validate($request, $rules);

        $data = $request->all();
        $data['user_id'] = $user->id;

         // Check if the user already has any addresses
    if ($user->userAddresses()->count() == 0) {
        // Set the default value of the new address to true
        $data['default'] = UserAddress::DEFAULT_ADDRESS;
    } else {
        // Set the default value of the new address to false
        $data['default'] = UserAddress::NOT_DEFAULT_ADDRESS;
    }

        $useraddress = UserAddress::create($data);

        return $this->showOne($useraddress, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user, UserAddress $address)
    {
        $address = UserAddress::where('user_id', $user->id)
            ->where('id', $address->id)
            ->first();

        if (!$address) {
            return response()->json(['error' => 'Address not found'], 404);
        }

        return $this->showOne($address);
    }


    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request, User $user, UserAddress $address){
        $rules = [
            'default'=> 'in:'. UserAddress::DEFAULT_ADDRESS. ',' . UserAddress::NOT_DEFAULT_ADDRESS,
        ];

        $this->validate($request, $rules);

        $this->checkAddress($user, $address);

        $address->fill($request->only(['title', 'address', 'country', 'city', 'postalcode', 'name', 'company']));

        if ($request->default == UserAddress::DEFAULT_ADDRESS) {
            // Set the default value of the selected address to true
            $address->default = UserAddress::DEFAULT_ADDRESS;
            $address->save();
    
            // Update the default value of all other addresses belonging to the same user to false
            $user->userAddresses()->where('id', '!=', $address->id)->update(['default' =>  UserAddress::NOT_DEFAULT_ADDRESS]);
        } else {
            // If the default value is being changed to false, simply update the address without affecting other addresses
            $address->fill($request->only(['title', 'address', 'country', 'city', 'postalcode','name', 'company']));
    
            if ($address->isClean()) {
                return $this->errorResponse('You need to specify a different value to update', 422);
            }
    
            $address->save();
        }

        return $this->showOne($address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, UserAddress $address )
    {
        $this->checkAddress($user, $address);
        $address->delete();

        return $this->showOne($address);
    }

    
    protected function checkAddress(User $user, UserAddress $address){
        if($user->id != $address->user_id){
            throw new HttpException(422,'The Specified user is not the actual user of the address');
        }
    }
}
