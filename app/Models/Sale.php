<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_name', 'quantity', 'price', 'total', 'sale_date',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
    ];
    
}
