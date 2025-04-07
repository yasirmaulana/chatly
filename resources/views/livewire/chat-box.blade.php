<div class="p-4 space-y-4">
    <div class="overflow-y-auto h-[400px] space-y-2">
        @foreach ($chats as $chat)
            <div class="flex {{ $chat['sender'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-lg px-4 py-2 rounded-2xl shadow-md
                    {{ $chat['sender'] === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                    
                    {{-- Jika pesan bot mengandung konteks transaksi --}}
                    @if ($chat['sender'] === 'bot' && Str::contains($chat['message'], 'Berikut adalah') && Str::contains($chat['message'], 'transaksi'))
                        <div class="mb-2 font-semibold">📊 Riwayat Transaksi:</div>
                        <table class="text-sm table-auto border border-gray-300">
                            <thead>
                                <tr>
                                    <th class="px-2 py-1 border">Tanggal</th>
                                    <th class="px-2 py-1 border">Item</th>
                                    <th class="px-2 py-1 border">Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (explode("\n", $chat['message']) as $line)
                                    @if (Str::startsWith(trim($line), '-'))
                                        @php
                                            preg_match('/- (\d{4}-\d{2}-\d{2}): (.+) seharga Rp(\d+)/', $line, $matches);
                                        @endphp
                                        @if (count($matches) === 4)
                                            <tr>
                                                <td class="px-2 py-1 border">{{ $matches[1] }}</td>
                                                <td class="px-2 py-1 border">{{ $matches[2] }}</td>
                                                <td class="px-2 py-1 border">Rp{{ number_format($matches[3], 0, ',', '.') }}</td>
                                            </tr>
                                        @endif
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        {{-- Tampilkan pesan biasa --}}
                        {!! nl2br(e($chat['message'])) !!}
                    @endif
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
