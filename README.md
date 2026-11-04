# 🚀 Hokigo WiFi - Provider & Billing Management System

Aplikasi manajemen operasional, penagihan pelanggan, otomasi WhatsApp, dan pembukuan keuangan lengkap untuk usaha provider internet WiFi (**Hokigo**) berbasis **Laravel 12**, **Tailwind CSS**, **Chart.js**, dan **Alpine.js**.

---

## 🎨 Identitas Visual Brand Hokigo
- **Warna Utama**: `#1055CC` (Hokigo Blue)
- **Warna Aksen / Gelap**: `#141414` (Deep Charcoal / Black)
- **Warna Latar & Kontras**: `#FEFEFE` / Clean White
- **Logo Resmi**: Tersedia di `public/images/logo.png` dan ditampilkan pada halaman login, sidebar, faktur, serta dokumen struk pembayaran.

---

## ✨ Fitur-Fitur Utama

### 1. Multi-Role & Autentikasi Pengguna (Owner & Admin)
- **Owner (Pemilik)**: Memiliki hak akses penuh melihat data keuangan rahasia, margin laba bersih, omzet, kelola pengguna/admin, dan pengaturan usaha.
- **Admin (Staf Lapangan / Kantor)**: Mengelola pelanggan, membuat tagihan bulanan, mencatat pelunasan, mengirimkan pengingat WhatsApp, dan mencatat pengeluaran harian.
- **Akun Bawaan (Default)**:
  - 👑 **Owner**: `owner@hokigo.net` | Password: `password`
  - 🛡️ **Admin**: `admin@hokigo.net` | Password: `password`

### 2. Dashboard Bisnis & Grafik Real-time (Chart.js)
- **Metrik Utama**:
  - **Omzet Bulan Ini** (Total pemasukan dari tagihan lunas & jasa pasang baru) + persentase perbandingan vs bulan lalu.
  - **Pengeluaran Bulan Ini** (Biaya bandwidth ISP upstream, listrik OLT, gaji teknisi, pembelian kabel/alat).
  - **Laba Bersih Bulan Ini (Net Profit)** = Omzet dikurangi Pengeluaran (dengan indikator status surplus/defisit).
  - **Pelanggan Aktif, Isolir, dan Nonaktif**.
- **Grafik Interaktif**:
  - Tren Keuangan 6 Bulan Terakhir (Grafik batang Pemasukan vs Pengeluaran & garis Laba Bersih).
  - Rasio Pembayaran Tagihan Bulan Ini (Donut Chart: Lunas vs Belum Bayar).
  - Sebaran Pelanggan per Desa (Bar Chart per wilayah).
- **Aksi Cepat Tagihan Jatuh Tempo Hari Ini**: Menampilkan pelanggan yang harus difollow-up hari ini dengan tombol 1-klik kirim pesan WhatsApp.

### 3. Manajemen Pelanggan Berdasarkan Desa (Grouped by Village)
- Pelanggan dikelompokkan secara rapi berdasarkan **Desa / Wilayah / Titik Distribusi**.
- **Catatan Khusus (Note Lapangan)** per pelanggan: Menyimpan rute kabel dropcore, nomor tiang ODP, warna core, letak router/ONT, kontak cadangan, atau perjanjian penagihan.
- Status Pelanggan: **Aktif**, **Isolir** (menunggak), atau **Nonaktif**.
- Informasi teknis perangkat: Model router (ZTE/Huawei), username & password PPPoE, serta IP Address pelanggan.
- Indikator status tagihan bulan ini langsung terpampang di tabel pelanggan.

### 4. Tagihan & Otomasi Pengingat WhatsApp
- **Generate Tagihan Bulanan Serentak**: Menerbitkan tagihan untuk seluruh pelanggan aktif pada bulan yang dipilih hanya dalam 1 klik (anti-duplikat).
- **1-Klik WhatsApp Direct Notification**: Tombol kirim pesan WhatsApp yang langsung membuka obrolan ke nomor pelanggan dengan standar nomor internasional (`+62`), tanpa perlu simpan kontak.
- **Template Pesan WhatsApp Rapi & Profesional**:
  - Salam ramah & nama pelanggan
  - ID Pelanggan & Paket Langganan
  - Nominal Tagihan & Tanggal Jatuh Tempo
  - Daftar Rekening Pembayaran (BCA, BRI, Mandiri, QRIS, DANA, GoPay)
- **Pelunasan 1-Klik**:
  - Memilih metode bayar (Tunai, Transfer BCA/BRI/Mandiri, QRIS, DANA).
  - **Otomatis Masuk ke Buku Kas Keuangan & Omzet** bulan berjalan!
  - Tersedia tombol kirim struk lunas via WhatsApp.
