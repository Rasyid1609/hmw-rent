<?php

namespace App\Models;

use App\Models\ReturnProduct;
use App\Enums\ReturnProductCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnProductChecks extends Model
{
    protected $fillable = [
        'return_product_id',
        'condition',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'condition' => ReturnProductCondition::class,
        ];
    }

    public function returnProduct(): BelongsTo
    {
        return $this->belongsTo(ReturnProduct::class);
    }
}
