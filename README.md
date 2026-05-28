# MyPengeluaran

MyPengeluaran adalah aplikasi pencatat pemasukan dan pengeluaran berbasis web dengan tampilan fintech modern. Aplikasi ini dibuat mobile-first, tetapi sudah memiliki mode desktop responsif dengan sidebar navigation.

Fokus utama aplikasi:

- Mencatat transaksi manual dari web.
- Mencatat transaksi otomatis dari Telegram bot.
- Melihat ringkasan saldo, pemasukan, dan pengeluaran bulanan.
- Melihat daftar transaksi, filter, pencarian, dan statistik kategori.
- Melihat analytics dengan ApexCharts.
- Memantau aktivitas Telegram bot.
- Menghubungkan akun web ke Telegram dari menu Profile.

WhatsApp integration saat ini masih diskip dan belum diaktifkan.

## Tech Stack

- PHP 8.3
- Laravel Framework, current composer constraint `^13.8`
- Laravel Breeze authentication
- Supabase PostgreSQL
- Blade
- Tailwind CSS
- Alpine.js
- ApexCharts
- Telegram Bot API
- Google Gemini API

## UI Style

UI mengikuti design system MyPengeluaran:

- Mobile-first fintech dashboard.
- Ocean blue / emerald premium aesthetic.
- Soft glassmorphism.
- Rounded-2xl cards.
- Floating bottom navigation untuk mobile.
- Sidebar glass navigation untuk desktop.
- Smooth spacing dan hierarchy yang ringan.

Referensi desain:

- `design.md`
- `project-rules.md`
- `stitch_mypengeluaran_smart_finance_dashboard/emerald_precision/DESIGN.md`

## Halaman Utama

### Dashboard

Route:

```text
GET /dashboard
```

Fitur:

- Greeting header berdasarkan user login.
- Avatar initial user.
- Total balance dari database.
- Monthly income dari database.
- Monthly expense dari database.
- Smart insight berdasarkan kategori pengeluaran terbesar.
- Cashflow chart placeholder.
- Recent transactions dari database.
- Floating add transaction modal.

### Transactions

Route:

```text
GET /transactions
POST /transactions
PATCH /transactions/{transaction}
DELETE /transactions/{transaction}
```

Fitur:

- List transaksi dari database.
- Group transaksi berdasarkan tanggal.
- Search transaksi berdasarkan note atau category.
- Filter `income` dan `expense`.
- Summary pemasukan dan pengeluaran bulan berjalan.
- Edit transaksi melalui bottom sheet modal.
- Delete transaksi melalui swipe action.
- Add transaction dari floating action button.

### Analytics

Route:

```text
GET /analytics
```

Fitur:

- Monthly spending chart data.
- Category spending breakdown.
- Weekly expense trends.
- Monthly income vs expense.
- Top spending categories.
- ApexCharts donut, line, dan bar chart.

### Bot Assistant

Route:

```text
GET /bot
```

Fitur:

- Status Telegram integration.
- Statistik pesan Telegram yang sudah tersinkron.
- Accuracy parsing transaksi.
- Latest Telegram activity dari `bot_messages`.
- Preview format input bot.
- Automation toggle UI.

Catatan:

- WhatsApp belum dihubungkan.
- QR WhatsApp belum digunakan.

### Profile

Route:

```text
GET /profile
GET /profile/edit
PATCH /profile
DELETE /profile
POST /logout
```

Fitur:

- Profile card dari user login.
- Savings rate bulanan.
- Jumlah bot yang aktif.
- Jumlah transaksi bulan ini.
- Status koneksi Telegram.
- Command connect Telegram dengan tombol copy.
- Akses ke Bot Assistant.
- Notification toggle UI.
- Dark mode preview UI.
- Link edit profile.
- Logout.

## Alur Pengguna

1. User register atau login.
2. User masuk ke dashboard.
3. User dapat menambah transaksi manual melalui tombol plus.
4. Transaksi masuk ke tabel `transactions`.
5. Dashboard, Transactions, dan Analytics membaca data user yang sedang login.
6. User dapat mencari, memfilter, edit, atau delete transaksi.
7. User membuka Profile, menyalin command Telegram seperti `/start ABC123XYZ789`, lalu mengirim command tersebut ke bot.
8. Sistem menghubungkan Telegram `from.id` ke akun user yang sedang login.
9. Jika Telegram webhook aktif, user bisa mengirim pesan ke bot seperti:

```text
makan 25000
kopi 18rb
gaji 5000000
```

10. Sistem akan parsing nominal, tipe transaksi, kategori, lalu menyimpan transaksi.
11. Aktivitas Telegram tampil di halaman Bot Assistant.

## Alur Telegram Bot

Endpoint webhook:

```text
POST /telegram/webhook
POST /webhooks/telegram
```

Controller:

```text
app/Http/Controllers/TelegramWebhookController.php
```

Service yang digunakan:

```text
app/Services/FinanceMessageParser.php
app/Services/GeminiFinanceMessageParser.php
app/Services/TelegramBotClient.php
app/Services/TelegramUserResolver.php
app/Services/TransactionService.php
```

