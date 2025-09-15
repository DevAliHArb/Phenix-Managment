<?php

namespace App\Http\Services\Orders;

use App\Models\PurchasedBook;


class PurchasedOrderService
{

    public function handleStore($data, $order_id)
    {
        foreach ($data as $item) {

            PurchasedBook::create([
                'title' => $item['title'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'isbn' => $item['isbn'],
                'purchased_order_id' => $order_id,
                'image' => $item['image'],
            ]);

        }

    }
}