<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SalesAndStocksSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('stocks')->truncate();
        DB::table('sales')->truncate();

        // Dummy data stok
        $products = [
            ['product_name' => 'Kopi Latte', 'quantity' => 50, 'price' => 15000],
            ['product_name' => 'Roti Bakar', 'quantity' => 30, 'price' => 12000],
            ['product_name' => 'Air Mineral', 'quantity' => 100, 'price' => 5000],
            ['product_name' => 'Es Teh Manis', 'quantity' => 80, 'price' => 7000],
        ];

        foreach ($products as $product) {
            DB::table('stocks')->insert([
                ...$product,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Dummy data penjualan (user_id diasumsikan 1)
        $userId = User::first()?->id ?? 1;

        $sales = [
            ['product_name' => 'Kopi Latte', 'quantity' => 2, 'price' => 15000, 'sale_date' => today()],
            ['product_name' => 'Roti Bakar', 'quantity' => 1, 'price' => 12000, 'sale_date' => today()],
            ['product_name' => 'Es Teh Manis', 'quantity' => 3, 'price' => 7000, 'sale_date' => today()],
            ['product_name' => 'Air Mineral', 'quantity' => 2, 'price' => 5000, 'sale_date' => today()->subDay()],
        ];

        foreach ($sales as $sale) {
            Sale::create([
                'user_id' => $userId,
                'product_name' => $sale['product_name'],
                'quantity' => $sale['quantity'],
                'price' => $sale['price'],
                'total' => $sale['quantity'] * $sale['price'],
                'sale_date' => $sale['sale_date'],
            ]);
        }
    }
}
