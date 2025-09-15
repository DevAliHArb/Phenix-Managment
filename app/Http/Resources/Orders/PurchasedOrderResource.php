<?php

namespace App\Http\Resources\Orders;

use App\Enums\PurchasedOrdersStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchasedOrderResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'books' => $this->whenLoaded("purchasedBooks")
        ];
    }
}
