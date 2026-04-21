# Docker Setup Guide - Football Booking Platform

## Cấu trúc Docker

Project sử dụng Docker Compose với các services sau:

- **app**: PHP 8.2 FPM - Application backend
- **nginx**: Nginx web server (port 80)
- **mysql**: MySQL 8.0 database
- **redis**: Redis cache
- **websocket**: Node.js WebSocket server (port 8080)
- **node**: Node.js Vite development server (port 5173)

## Yêu cầu

- Docker Desktop (hoặc Docker Engine + Docker Compose)
- Min 4GB RAM available
- Min 5GB disk space

## Cách chạy

### 1. Chuẩn bị môi trường

```bash
# Copy .env file
cp .env.example .env

# Hoặc tạo .env với các cấu hình cần thiết
```

### 2. Build và khởi động Docker

```bash
# Build images và start containers
docker-compose up -d

# Hoặc build từ đầu
docker-compose up -d --build
```

### 3. Setup Laravel

```bash
# Install PHP dependencies
docker-compose exec app composer install

# Generate app key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Seed database (optional)
docker-compose exec app php artisan db:seed
```

### 4. Install Frontend Dependencies

```bash
# Install npm packages
docker-compose exec node npm install

# Hoặc build trực tiếp
docker-compose exec node npm run build
```

## Truy cập dịch vụ

- **Web Application**: http://localhost
- **Vite Dev Server**: http://localhost:5173
- **WebSocket Server**: ws://localhost:8080
- **MySQL**: localhost:3306
  - User: `football`
  - Password: `password`
  - Database: `football`
- **Redis**: localhost:6379

## Các lệnh thường dùng

```bash
# Xem logs
docker-compose logs -f app

# Chạy Artisan commands
docker-compose exec app php artisan {command}

# Chạy Tinker
docker-compose exec app php artisan tinker

# Bash vào container
docker-compose exec app bash

# Stop services
docker-compose down

# Xóa volumes (database)
docker-compose down -v

# Rebuild services
docker-compose up -d --build
```

## Troubleshooting

### Lỗi: "Port 80 already in use"
```bash
# Thay đổi port trong docker-compose.yml
# Thay "80:80" thành "8000:80"
```

### Lỗi: "Permission denied"
```bash
# Fix permissions trên Windows không cần, nhưng trên Linux:
sudo chown -R $USER:$USER .
sudo chmod -R 755 storage bootstrap/cache
```

### Database connection failed
```bash
# Đảm bảo MySQL container đang chạy
docker-compose ps

# Check MySQL logs
docker-compose logs mysql

# Restart MySQL
docker-compose restart mysql
```

### Node modules issues
```bash
# Clear node_modules
docker-compose exec node rm -rf node_modules package-lock.json

# Reinstall
docker-compose exec node npm install
```

## File cấu hình

- `Dockerfile` - PHP 8.2 FPM application image
- `docker-compose.yml` - Services orchestration
- `.dockerignore` - Files to exclude from Docker build
- `docker/nginx/conf.d/default.conf` - Nginx configuration
- `docker/mysql/my.cnf` - MySQL configuration
- `.env` - Environment variables (create từ .env.example)

## Development Workflow

1. **Backend Development**: Edit Laravel files, container sẽ tự reload
2. **Frontend Development**: Chỉnh sửa files, Vite sẽ tự rebuild (http://localhost:5173)
3. **Database Changes**: Sử dụng `php artisan make:migration` và `php artisan migrate`
4. **WebSocket**: Chạy trong container `websocket`

## Production Deployment

Để deploy, tạo một `docker-compose.prod.yml`:

```yaml
# Ví dụ cấu hình production
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    environment:
      APP_ENV: production
      APP_DEBUG: 'false'
    # ... thêm cấu hình production
```

```bash
docker-compose -f docker-compose.prod.yml up -d
```
