{{-- <div class="max-w-3xl mx-auto">
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
</div> --}}
<div class="max-w-2xl mx-auto p-4 bg-white rounded shadow">

    <div class="h-96 overflow-y-auto mb-4 border p-2 rounded bg-gray-50">
        @foreach ($chats as $chat)
            <div class="mb-2 {{ $chat['sender'] === 'user' ? 'text-right' : 'text-left' }}">
                <span
                    class="inline-block px-3 py-2 rounded 
                    {{ $chat['sender'] === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                    {{ $chat['message'] }}
                </span>
            </div>
        @endforeach
        <!-- Spinner -->
        <div wire:loading wire:target="send" class="text-center my-4">
            <div class="flex justify-center items-center space-x-2">
                <div class="w-4 h-4 bg-blue-500 rounded-full animate-bounce"></div>
                <div class="w-4 h-4 bg-blue-500 rounded-full animate-bounce [animation-delay:.2s]"></div>
                <div class="w-4 h-4 bg-blue-500 rounded-full animate-bounce [animation-delay:.4s]"></div>
            </div>
            <p class="text-sm text-gray-500 mt-2">Chatly sedang mengetik...</p>
        </div>
    </div>

    <form wire:submit.prevent="send" class="flex gap-2">
        <input type="text" wire:model.defer="message" class="flex-1 border rounded px-3 py-2"
            placeholder="Ketik pesan...">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Kirim
        </button>
    </form>
</div>
