<!DOCTYPE html>
<html>
<head>
    <title>Chatbot</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<h2>Chat với Shop</h2>

<input type="text" id="message" placeholder="Nhập câu hỏi..." />

<button onclick="sendMessage()">Gửi</button>

<p id="reply"></p>

<script>

async function sendMessage() {

    let message = document.getElementById("message").value;

    let response = await fetch("/chatbot/message", {

        method: "POST",

        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },

        body: JSON.stringify({
            message: message
        })

    });

    let data = await response.json();

    document.getElementById("reply").innerText = data.reply;

}

</script>

</body>
</html>

