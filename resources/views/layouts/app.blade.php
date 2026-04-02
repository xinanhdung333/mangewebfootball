

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" sizes="256x256" href="{{ asset('assets/images/logo.jpg') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($page_title) ? $page_title . ' - ' . config('app.name') : config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <style>
        .chat-header {
            background: #007bff;
            color: white;
            padding: 10px;
        }
        .admin-nav {
            display: flex;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
            align-items: center;
        }
        .admin-nav li a {
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            transition: 0.2s;
            display: block;
        }
        .admin-nav li a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        #mascot {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 140px;
            max-width: 90px;
            height: auto;
            cursor: grab;
            z-index: 9999;
        }

        html,
        body {
            width: 100%;
            overflow-x: hidden;
        }

        #chat-box {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 300px;
            max-width: 90vw;
            height: 400px;
            background: white;
            border: 1px solid #ccc;
            display: none;
            z-index: 9999;
        }

        .chat-body {
            height: calc(100% - 92px);
            overflow-y: auto;
            padding: 12px;
        }

        #message,
        #chat-box button {
            width: 100%;
            box-sizing: border-box;
        }

        #message {
            margin: 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-top: none;
        }

        #chat-box button {
            border: none;
            background: #007bff;
            color: #fff;
            padding: 10px;
        }

        @media (max-width: 991px) {
            .navbar-nav {
                flex-direction: column;
                width: 100%;
            }

            .navbar-nav .nav-item,
            .navbar-nav > li {
                width: 100%;
            }

            .navbar-nav .nav-link {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
                width: 100%;
            }

            .dropdown-menu {
                width: 100%;
            }

            .container-fluid,
            .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            #mascot {
                width: 70px;
                right: 10px;
                bottom: 10px;
            }

            #chat-box {
                right: 10px;
                left: 10px;
                width: auto;
                max-width: calc(100% - 20px);
                height: auto;
                max-height: 70vh;
            }

            .chat-body {
                height: auto;
                max-height: calc(70vh - 92px);
            }
        }

        .desktop-layout {
            width: 100%;
            transform-origin: top center;
        }

        @media (max-width: 991px) {
            .desktop-layout {
                transform: scale(0.85);
            }
        }

        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1.1rem;
            }

            .navbar-toggler {
                margin-left: auto;
            }

            .navbar-nav .nav-link {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            .container-fluid,
            .container {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            main.container {
                padding-top: 0.5rem;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="desktop-layout">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
    @if(auth()->check() && auth()->user()->role === 'boss')
      <a class="navbar-brand" href="{{ route('boss.home') }}">
    <i class="bi bi-dribbble"></i> Football Booking
</a>
    @elseif(auth()->check() && auth()->user()->role === 'admin')
      <a class="navbar-brand" href="{{ route('admin.home') }}">
    <i class="bi bi-dribbble"></i> Football Booking
</a>
    @else
      <a class="navbar-brand" href="{{ route('home') }}">
    <i class="bi bi-dribbble"></i> Football Booking
</a>
   @endif

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                @auth
                    @if(auth()->user()->role === 'user')
                        <li class="nav-item"><a class="nav-link" href="{{ route('user.dashboard') }}"><i class="bi bi-house"></i> Trang chủ</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('user.fields') }}"><i class="bi bi-grid"></i> Sân</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('user.myBookings') }}"><i class="bi bi-calendar"></i> Sân đã đặt</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('user.services') }}"><i class="bi bi-bag"></i> Dịch vụ</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('user.myServices') }}"><i class="bi bi-bag-check"></i> Dịch vụ đã mua</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('cart.index') }}"><i class="bi bi-cart-fill"></i> Giỏ hàng</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('user.feedback') }}"><i class="bi bi-chat-dots"></i> Feedback</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('about') }}"><i class="bi bi-chat-dots"></i> About</a></li>
                    @endif

                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.manage.fields') }}"><i class="bi bi-gear"></i> Quản lý sân</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.manage.bookings') }}"><i class="bi bi-clipboard-check"></i> Quản lý đặt sân</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.manage.services') }}"><i class="bi bi-grid"></i> Quản lý dịch vụ</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.user.service.history') }}"><i class="bi bi-bag-check"></i> Chi tiết mua hàng</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.manage.feedback') }}"><i class="bi bi-chat-dots"></i> Quản lý Feedback</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.invoices') }}"><i class="bi bi-file-earmark-pdf"></i> Quản lý hóa đơn</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.statistics') }}"><i class="bi bi-bar-chart"></i> Thống kê</a></li>
                    @endif
                    @if(auth()->user()->role === 'boss')
                        <li class="nav-item"><a class="nav-link" href="{{ route('boss.manage.fields') }}"><i class="bi bi-gear"></i> Quản lý sân</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('boss.manage.bookings') }}"><i class="bi bi-clipboard-check"></i> Quản lý đặt sân</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('boss.manage.services') }}"><i class="bi bi-grid"></i> Quản lý dịch vụ</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('boss.user.service.history') }}"><i class="bi bi-bag-check"></i> Chi tiết mua hàng</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('boss.manage.feedback') }}"><i class="bi bi-chat-dots"></i> Quản lý Feedback</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('boss.invoices') }}"><i class="bi bi-file-earmark-pdf"></i> Quản lý hóa đơn</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('boss.manage.users') }}"><i class="bi bi-people"></i> Quản lý người dùng</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('boss.statistics') }}"><i class="bi bi-bar-chart"></i> Thống kê</a></li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('boss.profile') }}">Hồ sơ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('logout') }}">Đăng xuất</a></li>
                        </ul>
                    </li>
                @else
               <li class="nav-item"><a class="nav-link" href="{{ route('visitor.dashboard') }}"><i class="bi bi-house"></i> Trang chủ</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('visitor.fields') }}"><i class="bi bi-grid"></i> Sân</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('myServices') }}"><i class="bi bi-bag"></i> Dịch vụ</a></li>
                         <li class="nav-item"><a class="nav-link" href="{{ route('visitor.feedback') }}"><i class="bi bi-chat-dots"></i> Feedback</a></li>
                                                  <li class="nav-item"><a class="nav-link" href="{{ route('about') }}"><i class="bi bi-chat-dots"></i>About</a></li>

                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}"><i class="bi bi-person-plus"></i> Đăng ký</a></li>

                @endauth
            </ul>
        </div>
    </div>
