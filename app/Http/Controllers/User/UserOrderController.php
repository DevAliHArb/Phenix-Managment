<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Models\Article;
use App\Models\User;
use App\Models\websiteBooks;
use Illuminate\Http\Request;

class UserOrderController extends ApiController
{

    public function index(User $user)
    {
        $orderInvoices = $user->orderInvoices()
            ->with([
                'lookUp',
                    'orderstatushistories' => function ($query) {
                        $query->with(['LookUp']);
                    },
                    'orderInvoiceItems' => function ($query) {
                        $query->with([
                            'product' => function ($query1) {
                                $query1->with([
                                    'Category',
                                    'SubCategory',
                                    'Brand',
                                    'ProductMedia',
                                    'ProductSpecifications',
                                    'ProductVariants' => function ($query) {
                                        $query->with('VariantItems');
                                    }
                                ]);
                            },
                            'orderinvoiceitemvariant'
                            => function ($query) {
                                $query->with([
                                    'productvariant', // Adjusted to match the model method
                                    'variantitem' // Adjusted to match the model method
                                ]);
                            }
                        ]);
                    },
                ])
            ->get();

        $orderInvoice = $user->orderInvoices()->with(['lookUp',
        'orderstatushistories' => function ($query) {
            $query->with(['LookUp']);
        },'orderInvoiceItems'=> function ($query) {
            $query->with(['product'=> function ($query1) {
                $query1->with([
                    'Category',
        'SubCategory',
        'Brand',
        'ProductMedia',
        'ProductSpecifications',
        'ProductVariants' => function ($query) {
            $query->with('VariantItems');
        }]);
            },
            'orderinvoiceitemvariant' 
            => function ($query) {
                $query->with([
                    'productvariant', // Adjusted to match the model method
                    'variantitem' // Adjusted to match the model method
                ]);
            }]);
        },])->get();

        return $this->showAll($orderInvoice);
    }
    
    
}