Alur teknis:

1. Telegram mengirim update ke webhook Laravel.
2. `TelegramWebhookController` membaca pesan.
3. Jika pesan `/start <token>`, bot menghubungkan akun Telegram ke user pemilik token.
4. Jika pesan `/start` tanpa token atau `/help`, bot mengirim panduan.
5. Jika pesan transaksi, `GeminiFinanceMessageParser` mencoba membaca nominal, tipe, note, dan kategori memakai Gemini.
6. Jika Gemini belum dikonfigurasi atau request gagal, sistem otomatis fallback ke `FinanceMessageParser`.
7. `TelegramUserResolver` menentukan user tujuan berdasarkan `telegram_user_id` atau `telegram_chat_id`.
8. `TransactionService` menyimpan data ke:
   - `bot_messages`
   - `transactions`
9. Bot mengirim balasan konfirmasi ke Telegram, termasuk parser yang dipakai.

Contoh pesan:

```text
makan 25000      -> expense, Food & Drink, Rp 25.000
kopi 18rb        -> expense, Food & Drink, Rp 18.000
gaji 5000000     -> income, Salary, Rp 5.000.000
```

## Struktur Database

Tabel utama:

```text
users
categories
transactions
bot_messages
```

### users

Field status:

- `is_active` enum `'1'` atau `'0'`

Catatan:

- `1` berarti akun aktif dan bisa login.
- `0` berarti akun dinonaktifkan, tidak bisa login, dan session lama akan otomatis dikeluarkan.
- Akun inactive juga tidak bisa menerima transaksi dari Telegram atau melakukan connect ulang.

Field Telegram:

- `telegram_user_id`
- `telegram_chat_id`
- `telegram_username`
- `telegram_link_token`
- `telegram_link_token_expires_at`

Catatan:

- `telegram_user_id` adalah identitas utama Telegram yang stabil.
- `telegram_username` hanya untuk label tampilan.
- `telegram_link_token` dipakai sementara saat user menghubungkan akun dari Profile.

### categories

Field penting:

- `name`
- `icon`
- `type`
- `deleted_at`

Relasi:

- Category hasMany Transactions

### transactions

Field penting:

- `user_id`
- `category_id`
- `type`
- `amount`
- `note`
- `source`
- `transaction_date`
- `deleted_at`

Relasi:

- Transaction belongsTo User
- Transaction belongsTo Category

### bot_messages

Field penting:

- `user_id`
- `platform`
- `message`
- `parsed_data`
- `status`
- `deleted_at`

Relasi:

- BotMessage belongsTo User

## Struktur Folder Penting

```text
app/
  Http/
    Controllers/
      AnalyticsController.php
      BotController.php
      CategoryController.php
      DashboardController.php
      ProfileController.php
      TelegramWebhookController.php
      TransactionController.php
    Requests/
  Models/
    BotMessage.php
    Category.php
    Transaction.php
    User.php
  Services/
    FinanceMessageParser.php
    GeminiFinanceMessageParser.php
    TelegramAccountLink.php
    TelegramBotClient.php
    TelegramUserResolver.php
    TransactionService.php

resources/
  views/
    analytics/
    bot/
    components/
    dashboard/
    layouts/
    profile/
    transactions/
  js/
    app.js
  css/
    app.css

database/
  migrations/
  seeders/
```

## Setup Local

Install dependency PHP:

```bash
composer install
```

Install dependency frontend:

```bash
npm install
```

Copy environment:

```bash
cp .env.example .env
```

Generate key:

```bash
php artisan key:generate
```

Isi konfigurasi database Supabase PostgreSQL di `.env`.

Contoh key yang perlu ada:

```env
APP_NAME=MyPengeluaran
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=
DB_PASSWORD=

TELEGRAM_BOT_TOKEN=
TELEGRAM_BOT_USERNAME=
TELEGRAM_DEFAULT_USER_EMAIL=

GEMINI_API_KEY=
GEMINI_MODEL=gemini-3.5-flash
```

Jangan commit `.env` ke Git. File `.env` sudah masuk `.gitignore`.

## Migrasi dan Seeder

Jalankan migrasi:

```bash
php artisan migrate
```

Jalankan seeder:

```bash
php artisan db:seed
```

Seeder membuat:

- User demo.
- Category income dan expense.
- Dummy transactions.
- Dummy bot messages.

Lihat `database/seeders/DatabaseSeeder.php` untuk credential user demo.

## Menjalankan Aplikasi

Jalankan Laravel server:

```bash
php artisan serve
```

Jalankan Vite:

```bash
npm run dev
```

Buka:

```text
http://127.0.0.1:8000
```

Untuk build production asset:

```bash
npm run build
```

## Setup Telegram Webhook

Saat development lokal, gunakan tunnel seperti ngrok:

```bash
ngrok http 8000
```

Set webhook ke URL publik:

```text
https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook?url=https://your-ngrok-url.ngrok-free.app/telegram/webhook
```

