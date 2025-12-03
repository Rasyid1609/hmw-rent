<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function created(Product $product)
    {
        $product->stock()->create([
            'total' => $total = request()->total,
            'available' => $total,
        ]);
    }
}
