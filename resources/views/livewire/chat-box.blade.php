<div>
    <div class="chat-container bg-white rounded p-4 shadow-sm mb-3">
        @foreach($chats as $chat)
            <div class="chat-message {{ $chat['sender'] }}">
                <div class="chat-bubble {{ $chat['sender'] }}">
                    {{ $chat['text'] }}
                </div>
            </div>
        @endforeach
    </div>

    <form wire:submit.prevent="send" class="d-flex gap-2">
        <input type="text" class="form-control" placeholder="Tanyakan sesuatu..." wire:model.defer="message">
        <button type="submit" class="btn btn-primary">Kirim</button>
    </form>
</div>
