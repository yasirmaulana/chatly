<?php

namespace App\Livewire;

use Auth;
use App\Models\Chat;
use Livewire\Component;
use App\Services\ChatService;

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

        $userId = Auth::id();
        $userMessage = trim($this->message);

        $this->chats[] = ['sender' => 'user', 'message' => $userMessage];
        Chat::create([
            'user_id' => $userId,
            'sender' => 'user',
            'message' => $userMessage,
        ]);

        $this->message = '';

        $botReply = app(ChatService::class)->handleUserMessage($userId, $userMessage);

        $this->chats[] = ['sender' => 'bot', 'message' => $botReply];
        Chat::create([
            'user_id' => $userId,
            'sender' => 'bot',
            'message' => $botReply,
        ]);

        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.chat-box');
    }
}
