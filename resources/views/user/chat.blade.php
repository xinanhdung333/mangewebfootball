@extends('layouts.app')
@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap');
:root{--orange:#ff6b1a;--blue:#2563eb;--dark:#0f172a;--line:#eef0f6;--bg:#f5f6fb;--muted:#94a3b8}
*{font-family:'Inter',system-ui}
.container-fluid.py-4{background:var(--bg)!important; min-height:100vh; padding:24px 16px!important}
.row.mb-4 h1{font-weight:800; font-size:20px; letter-spacing:-.02em}

/* LAYOUT CHÍNH */
.chat-layout{display:grid; grid-template-columns: 1fr 360px; gap:20px; max-width:1280px; margin:0 auto}
@media(max-width:992px){.chat-layout{grid-template-columns:1fr}}

/* CARD CHAT */
.card{border:1px solid var(--line)!important; border-radius:20px!important; overflow:hidden; box-shadow:0 16px 40px rgba(15,23,42,.07)!important; background:#fff}
.card-header{background:#fff!important; border-bottom:1px solid var(--line)!important; padding:14px 18px!important}
#assigned-admin-avatar{width:42px!important; height:42px!important; border:2px solid #fff!important; box-shadow:0 0 0 2px var(--orange)!important}
#assigned-admin-name{font-weight:700!important; font-size:14.5px!important}
#assigned-admin-status{font-size:12px!important; color:var(--muted)!important}
#assigned-admin-status::before{content:''; display:inline-block; width:7px; height:7px; background:#22c55e; border-radius:50%; margin-right:6px}

#user-chat-body{background:radial-gradient(#e9ecf5 1px,transparent 1px) 0 0/22px 22px,#f8f9fc!important; padding:18px!important}
#user-chat-body.bg-primary{background:var(--blue)!important; border-radius:18px 18px 4px 18px!important; box-shadow:0 6px 16px rgba(37,99,235,.2)!important; padding:10px 14px!important; font-size:14px}
#user-chat-body.bg-light{background:#fff!important; border:1px solid var(--line)!important; border-radius:18px 18px 18px 4px!important; padding:10px 14px!important; font-size:14px}
#user-chat-body.text-xs{font-size:11px!important; opacity:.7; margin-top:5px!important}
.card-footer{background:#fff!important; border-top:1px solid var(--line)!important; padding:10px 12px!important}
#chat-form.input-group{background:#f1f3f9; border-radius:999px; padding:4px; border:1px solid #e8ecf5}
#chat-file-button{border:none!important; background:transparent!important; color:#94a3b8!important}
#chat-form.form-control{background:transparent!important; border:none!important; box-shadow:none!important; font-size:14px!important}
#chat-form.btn-primary{background:var(--orange)!important; border:none!important; border-radius:999px!important; font-weight:700!important; padding:8px 18px!important}

/* SIDEBAR ADS */
.ads-sidebar{display:flex; flex-direction:column; gap:16px; position:sticky; top:84px; height:fit-content}
.ad-card{background:#fff; border:1px solid var(--line); border-radius:16px; padding:16px; box-shadow:0 8px 24px rgba(15,23,42,.04)}
.ad-card.orange{background:linear-gradient(135deg,#ff6b1a 0%,#ff8c42 100%); color:#fff; border:none}
.ad-card.dark{background:var(--dark); color:#fff; border:none}
.ad-card h6{font-weight:700; font-size:14px; margin:0 0 6px}
.ad-card p{font-size:12.5px; opacity:.9; line-height:1.5; margin:0 0 12px}
.ad-card.btn-white{background:#fff; color:var(--orange); border-radius:999px; font-weight:700; font-size:13px; padding:7px 14px; border:none}
.ad-product{display:flex; gap:10px; align-items:center; padding:10px 0; border-bottom:1px solid var(--line)}
.ad-product:last-child{border:none}
.ad-product img{width:52px; height:52px; border-radius:10px; object-fit:cover; background:#f1f5f9}
.ad-product.price{color:var(--orange); font-weight:700; font-size:13px}

@media(max-width:992px){.ads-sidebar{position:static; display:grid; grid-template-columns:1fr 1fr; } }
@media(max-width:576px){.ads-sidebar{grid-template-columns:1fr} #user-chat-body [style*="max-width"]{max-width:86%!important} }
</style>

<div class="container-fluid py-4">
    <div class="row mb-3"><div class="col-12"><h1><i class="bi bi-chat-dots"></i> Chat với Admin</h1></div></div>

    <div class="chat-layout">
        {{-- LEFT: CHAT --}}
        <div class="col-left">
            <div class="card">
                <div class="card-header bg-white border-bottom py-3">
                    <div id="user-chat-admin-info" class="d-flex align-items-center gap-3">
                        @if($conversation->admin)
                            <img id="assigned-admin-avatar" src="{{ $conversation->admin->avt? asset('uploads/avatars/'.$conversation->admin->avt) : asset('assets/images/default.png') }}" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                            <div>
                                <h5 id="assigned-admin-name" class="mb-0">Chat với {{ $conversation->admin->name }}</h5>
                                <small id="assigned-admin-status" class="text-muted">Đã được gán cho admin này</small>
                            </div>
                        @else
                            <div id="assigned-admin-placeholder" class="text-muted">Chờ admin bất kỳ trả lời...</div>
                        @endif
                    </div>
                </div>

                <div id="user-chat-body" class="card-body" style="min-height:420px; max-height:600px; overflow-y:auto;">
                    @foreach($messages as $message)
                        @php $senderAvatar = $message->sender && $message->sender->avt? asset('uploads/avatars/'.$message->sender->avt) : asset('assets/images/default.png'); @endphp
                        @if($message->sender_id === auth()->id())
                            <div class="mb-3"><div class="d-flex justify-content-end"><div class="bg-primary text-white p-3 rounded" style="max-width:80%;">@if($message->message!=='')<div>{{ $message->message }}</div>@endif @include('chat._message_attachment',['message'=>$message])<div class="text-end text-xs text-light mt-1">{{ $message->created_at->format('H:i d/m/Y') }}</div></div></div></div>
                        @else
                            <div class="mb-3"><div class="d-flex justify-content-start align-items-start"><img src="{{ $senderAvatar }}" class="rounded-circle me-2" style="width:36px;height:36px;object-fit:cover;"><div class="bg-light p-3 rounded" style="max-width:80%;"><strong>Admin:</strong>@if($message->message!=='')<div>{{ $message->message }}</div>@endif @include('chat._message_attachment',['message'=>$message])<div class="text-end text-xs text-muted mt-1">{{ $message->created_at->format('H:i d/m/Y') }}</div></div></div></div>
                        @endif
                    @endforeach
                </div>

                <div class="card-footer">
                    <form id="chat-form" action="{{ route('user.chat.send') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="attachment" id="chat-attachment" class="d-none">
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" id="chat-file-button"><i class="bi bi-paperclip"></i></button>
                            <input type="text" name="message" class="form-control" placeholder="Nhập tin nhắn..." required>
                            <button class="btn btn-primary" type="submit">Gửi</button>
                        </div>
                        <div id="chat-file-preview" class="small text-muted mt-2 d-none"></div>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT: QUẢNG CÁO --}}
        <div class="ads-sidebar">
            <div class="ad-card orange">
                <h6>🔥 FLASH SALE 50%</h6>
                <p>Giày chạy bộ Nike Air - chỉ hôm nay, freeship toàn quốc</p>
                <button class="btn-white">Mua ngay →</button>
            </div>

            <div class="ad-card dark">
                <h6>Bộ sưu tập mới 2026</h6>
                <p>Ra mắt BST SportsHub x Adidas - đặt trước giảm 20%</p>
                <div style="background:rgba(255,255,255,.12); border-radius:10px; height:90px; display:grid; place-items:center; font-size:12px; margin-top:8px">Banner 360x120</div>
            </div>

            <div class="ad-card">
                <h6>⚡️ Gợi ý cho bạn</h6>
                <div class="ad-product"><img src="{{ asset('assets/images/default.png') }}"><div><div style="font-size:13px; font-weight:600">Giày bóng rổ Jordan</div><div class="price">1.850.000đ <span style="text-decoration:line-through; color:#94a3b8; font-weight:400; font-size:11px">2.5tr</span></div></div></div>
                <div class="ad-product"><img src="{{ asset('assets/images/default.png') }}"><div><div style="font-size:13px; font-weight:600">Bóng đá Adidas Pro</div><div class="price">450.000đ</div></div></div>
            </div>

            <div class="ad-card" style="background:#f8fafc">
                <h6>💬 Hỗ trợ nhanh</h6>
                <p style="color:#64748b">Cần tư vấn size, đổi trả? Chat ngay hoặc gọi 1900 9999</p>
                <small style="font-size:11px; color:#94a3b8">✓ Chính hãng 100% &nbsp; ✓ Đổi trả 30 ngày</small>
            </div>
        </div>
    </div>
</div>

{{-- GIỮ NGUYÊN JS CŨ CỦA BẠN --}}
@push('scripts')
<script>
    const userConversationId = {{ $conversation->id }}; const currentUserId = {{ auth()->id() }};
    const userChatBody = document.getElementById('user-chat-body'); const chatForm = document.getElementById('chat-form');
    const messageInput = chatForm.querySelector('[name="message"]'); const attachmentInput = document.getElementById('chat-attachment');
    const fileButton = document.getElementById('chat-file-button'); const filePreview = document.getElementById('chat-file-preview');
    const csrfToken = chatForm.querySelector('[name="_token"]').value; const wsEndpoint = @json(config('services.websocket.endpoint'));
    const currentUserAvatar = '{{ auth()->user()->avt? asset('uploads/avatars/'.auth()->user()->avt) : asset('assets/images/default.png') }}';
    const defaultAvatar = '{{ asset('assets/images/default.png') }}'; const pendingMessages = []; messageInput.required=false;
    const userWs = new WebSocket(wsEndpoint);
    function escapeHtml(v){return String(v||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]))}
    function renderAttachment(m){if(!m.attachment_url&&!m.attachment_name)return'';const n=escapeHtml(m.attachment_name||'File');if(!m.attachment_url)return`<div class="small mt-2"><i class="bi bi-paperclip"></i> ${n}</div>`;const u=encodeURI(m.attachment_url);if(m.attachment_is_image)return`<a href="${u}" target="_blank" class="d-block mt-2"><img src="${u}" class="img-fluid rounded" style="max-height:220px"></a>`;return`<a href="${u}" target="_blank" class="btn btn-sm btn-light border mt-2"><i class="bi bi-download"></i> Tai file: ${n}</a>`}
    function renderMessageBody(m,p=''){return`${p}${m.message?`<div>${escapeHtml(m.message)}</div>`:''}${renderAttachment(m)}`}
    function appendMessage(msg,pending=false){
        if(msg.id&&!msg.id.toString().startsWith('temp')){if(userChatBody.querySelector(`[data-message-id="${msg.id}"]`))return;}
        const w=document.createElement('div'); const isMe=msg.sender_id===currentUserId; const av=msg.sender_avatar||(isMe?currentUserAvatar:defaultAvatar);
        w.className='mb-3'; if(pending){w.dataset.tempId=msg.id}else if(msg.id){w.dataset.messageId=msg.id;}
        w.innerHTML=`<div class="d-flex ${isMe?'justify-content-end':'justify-content-start align-items-start'}">${isMe?'':`<img src="${av}" class="rounded-circle me-2" style="width:36px;height:36px">`}<div class="${isMe?'bg-primary text-white':'bg-light'} p-3 rounded" style="max-width:80%"><div class="message-content">${renderMessageBody(msg,isMe?'':'<strong>Admin:</strong> ')}</div><div class="text-end text-xs ${isMe?'text-light':'text-muted'} mt-1">${msg.created_at}</div></div></div>`;
        userChatBody.appendChild(w); userChatBody.scrollTop=userChatBody.scrollHeight;
    }
    function resolvePendingMessage(m){const i=pendingMessages.findIndex(p=>p.clientTempId===m.client_temp_id); if(i===-1)return false; const p=pendingMessages[i]; const ex=userChatBody.querySelector(`[data-temp-id="${p.tempId}"]`); if(ex){ex.dataset.messageId=m.id; ex.removeAttribute('data-temp-id'); const ts=ex.querySelector('.text-xs'); if(ts) ts.textContent=m.created_at; const ct=ex.querySelector('.message-content'); if(ct) ct.innerHTML=renderMessageBody(m); pendingMessages.splice(i,1); return true;} pendingMessages.splice(i,1); return false;}
    fileButton.addEventListener('click',()=>attachmentInput.click());
    attachmentInput.addEventListener('change',()=>{const f=attachmentInput.files[0]; if(!f){filePreview.classList.add('d-none'); return;} filePreview.classList.remove('d-none'); filePreview.textContent=`Đã chọn: ${f.name}`});
    chatForm.addEventListener('submit',async e=>{e.preventDefault(); const txt=messageInput.value.trim(); const file=attachmentInput.files[0]||null; if(!txt&&!file)return; const temp='temp-'+Date.now(); const now=new Date(); const local=now.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'})+' '+now.toLocaleDateString('en-GB'); const pend={id:temp,conversation_id:userConversationId,sender_id:currentUserId,message:txt,attachment_name:file?file.name:null,attachment_url:null,attachment_is_image:file?file.type.startsWith('image/'):false,created_at:local}; pendingMessages.push({tempId:temp,clientTempId:temp}); appendMessage(pend,true); const fd=new FormData(chatForm); fd.set('message',txt); fd.set('client_temp_id',temp); try{const r=await fetch(chatForm.action,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken},body:fd}); const d=await r.json(); if(d.status==='ok')resolvePendingMessage(d.message); messageInput.value=''; attachmentInput.value=''; filePreview.classList.add('d-none');}catch(e){console.error(e)}});
    userWs.addEventListener('open',()=>userWs.send(JSON.stringify({type:'subscribe',conversation_id:userConversationId})));
    userWs.addEventListener('message',ev=>{try{const data=JSON.parse(ev.data); if(data.type!=='message'||data.conversation_id!==userConversationId)return; if(data.message.sender_id===currentUserId&&resolvePendingMessage(data.message))return; appendMessage(data.message);}catch{}});
    setTimeout(()=>{userChatBody.scrollTop=userChatBody.scrollHeight},100);
</script>
@endpush
@endsection