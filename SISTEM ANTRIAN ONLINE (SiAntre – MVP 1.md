SISTEM ANTRIAN ONLINE (SiAntre – MVP 1.0)
🎯 TARGET & KONSEP

Target: Klinik kecil, puskesmas pembantu, bengkel, salon, kantor desa
Model: Web-based, B2B, ringan, tanpa login pasien
Prinsip:

Sederhana, stabil, mudah dipakai operator non-IT

🧠 FLOW SISTEM UTAMA

Pasien buka link / scan QR

Pilih layanan

Ambil nomor antrian

Data masuk DB → status = waiting

Operator klik Panggil

Status berubah → called

Nomor tampil di layar TV

Operator set Selesai / Batal

🧩 FITUR MVP (FIX – JANGAN NGELEBAR)
1️⃣ Pasien / Pengunjung

Ambil nomor antrian (tanpa login)

Pilih layanan

Lihat:

nomor sendiri

status

sisa antrian (opsional)

2️⃣ Operator

Login

Pilih layanan

Panggil antrian berikutnya

Ubah status:

waiting

called

done

canceled

Lihat riwayat hari ini

3️⃣ Admin

Login

Kelola layanan

Kelola user (admin / operator)

Reset antrian harian (manual / auto)

4️⃣ Layar TV / Monitor

Nomor yang sedang dipanggil

Nama layanan / loket

Auto refresh (Livewire / polling)

🔧 PENYEMPURNAAN KECIL (MASIH MVP)
✅ Reset Antrian Harian

Wajib untuk dunia nyata

Opsi implementasi:

Cron job Laravel (jam 00:00)

Tombol “Reset Hari Ini” (admin)

Efek:

Nomor tetap kecil

Operator ga bingung

Data rapi per hari

✅ Role User (SIMPLE)

Tidak pakai permission ribet

admin

operator

Disimpan di kolom role (enum/string)

✅ Estimasi Giliran (AMAN)

Bukan menit ⛔
Tampilkan:

“Sisa 3 antrian”

Lebih jujur & minim komplain.

🗄️ STRUKTUR DATABASE (FINAL & IDEAL)
services
field tipe
id bigint
name string
created_at timestamp
updated_at timestamp
queues
field tipe
id bigint
number integer
service_id foreign
status enum(waiting,called,done,canceled)
created_at timestamp
updated_at timestamp

📌 created_at = penanda tanggal (cukup)

users
field tipe
id bigint
name string
email string
password string
role enum(admin,operator)
created_at timestamp
updated_at timestamp
🛠️ STACK TEKNIS (REKOMENDASI)

Laravel 10 / 11

Blade + Bootstrap / Tailwind

MySQL

Laravel Breeze (auth)

Livewire (real-time layar & operator)

Hosting: shared / VPS kecil

📦 STRUKTUR MODULE (LOGIS)

Auth

Service Management

Queue Management

Display Screen

Daily Reset

Reporting (harian)

💰 MODEL BISNIS (TEKNIS READY)

Setup awal: 300–500k

Bulanan: 50–100k

Hosting + maintenance

Custom fitur = upsell

🚀 STATUS PROYEK

✔ MVP jelas
✔ Scope terkunci
✔ Realistis dijual
✔ Cocok skill Laravel
✔ Bisa dikembangkan bertahap
