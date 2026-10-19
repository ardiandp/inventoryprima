# docs/ARCHITECTURE.md — Arsitektur Sistem

Berdasarkan PRD bagian 8 (Teknologi dan Arsitektur).

## Stack

- **Backend:** Laravel 13, PHP 8.3
- **Database:** MySQL 8.x / InnoDB, foreign keys, migrations, indexes
- **Frontend:** Blade + AdminLTE 3, Bootstrap, jQuery
- **Cache/Sesi/Queue:** file/database (sesuai `.env`), dapat disesuaikan di produksi

## Pola Kode

- Eloquent untuk akses data
- Form Request untuk validasi input
- Policies/Gates untuk otorisasi (diterapkan di route/controller)
- Service/Action class untuk alur bisnis kritis (posting stok, approval, PO, pembayaran)
- Modular monolith di dalam satu aplikasi Laravel

## Aturan Data

- Uang menggunakan DECIMAL (bukan float)
- Ledger stok adalah sumber kebenaran transaksi; `stock_balances` hanya ringkasan yang dapat direkonsiliasi
- Nomor dokumen unik aman dari race condition (bukan `count()+1`)
- File disimpan di Laravel Storage, upload divalidasi, akses lampiran dilindungi
- Rahasia hanya di environment variables (tidak di-commit)
- Transaksi yang sudah diposting tidak boleh hard-delete; koreksi via reversal/adjustment

## RBAC

- Role: super_admin, warehouse, production, purchasing, director, finance, qc
- Permission dicek di server-side (route/controller), bukan hanya menyembunyikan menu
- Pengaju tidak boleh menyetujui pengajuan sendiri

## Struktur Folder Utama

```
app/
  Actions/          (aksi bisnis kritis, jika diperlukan)
  Http/Controllers/
  Http/Requests/    (Form Request)
  Models/
  Policies/
database/
  migrations/
  factories/
  seeders/
resources/
  views/
  lang/id/
routes/
tests/
```
