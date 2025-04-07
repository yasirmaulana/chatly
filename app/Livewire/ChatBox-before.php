<?php

namespace App\Livewire;

use Auth;
use App\Models\Chat;
use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ChatBox extends Component
{
    public $message = '';
    public $chats = [];
    public $isLoading = false;

    public function mount()
    {
        $this->chats = Chat::where('user_id', Auth::id())
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    public function send()
    {
        $this->validate(['message' => 'required|string']);

        $this->isLoading = true;

        $userMessage = trim($this->message);
        \Log::info('Pesan dari user: ' . $userMessage);

        $this->chats[] = ['sender' => 'user', 'message' => $userMessage];

        Chat::create([
            'user_id' => Auth::id(),
            'sender' => 'user',
            'message' => $userMessage,
        ]);

        $this->message = '';

        $botReply = $this->getGroqReply($userMessage);

        $this->chats[] = ['sender' => 'bot', 'message' => $botReply];

        Chat::create([
            'user_id' => Auth::id(),
            'sender' => 'bot',
            'message' => $botReply,
        ]);

        $this->isLoading = false;
    }

    public function getGroqReply($userMessage)
    {
        $context = $this->getTransactionContext(Auth::id());

        try {
            $response = Http::withToken(config('services.groq.key'))
                ->timeout(10)
                ->post(config('services.groq.url'), [
                    'model' => config('services.groq.model'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'Kamu adalah chatbot pintar bernama Chatly sebagai asiten AI, menjawab dengan bahasa Indonesia.' . "\n\n $context"],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 5000,
                ]);

            \Log::info('Groq response: ' . $response->body());

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }

            return 'Maaf, sistem sedang sibuk. Silakan coba lagi nanti.';
        } catch (\Exception $e) {
            return 'Terjadi kesalahan saat menghubungi Chatly.';
        }
    }

    public function getTransactionContext(int $userId): string
    {
        $cacheKey = "chatly_context_user_$userId";

        return Cache::remember($cacheKey, 120, function () use ($userId) {
            $transactions = Transaction::where('user_id', $userId)
                ->orderByDesc('transaction_date')
                ->limit(10)
                ->get();

            $summary = "Berikut adalah 10 transaksi terakhir Anda:\n";

            foreach ($transactions as $tx) {
                $summary .= "- {$tx->transaction_date->format('Y-m-d')}: {$tx->item_name} seharga Rp{$tx->amount}\n";
            }

            return $summary;
        });
    }

    public function render()
    {
        return view('livewire.chat-box');
    }

}