Cek webhook:

```text
https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/getWebhookInfo
```

Kirim pesan ke bot:

```text
kopi 18rb
makan 25000
gaji 5000000
```

Jika berhasil:

- Data masuk ke `bot_messages`.
- Data transaksi masuk ke `transactions`.
- Bot membalas pesan konfirmasi.
- Halaman Bot Assistant menampilkan activity terbaru.
- Profile menampilkan akun Telegram sebagai connected.

Command bot:

```text
/start <token>
/help
/categories
```

Command `/start <token>` dipakai untuk menghubungkan akun. Token dibuat dari Profile dan berlaku 30 menit. Command lain menampilkan contoh input dan daftar kategori income/expense yang tersedia.

## Connect Telegram Account

Telegram tidak memakai username sebagai identitas utama karena username bisa kosong dan bisa diganti. Aplikasi menyimpan Telegram `from.id` sebagai `telegram_user_id`, sedangkan username hanya dipakai sebagai label tampilan.

Alur koneksi:

1. Login ke web.
2. Buka `Profile`.
3. Di bagian `Connected Accounts`, salin `Telegram command`.
4. Kirim command tersebut ke bot Telegram.
5. Setelah bot membalas berhasil tersambung, kirim transaksi seperti `Makan 5k`.

Contoh command:

```text
/start AbC123xYz789
```

Token connect:

- Panjang token 12 karakter.
- Berlaku 30 menit.
- Jika expired, buka ulang Profile untuk mendapat token baru.
- Jika `TELEGRAM_BOT_USERNAME` diisi, tombol `Open` akan membuka Telegram langsung dengan token.

## Setup Gemini AI Parser

Isi API key Gemini di `.env`:

```env
GEMINI_API_KEY=your-gemini-api-key
GEMINI_MODEL=gemini-3.5-flash
```

Refresh konfigurasi Laravel:

```bash
php artisan config:clear
```

Setelah aktif, pesan Telegram seperti:

```text
kopi 18rb
makan siang 25k
bayar listrik 150rb
gaji freelance 1,5jt
```

akan diproses oleh Gemini untuk menentukan:

- `type`
- `amount`
- `note`
- `category_hint`
- `confidence`

Jika Gemini gagal, sistem tetap mencatat lewat parser regex lama selama format nominal masih terbaca.

## Route Ringkas

```text
GET     /dashboard
GET     /transactions
POST    /transactions
PATCH   /transactions/{transaction}
DELETE  /transactions/{transaction}
GET     /analytics
GET     /bot
GET     /profile
GET     /profile/edit
DELETE  /profile/telegram
POST    /telegram/webhook
POST    /webhooks/telegram
POST    /logout
```

## Query dan Perhitungan

Dashboard:

- Current balance = total income - total expense.
- Monthly income = total transaksi `income` bulan berjalan.
- Monthly expense = total transaksi `expense` bulan berjalan.
- Spending category summary = total expense per category bulan berjalan.

Analytics:

- Monthly spending.
- Weekly expense trends.
- Income vs expense per month.
- Top spending categories.

Transactions:

- Filter berdasarkan type.
- Search berdasarkan note dan category.
- Pagination.
- Soft delete.

## Timezone

Timezone aplikasi menggunakan WIB:

```env
APP_TIMEZONE=Asia/Jakarta
```

Konfigurasi:

```php
'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta')
```

Database memakai `timestampTz`, jadi storage PostgreSQL tetap aman untuk timezone-aware timestamp.

## Status Fitur

Sudah berjalan:

- Authentication.
- Dashboard data dari database.
- Transaction create, read, update, delete.
- Add transaction modal.
- Analytics backend query dan ApexCharts.
- Telegram webhook.
- Telegram account linking dari Profile.
- Telegram disconnect dari Profile.
- Telegram message parsing.
- Gemini AI parser dengan fallback regex.
- Bot message logging.
- Account active/inactive guard berbasis enum `users.is_active`.
- Profile data dari user login.
- Mobile-first layout.
- Desktop responsive sidebar.

Belum dikerjakan / masih UI-only:

- WhatsApp integration.
- Persistent dark mode setting.
- Persistent notification setting.
- Export report PDF/CSV.
- Category management UI khusus.

## GitLab Push

Project ini aman untuk dipush karena `.env`, `vendor`, `node_modules`, log, dan build output sudah masuk `.gitignore`.

Inisialisasi Git:

```bash
git init
git add .
git commit -m "Initial commit MyPengeluaran"
```

Tambah remote GitLab:

```bash
git remote add origin https://gitlab.com/username/mypengeluaran.git
git branch -M main
git push -u origin main
```

Jika GitLab meminta password, gunakan Personal Access Token dengan scope:

- `read_repository`
- `write_repository`

## Catatan Keamanan

- Jangan commit `.env`.
- Jangan share `TELEGRAM_BOT_TOKEN`.
- Jika token Telegram pernah terlihat di screenshot atau publik, regenerate token lewat BotFather.
- Untuk production, gunakan HTTPS public URL untuk webhook.
