<div class="bg-white py-12 px-6 rounded-xl shadow-md max-w-xl mx-auto">
    <h2 class="text-2xl font-bold text-center mb-6">Kontak Langsung</h2>

    @if ($success)
        <div class="mb-4 text-green-600 font-semibold">
            Pesan Anda telah dikirim! Terima kasih.
        </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
            <input wire:model.defer="name" type="text" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input wire:model.defer="email" type="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="message" class="block text-sm font-medium text-gray-700">Pesan</label>
            <textarea wire:model.defer="message" id="message" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            @error('message') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
            Kirim Pesan
        </button>
    </form>
</div>
