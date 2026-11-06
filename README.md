# MinTour — AI-Powered Bilingual Tourism Platform

MinTour is a Laravel 12 application for tourism information and destination promotion in Victoria, Oriental Mindoro. It includes public destination pages, an interactive map, user accounts, reviews/favorites, an admin dashboard, a knowledge base, and a bilingual Groq-powered chatbot.

## Requirements

- PHP 8.2+
- Composer
- Node.js 20+ and npm
- MySQL 8+ (recommended for production because the knowledge base uses MySQL FULLTEXT search)
- Laragon works well on Windows

## Laragon setup

1. Extract the ZIP into `C:\laragon\www\mintour`.
2. Start Apache/Nginx and MySQL from Laragon.
3. Create a MySQL database named `mintour`.
4. Open a terminal in the project directory.
5. Run:

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run dev
```

Open `http://mintour.test` if Laragon is configured for auto virtual hosts, or use the URL shown by Laragon.

The standard database seed imports the 27 public Victoria listings from `victoria_dataset.json` into destinations, accommodations, restaurants, events, and the chatbot knowledge base. To re-import after updating that file, run:

```bash
php artisan db:seed --class=VictoriaPortalSeeder
```

## Deploying to Render

This repository's main application is Laravel. Deploy it as a **Docker** web service, not as the separate Node API:

1. Push the repository, including `Dockerfile`, `.dockerignore`, and `docker/entrypoint.sh`, to GitHub.
2. In Render, create a Web Service for this repository and select **Docker** as the language.
3. Leave **Root Directory**, **Build Command**, and **Start Command** blank. Render uses the root `Dockerfile`; it builds the Vite assets and starts Apache on Render's `PORT`.
4. Create a production MySQL database with an external provider and make sure it accepts connections from Render. Render's managed database offering is PostgreSQL; this app currently requires MySQL. Add these environment variables to the web service:

   | Key | Value |
   | --- | --- |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_KEY` | Generate with `php artisan key:generate --show` |
   | `APP_URL` | Your Render service URL, including `https://` |
   | `DB_CONNECTION` | `mysql` |
   | `DB_HOST` | MySQL host |
   | `DB_PORT` | MySQL port |
   | `DB_DATABASE` | MySQL database name |
   | `DB_USERNAME` | MySQL username |
   | `DB_PASSWORD` | MySQL password |
   | `GROQ_API_KEY` | Optional; required for the chatbot |

   Keep secrets in Render's environment settings, not in Git. The container runs `php artisan migrate --force` at startup.

Render's free service filesystem is temporary. Admin-uploaded images may be lost when the service restarts or deploys; use object storage or a persistent disk for uploads. Do not seed production with the demo accounts and passwords in `RoleUsersSeeder`.

## Admin login

The seed creates the account configured by:

```env
Mintour_ADMIN_EMAIL=admin@mintour.local
Mintour_ADMIN_PASSWORD=admin123
```

Change both values in `.env` before using the system beyond local development.

## Groq chatbot

Create a Groq API key at [Groq Console](https://console.groq.com/keys), then add it to `.env`:

```env
GROQ_API_KEY=your_key_here
GROQ_MODEL=openai/gpt-oss-20b
```

If you previously configured a different Groq model in the admin settings, update it there to `openai/gpt-oss-20b`. Then run:

```bash
php artisan config:clear
```

The chatbot combines live, admin-maintained tourism records from the MinTour database (destinations, accommodations, restaurants, events, products, announcements, FAQs, and emergency contacts) with published Travel Oriental Mindoro portal listings and a small set of MinTour directory/map records. Live database records are treated as the most current source for operational details, while portal records are source-attributed and local directory/map records may be outdated. Sources are labelled in the prompt, and other manually added articles are not used for recommendations.

The assistant answers English, Filipino, and Taglish questions, follows up on clear short references (for example, "Magkano?" after asking about a place), and uses general LLM knowledge for stable general, geographic, and cultural questions while clearly separating that from verified Victoria tourism information. New questions with their own subject do not inherit unrelated search context from the previous turn. It avoids inventing local prices, schedules, contacts, and other specifics, says when a detail is not listed, and returns a helpful fallback when local tourism information is unavailable. Provider errors such as an invalid key, unavailable model, quota limit, or network issue are shown separately from missing tourism information. Do not put API keys in source code or share them in chat; use a private key in the ignored `.env` file.

## Useful commands

```bash
php artisan migrate:fresh --seed
php artisan storage:link
php artisan optimize:clear
php artisan mintour:doctor
php artisan test
npm run dev
npm run build
```

## Important content note

Victoria destination, accommodation, and food listings in the demo seeder are based on mapped places, not official endorsements. Map listings and the available photos can be out of date; confirm a venue is operating, check current rates and opening hours, and verify access details before travelling. The Naujan Lake entry is a nearby day trip outside Victoria. Wikimedia Commons image credits and license details are included with the relevant destination descriptions.

## Structure

- `app/Models` — Eloquent models
- `app/Http/Controllers` — public, admin, auth and API controllers
- `app/Services` — chatbot/RAG services
- `database/migrations` — database schema
- `database/seeders` — demo data
- `resources/views` — Blade UI
- `routes` — web/admin/API routes
- `public` — Laravel entry point
- `resources/js` + `resources/css` — Vite/Tailwind frontend

## Notes on the supplied build

The original specification contained several "representative" controller/view sections rather than concrete files. This ZIP fills those structural gaps with working controllers/views so every declared route has a target. It also includes cache/job migrations because the supplied `.env.example` uses database-backed cache and queues.

For production, use HTTPS, a strong admin password, a private Groq key, verified tourism data, and real image assets.