- **Pusat Reminder WhatsApp**: Halaman khusus yang menyaring pelanggan yang jatuh tempo hari ini atau sudah lewat jatuh tempo (overdue).
- **Cetak Struk Thermal & A4**: Tampilan cetak invoice ramah printer kasir bluetooth / thermal 58mm/80mm atau printer biasa.

### 5. Data Keuangan Lengkap (Buku Kas & Laporan Laba Rugi)
- Filter berdasarkan Bulan, Tahun, atau rentang tanggal custom.
- Ringkasan: Total Omzet, Total Pengeluaran, Laba Bersih, dan Margin Keuntungan (%).
- Rincian Pemasukan per Kategori (Tagihan WiFi, Biaya Pasang Baru, Upgrade, Jual Router).
- Rincian Pengeluaran per Kategori (Bandwidth / ISP Utama, Listrik & ODP, Gaji Karyawan, Pembelian Kabel & Alat, Perbaikan Jalur, Konsumsi & Operasional).
- Form Catat Pengeluaran Baru & Catat Pemasukan Tambahan.
- **Ekspor Laporan ke File CSV (Excel)**.

### 6. Master Data & Pengaturan Usaha
- **Master Desa**: Tambah, edit, dan pantau total pelanggan serta omzet per desa.
- **Master Paket Internet**: Konfigurasi harga, batas kecepatan (speed), dan deskripsi paket.
- **Pengaturan Usaha**: Ubah nama provider, alamat, nomor WhatsApp CS, dan daftar rekening transfer yang otomatis masuk ke pesan WhatsApp pelanggan.
- **Manajemen Akun**: Tambah admin baru, edit password, atau atur peran.

---

## 💻 Cara Menjalankan di Komputer Lokal

1. Buka folder proyek di Terminal / PowerShell:
   ```bash
   cd d:\provider-wifi
   ```
2. Jalankan server lokal:
   ```bash
   php artisan serve
   ```
3. Buka browser dan akses:
   ```
   http://127.0.0.1:8000
   ```
4. Masuk menggunakan akun demo:
   - **Email**: `owner@hokigo.net`
   - **Password**: `password`

---

## 🌐 Panduan Deploy ke Vercel & Penggunaan Custom Domain

Konfigurasi serverless Vercel (`vercel.json` dan `api/index.php`) telah disiapkan di proyek ini.

### Langkah 1: Siapkan Database Online (Cloud Database)
Karena lingkungan serverless Vercel bersifat ephemeral (sementara), gunakan layanan database online gratis / terjangkau:
- **MySQL Cloud**: Aiven MySQL, PlanetScale, TiDB Cloud, atau database cPanel hosting Anda.
- **PostgreSQL Cloud**: Supabase (sangat direkomendasikan & gratis) atau Neon.tech.

### Langkah 2: Hubungkan Proyek ke GitHub
1. Buat repository baru di [GitHub](https://github.com).
2. Push proyek ini ke repository Anda:
   ```bash
   git remote add origin https://github.com/USERNAME/hokigo-wifi.git
   git branch -M master
   git push -u origin master
   ```

### Langkah 3: Import ke Vercel
1. Buka [vercel.com](https://vercel.com) dan login.
2. Klik **Add New Project** -> Pilih repository GitHub `hokigo-wifi`.
3. Di bagian **Environment Variables**, tambahkan variabel berikut:
   - `APP_NAME`: `Hokigo WiFi`
   - `APP_ENV`: `production`
   - `APP_KEY`: `base64:SKwMcuMhZCf7SGeYtvCetJOkZqg/Fu2C4tSjOR35pG0=` (atau generate baru dengan `php artisan key:generate --show`)
   - `APP_DEBUG`: `false`
   - `APP_URL`: `https://domain-anda.com` (atau URL vercel.app sementara)
   - `DB_CONNECTION`: `mysql` (atau `pgsql`)
   - `DB_HOST`: Host database cloud Anda
   - `DB_PORT`: `3306` (atau `5432`)
   - `DB_DATABASE`: Nama database
   - `DB_USERNAME`: Username database
   - `DB_PASSWORD`: Password database
4. Klik **Deploy**.

### Langkah 4: Hubungkan Custom Domain
1. Beli domain Anda di penyedia domain pilihan (misalnya Niagahoster, DomaiNesia, IDCloudHost, Namecheap, Cloudflare, dll).
2. Di Dashboard Vercel proyek Hokigo:
   - Buka menu **Settings** -> **Domains**.
   - Masukkan nama domain Anda (misal: `hokigo.id` atau `wifi.hokigo.com`).
3. Ubah DNS Record di tempat Anda membeli domain sesuai instruksi Vercel:
   - **A Record**: `@` diarahkan ke IP Vercel `76.76.21.21`
   - **CNAME Record**: `www` diarahkan ke `cname.vercel-dns.com`
4. Dalam beberapa menit, sertifikat SSL HTTPS akan otomatis aktif dan website Hokigo siap digunakan!
