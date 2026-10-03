# SISKOSAN - Aplikasi Manajemen Kos-Kosan

Aplikasi web berbasis Native PHP untuk mengelola properti kos-kosan, penyewa, pembayaran, dan operasional harian.

## Fitur Utama

- **Multi-role**: Owner, Admin, Tenant
- **Manajemen Properti & Kamar**: CRUD properti, kamar, fasilitas, status
- **Manajemen Penyewa**: Check-in/out, kontrak, dokumen KTP/KK
- **Penagihan & Pembayaran**: Tagihan bulanan otomatis, pencatatan pembayaran, bukti transfer
- **Dashboard & Laporan**: Occupancy rate, revenue, tunggakan, aging report
- **Maintenance**: Komplain penyewa, tracking perbaikan
- **Notifikasi**: Reminder jatuh tempo, status pembayaran, komplain
- **Responsive**: Mobile-first dengan TailwindCSS

## Persyaratan Sistem

- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.3+
- Apache/Nginx dengan mod_rewrite
- Extensions: PDO, mbstring, json, gd, curl

## Instalasi Cepat

### 1. Clone/Extract ke htdocs
```bash
# Jika menggunakan XAMPP
C:\xampp\htdocs\siskosan
```

### 2. Jalankan Installer
Buka browser ke: `http://localhost/siskosan/install.php`

Isikan:
- **Host Database**: `localhost`
- **Port**: `3306`
- **Nama Database**: `kosmanager` (akan dibuat otomatis)
- **Username**: `root`
- **Password**: (kosongkan untuk XAMPP default)
- **URL Aplikasi**: `http://localhost/siskosan`

Klik **Install Sekarang**

### 3. Login Default
Setelah instalasi berhasil, akses: `http://localhost/siskosan/login`

| Role | Email | Password |
|------|-------|----------|
| Owner | owner@kosmanager.com | password |
| Admin | admin@kosmanager.com | password |
| Tenant | tenant@kosmanager.com | password |

> **Penting**: Segera ganti password default setelah login pertama!

## Struktur Folder

```
siskosan/
├── app/
│   ├── Database.php     # Database connection & query builder
│   ├── Auth.php         # Authentication
│   ├── Controllers/
│   ├── Models/
│   └── helpers.php      # Helper functions
├── config/
│   ├── app.php          # App configuration
│   └── database.php     # Database configuration
├── database/
│   └── schema.sql       # Database schema
├── public/
│   ├── index.php        # Entry point & router
│   ├── .htaccess        # Apache rewrite rules
│   ├── assets/          # Static assets
│   └── uploads/         # User uploads
├── views/
│   ├── layouts/         # Layout templates
│   ├── auth/            # Login, register
│   ├── dashboard/       # Dashboard views
│   ├── properties/      # Property management
│   ├── rooms/           # Room management
│   ├── tenants/         # Tenant management
│   ├── invoices/        # Invoice management
│   ├── payments/        # Payment management
│   ├── maintenance/     # Maintenance/Complaints
│   ├── reports/         # Reports
│   ├── profile/         # User profile
│   └── notifications/   # Notifications
├── install.php          # Web installer
├── .env                 # Environment config
└── README.md
```

## Konfigurasi Environment (.env)

```env
APP_NAME=KosManager
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost/siskosan

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=kosmanager
DB_USERNAME=root
DB_PASSWORD=

APP_CURRENCY=IDR
CURRENCY_SYMBOL=Rp

INVOICE_DUE_DAY=5
LATE_FEE_PERCENTAGE=2

NOTIFICATION_EMAIL_ENABLED=false
NOTIFICATION_WHATSAPP_ENABLED=false
WHATSAPP_API_URL=https://api.fonnte.com/send
WHATSAPP_API_TOKEN=
```

## Cron Job untuk Tagihan Otomatis

Tambahkan ke crontab untuk generate tagihan bulanan otomatis (jalankan tanggal 1 setiap bulan):

```bash
# Linux/Mac
0 0 1 * * /usr/bin/php /path/to/siskosan/public/index.php invoices/generate

# Windows Task Scheduler
# Program: php.exe
# Arguments: C:\xampp\htdocs\siskosan\public\index.php invoices/generate
# Trigger: Monthly, day 1, 00:00
```

Atau akses manual via: `http://localhost/siskosan/invoices/generate` (POST request)

## Akses URL

| Modul | URL |
|-------|-----|
| Dashboard | `/dashboard` |
| Properti | `/properties` |
| Kamar | `/rooms` |
| Penyewa | `/tenants` |
| Tagihan | `/invoices` |
| Pembayaran | `/payments` |
| Maintenance | `/maintenance` |
| Laporan Keuangan | `/reports/financial` |
| Laporan Occupancy | `/reports/occupancy` |
| Laporan Tunggakan | `/reports/arrears` |
| Profil | `/profile` |
| Notifikasi | `/notifications` |
| Pengaturan | `/settings` |

## Default Data

Installer membuat:
- 1 Owner: `owner@kosmanager.com` / `password`
- 1 Admin: `admin@kosmanager.com` / `password`
- 1 Tenant: `tenant@kosmanager.com` / `password`
- Settings default (mata uang, jatuh tempo, dll)

## Keamanan

- Password di-hash dengan bcrypt
- CSRF protection pada semua form
- Prepared statements (PDO) mencegah SQL injection
- Role-based access control
- Session secure settings
- Input validation & sanitization

## Troubleshooting

### Error 500 / White Screen
1. Cek `APP_DEBUG=true` di .env untuk detail error
2. Pastikan PHP extensions terinstall: `pdo_mysql`, `mbstring`, `gd`, `curl`
3. Cek permission folder `public/uploads` (writable)

### Database Connection Failed
1. Pastikan MySQL service running
2. Cek kredensial di .env
3. Pastikan user database punya akses CREATE, INSERT, UPDATE, DELETE

### URL Rewrite Not Working (Apache)
1. Pastikan `mod_rewrite` enabled: `a2enmod rewrite`
2. Pastikan `AllowOverride All` di VirtualHost/httpd.conf
3. Restart Apache

### File Upload Failed
1. Cek `upload_max_filesize` & `post_max_size` di php.ini
2. Pastikan folder `public/uploads` writable (chmod 755)

## Development

```bash
# Enable debug mode
APP_DEBUG=true

# View error logs
tail -f /var/log/apache2/error.log
# atau XAMPP: C:\xampp\apache\logs\error.log
```

## Lisensi

MIT License - Silakan gunakan dan modifikasi untuk kebutuhan Anda.

## Support

Untuk pertanyaan atau issue, silakan buat issue di repository ini.

---

**KosManager v1.0** - Dibangun dengan Native PHP + TailwindCSS
