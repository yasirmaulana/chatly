<?php

namespace App\Livewire;

use Livewire\Component;

class ChatBox extends Component
{
    public $message = '';
    public $chats = [];

    public function send()
    {
        if (trim($this->message) === '') return;

        // tambah pesan user ke array chats
        $this->chats[] = ['sender' => 'user', 'text' => $this->message];

        // simulasi response bot
        $this->chats[] = ['sender' => 'bot', 'text' => 'saya sedang memproses pesan anda...'];

        // kosongkan input message
        $this->message = '';
    }


    public function render()
    {
        return view('livewire.chat-box');
    }
}
