# Medical Triage Assistant

A lightweight full-stack app for preliminary symptom triage.

- Backend: `Laravel 13` (REST API + auth with Sanctum)
- Frontend: `Vue 3` + `Vue Router` + `Vite`
- AI analysis: `Groq` (with optional `DeepSeek` fallback)
- Context enrichment: external medical sources + OWID insights

## What It Does

- Accepts symptom text and optional image.
- Generates a structured triage response (diagnosis hypothesis, urgency, care plan, red flags).
- Stores diagnosis history for authenticated users.
- Provides profile and portal endpoints with user-level stats.

## Architecture

The backend follows a layered structure:

- `Http/Controllers` - thin HTTP entry points.
- `Http/Requests` - validation per endpoint.
- `Services` + `Contracts` - business use cases.
- `Handlers` - database access and persistence.
- `Application/Diagnosis` - diagnosis pipeline internals:
  - query handler orchestration
  - AI provider client/fallback logic
  - domain/triage analysis
  - source processing
  - prompt building
  - response mapping

Request flow:

`Request -> Controller -> Service -> Handler (DB)`  
For diagnosis:
`AnalyzeDiagnosisQuery -> AnalyzeDiagnosisHandler -> Support components`

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

Run in development:

```bash
composer run dev
```

Or separately:

```bash
php artisan serve
npm run dev
```

## Main API Endpoints

- `POST /api/diagnosis/analyze`
- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`
- `GET /api/history`
- `GET /api/history/{id}`
- `GET /api/profile`
- `PATCH /api/profile/email`
- `PATCH /api/profile/password`
- `GET /api/portal`

## Environment (Key Variables)

- `GROQ_AI_API_KEY`
- `GROQ_AI_MODEL`
- `GROQ_AI_VISION_MODEL` (optional)
- `GROQ_AI_BASE_URL`
- `GROQ_AI_VERIFY_SSL`
- `DEEPSEEK_API_KEY` (optional)
- DB variables (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)

