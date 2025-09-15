<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Models\websiteBooks;
use Illuminate\Http\Request;

class UserfavoriteController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user)
    {

        $favorites = $user->favorite()->with([
            'user',
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
        ])->get();
        

        return $this->showAll($favorites);
    }

    
}
