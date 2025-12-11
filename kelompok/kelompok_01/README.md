# EasyResto - Sistem Manajemen Kasir Restoran
**EasyResto** adalah aplikasi kasir digital berbasis web yang dirancang untuk modernisasi operasional restoran. Aplikasi ini menggantikan sistem pencatatan manual dengan sistem terintegrasi yang menghubungkan kasir, admin, dan pemilik usaha (owner) dalam satu platform. Aplikasi ini diharapkan dapat meningkatkan efisiensi operasional restoran, mengurangi kesalahan manusia dalam transaksi, serta memberikan kemudahan bagi pengelola restoran dalam menganalisis data penjualan.

Proyek ini dikembangkan sebagai **Tugas Besar Pemrograman Web** untuk menciptakan efisiensi manajemen pesanan, dan transparansi laporan keuangan

-----

## Anggota Kelompok 01

| NIM | Nama Anggota | Role & Tanggung Jawab |
| :--- | :--- | :--- |
| **2315061001** | **Alya Nayra Syafiqa** | **Project Manager, Database & *Semua halaman role Kasir dan Fixing Admin (Manajemen Menu, Dashboard dan Laporan)*** |
| **2315061033** | **Saskiya Dwi Septiani** | **ERD dan Semua Halaman role Owner, Serta Fixing Admin (Manajemen User, Manajemen Transaksi dan Profile)**  |
| **2315061045** | **Dewi Resmiyanti** | **Halaman Role Admin** |
| **2215061095** | **Yosi Arjunita Putri** | - |


-----

## Cara Penggunaan / Installasi

Aplikasi memiliki 3 role yang berbeda. Berikut adalah kredensial untuk pengujian fitur di `localhost`:

### Akun Testing (Dummy)

Silakan gunakan kredensial berikut untuk mencoba berbagai *role*:

| Role | Username | Password | Deskripsi Akses |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` | Dashboard, Kelola menu, Kelola User, Laporan, Transaksi, Profil. |
| **Kasir** | `kasir` | `alya123` | Dashboard, Transaksi, Laporan, Riwayat, Profil |
| **Owner** | `owner` | `owner123` | Dashboard, Laporan, Manjemen Menu, Manajemen Pengguna, Profil |

> **Catatan:** Setiap transaksi yang dilakukan oleh Kasir akan langsung masuk ke rekap laporan di dashboard Admin dan Owner.

-----

## Teknologi yang Digunakan

Dibangun menggunakan teknologi web standar untuk memastikan performa yang ringan:

* **Bahasa:** Native PHP 8.3 (Backend Logic)
* **Database:** MySQL (HeidiSQL) 
* **Frontend:** HTML, CSS (Tailwind), JavaScript, AJAX (tanpa reload) dan pakai https://ui-avatars.com/api/?name= (agar kalo belum masukin foto, muncul inisial nama)
* **ERD:** draw.io 
-----

## Alur Sistem (Workflow)

1.  **Setup (Admin):** Admin login dan menginput daftar Menu Makanan/Minuman beserta fotonya.
2.  **Transaksi (Kasir):**
    * Kasir login saat shift dimulai.
    * Memilih menu yang dipesan pelanggan lalu otomatis Masuk Keranjang.
    * Klik Bayar Sekarang
    * Sistem mencetak struk dan menyimpan data penjualan.
3.  **Monitoring (Owner):** Pemilik restoran login dari mana saja untuk melihat total pendapatan hari ini dan menu apa yang paling laku dan bisa melakukan manajemen .

-----

## Instalasi Lokal

Jika ingin menjalankan di `localhost` (XAMPP/Laragon):
1. Buat folder lokal di folder www jika pakai laragon, atau di htdocs jika pakai xampp (misal : TUBES)
2. Lalu masuk ke vscode/gitbash, masuk ke folder tersebut
3. Pada terminal, masukan perintah
   ```bash
    git init
    ```
4. Clone repository ini:
    ```bash
    git clone https://github.com/AlyaNayraSyafiqa/TUBES_PRK_PEMWEB_2025.git
    ```
2.  Import file database (`easyresto.sql`) ke phpMyAdmin ataupun HeidiSQL.
3.  Sesuaikan konfigurasi database di `config.php`:
    ```php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "easyresto"; // Sesuaikan nama database
    ```
4.  Buka browser dan akses path proyek:
    `http://localhost/TUBES/TUBES_PRK_PEMWEB_2025/kelompok/kelompok_01/` jika pakai folder tambahan TUBES (bisa lebih rapih), kalo tidak pakai folder TUBES tidak perlu di masukan