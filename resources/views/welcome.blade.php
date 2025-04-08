<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Chatly</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>

</head>

<body class="antialiased font-sans">
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 flex flex-col">
        {{-- Header --}}
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    <img src="{{ asset('assets/img/chatly.webp') }}" alt="Chatly Logo" width="40"
                        class="rounded mx-auto ">
                </h1>
                <div class="flex items-center space-x-4">
                    @if (Route::has('login'))
                        <livewire:welcome.navigation />
                    @endif
                    {{-- <button id="toggle-dark" class="text-sm text-blue-600 dark:text-blue-300 hover:underline">🌓 Mode
                        Gelap</button> --}}
                </div>
            </div>
        </header>

        {{-- Hero Section --}}
        <section
            class="flex-grow py-20 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-gray-800 dark:to-gray-700 animate__animated animate__fadeInUp">
            <div class="max-w-5xl mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold mb-4 text-blue-700 dark:text-white">Asisten Keuangan Pintar</h2>
                <p class="text-lg mb-8 text-gray-700 dark:text-gray-300">Tanyakan apa pun soal transaksi, Chatly siap
                    bantu!</p>
                <a href="/chat"
                    class="bg-blue-600 text-white px-6 py-3 rounded-full shadow hover:bg-blue-700 transition">Mulai
                    Chat</a>
            </div>
        </section>

        {{-- Features Section --}}
        <section id="features" class="py-16 bg-white dark:bg-gray-800">
            <div class="max-w-6xl mx-auto px-6">
                <h3 class="text-2xl font-bold text-center mb-10 text-blue-600 dark:text-white">Fitur Chatly</h3>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-blue-50 dark:bg-gray-700 p-6 rounded-xl shadow">
                        <h4 class="font-semibold text-xl mb-2 text-blue-700 dark:text-white">Cek Pengeluaran</h4>
                        <p class="text-gray-600 dark:text-gray-300">Tanya detail transaksi seperti "Pengeluaran ngopi
                            bulan ini?"</p>
                    </div>
                    <div class="bg-blue-50 dark:bg-gray-700 p-6 rounded-xl shadow">
                        <h4 class="font-semibold text-xl mb-2 text-blue-700 dark:text-white">Filter Pintar</h4>
                        <p class="text-gray-600 dark:text-gray-300">Gunakan filter tanggal, nama barang, atau nominal
                            dengan mudah.</p>
                    </div>
                    <div class="bg-blue-50 dark:bg-gray-700 p-6 rounded-xl shadow">
                        <h4 class="font-semibold text-xl mb-2 text-blue-700 dark:text-white">Jawaban Natural</h4>
                        <p class="text-gray-600 dark:text-gray-300">Chatly memberikan respon seperti teman ngobrol
                            biasa.</p>
                    </div>
                </div>
            </div>
        </section>
        {{-- Integration Section --}}
        <section class="py-16 bg-white border-t border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-6 text-center">
                <h2 class="text-3xl font-bold mb-4">Integrasi Mudah dengan Sistem Anda

                    <span class="px-4 py-2 bg-red-100 text-red-800 rounded-full text-sm">Cooming Soon</span>
                </h2>

                <p class="text-lg mb-8 text-gray-600">Chatly dapat diintegrasikan dengan berbagai sistem backend atau
                    aplikasi lain melalui REST API, Webhook, atau SDK yang disediakan. Cocok untuk kebutuhan bisnis dan
                    personalisasi yang lebih luas.</p>
                <div class="flex justify-center flex-wrap gap-6 mt-6">
                    <span class="px-4 py-2 bg-indigo-100 text-indigo-800 rounded-full text-sm">REST API</span>
                    <span class="px-4 py-2 bg-purple-100 text-purple-800 rounded-full text-sm">Webhook</span>
                    <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm">SDK Integrasi</span>
                    {{-- <span class="px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm">OpenAPI</span> --}}
                </div>
            </div>
        </section>

        {{-- Custom Services Section --}}
        <section class="py-16 bg-gray-50 border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-6 text-center">
                <h2 class="text-3xl font-bold mb-4">Layanan Custom Sesuai Kebutuhan Anda</h2>
                <p class="text-lg mb-8 text-gray-600">Butuh fitur khusus? Kami siap membantu Anda mengembangkan Chatly
                    sesuai kebutuhan bisnis atau sistem Anda.</p>
                <div class="grid md:grid-cols-3 gap-8 text-left">
                    <div class="bg-white rounded-xl shadow p-6">
                        <h3 class="text-xl font-semibold mb-2 text-indigo-600">Integrasi Data Khusus</h3>
                        <p class="text-gray-700">Kami dapat membantu mengintegrasikan data dari sumber eksternal seperti
                            API pihak ketiga, sistem ERP, atau database perusahaan Anda.</p>
                    </div>
                    <div class="bg-white rounded-xl shadow p-6">
                        <h3 class="text-xl font-semibold mb-2 text-indigo-600">Kustomisasi Prompt & NLP</h3>
                        <p class="text-gray-700">Sesuaikan cara Chatly menjawab pertanyaan agar sesuai dengan gaya
                            bicara dan kebijakan perusahaan Anda.</p>
                    </div>
                    <div class="bg-white rounded-xl shadow p-6">
                        <h3 class="text-xl font-semibold mb-2 text-indigo-600">Branding & Tema</h3>
                        <p class="text-gray-700">Kami menyediakan layanan white-labeling, logo, warna, dan nama produk
                            bisa disesuaikan sesuai identitas brand Anda.</p>
                    </div>
                </div>

                <div class="mt-10">
                    <a href="http://wa.me/6281586245143" target="_blank"
                        class="inline-block px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition">Hubungi
                        Kami</a>
                </div>
            </div>
        </section>

        {{-- Get Started --}}
        <section id="get-started" class="py-20 bg-gradient-to-tr from-blue-600 to-blue-500 text-white text-center">
            <h3 class="text-3xl font-bold mb-4">Siap ngobrol dengan Chatly?</h3>
            <p class="mb-8">Mulai chat dan kelola keuanganmu dengan lebih mudah.</p>
            <a href="/chat"
                class="bg-white text-blue-600 px-6 py-3 rounded-full font-semibold hover:bg-gray-100 transition">💬
                Mulai Sekarang</a>
        </section>

        {{-- Footer --}}
        <footer class="bg-white dark:bg-gray-800 text-center py-6 text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} Chatly. Dibuat dengan ❤️ oleh Muhammad Yasir Maulana.
        </footer>

        {{-- Floating chat button --}}
        <a href="/chat"
            class="fixed bottom-6 right-6 bg-blue-600 text-white p-4 rounded-full shadow-lg hover:bg-blue-700 transition md:hidden z-50">
            💬
        </a>

        {{-- Dark Mode Script --}}
        <script>
            const toggleBtn = document.getElementById('toggle-dark');
            toggleBtn.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
            });
        </script>

        {{-- Animate.css --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    </div>
</body>

</html>
