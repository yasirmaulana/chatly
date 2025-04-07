<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            $transactions = $this->queryFilteredTransactions($userId, $filters);
            // dd($transactions);
            $finalPrompt = $this->generatePromptFromFilteredData($message, $transactions);

            return $this->askGroq($finalPrompt);
        } catch (\Exception $e) {
            Log::error('ChatService error: ' . $e->getMessage());
            return "Maaf, terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi nanti.";
        }
    }

    public function parseUserIntent(string $message): array
    {
        $prompt = <<<PROMPT
Kamu adalah asisten AI yang menerjemahkan pesan pengguna menjadi filter query transaksi.

Jawaban HARUS dalam format JSON berikut:
{
  "date_filter": "today | yesterday | last_week | this_month | last_month | custom",
  "item_name": "..."
  "start_date": "YYYY-MM-DD",
  "end_date": "YYYY-MM-DD"
}

- Gunakan "date_filter" hanya dari daftar yang tersedia.
- Jika pengguna menyebutkan bulan tertentu (misalnya "April 2024"), konversi ke date_filter yang paling cocok (misalnya: "this_month" jika saat ini April 2024, atau "last_month" jika sudah lewat).
- start_date dan end_date diisi jika date_filter adalah "custom", jika bukan, isi null.
- Jika pengguna menyebutkan rentang tanggal, isi start_date dan end_date sesuai dengan rentang tersebut dan date_filter = custom.
- Jika pengguna tidak menyebutkan item, isi "item_name": null.
- Ubah item_name menjadi huruf kecil (lowercase), kecuali jika item_name diapit tanda petik dua ("). Jika ada tanda petik dua, gunakan isi di dalam tanda petik tersebut sebagai item_name tanpa diubah.
- Jika pengguna menyebutkan lebih dari satu item, ambil yang pertama.
- Jangan ubah format, hanya keluarkan JSON valid.

Contoh input:
"Saya belanja makanan minggu lalu"
Output:
{
  "date_filter": "last_week",
  "item_name": "makanan",
  "start_date": null,
  "end_date": null
}

Contoh input:
"tanggal berapa saja saya "Beli Pulsa" di bulan april 2024?"
Output:
{
  "date_filter": "this_month",
  "item_name": "Beli Pulsa",
  "start_date": null,
  "end_date": null
}

Input pengguna:
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
            case 'today':
                $query->whereDate('transaction_date', today());
                break;
            case 'yesterday':
                $query->whereDate('transaction_date', now()->subDay());
                break;
            case 'last_week':
                $query->whereBetween('transaction_date', [
                    now()->subWeek()->startOfWeek(),
                    now()->subWeek()->endOfWeek()
                ]);
                break;
            case 'this_month':
                $query->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
                break;
            case 'last_month':
                $query->whereMonth('transaction_date', now()->subMonth()->month)
                    ->whereYear('transaction_date', now()->subMonth()->year);
                break;
            case 'custome':
                $query->whereBetween('transaction_date', [
                    $filters['start_date'],
                    $filters['end_date']
                ]);
                break;
        }

        if (!empty($filters['item_name'])) {
            $query->where('item_name', $filters['item_name']);
        }

        return $query->orderByDesc('transaction_date')->limit(20)->get();
    }

    public function generatePromptFromFilteredData(string $userMessage, Collection $transactions): string
    {
        $summary = "Berikut ini adalah data transaksi yang relevan:\n";

        foreach ($transactions as $tx) {
            $summary .= "- {$tx->transaction_date->format('Y-m-d')}: {$tx->item_name} seharga Rp" .
                number_format($tx->amount) . "\n";
        }

        return $summary . "\n\nPertanyaan user:\n" . $userMessage;
    }

    public function askGroq(string $prompt): string
    {
        $response = Http::withToken($this->groqApiKey)
            ->post($this->groqApiUrl, [
                'model' => $this->groqModel,
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu adalah chatbot keuangan pribadi. Jawablah sesuai data.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
                'max_tokens' => 5000,
            ]);

        return $response['choices'][0]['message']['content'] ?? "Maaf, saya tidak bisa menjawab saat ini.";
    }
}
