# 🎵 YouTube to MP3 Converter (Self-Hosted)
A premium, self-hosted application to download and convert YouTube videos to high-quality MP3/FLAC audio files. Built with **Laravel 12**, **Vue.js 3**, and **Tailwind CSS**.

![Dashboard Screenshot](https://via.placeholder.com/800x400?text=Dashboard+Preview)
## 📚 Table of Contents
- [About](#about)
- [Important Disclaimer & Terms of Use](#disclaimer)
- [Full Setup](#full-setup)
- [Run the App](#run-the-app)
- [Scheduler Setup](#scheduler-setup)
- [Health Checks & Troubleshooting](#health-checks)

<a id="about"></a>
## ℹ️ About
## ℹ️ About
This project is a self-hosted YouTube audio converter built as a learning-driven passion project. It focuses on practical Laravel queue processing, media conversion workflows (`yt-dlp` + `ffmpeg`), and a modern Vue-based UI for personal experimentation.
<a id="disclaimer"></a>
## ⚠️ Important Disclaimer & Terms of Use
## ⚠️ Important Disclaimer & Terms of Use
This project is a hobbyist experiment and is provided strictly for educational and demonstration purposes. Before exploring or implementing this codebase, please note the following:

- **"Vibe-Coded" Architecture:** Approximately 90% of this codebase was generated using various AI models through "vibe-coding" (natural language prompting). While the logic is functional in the current demo, it has not undergone rigorous manual auditing, security hardening, or unit testing common in production-grade software.
- **Use at Your Own Risk:** This software is free to use, but it is provided "as-is" without any warranties of any kind. By using this code, you acknowledge that you do so at your own discretion.
- **Compliance Warning:** Use of this codebase may not align with Google’s (or other third-party) Service Policies. It is your responsibility to ensure that your specific implementation complies with the Terms of Service of any platforms or APIs you integrate with.
- **No Developer Liability:** The creator of this project shall not be held responsible or liable for any damages, data loss, account suspensions, or financial losses incurred by individuals or businesses who choose to adapt this code into a live product.
- **Not a Commercial Product:** This is a passion project, not a commercial offering. If you decide to transform this hobbyist codebase into a commercial product, you assume 100% of the legal and technical responsibility.

In short: Use it, learn from it, and break it—but do so with caution!

## ✨ Features
- **Single Video Download**: Convert individual YouTube or YouTube Music links.
- **Playlist Support**: Bulk import entire playlists with smart selection.
- **High Quality**: Support for MP3 (up to 320kbps) and FLAC formats.
- **Metadata Tagging**: Automatically embeds Title, Artist, Album, and Cover Art.
- **Queue System**: Background processing with rate limiting to prevent IP bans.
- **Real-time Dashboard**: Monitor disk usage, active downloads, and failed jobs.
- **History**: Searchable archive of all your downloads.
- **Settings**: Configure download paths, limits, and system checks directly from the UI.
- **Responsive Design**: Beautiful dark-mode UI with glassmorphism effects.

## 🛠️ Requirements
- **OS**: Windows, Linux, or macOS
- **PHP**: `>= 8.2`
- **Composer**: `>= 2.x`
- **Node.js**: `>= 18` and npm
- **Database**: SQLite (default) or MySQL/MariaDB
- **System tools**:
  - `yt-dlp` (downloader)
  - `ffmpeg` (audio conversion)

<a id="full-setup"></a>
## 🚀 Full Setup
### 1) Clone and install dependencies
```bash
git clone <your-repo-url>
cd youtube_converter
composer install
npm install
```

### 2) Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

PowerShell equivalent:
```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### 3) Database setup
By default, `.env.example` uses:
```env
DB_CONNECTION=sqlite
```

#### Option A: SQLite
```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=SettingsSeeder
```

PowerShell equivalent:
```powershell
New-Item -ItemType File -Path .\database\database.sqlite -Force
php artisan migrate
php artisan db:seed --class=SettingsSeeder
```

#### Option B: MySQL/MariaDB
Update `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

Then run:
```bash
php artisan migrate
php artisan db:seed --class=SettingsSeeder
```

### 4) Install and verify system dependencies
```bash
yt-dlp --version
ffmpeg -version
```

If not recognized, add both binaries to your system `PATH` and restart your terminal.

### 5) Optional app env overrides
```env
YOUTUBE_DOWNLOAD_PATH=storage/app/downloads
MAX_PLAYLIST_SIZE=50
MAX_CONCURRENT_DOWNLOADS=3
RATE_LIMIT_SECONDS=2
MIN_DISK_SPACE_MB=500
YOUTUBE_JOB_TIMEOUT=600
YOUTUBE_JOB_TRIES=3
```

<a id="run-the-app"></a>
## ▶️ Run the App
### Recommended (single command)
```bash
composer run dev
```

This starts:
- Laravel dev server
- Queue listener
- Log stream (`php artisan pail`)
- Vite dev server

App URL:
- `http://127.0.0.1:8000`

### Alternative (separate terminals)
Terminal 1:
```bash
php artisan serve
```

Terminal 2:
```bash
php artisan queue:work
```

Terminal 3:
```bash
npm run dev
```

Optional Terminal 4:
```bash
php artisan pail --timeout=0
```

<a id="scheduler-setup"></a>
## ⏱️ Scheduler Setup (Required for automated cleanup)
This app schedules a daily cleanup job (`CleanupOldDownloadsJob`) in `routes/console.php`.

### Local development
Run the scheduler worker in a separate terminal:
```bash
php artisan schedule:work
```

### Production (Linux cron)
Add this cron entry so Laravel checks scheduled tasks every minute:
```cron
* * * * * cd /path/to/youtube_converter && php artisan schedule:run >> /dev/null 2>&1
```

### Production (Windows Task Scheduler)
Create a task that runs every 1 minute with:
```powershell
php artisan schedule:run
```

### Verify scheduler is working
```bash
php artisan schedule:list
php artisan schedule:run
```

You should see the scheduled cleanup task in the list, and `schedule:run` should execute due tasks.

## 🏗️ Build for Production
```bash
npm run build
```

## 📖 Usage Guide
1. Open the app in browser.
2. Paste a YouTube URL.
3. Select format (`mp3` or `flac`) and quality.
4. Start conversion and monitor queue/progress.
5. Download completed file from the app.

<a id="health-checks"></a>
## 🩺 Health Checks & Troubleshooting
### Useful checks
```bash
php artisan about
php artisan route:list
php artisan queue:failed
php artisan optimize:clear
```

### Jobs stuck / no progress
- Ensure worker is running: `php artisan queue:work`
- Retry failed jobs:
  ```bash
  php artisan queue:failed
  php artisan queue:retry all
  ```

### `yt-dlp` / `ffmpeg` not found
- Verify commands:
  ```bash
  yt-dlp --version
  ffmpeg -version
  ```
- Ensure both are available in the same terminal environment as Laravel/queue.

### Frontend not updating
- Run `npm run dev` in development.
- Rebuild assets with `npm run build` if needed.

### Database issues
- Confirm DB values in `.env`.
- Reset and reseed:
  ```bash
  php artisan migrate:fresh --seed
  ```

## ⚡ One-Time Setup Shortcut
```bash
composer run setup
```

This runs:
- `composer install`
- creates `.env` if missing
- `php artisan key:generate`
- `php artisan migrate --force`
- `npm install`
- `npm run build`

Then start:
```bash
composer run dev
```

## 🤝 Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## 📝 License
[MIT](https://choosealicense.com/licenses/mit/)
