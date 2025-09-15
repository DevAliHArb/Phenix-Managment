<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Http\Request;

class UserCartController extends ApiController
{
    public function index(User $user)
    {
        // Eager load cart items and their variants
        $carts = $user->cart()->with([
            'user',
            'ProductVariantCombination',
            'product' => function ($query) {
                $query->with([
                    'category', // Adjusted to match the model method
                    'subCategory', // Adjusted to match the model method
                    'brand', // Adjusted to match the model method
                    'productMedia', // Adjusted to match the model method
                    'productSpecifications', // Adjusted to match the model method
                    'productVariants' => function ($query) {
                        $query->with('variantItems'); // Adjusted to match the model method
                    }
                ]);
            },
            'cartItemsVariants' 
            => function ($query) {
                $query->with([
                    'productvariant', // Adjusted to match the model method
                    'variantitem' // Adjusted to match the model method
                ]);
            }
        ])->get();

        return $this->showAll($carts);
    }

    public function destroy(User $user)
    {
        // Get all cart items for the user
        $cartItems = $user->cart()->get();

        // Delete related cart items variants first
        foreach ($cartItems as $cartItem) {
            $cartItem->cartitemvariant()->delete();
        }

        // Now delete the cart items
        $user->cart()->delete();

        return response()->json([
            'message' => 'Cart for user ' . $user->id . ' and its variants deleted successfully.',
        ], 200);
    }
}

