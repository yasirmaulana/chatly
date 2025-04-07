<?php

namespace App\Livewire;

use App\Models\Chat;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Auth;

class ChatBox extends Component
{
    public $message = '';
    public $chats = [];
    public $isLoading = false;

    public function mount()
    {
        $this->chats = Chat::where('user_id', Auth::id())->orderBy('created_at')->get()->toArray();
    }

    public function send()
    {
        // if (trim($this->message) === '') return;
        $this->validate(['message' => 'required|string']);

        $this->isLoading = true;

        $userMessage = trim($this->message);
        // \Log::info('Pesan dari user: ' . $userMessage);

        $this->chats[] = ['sender' => 'user', 'message' => $userMessage];

        Chat::create([
            'user_id' => Auth::id(),
            'sender' => 'user',
            'message' => $userMessage,
        ]);

        $this->message = '';

        $botReply = $this->getGroqReply($userMessage);

        // $response = Http::withToken(env('GROQ_API_KEY'))
        //     ->post(env('GROQ_API_URL'), [
        //         'model' => env('GROQ_MODEL'),
        //         'messages' => [
        //             ['role' => 'system', 'content' => 'Kamu adalah chatbot pintar bernama Chatly, menjawab dengan bahasa Indonesia.'],
        //             ['role' => 'user', 'content' => $userMessage],
        //         ],
        //     ]);

        // $botReply = $response->json('choices.0.message.content') ?? 'Maaf, tidak bisa menjawab.';

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
        try {
            $response = Http::withToken(config('services.groq.key'))
                ->timeout(10)
                ->post(config('services.groq.url'), [
                    'model' => config('services.groq.model'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'Kamu adalah chatbot pintar bernama Chatly sebagai asiten AI, menjawab dengan bahasa Indonesia.'],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 512,
                ]);

            // \Log::info('Groq response: ' . $response->body());

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }

            return 'Maaf, sistem sedang sibuk. Silakan coba lagi nanti.';
        } catch (\Exception $e) {
            return 'Terjadi kesalahan saat menghubungi Chatly.';
        }
    }

    public function render()
    {
        return view('livewire.chat-box');
    }
}
