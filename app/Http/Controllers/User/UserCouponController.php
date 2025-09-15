<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Models\Coupon;
use App\Models\User;
use App\Models\UserCoupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserCouponController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user, Request $request)
    {
        // Retrieve the user's coupons
        $coupons = $user->userCoupon();
    
        // Check if the coupon_id parameter is provided in the request
        if ($request->has('coupon_id')) {
            $couponId = $request->coupon_id;
            // Filter coupons by the provided coupon_id
            $coupons->where('coupon_id', $couponId);
        }
    
        // Get the filtered coupons
        $filteredCoupons = $coupons->get();
    
        // Check if any coupons are found
        if ($filteredCoupons->isEmpty()) {
            // Return an error response if no coupons are found
            return $this->errorResponse('Coupon not found.', 404);
        }
    
        // Return the list of coupons
        return $this->showAll($filteredCoupons);
    }
    


    public function store(Request $request, User $user)
    {
        // Validate the incoming request data
        $rules = [
            'coupon_id' => 'required|string',
        ];

        $this->validate($request, $rules);

        $coupon = Coupon::find($request->coupon_id);
        if (!$coupon) {
            throw ValidationException::withMessages(['coupon_id' => 'This coupon does not exist.']);
        }
        
    // Check if the coupon is active
    if ($coupon->active === 'false') {
        throw ValidationException::withMessages(['coupon_id' => 'This coupon is not active.']);
    }
    
    // Check if the coupon is already used by another user
    if (!is_null($coupon->user_id)) {
        throw ValidationException::withMessages(['coupon_id' => 'This coupon is already used by another user.']);
    }

    // Check if the coupon has expired
    if ($coupon->expiry && Carbon::parse($coupon->expiry)->isPast()) {
        throw ValidationException::withMessages(['coupon_id' => 'This coupon has expired.']);
    }

        // Check if the user already has the coupon
        if ($user->userCoupon()->where('coupon_id', $request->coupon_id)->exists()) {
            throw ValidationException::withMessages(['coupon_id' => 'This coupon is already taken by the user.']);
        }

        $data = $request->all();
        $data['user_id'] = $user->id;

    
        $useraddress = UserCoupon::create($data);

        return $this->showOne($useraddress, 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(User $user, UserCoupon $coupon)
    {
        $this->checkCoupon($user, $coupon);

        if ($coupon->used == UserCoupon::USED_COUPON) {
            return $this->errorResponse('This coupon has already been used.', 400);
        } else {
            return $this->showOne($coupon);
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user, UserCoupon $coupon)
    {
        // $rules = [
        //     'used' => 'in:' . UserCoupon::USED_COUPON . ',' . UserCoupon::UNUSED_COUPON,
        // ];
    
        // $this->validate($request, $rules);
    
        if ($request->has('used')) {
            $coupon->used = $request->used;
    
            if ($request->used == UserCoupon::USED_COUPON) {
                // Retrieve the user_id from the $user object
                $userId = $user->id;
                
                // Update all UserCoupon records with the same coupon_id
                UserCoupon::where('coupon_id', $coupon->coupon_id)->update(['used' => UserCoupon::USED_COUPON]);

                // Update the corresponding coupon data
                $couponData = Coupon::find($coupon->coupon_id);
                if ($couponData) {
                    $couponData->user_id = $userId;
                    $couponData->active = Coupon::UNACTIVE_COUPON;
                    $couponData->save();
                }
            }
        }
    
        $coupon->save(); // Update the coupon
    
        return $this->showOne($coupon);
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, UserCoupon $coupon)
    {
        $this->checkCoupon($user, $coupon);
        $coupon->delete();

        return $this->showOne($coupon);
    }


    protected function checkCoupon(User $user, UserCoupon $coupon)
    {
        if ($user->id != $coupon->user_id) {
            throw new HttpException(422, 'The Specified user is not the actual user of the coupon');
        }
    }
}