</nav>
<img class = "chatbot" src="{{ asset('assets/images/chatbot.png') }}" id="mascot" alt="Mascot">

<div id="chat-box">
    <div class="chat-header">
        Chat hỗ trợ
        <span id="close-chat">✖</span>
    </div>

    <div class="chat-body" id="chat-body">
    Xin chào 👋
</div>

<input type="text" id="message">

<button onclick="sendMessage()">Gửi</button>
</div>

<main class="container mt-4">
    <div class="container-fluid px-4">

    @yield('content')
    </div>
</main>

@include('partials.visitor.footer')

<script>
const mascot = document.getElementById('mascot');
const chatBox = document.getElementById('chat-box');
const closeChat = document.getElementById('close-chat');

let isDragging = false;
let offsetX = 0, offsetY = 0;

// click mở chat
mascot.addEventListener('click', () => {
    chatBox.style.display = 'block';
});

// kéo mascot
mascot.addEventListener('mousedown', (e) => {
    isDragging = true;
    offsetX = e.clientX - mascot.offsetLeft;
    offsetY = e.clientY - mascot.offsetTop;
    mascot.style.cursor = 'grabbing';
});

document.addEventListener('mouseup', () => {
    isDragging = false;
    mascot.style.cursor = 'grab';
});

document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;

    mascot.style.left = (e.clientX - offsetX) + 'px';
    mascot.style.top  = (e.clientY - offsetY) + 'px';
});

// đóng chat
closeChat.addEventListener('click', () => {
    chatBox.style.display = 'none';
});
</script>
<script>
function sendMessage()
{
    fetch('/chat/send', {

        method:'POST',

        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },

        body:JSON.stringify({

            conversation_id:1,
            message:document.getElementById('message').value

        })

    });
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.2.0/pusher.min.js"></script>
<script>
Pusher.logToConsole = true;

var pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {
    cluster: "ap1"
});
var conversationId = 1;
var channel = pusher.subscribe('chat.' + conversationId);
channel.bind('MessageSent', function(data) {

    let chatBody = document.querySelector(".chat-body");

    chatBody.innerHTML +=
        `<div>${data.message.message}</div>`;

});
</script>
</div>
@stack('scripts')
</body>
</html>