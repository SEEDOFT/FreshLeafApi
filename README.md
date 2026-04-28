<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# FreshLeaf Organics API (Multi-Vendor Organic Marketplace)

A Laravel 13 REST API powering a multi-vendor organic vegetable marketplace. This backend manages the entire ecosystem:

- **B2C Consumer App**: Mobile application for consumers to browse and buy organic vegetables.
- **Vendor Web App**: A dedicated platform where vendors register their real identity, manage their store profile, and sell products.
- **Admin Management**: System administrators manage the platform, verify vendors, and earn a commission fee from vendors upon each successfully completed sale.

## Features

- **Multi-Vendor Marketplace**: Support for individual vendor stores and product management.
- **Admin Commission System**: Automated tracking of commission fees on completed orders.
- **Payment Processing**: Multi-method support (Wallet, Credit/Debit, ABA, ACLEDA, COD)
- **Wallet System**: Internal wallet tracking with full transaction history and CRUD support
- **AI Chat**: Session-based AI chat with real-time SSE streaming over Laravel Reverb (private WebSocket channels)
- **Offline AI**: Native integration with `llama.cpp` for local testing without external dependencies
- **Authentication**: Laravel Sanctum for API token auth
- **API Versioning**: `/api/v1/` prefix with RESTful endpoints

## Quick Start

```bash
# Install dependencies
composer install

# Setup environment
# macOS / Linux
cp .env.example .env

# Windows (PowerShell)
copy .env.example .env

php artisan key:generate
php artisan migrate

# Optional: Setup Offline AI (llama.cpp)
php ai/setup-ai.php

# Start development (all services)
composer run dev

# Or with custom Laravel server host/port
composer run dev -- --host=192.168.0.108 --port=8000
```

## AI Chat Flow (Flutter)

1. `POST /api/v1/ai/chat/sessions` to create / reuse a session.
2. Subscribe to `private-ai-chat.{userId}.{sessionId}`.
3. `POST /api/v1/ai/chat/messages` to send user prompt.
4. Stream UI updates from: `AiMessageStarted`, `AiMessageChunk` (received in real-time as AI generates), `AiMessageCompleted`, `AiMessageFailed`.
5. Hydrate on cold start with `POST /api/v1/ai/chat/history`.

## Environment Variables

```env
# ... (existing reverb and payment keys)

# AI provider selection: gemini, zen, or llama_cpp
AI_PROVIDER=llama_cpp
AI_FALLBACK_PROVIDERS=zen

# Llama.cpp (Internal Local AI)
LLAMA_CPP_BASE_URL=http://127.0.0.1:9000
LLAMA_CPP_MODEL=phi-3-mini
LLAMA_CPP_TIMEOUT=120
```

## Offline AI (llama.cpp)

This project includes a native integration with `llama.cpp`. Testers can run AI features entirely on their local CPU.

1. Run `php ai/setup-ai.php` to download the engine and Phi-3 model (~2.4GB).
2. Start the AI server:
   - Windows: `ai\start-ai.bat`
   - Mac/Linux: `./ai/start-ai.sh`
3. Set `AI_PROVIDER=llama_cpp` in your `.env`.

## API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/register` | Register user |
| POST | `/api/v1/auth/login` | Login (get token) |
| POST | `/api/v1/auth/logout` | Logout (require auth) |

### Wallet & Transactions (require auth)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/user/wallets` | List own wallets |
| GET | `/api/v1/user/wallets/{id}/histories` | View wallet balance history |
| GET | `/api/v1/user/wallet-transactions` | List all wallet transactions |
| POST | `/api/v1/user/wallet-transactions` | Create a new transaction |
| GET | `/api/v1/user/wallet-transactions/{id}` | View transaction details |
| PATCH | `/api/v1/user/wallet-transactions/{id}` | Update transaction (e.g. status) |
| DELETE | `/api/v1/user/wallet-transactions/{id}` | Delete transaction record |

### Payments (require auth)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/user/payment-methods` | List saved payment methods |
| POST | `/api/v1/user/payment-methods` | Save new payment method |

### AI Chat (require auth)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/ai/chat/sessions` | Create / reuse chat session |
| POST | `/api/v1/ai/chat/messages` | Submit user message (starts real-time stream) |
| POST | `/api/v1/ai/chat/history` | Load message history |

### Broadcast Auth (require auth)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/broadcasting/auth` | Authorize private channel subscription |

The auth request must include your Bearer token and Pusher-compatible payload (`socket_id`, `channel_name`).

### WebSocket Events
- Subscribe to: `private-ai-chat.{userId}.{sessionId}`
- Listen for: `AiMessageStarted`, `AiMessageChunk`, `AiMessageCompleted`, `AiMessageFailed`

## Services Running

| Service | Default | Description |
|---------|---------|-------------|
| Laravel Server | 127.0.0.1:8000 | HTTP API |
| Queue Worker | - | Async job processing |
| Reverb | 127.0.0.1:8080 | WebSocket server |
| Vite | localhost:5173 | Frontend dev server |

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
