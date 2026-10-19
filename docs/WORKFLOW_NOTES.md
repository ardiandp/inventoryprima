# docs/WORKFLOW_NOTES.md — Catatan Alur Pengerjaan (Handover)

Tujuan: developer lain dapat melanjutkan pengembangan dari catatan ini tanpa mulai dari nol.

## Status Saat Ini (PHASE 1 Selesai)

- **Branch:** `master` (commit terakhir: `672c6d1` — feat(fase-1): database foundation - migration, model, factory, seeder)
- **Fase saat ini:** PHASE 1 — ERD, Data Dictionary, Database Foundation ✅ (selesai)
- **Fase berikutnya:** PHASE 2 — Auth, RBAC, Audit

## Apa yang Sudah Dibangun (PHASE 0)

| Item | File / Lokasi | Keterangan |
|------|---------------|------------|
| Git repo diinitiasi | `.git/` | Commit awal state fresh Laravel 13 |
| Environment | `.env` | `DB_CONNECTION=mysql`, DB `primainventory` (tidak di-commit) |
| Laravel Boost | `composer.json`, `bootstrap/providers.php` | `laravel/boost ^2.10` + service provider terdaftar |
| Pedoman agen | `AGENTS.md` | Stack, role, aturan bisnis kritis, alur pengerjaan, DoD |
| Dokumentasi | `README.md`, `docs/` | Lihat daftar di `AGENTS.md` bagian 8 |
| Layout AdminLTE 3 | `resources/views/layouts/app.blade.php` | Navbar, sidebar, content, footer (CDN assets) |
| Halaman beranda | `resources/views/home.blade.php` | Route `/home` (nama: `home`), `/` redirect ke `/home` |
| Flash alerts | `resources/views/partials/alerts.blade.php` | Success/error/validasi |
| Rute | `routes/web.php` | `/` → redirect `/home`; `/home` → view `home` |
| Test | `tests/Feature/SmokeTest.php`, `ExampleTest.php` | 5 test, 5 lulus, 9 assertion |
| Database | MySQL `primainventory` | Migration default Laravel (users, cache, jobs) berhasil |

## Cara Menjalankan Aplikasi (Lokal)

```powershell
# 1. Install dependensi
composer install

# 2. Salin env dan atur database (sudah diatur di .env lokal)
copy .env.example .env
# pastikan di .env: DB_CONNECTION=mysql, DB_DATABASE=primainventory

# 3. Generate key
php artisan key:generate

# 4. Buat database di MySQL (nama: primainventory), lalu jalankan migration
php artisan migrate

# 5. Jalankan server
php artisan serve

# 6. Buka http://localhost:8000/home
```

## Cara Menjalankan Test

```powershell
vendor\bin\phpunit
# atau
php artisan test
```

Hasil PHASE 0: 5 test, 5 lulus.

## Contoh Pola Commit (WAJIB setiap task selesai)

```
feat(fase-1): create users dan roles migration

- buat migration roles, departments, users
- tambahkan factory dan seeder awal
```

Format: `feat(fase-N): deskripsi singkat` atau `fix(...)`, `docs(...)`, `chore(...)`.

## Aturan Kerja (dari AGENTS.md & PRD)

1. **Satu fase pada satu waktu** — jangan membangun semua fase sekaligus.
2. Sebelum coding, tulis rencana file/migration/model/route/view/test.
3. Setiap task selesai **harus di-commit**.
4. Tambahkan test untuk acceptance criteria, jalankan, lapor hasil aktual.
5. Jangan klaim test lulus sebelum dijalankan.
6. Jangan mengerjakan fase berikutnya sebelum acceptance criteria fase ini terpenuhi.
7. Perbarui `AGENTS.md`, `README.md`, `docs/` setelah tiap fase.
8. Aturan bisnis kritis: jangan ubah stok tanpa ledger; jangan terbitkan PO sebelum approval; jangan `count()+1` nomor dokumen; jangan edit/hapus ledger posted; penerimaan ≠ QC lolos ≠ invoice lunas.

## Fase Berikutnya: PHASE 1 — ERD, Data Dictionary, Database Foundation

Rencana (lihat `docs/IMPLEMENTATION_ROADMAP.md`):
1. Buat ERD akhir dan data dictionary (draft ada di `docs/database/`)
2. Migration identitas/RBAC: users, departments, roles, permissions, pivot, audit_logs
3. Migration master: item_categories, units, items, warehouses, suppliers, projects
4. Migration pengadaan: purchase_requests, purchase_orders, document_sequences, approvals
5. Factory dan seeder untuk setiap tabel
6. Uji `php artisan migrate` dan `migrate:rollback`
7. Test acceptance: FK, unique constraints, index tersedia

## Pertanyaan Terbuka (lihat `docs/OPEN_QUESTIONS.md`)

- Kode barang legacy, barang wajib QC, persetujuan retur, proyek/BOM, pajak/mata uang, integrasi akunting, barcode, delegasi approval, toleransi perubahan harga/kuantitas.
- Dicatat agar diputuskan pemilik proses sebelum fase terkait dimulai.

## Kontak / Pemilik Proses

- Gudang: RPO stok, penerimaan, stok, transfer
- Purchasing: verifikasi RPO, sourcing, PO
- Direktorat: approval pengadaan
- Finance: tagihan, pembayaran
- QC: inspeksi kualitas
- Produksi: RPO material, pemakaian/pengembalian
