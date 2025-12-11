# Sistem Manajemen Kasir Restoran Digital (EasyResto)

## Daftar Anggota
1. Alya Nayra Syafiqa
2. Saskiya Dwi Septiani   
3. Dewi Resmiyanti
4. Yosi Arjunita Putri

## Judul Proyek
**Sistem Manajemen Kasir Restoran Digital (EasyResto)**

## Summary Proyek
EasyResto adalah sebuah aplikasi kasir digital yang dirancang untuk memudahkan pengelolaan transaksi di restoran. Aplikasi ini bertujuan untuk menggantikan sistem kasir tradisional yang menggunakan mesin kasir fisik dengan sistem digital berbasis web. Fitur-fitur utama dari EasyResto termasuk manajemen menu, manajemen pesanan pelanggan, dan laporan transaksi yang dapat diakses secara real-time. 
Aplikasi ini diharapkan dapat meningkatkan efisiensi operasional restoran, mengurangi kesalahan manusia dalam transaksi, serta memberikan kemudahan bagi pengelola restoran dalam menganalisis data penjualan.

## Cara Instalasi Proyek EasyResto

### 1️⃣ Clone Repository
Buka terminal dan jalankan:

```bash
cd C:\laragon\www
git clone https://github.com/AlyaNayraSyafiqa/TUBES_PRK_PEMWEB_2025.git
cd EasyResto

---
## Setup Database
### 2️⃣ Konfigurasi Database (config/database.php)

Sesuaikan pengaturan database pada file berikut:

```
config/database.php
```

## Workflow Pengembangan (Setup Ngoding)

### 1. Pindah ke Master & Tarik Data Terbaru
```bash
git checkout master
git pull origin master
```

### 2. Buat Branch Baru
```bash
git checkout -b nama-branch
```

### 3. Mulai Mengerjakan Fitur
```bash
git add .
git commit -m "Menambahkan fitur X atau memperbaiki bug Y"
git push origin nama-branch
```

### 4. Syncing Branch Supaya Tetap Update

**A. Simpan pekerjaan sementara**
```bash
git add .
git commit -m "Save progress"
```

**B. Pindah ke master & update**
```bash
git checkout master
git pull origin master
```

**C. Kembali ke branch**
```bash
git checkout nama-branch
```

**D. Merge master ke branch**
```bash
git merge master
```

---

## 🔀 Pull Request (Untuk Merge ke Master)

Jika fitur sudah selesai:

1. Push branch  
   ```bash
   git push origin nama-branch
   ```
2. Buka GitHub repository  
3. Klik **Compare & Pull Request**  
4. Isi deskripsi perubahan  
5. Submit PR untuk direview oleh tim  

---

## 📌 Catatan Tambahan
- Lakukan **pull master** sebelum mulai coding.   

---