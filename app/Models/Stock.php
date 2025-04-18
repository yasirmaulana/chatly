<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'quantity',
        'price'
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'product_name', 'product_name');
    }
}
