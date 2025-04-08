<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;

class ContactForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $message = '';
    public bool $success = false;

    public function submit()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'message' => 'required|min:5',
        ]);

        // Kirim email / simpan ke database sesuai kebutuhan
        Mail::raw($this->message, function ($mail) {
            $mail->to('yasir.maulana@gmail.com')
                 ->subject("Pesan dari {$this->name} ({$this->email})");
        });

        $this->reset(['name', 'email', 'message']);
        $this->success = true;
    }
    
    public function render()
    {
        return view('livewire.contact-form');
    }
}
