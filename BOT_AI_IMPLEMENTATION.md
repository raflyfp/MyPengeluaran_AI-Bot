# Panduan Implementasi Bot AI Laravel

Dokumen ini adalah catatan praktis kalau nanti ingin membuat fitur seperti bot Telegram MyPengeluaran lagi: chat masuk, AI membaca pesan bebas, lalu Laravel menyimpan hasilnya ke database.

## Tujuan

Alur yang dibuat:

1. User mengirim pesan ke Telegram bot.
2. Telegram mengirim webhook ke Laravel.
3. Laravel mengambil teks pesan.
4. Gemini membaca pesan dan mengubahnya menjadi JSON terstruktur.
5. Laravel validasi hasil AI.
6. Jika AI gagal, sistem fallback ke parser biasa.
7. Data disimpan ke tabel log bot dan tabel transaksi.
8. Bot mengirim balasan konfirmasi ke Telegram.
9. Bot menampilkan inline button untuk ringkasan, kategori, contoh input, dan bantuan.

## Persona Bot

Bot memakai persona Eva:

- Nama assistant: Eva.
- Nama bot experience: Eva-Assist.
- Bahasa: Indonesia casual.
- Gaya: ramah, singkat, santai, tetap profesional.
- Fokus: catat transaksi, ringkasan, insight pengeluaran, dan kebiasaan finansial.
- Aturan penting: jangan mengarang data transaksi; jika data belum ada, bilang jujur.

Command utama:

```text
/eva
/summary
/today
/help
```

Contoh:

```text
/eva makan bakso 20rb
/eva bulan ini aku boros gak?
/eva mau beli keyboard 500rb aman gak?
/eva rekomendasi budget mouse wireless min max berapa?
/eva halo
/summary
/today
```

## File Utama

```text
routes/web.php
app/Http/Controllers/TelegramWebhookController.php
app/Services/EvaFinanceAssistant.php
app/Services/GeminiFinanceMessageParser.php
app/Services/FinanceMessageParser.php
app/Services/TelegramBotClient.php
app/Services/TelegramUserResolver.php
app/Services/TransactionService.php
config/services.php
.env
```

## Environment

Tambahkan key di `.env`:

```env
TELEGRAM_BOT_TOKEN=
TELEGRAM_DEFAULT_USER_EMAIL=

GEMINI_API_KEY=
GEMINI_MODEL=gemini-3.5-flash
```

Setelah mengubah `.env`, refresh config:

```bash
php artisan config:clear
```

Jangan commit `.env` karena berisi token dan API key.

## Config Service

Simpan secret di `config/services.php`, bukan hardcode di controller/service:

```php
'telegram' => [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'default_user_email' => env('TELEGRAM_DEFAULT_USER_EMAIL'),
],

'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),
],
```

Dengan pola ini, kode bisa membaca config lewat:

```php
config('services.gemini.api_key');
config('services.telegram.bot_token');
```

## Route Webhook

Daftarkan endpoint POST untuk Telegram:

```php
Route::post('/telegram/webhook', TelegramWebhookController::class)
    ->name('webhooks.telegram');
```

Saat local development, buat URL publik memakai ngrok:

```bash
ngrok http 8000
```

Lalu set webhook Telegram:

```text
https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://your-ngrok-url.ngrok-free.app/telegram/webhook
```

Cek status webhook:

```text
https://api.telegram.org/bot<TOKEN>/getWebhookInfo
```

## Controller Webhook

Tugas controller sebaiknya tipis:

1. Ambil payload Telegram.
2. Ambil `text` dan `chat.id`.
3. Handle command seperti `/start`, `/help`, `/categories`.
4. Resolve user tujuan.
5. Panggil parser AI.
6. Panggil service penyimpanan.
7. Kirim balasan ke Telegram.

Contoh bentuk alur:

```php
$parsed = $parser->parse($text);

$result = $transactionService->storeFromTelegramMessage(
    $user,
    $text,
    $parsed,
    $telegramMeta,
);

$telegram->sendMessage($chatId, $this->replyText($result['transaction'], $parsed));
```

Controller tidak perlu berisi logic parsing panjang. Parsing taruh di service.

