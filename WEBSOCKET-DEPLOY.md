# Hướng dẫn bật WebSocket trên production

Tài liệu này áp dụng cho ứng dụng tại:

```text
/var/www/myapp/mangewebfootball
```

WebSocket Node chạy nội bộ ở port `6001`. Trình duyệt kết nối qua Nginx bằng
địa chỉ `wss://.../ws`.

## 1. Kiểm tra server và mã nguồn

Đăng nhập server rồi chạy:

```bash
cd /var/www/myapp/mangewebfootball
node --version
npm --version
git status
git pull origin main
npm install --omit=dev --no-audit --no-fund
```

Đảm bảo package `ws` đã được cài:

```bash
npm ls ws
```

## 2. Cấu hình `.env` production

Mở file `.env`:

```bash
nano /var/www/myapp/mangewebfootball/.env
```

Đặt các biến sau. Thay domain nếu domain production thực tế khác:

```env
WS_SERVER_URL=http://127.0.0.1:6001
WS_ENDPOINT=wss://sporthub.tiengion333.trade/ws
WS_HOST=127.0.0.1
WS_PORT=6001
```

`WS_SERVER_URL` dùng để Laravel gọi WebSocket nội bộ. `WS_ENDPOINT` dùng cho
trình duyệt, vì vậy không được dùng `127.0.0.1` ở biến này.

## 3. Tạo systemd service

Tạo service:

```bash
sudo nano /etc/systemd/system/football-websocket.service
```

Nội dung:

```ini
[Unit]
Description=SportsHub WebSocket Server
After=network.target

[Service]
Type=simple
User=hadoop
WorkingDirectory=/var/www/myapp/mangewebfootball
Environment=NODE_ENV=production
Environment=WS_HOST=127.0.0.1
Environment=WS_PORT=6001
ExecStart=/usr/bin/node /var/www/myapp/mangewebfootball/websocket-server.js
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Nếu `node` không nằm ở `/usr/bin/node`, lấy đường dẫn thật bằng:

```bash
which node
```

Sau đó thay đường dẫn trong `ExecStart`.

Kích hoạt service:

```bash
sudo systemctl daemon-reload
sudo systemctl enable football-websocket
sudo systemctl restart football-websocket
sudo systemctl status football-websocket --no-pager
```

Xem log khi service lỗi:

```bash
sudo journalctl -u football-websocket -n 100 --no-pager
```

## 4. Kiểm tra WebSocket nội bộ

Phải thấy Node lắng nghe port `6001`:

```bash
sudo ss -tulnp | grep 6001
```

Kiểm tra health endpoint:

```bash
curl --fail http://127.0.0.1:6001/health
```

Kết quả mong đợi:

```json
{"status":"ok"}
```

## 5. Cấu hình Nginx reverse proxy

Mở file cấu hình domain đang dùng:

```bash
sudo nginx -T | less
```

Trong `server` block của domain, thêm:

```nginx
location /ws {
    proxy_pass http://127.0.0.1:6001;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_read_timeout 86400;
    proxy_send_timeout 86400;
}
```

Kiểm tra và tải lại Nginx:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

Nếu `nginx -t` báo lỗi, không reload cho đến khi sửa xong lỗi cấu hình.

## 6. Làm mới cache Laravel

Sau khi sửa `.env`:

```bash
cd /var/www/myapp/mangewebfootball
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 7. Kiểm tra workflow deploy

Workflow [.github/workflows/deploy.yml](.github/workflows/deploy.yml) đã được
cập nhật để:

1. Cài package Node production.
2. Chạy migration và cache Laravel.
3. Restart `football-websocket`.
4. Gọi `/health` để xác nhận WebSocket hoạt động.

Vì vậy service `football-websocket` phải được tạo trước lần deploy tự động đầu
tiên. Nếu service chưa tồn tại, bước `systemctl restart` sẽ làm workflow thất
bại.

## 8. Kiểm tra từ trình duyệt

Mở trang chat rồi mở Developer Tools → Console. Endpoint phải có dạng:

```text
wss://sporthub.tiengion333.trade/ws
```

Không được xuất hiện:

```text
ws://127.0.0.1:6001
```

Trong Network → WS, kết nối phải có trạng thái `101 Switching Protocols`.

## 9. Checklist hoàn thành

- [ ] `npm ls ws` không báo thiếu package.
- [ ] `.env` có `WS_SERVER_URL`, `WS_ENDPOINT`, `WS_HOST`, `WS_PORT`.
- [ ] `football-websocket.service` ở trạng thái `active (running)`.
- [ ] Port `6001` đang được Node lắng nghe.
- [ ] `curl http://127.0.0.1:6001/health` trả về `{"status":"ok"}`.
- [ ] `sudo nginx -t` thành công.
- [ ] Nginx có proxy `/ws` với header Upgrade.
- [ ] Laravel đã được clear/cache lại.
- [ ] Browser kết nối tới `wss://<domain>/ws` với mã `101`.
- [ ] Gửi tin nhắn giữa user và admin nhận realtime.

## Lỗi thường gặp

### Không thấy port 6001

Kiểm tra service và log:

```bash
sudo systemctl status football-websocket --no-pager
sudo journalctl -u football-websocket -n 100 --no-pager
```

### Browser vẫn kết nối `127.0.0.1`

Kiểm tra `WS_ENDPOINT` trong `.env`, sau đó chạy lại:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

Sau đó tải trang bằng `Ctrl + F5`.

### Kết nối WebSocket bị 400/404 hoặc không có mã 101

Kiểm tra `location /ws` trong Nginx, đặc biệt là:

```nginx
proxy_http_version 1.1;
proxy_set_header Upgrade $http_upgrade;
proxy_set_header Connection "Upgrade";
```

### Laravel gửi tin nhưng không realtime

Kiểm tra lần lượt:

```bash
curl --fail http://127.0.0.1:6001/health
sudo systemctl status football-websocket --no-pager
sudo journalctl -u football-websocket -n 100 --no-pager
```

