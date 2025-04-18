<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ChatService
{
    protected string $groqApiKey;
    protected string $groqApiUrl;
    protected string $groqModel;

    public function __construct()
    {
        $this->groqApiKey = config('services.groq.key');
        $this->groqModel = config('services.groq.model');
        $this->groqApiUrl = config('services.groq.url');
    }

    public function handleUserMessage(int $userId, string $message): string
    {
        try {
            $filters = $this->parseUserIntent($message);
            // dd($filters);

            if ($filters['query_type'] === 'stock') {
                if ($filters['flag'] === 'low_stock') {
                    $stocks = Stock::where('quantity', '<', 10)->orderBy('quantity')->get();
                    $context = $this->generateContextFromStocks($stocks);
                    return $this->askGroq($context, $message);
                }

                if ($filters['flag'] === 'never_sold') {
                    $stocks = Stock::whereDoesntHave('sales')->get();
                    $context = $this->generateContextFromStocks($stocks);
                    return $this->askGroq($context, $message);
                }

                if ($filters['flag'] === 'expired_soon') {
                    $stocks = Stock::whereBetween('expired_date', [now(), now()->addMonth()])->get();
                    $context = $this->generateContextFromStocks($stocks);
                    return $this->askGroq($context, $message);
                }

                if ($filters['flag'] === 'oldest_stock') {
                    $stocks = Stock::orderBy('created_at')->limit(5)->get();
                    $context = $this->generateContextFromStocks($stocks);
                    return $this->askGroq($context, $message);
                }

                $stocks = $this->queryFilteredStock($filters);
                $context = $this->generateContextFromStocks($stocks);
                return $this->askGroq($context, $message);
            }

            if (!empty($filters['intent'])) {
                return $this->handleSaleIntent($userId, $filters, $message);
            }

            $sales = $this->queryFilteredSales($userId, $filters);
            // dd($sales);
            $context = $this->generateContextFromSales($sales);
            return $this->askGroq($context, $message);
        } catch (\Exception $e) {
            Log::error('ChatService error: ' . $e->getMessage());
            return "Maaf, terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi nanti.";
        }
    }

    public function parseUserIntent(string $message): array
    {
        $today = Carbon::now()->toDateString();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $prompt = <<<PROMPT
        Kamu adalah AI yang mengubah pesan pengguna menjadi filter sale dan stock. Jawaban HARUS berupa JSON VALID dan HANYA JSON, sesuai format ini:

        {
            "query_type": "stock | sale",
            "intent": "sale_above | sale_below | top_selling | most_transaction | unique_customer | top_stock | low_stock | never_sold | expired_soon | expired | fastest_turnover",
            "product_name": "string atau null",
            "amount": "integer atau null",
            "quantity": "integer atau null",
            "date_filter": "hari_ini | kemarin | minggu_lalu | bulan_ini | bulan_kemarin | custom | riwayat",
            "start_date": "YYYY-MM-DD",
            "end_date": "YYYY-MM-DD",
            "flag": "> | < | = ",
        }

        Hari ini adalah: {$today}
        Bulan saat ini adalah: {$currentMonth}
        Tahun saat ini adalah: {$currentYear}

        Aturan:
        - parameter $message dibuat lowercase.
        - query_type, Gunakan:
            - "stock" untuk stok
            - "sale" untuk penjualan
        - intent adalah niat pengguna. Gunakan:
            - "sale_above" untuk penjualan di atas total tertentu
            - "sale_below" untuk penjualan di bawah total tertentu
            - "top_selling" untuk produk/barang paling laku
            - "most_transaction" untuk produk/barang dengan transaksi terbanyak
            - "unique_customer" untuk produk/barang dengan pelanggan unik terbanyak
            - "top_stock" untuk stok produk/barang terbanyak
            - "low_stock" untuk stok produk/barang hampir habis
            - "never_sold" untuk produk/barang yang belum pernah terjual
            - "expired_soon" untuk produk/barang yang kadaluarsa dalam waktu dekat
            - "expired" untuk produk/barang kadaluarsa
            - "fastest_turnover" untuk produk/barang dengan perputaran stok tercepat
            - null untuk tidak ada intent
        - product_name untuk produk/barang atau isi null jika tidak disebutkan
        - amount untuk price atau total atau harga jika disebutkan angka tertentu, isi null jika tidak disebutkan.
        - penyebutan price atau total atau harga bisa seperti berikut:
            - dengan angka, 5000 maka amount = 5000
            - dengan delimiter, 5.000 maka amount = 5000
            - gabungan angka huruf, 5 ribu maka amount = 5000
            - dengan kata, lima ribu maka amount = 5000
            - begitu juga puluh ribuan, ratus ribuan, juta, milyar
        - quantity untuk jumlah atau unit atau stok jika disebutkan, isi quantity dengan angka itu, dan isi null jika tidak disebutkan
        - Tahun saat ini adalah {$currentYear}. Semua tanggal harus berada di tahun ini, kecuali jika pesan pengguna menyebutkan tahun lain.
        - date_filter untuk waktu. Gunakan
            - "hari_ini": isi start_date dan end_date dengan tanggal hari ini ({$today}).
            - "kemarin": isi start_date dan end_date dengan tanggal kemarin (1 hari sebelum {$today}).
            - "minggu_lalu": isi tanggal dengan tanggal hari Senin hingga tanggal hari Minggu di minggu lalu.
            - "bulan_ini": isi tanggal 1 sampai hari ini.
            - "bulan_kemarin": isi tanggal 1 sampai akhir bulan, di bulan sebelumnya.
            - "custom" atau "riwayat" jika disebut rentang tanggal atau tanggal tertentu, isi start_date & end_date
        - start_date dan end_date jika tidak disebutkan, isi null
        - flag diisi dengan:
            - ">" jika disebutkan "lebih dari" atau "lebih besar" atau "lebih tinggi"
            - "<" jika disebutkan "kurang dari" atau "lebih kecil" atau "di bawah"
            - "=" jika disebutkan "sama dengan" atau "setara dengan" atau "persis sama"
            - jika tidak disebutkan, isi null
        Jangan berikan penjelasan apapun. Output HANYA JSON valid.
        PROMPT;

        $response = Http::withToken($this->groqApiKey)
            ->post($this->groqApiUrl, [
                'model' => $this->groqModel,
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.2,
                'max_tokens' => 5000,
            ]);

        $content = $response['choices'][0]['message']['content'] ?? '{}';
        return json_decode($content, true) ?? ['query_type' => 'sale', 'product_name' => null];
    }

    public function handleStockIntent(array $filters, string $message): string
    {
        switch ($filters['intent']) {
            case 'top_stock':
                $stocks = Stock::orderByDesc('quantity')->take(5)->get();

                $context = "Berikut adalah 5 barang dengan stok terbanyak:\n";
                foreach ($stocks as $item) {
                    $context .= "- {$item->product_name}: {$item->quantity} unit\n";
                }
                return $this->askGroq($context, $message);

            case 'low_stock':
                $threshold = $filters['quantity'] ?? 10;
                $stocks = Stock::where('quantity', '<=', $threshold)->orderBy('quantity')->get();

                if ($stocks->isEmpty()) {
                    return "Semua stok aman, tidak ada yang di bawah threshold {$threshold} unit.";
                }

                $context = "Berikut adalah barang dengan stok rendah (≤ {$threshold} unit):\n";
                foreach ($stocks as $item) {
                    $context .= "- {$item->product_name}: {$item->quantity} unit\n";
                }
                return $this->askGroq($context, $message);

            case 'never_sold':
                $soldProducts = Sale::distinct()->pluck('product_name');
                $unsoldStocks = Stock::whereNotIn('product_name', $soldProducts)->get();

                if ($unsoldStocks->isEmpty()) {
                    return "Semua barang pernah terjual.";
                }

                $context = "Berikut adalah barang yang belum pernah terjual:\n";
                foreach ($unsoldStocks as $item) {
                    $context .= "- {$item->product_name} ({$item->quantity} unit tersedia)\n";
                }
                return $this->askGroq($context, $message);

            default:
                return "Intent stok tidak dikenali.";
        }
    }

    public function handleSaleIntent(int $userId, array $filters, string $message): string
    {
        switch ($filters['intent']) {
            case 'sale_above':
                $data = Sale::where('user_id', $userId)
                    ->where('total', '>', $filters['amount'])
                    ->when(!empty($filters['start_date']) && !empty($filters['end_date']), function ($q) use($filters) {
                        $q->whereBetween('sale_date', [
                            Carbon::parse($filters['start_date'])->startOfDay(),
                            Carbon::parse($filters['end_date'])->endOfDay()
                        ]);
                    })
                    ->orderBy('sale_date', 'desc')
                    ->get();

                // dd($data);

                $context = "Berikut adalah data penjualan di atas Rp " . number_format($filters['amount']) . ":\n";
                foreach ($data as $item) {
                    $context .= "- {$item->sale_date->format('Y-m-d')}: {$item->quantity} unit {$item->product_name} (harga satuan Rp " . number_format($item->price) . ", total Rp " . number_format($item->total) . ")\n";
                }
                // dd($context);

                return $this->askGroq($context, $message);

            case 'sale_below':
                $data = Sale::where('user_id', $userId)
                ->where('total', '<', $filters['amount'])
                ->when(!empty($filters['start_date']) && !empty($filters['end_date']), function ($q) use($filters) {
                    $q->whereBetween('sale_date', [
                        Carbon::parse($filters['start_date'])->startOfDay(),
                        Carbon::parse($filters['end_date'])->endOfDay()
                    ]);
                })
                ->orderBy('sale_date', 'desc')
                ->get();

            $context = "Berikut adalah data penjualan di bawah Rp " . number_format($filters['amount']) . ":\n";
            foreach ($data as $item) {
                $context .= "- {$item->sale_date->format('Y-m-d')}: {$item->quantity} unit {$item->product_name} (harga satuan Rp " . number_format($item->price) . ", total Rp " . number_format($item->total) . ")\n";
            }
            return $this->askGroq($context, $message);

            case 'top_selling':
                $data = Sale::selectRaw('product_name, SUM(quantity) as total_quantity')
                    ->where('user_id', $userId)
                    ->when(!empty($filters['start_date']) && !empty($filters['end_date']), function ($q) use ($filters) {
                        $q->whereBetween('sale_date', [
                            Carbon::parse($filters['start_date'])->startOfDay(),
                            Carbon::parse($filters['end_date'])->endOfDay()
                        ]);
                    })
                    ->groupBy('product_name')
                    ->orderByDesc('total_quantity')
                    ->take(5)
                    ->get();

                $context = "Berikut adalah 5 produk paling laku berdasarkan jumlah terjual:\n";
                foreach ($data as $item) {
                    $context .= "- {$item->product_name}: {$item->total_quantity} unit\n";
                }
                return $this->askGroq($context, $message);

            case 'most_transaction':
                $data = Sale::selectRaw('product_name, COUNT(*) as transaction_count')
                    ->where('user_id', $userId)
                    ->when(!empty($filters['start_date']) && !empty($filters['end_date']), function ($q) use ($filters) {
                        $q->whereBetween('sale_date', [
                            Carbon::parse($filters['start_date'])->startOfDay(),
                            Carbon::parse($filters['end_date'])->endOfDay()
                        ]);
                    })
                    ->groupBy('product_name')
                    ->orderByDesc('transaction_count')
                    ->take(5)
                    ->get();

                $context = "Berikut adalah 5 produk dengan jumlah transaksi terbanyak:\n";
                foreach ($data as $item) {
                    $context .= "- {$item->product_name}: {$item->transaction_count} transaksi\n";
                }
                return $this->askGroq($context, $message);

            case 'unique_customer':
                $unique = Sale::where('user_id', $userId)
                    ->when(!empty($filters['start_date']) && !empty($filters['end_date']), function ($q) use ($filters) {
                        $q->whereBetween('sale_date', [
                            Carbon::parse($filters['start_date'])->startOfDay(),
                            Carbon::parse($filters['end_date'])->endOfDay()
                        ]);
                    })
                    ->distinct('user_id')
                    ->count('user_id');

                $context = "Jumlah pelanggan unik pada periode ini adalah: {$unique} pelanggan.";
                return $this->askGroq($context, $message);

            default:
                return "Intent tidak dikenali.";
        }
    }

    public function queryFilteredStock(array $filters): Collection
    {
        $query = Stock::query();

        if (!empty($filters['product_name'])) {
            $query->where('product_name', $filters['product_name']);
        }

        if (!empty($filters['quantity']) && !empty($filters['flag'])) {
            $query->where('quantity', $filters['flag'], $filters['quantity']);
        }

        return $query
            ->orderBy('quantity', 'asc')
            ->get();
    }

    public function queryFilteredSales(int $userId, array $filters): Collection
    {
        $query = Sale::where('user_id', $userId);

        switch ($filters['date_filter']) {
            case 'hari_ini':
                $query->whereDate('sale_date', today());
                break;

            case 'kemarin':
                $query->whereDate('sale_date', now()->subDay());
                break;

            case 'minggu_lalu':
            case 'bulan_ini':
            case 'bulan_kemarin':
            case 'custom':
            case 'riwayat':

                if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
                    $query->whereBetween('sale_date', [
                        Carbon::parse($filters['start_date'])->startOfDay(),
                        Carbon::parse($filters['end_date'])->endOfDay(),
                    ]);
                } 
                break;
        }

        if (!empty($filters['product_name'])) {
            $query->where('product_name', $filters['product_name']);
        }

        if (!empty($filters['total']) && !empty($filters['flag'])) {
            $query->where('total', $filters['flag'], $filters['total']);
        }

        // dd($query->toSql(), $query->getBindings());
        return $query
            ->orderBy('product_name', 'asc')
            ->orderBy('sale_date', 'desc')
            ->get();
    }

    public function generateContextFromSales(Collection $sales): string
    {
        if ($sales->isEmpty()) {
            return "Tidak ada data penjualan yang ditemukan untuk filter ini.";
        }

        $summary = "Berikut ini adalah data penjualan yang relevan:\n";

        foreach ($sales as $sale) {
            $summary .= "- {$sale->sale_date->format('Y-m-d')}: {$sale->quantity} unit {$sale->product_name} (harga satuan Rp " . number_format($sale->price) . ", total Rp " . number_format($sale->total) . ")\n";
        }

        return $summary;
    }

    public function generateContextFromStocks(Collection $stocks): string
    {
        if ($stocks->isEmpty()) {
            return "Tidak ada data stok produk yang ditemukan.";
        }

        $summary = "Berikut adalah daftar stok produk yang tersedia:\n";

        foreach ($stocks as $stock) {
            $summary .= "- {$stock->product_name}: {$stock->quantity} unit, harga satuan Rp " . number_format($stock->price) . "\n";
        }

        return $summary;
    }

    public function askGroq(string $context, string $userMessage): string
    {
        $response = Http::withToken($this->groqApiKey)
            ->post($this->groqApiUrl, [
                'model' => $this->groqModel,
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu adalah chatbot POS pintar. Jawab hanya berdasarkan data yang diberikan. Jangan mengarang. Jawab dengan gaya sopan dan jelas. Jawab dalam bahasa Indonesia yang baik dan benar.'],
                    ['role' => 'system', 'content' => $context],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.3,
                'max_tokens' => 5000,
            ]);

        return $response['choices'][0]['message']['content'] ?? "Maaf, saya tidak bisa menjawab saat ini.";
    }
}
