<div class="p-4 space-y-4" x-data x-init="$watch('Livewire.entangle(\'chats\')', () => {
    $nextTick(() => {
        let el = document.querySelector('.overflow-y-auto');
        if (el) el.scrollTop = el.scrollHeight;
    });
})">

    <div class="overflow-y-auto h-[400px] space-y-2">
        @foreach ($chats as $chat)
            <div class="flex {{ $chat['sender'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div
                    class="max-w-lg px-4 py-2 rounded-2xl shadow-md
                    {{ $chat['sender'] === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                    {!! nl2br(e($chat['message'])) !!}
                </div>
            </div>
        @endforeach

        {{-- Loading spinner --}}
        @if ($isLoading)
            <div class="flex justify-start">
                <div class="flex items-center space-x-2 text-gray-500">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-gray-500"></div>
                    <span>Chatly sedang berpikir...</span>
                </div>
            </div>
        @endif
    </div>

    <form wire:submit.prevent="send" class="flex space-x-2">
        <input type="text" wire:model="message" class="flex-1 px-4 py-2 border rounded-xl focus:outline-none"
            placeholder="Tulis pesan...">
        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600">Kirim</button>
    </form>
</div>
