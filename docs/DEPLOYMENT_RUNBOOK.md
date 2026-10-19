# docs/DEPLOYMENT_RUNBOOK.md — Panduan Deployment

## Prasyarat

- PHP 8.3+
- Composer 2.x
- MySQL 8.x
- Node.js + npm (untuk asset build)

## Langkah Deployment

1. Clone repository
2. `composer install --optimize-autoloader --no-dev` (produksi)
3. Salin `.env.example` ke `.env`, isi variabel produksi (DB, APP_KEY, dll)
4. `php artisan key:generate`
5. `php artisan config:cache`
6. `php artisan route:cache`
7. `php artisan view:cache`
8. `php artisan migrate --force`
9. `npm install && npm run build` (build asset AdminLTE 3)
10. Konfigurasi web server (Nginx/Apache) dengan document root `public/`
11. Aktifkan HTTPS
12. Atur backup database dan storages
13. Aktifkan logging dan monitoring

## Environment Variabel Penting

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://...`
- `DB_CONNECTION=mysql`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

## Backup & Restore

- Backup database MySQL secara teratur (mysqldump)
- Backup folder `storage/` (lampiran, log)
- Uji restore secara berkala

## Catatan

- Jangan commit file `.env` (mengandung rahasia)
- Rahasia hanya disimpan di environment variables
- Simpan file di Laravel Storage, lindungi akses lampiran
