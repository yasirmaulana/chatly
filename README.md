# 🤖 Chatly

Chatly is a smart AI chatbot built with [Laravel](https://laravel.com/), [Livewire](https://livewire.laravel.com/), and powered by [Groq](https://groq.com/) for blazing fast generative AI responses. It connects directly to your PostgreSQL transaction tables and lets users ask questions in natural language — and get answers directly from the data.

## ✨ Features

- 🔍 Ask natural-language questions based on your PostgreSQL data
- ⚡ Powered by Groq for ultra-fast LLM responses
- 💬 Real-time chat interface with Livewire
- 📊 Smart insight delivery (total, summary, trends, etc)
- 📚 Chat history & query logging

## 📦 Tech Stack

- Laravel 12
- Livewire 3
- PostgreSQL
- Groq LLM API
- TailwindCSS
- Redis

## 🖥️ UI Preview

Coming soon...

## 🚀 Getting Started

```bash
git clone https://github.com/yourusername/chatly.git
cd chatly
cp .env.example .env
composer install
npm install && npm run dev
php artisan migrate
php artisan serve
```

## ⚙️ Configuration

- Set your Groq API key in .env:

```bash
GROQ_API_KEY=your-api-key-here
GROQ_API_URL=https://api.groq.com/openai/v1/chat/completions
GROQ_MODEL=llama3-70b-8192 #llama-3.3-70b-versatile
```

- Configure your database as needed.

## 🤝 Contribution

Feel free to submit pull requests, open issues or suggest features!

## 📄 License

MIT License
