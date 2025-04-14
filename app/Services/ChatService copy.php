<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Transaction;
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
            $transactions = $this->queryFilteredTransactions($userId, $filters);
            // dd($transactions);
            $context = $this->generateContextFromTransactions($transactions);
            // dd($finalPrompt);

            return $this->askGroqWithContext($context, $message);
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
        Kamu adalah AI yang mengubah pesan pengguna menjadi filter transaksi. Jawaban HARUS berupa JSON VALID dan HANYA JSON, sesuai format ini:

        {
            "date_filter": "hari_ini | kemarin | minggu_lalu | bulan_ini | bulan_kemarin | custom | riwayat",
            "item_name": "string atau null",
            "start_date": "YYYY-MM-DD",
            "end_date": "YYYY-MM-DD",
            "amount": "integer atau null",
            "flag": "> | < | = ",
        }

        Hari ini adalah: {$today}
        Bulan saat ini adalah: {$currentMonth}
        Tahun saat ini adalah: {$currentYear}

        Aturan:
        - Gunakan HANYA nilai date_filter dari daftar.
        - Tahun saat ini adalah {$currentYear}. Semua tanggal harus berada di tahun ini, kecuali jika pesan pengguna menyebutkan tahun lain.
        - Jika disebut rentang tanggal atau tanggal tertentu, isi start_date & end_date, dan set date_filter = "custom" atau "riwayat".
        - Jika tidak disebutkan rentang tanggal atau tanggal tertentu dalam pesan, tentukan start_date dan end_date berdasarkan nilai date_filter:
        - "hari_ini": isi start_date dan end_date dengan tanggal hari ini ({$today}).
        - "kemarin": isi start_date dan end_date dengan tanggal kemarin (1 hari sebelum {$today}).
        - "minggu_lalu": isi start_date dan end_date dengan hari Senin hingga Minggu pada minggu lalu (berdasarkan tanggal hari ini).
        - "bulan_ini": isi start_date dengan tanggal 1 bulan ini, dan end_date dengan tanggal hari ini (semua di tahun ini).
        - "bulan_kemarin": isi start_date dengan tanggal 1 bulan lalu, dan end_date dengan tanggal terakhir bulan lalu (di tahun ini).
        - Jika disebutkan angka tertentu atau nominal text, isi amount dengan angka tersebut, misalnya ditulis:
        - "400ribu" = 400000
        - "400.000" = 400000
        - "lima ribu" = 5000 | "lima ratus ribu" = 500000 | "lima juta" = 5000000
        - Jika disebutkan "lebih dari", "kurang dari", "sama dengan", atau "lebih besar dari", isi flag dengan operator yang sesuai.
        - Jika item tidak disebut, isi "item_name": null.

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
        return json_decode($content, true) ?? ['date_filter' => 'all_time', 'item_name' => null];
    }

    public function queryFilteredTransactions(int $userId, array $filters): Collection
    {
        $query = Transaction::where('user_id', $userId);
        switch ($filters['date_filter']) {
            case 'hari_ini':
                $query->whereDate('transaction_date', today());
                break;

            case 'kemarin':
                $query->whereDate('transaction_date', now()->subDay());
                break;

            case 'minggu_lalu':
            case 'bulan_ini':
            case 'bulan_kemarin':
            case 'custom':
            case 'riwayat':
                $query->whereBetween('transaction_date', [
                    Carbon::parse($filters['start_date'])->startOfDay(),
                    Carbon::parse($filters['end_date'])->endOfDay()
                ]);
                break;
        }


        if (!empty($filters['item_name'])) {
            $query->where('item_name', $filters['item_name']);
        }

        if (!empty($filters['amount']) && !empty($filters['flag'])) {
            $query->where('amount', $filters['flag'], $filters['amount']);
        }

        // \Log::debug('Querying transactions', [
        //     'sql' => $query->toSql(),
        //     'bindings' => $query->getBindings(),
        // ]);

        return $query
            ->orderBy('item_name', 'asc')
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    public function generateContextFromTransactions(Collection $transactions): string
    {
        if ($transactions->isEmpty()) {
            return "Tidak ada transaksi yang ditemukan untuk filter ini.";
        }

        $context = "Berikut ini adalah data transaksi user:\n";

        foreach ($transactions as $tx) {
            $context .= "- {$tx->transaction_date->format('Y-m-d')}: {$tx->item_name} seharga Rp " . number_format($tx->amount) . "\n";
        }

        return $context;
    }

    public function askGroqWithContext(string $context, string $userMessage): string
    {
        // dd($prompt);

        $response = Http::withToken($this->groqApiKey)
            ->post($this->groqApiUrl, [
                'model' => $this->groqModel,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Kamu adalah chatbot keuangan pribadi. Jawablah hanya berdasarkan data yang diberikan sebelumnya. Jangan menebak. Jika data tidak tersedia, jawab dengan jujur. Gaya bahasa: sopan, formal, dan profesional. Gunakan bahasa Indonesia yang baik dan benar.'
                    ],
                    [
                        'role' => 'assistant',
                        'content' => $context
                    ],
                    [
                        'role' => 'user', 
                        'content' => $userMessage
                    ],
                ],
                'temperature' => 0.3,
                'max_tokens' => 5000,
            ]);

        return $response['choices'][0]['message']['content'] ?? "Maaf, saya tidak bisa menjawab saat ini.";
    }
}
