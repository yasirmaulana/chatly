<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'quantity',
        'price',
        'total',
        'sale_date',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'product_name', 'product_name');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