## Bot Interaktif

Untuk membuat bot terasa lebih hidup, gunakan inline keyboard Telegram:

```php
$telegram->sendMessage($chatId, $text, [
    'inline_keyboard' => [
        [
            ['text' => 'Hari ini', 'callback_data' => 'bot:today'],
            ['text' => 'Bulan ini', 'callback_data' => 'bot:month'],
        ],
    ],
]);
```

Saat tombol diklik, Telegram mengirim `callback_query` ke webhook yang sama. Controller perlu membaca:

```php
$callbackPayload = data_get($update, 'callback_query');
$data = data_get($callbackPayload, 'data');
```

Jangan lupa jawab callback supaya loading tombol di Telegram berhenti:

```php
$telegram->answerCallbackQuery($callbackId, 'Siap');
```

Contoh aksi tombol:

- `bot:today` untuk ringkasan hari ini.
- `bot:month` untuk ringkasan bulan ini.
- `bot:categories` untuk daftar kategori.
- `bot:examples` untuk contoh input transaksi.
- `bot:help` untuk bantuan utama.
- `bot:eva` untuk membuka sapaan Eva.

## Command Eva

`/eva` punya tiga mode:

1. Jika pesan berisi nominal, Eva mencatat transaksi.
2. Jika tidak ada nominal, Eva menjawab sebagai finance assistant AI dengan konteks data user.
3. Jika pesan berisi rencana beli/rekomendasi budget, Eva menjawab konsultasi budget dan tidak mencatat transaksi.

Contoh transaksi:

```text
/eva kopi 18rb
```

Flow:

```text
/eva kopi 18rb
-> commandBody mengambil "kopi 18rb"
-> GeminiFinanceMessageParser membaca transaksi
-> TransactionService menyimpan ke bot_messages dan transactions
-> Eva membalas konfirmasi
```

GeminiFinanceMessageParser juga mengembalikan `intent`.

```text
intent=record_transaction -> boleh disimpan sebagai transaksi
intent=finance_advice     -> jangan disimpan, teruskan ke EvaFinanceAssistant
```

Contoh chat:

```text
/eva bulan ini aku boros gak?
/eva mau beli keyboard 500rb aman gak?
```

Flow:

```text
/eva bulan ini aku boros gak?
-> parser tidak menemukan amount
-> tidak membuat transaksi
-> EvaFinanceAssistant mengirim konteks transaksi real ke Gemini
-> Eva memberi insight singkat tanpa mengarang data

/eva mau beli keyboard 500rb aman gak?
-> terdeteksi sebagai advisory question
-> tidak membuat transaksi walaupun ada nominal
-> Eva membandingkan rencana beli dengan current_balance dan net_cashflow
```

Contoh yang tidak boleh dicatat sebagai transaksi:

```text
pulpen 100000 mahal gasi
keyboard 500rb worth gak?
budget mouse max berapa?
```

Fallback regex juga menjumlahkan banyak nominal dalam satu pesan:

```text
wonton 10k kopi 5k es teh 3k -> Rp18.000
```

Balasan bot memakai label ringan:

```text
Eva catet -> Gemini berhasil dipakai
Eva tidur -> Gemini gagal dan fallback lokal yang mencatat
```

EvaFinanceAssistant wajib membawa aturan persona:

- Eva bukan ChatGPT.
- Eva hanya fokus ke finance pribadi.
- Jika user bertanya di luar finance, Eva menolak halus.
- Eva tidak boleh mengarang transaksi, nominal, atau statistik.
- Jawaban ringkas, casual, dan natural.

## Service Parser AI

Service parser AI punya tanggung jawab:

1. Ambil API key Gemini.
2. Jika API key kosong, fallback ke parser lokal.
3. Kirim prompt ke Gemini.
4. Minta output JSON.
5. Decode JSON.
6. Validasi isi JSON.
7. Normalisasi kategori.
8. Kembalikan array bersih ke service transaksi.

Bentuk output yang enak dipakai:

```php
[
    'type' => 'expense',
    'amount' => 25000,
    'note' => 'makan 25000',
    'category_hint' => 'Food & Drink',
    'confidence' => 0.95,
    'raw_message' => 'makan 25000',
    'parser' => 'gemini',
]
```

