# Ashidiq Game Hub

MVP portal game pembelajaran untuk `game.smpmuashidiq.sch.id`.

## Fitur
- Portal publik tanpa login untuk siswa.
- Akses guru hanya dengan kode unik.
- Upload HTML mandiri atau ZIP (`index.html` + aset lokal).
- Draft / publish / unpublish / delete game milik guru.
- Preview game di iframe sandbox.
- File game disimpan di folder yang diblokir dari akses langsung dan disajikan melalui `play.php` dengan CSP sandbox.
- Admin berbasis kode untuk membuat/revoke akses guru.
- Rate limit sederhana untuk kode yang salah.
- Kode akses disimpan dengan lookup HMAC + `password_hash`, bukan plaintext.

## Kebutuhan server
- PHP 8.1+ (direkomendasikan 8.2+)
- MySQL/MariaDB
- Extension PHP: PDO MySQL, ZipArchive
- Apache + `.htaccess` / mod_rewrite
- HTTPS aktif

## Instalasi di cPanel
1. Buat subdomain `game.smpmuashidiq.sch.id` dan arahkan document root ke folder aplikasi ini.
2. Buat database MySQL + user database dari cPanel.
3. Salin `config/config.example.php` menjadi `config/config.php`.
4. Isi `base_url`, koneksi DB, `app_key`, dan `setup_secret`.
   - Gunakan string acak panjang untuk `app_key` dan `setup_secret`.
5. Pastikan folder `storage/games` dapat ditulis PHP (biasanya permission 750/755 sesuai hosting).
6. Buka:
   `https://game.smpmuashidiq.sch.id/install.php?key=SETUP_SECRET_ANDA`
7. Klik instalasi, lalu **simpan kode ADMIN yang hanya tampil sekali**.
8. Setelah berhasil, ubah `setup_enabled => false` di `config/config.php`.
9. Login admin di `/admin/`, buat kode guru.
10. Guru login di `/guru/`, upload game, lalu publish.

## Format ZIP game
```
game.zip
├── index.html
├── style.css
├── script.js
└── assets/
    └── gambar.png
```
`index.html` wajib berada di root ZIP.

File server-side atau tipe lain yang tidak didukung (misalnya `.php`) akan diabaikan saat ekstraksi, sehingga tidak menggagalkan seluruh upload dan tidak dapat dieksekusi oleh server. Game tetap harus berjalan sebagai HTML/CSS/JavaScript statis.

JavaScript game boleh memuat aset lokal dari ZIP dengan path relatif, misalnya `fetch('questions.json')`. Koneksi ke domain luar tetap diblokir oleh CSP.

## Troubleshooting 404 pada game yang sudah publish
- Pastikan **mod_rewrite AKTIF** — game diakses lewat `/play/<slug>/index.html` (ditangani `.htaccess`), bukan file fisik.
- Kalau game published & file ada di `storage/games/.../index.html` tapi tetap 404, cek **Error Log** hosting → cari baris `[game-hub] published game missing file: ...`. Ini kasih tau persis path-nya (`exists=0` = gak ketemu, atau `exists=1` = jalan tapi path diblokir).
- Bisa juga **storage/games/ tidak dibaca PHP** (izin folder 750/750, atau `open_basedir` php.ini memblokir path).
- **Folder game tiba-tiba hilang** dari disk → kemungkinan anti-virus hosting (Imunify360/CloudLinux) mengkuarantin atau disk penuh; tambah exclusion di cPanel untuk folder `storage/`.
- **Game ada di DB & published tapi game.php 404** ("Game tidak ditemukan.") → cek `SELECT ... FROM games WHERE ...`, atau teacher_id-nya mengarah ke teacher yang sudah terhapus (JOIN gagal).

## Batasan MVP
- Game harus self-contained. CDN/API eksternal diblokir oleh CSP.
- Belum ada leaderboard nilai siswa lintas perangkat.
- Belum ada editor metadata setelah upload.
- Belum ada thumbnail otomatis.

## Catatan keamanan
Jangan menonaktifkan CSP/sandbox hanya agar game pihak ketiga berjalan. Untuk game internal, lebih aman bundel semua CSS/JS/aset ke dalam ZIP.
