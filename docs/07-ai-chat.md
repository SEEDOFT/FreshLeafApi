# AI Assistant

## Overview

AI-powered shopping assistant with real-time streaming responses. Supports multiple AI providers (Ollama, Gemini) with hybrid web search capability.

## Components

| Component | Path | Description |
|-----------|------|-------------|
| **AI Chat Controller** | `app/Http/Controllers/Api/Ai/AiChatController.php` | Session/message handling |
| **AI Service** | `app/Services/Ai/AiService.php` | Main AI service |
| **Ollama Service** | `app/Services/Ai/OllamaService.php` | Local Ollama provider |
| **Gemini Service** | `app/Services/Ai/GeminiService.php` | Google Gemini provider |
| **Web Search Service** | `app/Services/Ai/WebSearchService.php` | Web search for hybrid AI |
| **AI Provider Contract** | `app/Services/Contracts/AiProviderContract.php` | Provider interface |
| **AI Chat Model** | `app/Models/AiChatSession.php` | Session storage |
| **AI Message Model** | `app/Models/AiChatMessage.php` | Message storage |

### Events (Real-time)

| Event | Path | Description |
|-------|------|-------------|
| AiMessageStarted | `app/Events/AiMessageStarted.php` | Start of AI response |
| AiMessageChunk | `app/Events/AiMessageChunk.php` | Streaming chunk |
| AiMessageCompleted | `app/Events/AiMessageCompleted.php` | Response complete |
| AiMessageFailed | `app/Events/AiMessageFailed.php` | Response failed |

### Livewire Component

| Component | Path | Description |
|-----------|------|-------------|
| AiAssistantChat | `app/Livewire/AiAssistantChat.php` | Admin AI chat UI |

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|--------------|
| POST | `/api/v1/ai/chat/sessions` | Create/reuse chat session |
| POST | `/api/v1/ai/chat/messages` | Send message (starts stream) |
| POST | `/api/v1/ai/chat/history` | Load message history |

## Real-time Flow

### 1. Create Session
```
POST /api/v1/ai/chat/sessions
→ Creates or returns existing session
→ Returns session_id
```

### 2. Subscribe to Channel
```
Channel: private-ai-chat.{userId}.{sessionId}
```

### 3. Send Message
```
POST /api/v1/ai/chat/messages
→ Server starts streaming response
→ Events dispatched: Started → Chunk(s) → Completed/Failed
```

### 4. Events Received
- `AiMessageStarted` - AI started responding (contains message_id)
- `AiMessageChunk` - Streaming text chunks
- `AiMessageCompleted` - Full response ready
- `AiMessageFailed` - Error occurred

## Database Tables

### ai_chat_sessions
Chat sessions:
- session_id (ULID)
- user_id (owner)
- title
- last_message_at

### ai_chat_messages
Chat messages:
- session_id
- role (user/assistant)
- message_id (for tracking streaming)
- content
- status (pending, streaming, completed, failed)

## AI Providers

### Ollama (Default - Local)
- Runs locally using llama.cpp
- Model: qwen2.5:1.5b (configurable via OLLAMA_MODEL)
- No external API calls
- Fast for local development

### Gemini (Cloud)
- Google Gemini API
- Requires GEMINI_API_KEY
- More powerful models

### Hybrid Mode
- Uses WebSearchService for current information
- Combines search results with AI responses

## Configuration

```env
# .env
AI_PROVIDER=ollama  # or 'gemini'
OLLAMA_MODEL=qwen2.5:1.5b
GEMINI_API_KEY=your_key
```

Config: `config/ai.php`

## Related Files

- `config/ai.php` - AI configuration
- `app/Filament/Pages/AiAssistant.php` - Admin AI page
- `routes/channels.php` - WebSocket channel definitions
- `routes/ai.php` - AI-specific routes