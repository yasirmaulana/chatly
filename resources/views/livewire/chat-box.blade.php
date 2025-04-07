<div class="max-w-3xl mx-auto">
    <div class="chat-container bg-white rounded-lg p-4 shadow mb-4 h-[50vh] overflow-y-auto">
        @foreach ($chats as $chat)
            <div class="chat-message mb-3 flex {{ $chat['sender'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div
                    class="chat-bubble px-4 py-2 rounded-2xl max-w-[75%] {{ $chat['sender'] === 'user' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                    {{ $chat['text'] }}
                </div>
            </div>
        @endforeach
    </div>

    <form wire:submit.prevent="send" class="flex gap-2">
        <input type="text"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Tanyakan sesuatu..." wire:model.defer="message">
        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition">
            Kirim
        </button>
    </form>
</div>