Jika fallback:

```php
[
    'type' => 'expense',
    'amount' => 25000,
    'note' => 'makan 25000',
    'category_hint' => 'Food & Drink',
    'confidence' => 0.82,
    'raw_message' => 'makan 25000',
    'parser' => 'regex',
    'fallback_reason' => 'gemini_request_failed',
]
```

## Prompt Design

Prompt harus jelas dan membatasi output:

```text
Return only valid JSON. No markdown.
type must be either "income" or "expense".
amount must be numeric rupiah.
category_hint must be one exact category from the allowed category lists.
```

Masukkan kategori dari database ke prompt supaya AI tidak membuat kategori liar:

```php
$expenseCategories = Category::where('type', 'expense')->pluck('name')->implode(', ');
$incomeCategories = Category::where('type', 'income')->pluck('name')->implode(', ');
```

## Validasi Output AI

Jangan langsung percaya output AI. Minimal cek:

- `type` hanya `income` atau `expense`
- field `amount` ada
- `amount` harus numeric atau null
- `category_hint` tidak kosong

Jika validasi gagal, fallback ke parser lokal.

## Penyimpanan Data

Simpan log bot dan transaksi dalam `DB::transaction()`:

```php
DB::transaction(function () {
    // create bot_messages
    // create transactions jika amount valid
});
```

Simpan `parsed_data` lengkap ke `bot_messages` supaya debugging mudah:

```php
'parsed_data' => [
    ...$parsed,
    'telegram' => $telegramMeta,
],
```

Dengan begitu kamu bisa cek parser apa yang dipakai:

```text
parsed_data->parser = gemini
parsed_data->fallback_reason = gemini_exception
```

## User Resolver

Untuk MVP, semua pesan Telegram bisa diarahkan ke user default dari `.env`:

```env
TELEGRAM_DEFAULT_USER_EMAIL=user@example.com
```

Untuk production, lebih bagus buat tabel mapping:

```text
telegram_accounts
- user_id
- telegram_chat_id
- telegram_user_id
- username
```

Jadi setiap chat Telegram bisa masuk ke akun yang benar.

## Testing Manual

Kirim pesan ke bot:

```text
kopi 18rb
makan siang 25000
bayar listrik 150rb
gaji freelance 1,5jt
```

Lalu cek:

1. Bot membalas pesan.
2. `bot_messages` bertambah.
3. `transactions` bertambah jika nominal valid.
4. `parsed_data.parser` bernilai `gemini` atau `regex`.
5. Dashboard dan Transactions menampilkan data baru.

## Debugging

Cek log Laravel:

```bash
tail -f storage/logs/laravel.log
```

Di Windows PowerShell:

```powershell
Get-Content storage/logs/laravel.log -Wait -Tail 80
```

Masalah umum:

- Webhook sudah set tapi tidak masuk database: cek ngrok masih hidup dan URL webhook benar.
- Bot tidak membalas: cek `TELEGRAM_BOT_TOKEN`.
- Parser selalu `regex`: cek `GEMINI_API_KEY`, lalu jalankan `php artisan config:clear`.
- Kategori salah: cek daftar kategori di database dan prompt kategori.
- Waktu transaksi aneh: simpan timestamp Telegram sebagai UTC, tampilkan ke user memakai timezone aplikasi.

## Checklist Implementasi Cepat

1. Buat migration dan model untuk log bot.
2. Buat route webhook POST.
3. Buat controller webhook tipis.
4. Buat client untuk membalas chat.
5. Buat resolver user.
6. Buat parser AI dengan fallback.
7. Buat service penyimpanan database.
8. Simpan output AI ke `parsed_data`.
9. Tampilkan parser di balasan bot untuk debugging.
10. Tambahkan inline keyboard untuk command yang sering dipakai.
11. Dokumentasikan env dan cara test.

## Referensi

- Gemini text generation: https://ai.google.dev/gemini-api/docs/text-generation
- Gemini structured output: https://ai.google.dev/gemini-api/docs/structured-output
- Telegram Bot API: https://core.telegram.org/bots/api
