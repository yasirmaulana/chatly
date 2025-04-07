<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ChatBox extends Component
{
    public $message = '';
    public $chats = [];

    public function send()
    {
        if (trim($this->message) === '') return;

        $userMessage = $this->message;
        \Log::info('Pesan dari user: ' . $userMessage);

        // tambah pesan user ke array chats
        $this->chats[] = ['sender' => 'user', 'text' => $userMessage];
        $this->message = '';

        // loading sementara
        $this->chats[] = ['sender' => 'bot', 'text' => 'loading...'];

        // panggil groq api
        $response = Http::withToken(env('GROQ_API_KEY'))
            ->post(env('GROQ_API_URL'), [
                'model' => env('GROQ_MODEL'),
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu adalah chatbot pintar bernama Chatly, menjawab dengan bahasa Indonesia.'],
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

        \Log::info('Groq response: ' . $response->body());
        
        $botReply = $response->json('choices.0.message.content') ?? 'Maaf, tidak bisa menjawab.';

        // hapus pesan loading
        array_pop($this->chats);

        $this->chats[] = ['sender' => 'bot', 'text' => $botReply];
    }


    public function render()
    {
        return view('livewire.chat-box');
    }
}
