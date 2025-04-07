<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chatly - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f3f4f6;
      font-family: 'Poppins', sans-serif;
    }
    .chat-container {
      height: 80vh;
      overflow-y: auto;
    }
    .chat-message {
      margin-bottom: 15px;
    }
    .chat-message.user {
      text-align: right;
    }
    .chat-message.bot {
      text-align: left;
    }
    .chat-bubble {
      display: inline-block;
      padding: 10px 15px;
      border-radius: 20px;
      max-width: 75%;
    }
    .chat-bubble.user {
      background-color: #3A8DFF;
      color: white;
    }
    .chat-bubble.bot {
      background-color: #e2e8f0;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <nav class="col-md-2 d-none d-md-block bg-white shadow-sm min-vh-100">
        <div class="p-3">
          <img src="{{ asset('assets/img/chatly.webp') }}" alt="Chatly Logo" class="rounded-circle shadow" style="height: 50px; width: 50px; object-fit: cover;">
          <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link active" href="#">Chat History</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Query Logs</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Settings</a></li>
          </ul>
        </div>
      </nav>

      <!-- Main content -->
      <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h2 class="mb-4">Ask Chatly</h2>

        @livewire('chat-box')
      
    </main>


    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
