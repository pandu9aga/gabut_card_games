# Gabut Card Games! 🎴

Gabut Card Games! adalah permainan Uno berbasis web yang dibangun dengan Laravel. Didesain untuk bermain bersama teman secara real-time di berbagai perangkat dengan antarmuka modern yang futuristik (Glassmorphism).

## 🚀 Fitur Utama

- **Multi-Device Multiplayer:** Main bareng teman cukup dengan membagikan kode 6 digit.
- **Real-time Polling:** Sinkronisasi status permainan tanpa perlu refresh halaman.
- **Desain Modern:** UI premium dengan efek glassmorphism, animasi halus, dan mode gelap otomatis.
- **Sistem Antrian & Turn:** Penanganan giliran pemain, arah putaran, dan tumpukan kartu (+2/+4).

## ⚖️ Aturan Khusus (House Rules)

Proyek ini menerapkan beberapa aturan modifikasi untuk menambah keseruan:

1.  **Stacking (Penumpukan):**
    *   Kartu dengan nomor atau tipe yang sama bisa dikeluarkan sekaligus dalam satu giliran.
    *   **Stack +2:** Bisa ditumpuk oleh pemain berikutnya dengan kartu +2 atau +4.
    *   **Stack +4:** Hanya bisa ditumpuk oleh pemain berikutnya dengan kartu +4 lagi.
2.  **Efek Kartu Spesial:**
    *   **Skip:** Setiap kartu skip yang ditumpuk akan melompati satu pemain tambahan.
    *   **Reverse:** Jumlah ganjil membalikkan arah, jumlah genap membatalkan pembalikan.
    *   **2-Player Rule:** Dalam permainan 2 orang, kartu **Reverse** berfungsi sebagai **Skip** (kamu main lagi).
3.  **Tanking Strategy:**
    *   Pemain diperbolehkan mengambil kartu dari deck meskipun memiliki kartu yang cocok di tangan (strategi sembunyi kartu).

## 🛠️ Teknologi

- **Backend:** Laravel 11.x
- **Frontend:** Vanilla CSS (Modern CSS Variables), JavaScript (Fetch API)
- **Database:** MySQL / MariaDB
- **Icons & Fonts:** Outfit Font, Emoji-based icons

## 📦 Instalasi

1.  **Clone repository:**
    ```bash
    git clone [url-repository]
    cd uno_gaes
    ```

2.  **Instal dependensi:**
    ```bash
    composer install
    ```

3.  **Konfigurasi Environment:**
    Salin `.env.example` ke `.env` dan sesuaikan database serta `APP_URL`.
    ```bash
    php artisan key:generate
    ```

4.  **Migrasi & Seeding Card:**
    PENTING: Jalankan seeder agar tabel kartu terisi.
    ```bash
    php artisan migrate --seed
    ```

5.  **Jalankan Server:**
    ```bash
    php artisan serve
    ```

## 🌐 Alamat Akses (Subdirectory)

Jika dijalankan di server lokal (XAMPP), pastikan `APP_URL` di `.env` sudah sesuai agar asset dan link tidak pecah:
`APP_URL=http://[ip-komputer]/uno_gaes/public`

---
Dibuat dengan ❤️ untuk keseruan bersama!
