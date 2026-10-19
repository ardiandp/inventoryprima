<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Aplikasi Inventori & Pengadaan Barang — PT Prima Mitrainti Sejati

Aplikasi web internal untuk mengintegrasikan permintaan barang (RPO), verifikasi Purchasing, penawaran supplier, approval Direktur, Purchase Order (PO), pembayaran Finance, penerimaan Gudang, inspeksi QC, retur barang reject, pengendalian stok, dan pengeluaran material produksi/proyek.

## Teknologi

- Laravel 13 + PHP 8.3
- MySQL 8.x / InnoDB
- Blade + AdminLTE 3

## Dokumentasi

| File | Keterangan |
|------|------------|
| `PRD_Inventory_Purchasing_PT_Prima_Inti_Laravel_AdminLTE3.md` | Product Requirements Document (sumber kebenaran) |
| `AGENTS.md` | Pedoman agen AI / developer |
| `docs/ARCHITECTURE.md` | Arsitektur sistem |
| `docs/IMPLEMENTATION_ROADMAP.md` | Roadmap fase (PHASE 0–13) |
| `docs/OPEN_QUESTIONS.md` | Pertanyaan terbuka |
| `docs/database/ERD.md` | Diagram entitas-relasi |
| `docs/database/DATA_DICTIONARY.md` | Kamus data |
| `docs/database/MIGRATION_PLAN.md` | Rencana migrasi |
| `docs/UAT_TEST_PLAN.md` | Rencana pengujian |
| `docs/DEPLOYMENT_RUNBOOK.md` | Panduan deployment |

## Role

`super_admin`, `warehouse`, `production`, `purchasing`, `director`, `finance`, `qc`

## Aturan Wajib

- Satu fase dikerjakan pada satu waktu (lihat PRD bagian 11).
- Jangan ubah stok tanpa ledger.
- Jangan terbitkan PO sebelum approval Direktur.
- Jangan gunakan `count()+1` untuk nomor dokumen.
- Jangan edit/hapus ledger yang sudah diposting.
- Penerimaan barang tidak otomatis berarti QC lolos atau invoice lunas.

## Cara Menjalankan

1. `composer install`
2. Salin `.env.example` ke `.env`, atur `DB_CONNECTION=mysql` dan kredensial MySQL
3. `php artisan key:generate`
4. `php artisan migrate`
5. `php artisan serve` (atau via Laragon)

## Lisensi

MIT






































