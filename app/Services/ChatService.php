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
                $stocks = $this->queryStock($filters['product_name'] ?? null);
                $context = $this->generateContextFromStocks($stocks);
                return $this->askGroq($context, $message);
            }

            $sales = $this->queryFilteredSales($userId, $filters);
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
            "date_filter": "hari_ini | kemarin | minggu_lalu | bulan_ini | bulan_kemarin | custom | riwayat",
            "product_name": "string atau null",
            "start_date": "YYYY-MM-DD",
            "end_date": "YYYY-MM-DD",
            "amount": "integer atau null",
            "flag": "> | < | =",
        }

        Hari ini adalah: {$today}
        Bulan saat ini adalah: {$currentMonth}
        Tahun saat ini adalah: {$currentYear}

        Aturan:
        - parameter $message dibuat lowercase.
        - stok = stock | sale = penjualan.
        - Gunakan HANYA nilai query_type dan date_filter dari daftar.
        - Tahun saat ini adalah {$currentYear}. Semua tanggal harus berada di tahun ini, kecuali jika pesan pengguna menyebutkan tahun lain.
        - Jika disebut rentang tanggal atau tanggal tertentu, isi start_date & end_date, dan set date_filter = "custom" atau "riwayat".
        - Jika tidak disebutkan rentang tanggal atau tanggal tertentu dalam pesan, tentukan start_date dan end_date berdasarkan nilai date_filter:
        - "hari_ini": isi start_date dan end_date dengan tanggal hari ini ({$today}).
        - "kemarin": isi start_date dan end_date dengan tanggal kemarin (1 hari sebelum {$today}).
        - "minggu_lalu": isi dengan Senin hingga Minggu minggu lalu.
        - "bulan_ini": dari tanggal 1 sampai hari ini.
        - "bulan_kemarin": dari tanggal 1 sampai akhir bulan lalu.
        - "query_type": "sale" jika tidak ada kata "stock" dalam pesan.
        - "untuk stock, tentukan product_name, dan set query_type = "stock",
        - Jika disebutkan angka tertentu, isi amount dengan angka itu, dan set flag jika perlu.
        - Jika produk tidak disebut, isi "product_name": null.

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

    public function queryStock(?string $productName = null): Collection
    {
        if ($productName) {
            return Stock::where('product_name', $productName)->get();
        }

        return Stock::orderBy('product_name')->get();
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
                $query->whereBetween('sale_date', [
                    Carbon::parse($filters['start_date'])->startOfDay(),
                    Carbon::parse($filters['end_date'])->endOfDay(),
                ]);
                break;
        }

        if (!empty($filters['product_name'])) {
            $query->where('product_name', $filters['product_name']);
        }

        if (!empty($filters['total']) && !empty($filters['flag'])) {
            $query->where('total', $filters['flag'], $filters['total']);
        }

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
            $summary .= "- {$stock->product_name}: {$stock->quantity} item, harga satuan Rp " . number_format($stock->price) . "\n";
        }

        return $summary;
    }


    public function askGroq(string $context, string $userMessage): string
    {
        $response = Http::withToken($this->groqApiKey)
            ->post($this->groqApiUrl, [
                'model' => $this->groqModel,
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu adalah chatbot POS. Jawab hanya berdasarkan data yang diberikan. Jangan mengarang. Jawab dengan gaya sopan dan jelas. Gunakan field "quantity" untuk menghitung jumlah unit yang terjual. Jangan mengira 1 baris = 1 unit.'],
                    ['role' => 'system', 'content' => $context],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.3,
                'max_tokens' => 5000,
            ]);

        return $response['choices'][0]['message']['content'] ?? "Maaf, saya tidak bisa menjawab saat ini.";
    }
}
