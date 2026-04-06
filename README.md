<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# FreshLeaf API

A Laravel 13 REST API with payment integrations (Stripe, PayPal) and backend-driven AI chat streaming through Laravel Reverb (REST + private WebSocket channels).

## Features

- **Payment Processing**: Stripe & PayPal sandbox integration
- **AI Chat**: Session-based AI chat over REST + Reverb private channels
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

# Start development (all services)
composer run dev

# Or with custom Laravel server host/port
composer run dev -- --host=192.168.0.108 --port=9000
```

## AI Chat Flow (Flutter)

1. `POST /api/v1/ai/chat/sessions` to create / reuse a session.
2. Subscribe to `private-ai-chat.{userId}.{sessionId}`.
3. `POST /api/v1/ai/chat/messages` to send user prompt.
4. Stream UI updates from: `AiMessageStarted`, `AiMessageChunk`, `AiMessageCompleted`, `AiMessageFailed`.
5. Hydrate on cold start with `POST /api/v1/ai/chat/history`.

## Environment Variables

```env
# Reverb server runtime bind
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# Reverb public host/port for clients
REVERB_HOST=127.0.0.1
REVERB_PORT=8080

# Flutter runtime keys (dart-define)
REVERB_APP_KEY=your_reverb_app_key
REVERB_WS_SCHEME=ws
REVERB_WS_HOST=127.0.0.1
REVERB_WS_PORT=8080
REVERB_AUTH_ENDPOINT=/broadcasting/auth

# Stripe
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# PayPal
PAYPAL_CLIENT_ID=...
PAYPAL_SECRET=...
PAYPAL_MODE=sandbox

# Gemini AI
GEMINI_API_KEY=...
GEMINI_MODEL=gemini-2.0-flash

# AI provider selection
AI_PROVIDER=gemini
AI_FALLBACK_PROVIDERS=zen,ollama

# OpenCode Zen (free tier)
ZEN_API_KEY=...
ZEN_BASE_URL=https://opencode.ai/zen/v1
ZEN_MODEL=minimax-m2.5-free
ZEN_TIMEOUT=40

# Ollama (local AI)
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=qwen2.5:1.5b
OLLAMA_TIMEOUT=60
```

AI uses Google AI Studio API key mode (Gemini REST API) on the backend. No Vertex ADC / service account is required.

## Lightweight Local AI (Free)

Recommended lightweight local model: `qwen2.5:1.5b`.

```bash
# Install Ollama, then pull a lightweight model
ollama pull qwen2.5:1.5b

# Run local Ollama server (default http://127.0.0.1:11434)
ollama serve
```

To switch backend provider to local AI:

```env
AI_PROVIDER=ollama
AI_FALLBACK_PROVIDERS=gemini
```

To use OpenCode Zen free tier first:

```env
AI_PROVIDER=zen
AI_FALLBACK_PROVIDERS=gemini,ollama
ZEN_API_KEY=your_zen_api_key
ZEN_MODEL=minimax-m2.5-free
```

## API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/register` | Register user |
| POST | `/api/v1/auth/login` | Login (get token) |
| POST | `/api/v1/auth/logout` | Logout (require auth) |

### Payments (require auth)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/users/payments/intent` | Create Stripe payment intent |
| POST | `/api/v1/users/payments/confirm` | Confirm Stripe payment |
| POST | `/api/v1/users/payments/paypal/order` | Create PayPal order |
| POST | `/api/v1/users/payments/paypal/capture` | Capture PayPal payment |

### AI Chat (require auth)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/ai/chat/sessions` | Create / reuse chat session |
| POST | `/api/v1/ai/chat/messages` | Submit user message (queues AI job) |
| POST | `/api/v1/ai/chat/history` | Load message history |

### Broadcast Auth (require auth)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/broadcasting/auth` | Authorize private channel subscription |

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
