<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user pertama, pastikan ada
        $user = User::first();

        if (!$user) {
            $this->command->error('Tidak ada user ditemukan. Jalankan "php artisan db:seed --class=UserSeeder" dulu.');
            return;
        }

        $itemNames = ['Beli Pulsa', 'Bayar Listrik', 'Belanja Online', 'Ngopi', 'Topup Gopay', 'Isi Bensin', 'Langganan Netflix'];

        for ($i = 0; $i < 100; $i++) {
            Transaction::create([
                'user_id' => $user->id,
                'item_name' => $itemNames[array_rand($itemNames)],
                'amount' => rand(10000, 500000),
                'transaction_date' => Carbon::now()->subDays(rand(0, 30))->setTime(rand(0, 23), rand(0, 59)),
            ]);
        }

        $this->command->info('✅ 100 transaksi berhasil disisipkan untuk user: ' . $user->email);
    }
}
