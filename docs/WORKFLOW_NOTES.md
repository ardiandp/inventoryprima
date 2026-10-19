# docs/WORKFLOW_NOTES.md — Catatan Alur Pengerjaan (Handover)

Tujuan: developer lain dapat melanjutkan pengembangan dari catatan ini tanpa mulai dari nol.

## Status Saat Ini (PHASE 2 Selesai)

- **Branch:** `master` (commit terakhir: `5311beb` — test(fase-2): tests auth, RBAC, user management, audit log)
- **Fase saat ini:** PHASE 2 — Auth, RBAC, Audit ✅ (selesai)
- **Fase berikutnya:** PHASE 3 — Master Data

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

## Apa yang Sudah Dibangun (PHASE 1 — Database Foundation)

| Item | File / Lokasi | Keterangan |
|------|---------------|------------|
| **Dokumentasi** | | |
| ERD Mermaid | `docs/database/ERD.md` | Diagram ERD 16 tabel + catatan desain |
| Data Dictionary | `docs/database/DATA_DICTIONARY.md` | Detail kolom lengkap semua tabel PHASE 1 |
| **Migrations (17 file)** | | |
| Identitas & RBAC | `2026_10_19_090000-090007` | departments, roles, permissions, role_user, permission_role, audit_logs, notifications, attachments |
| Master Data | `2026_10_19_090008-090015` | item_categories, units, items, item_unit_conversions, warehouses, warehouse_locations, suppliers, projects |
| Alter Users | `2026_10_19_090016` | add department_id ke tabel users |
| **Models (13 file)** | `app/Models/` | Department, Role, Permission, ItemCategory, Unit, Item, ItemUnitConversion, Warehouse, WarehouseLocation, Supplier, Project, AuditLog, Attachment |
| **User Model Update** | `app/Models/User.php` | Tambah relasi department, roles, method hasRole |
| **Factories (9 file)** | `database/Factories/` | Department, Role, Permission, ItemCategory, Unit, Item, Warehouse, Supplier, Project |
| **Seeder** | `database/seeders/DatabaseSeeder.php` | 7 dept, 7 role, 28 permission, 6 unit, 7 kategori, 4 gudang, 2 supplier, 2 proyek, 1 admin, 3 item |
| **Tests** | `tests/Feature/DatabaseFoundationTest.php` | 12 tests, 73 assertions, semua passing |

## Apa yang Sudah Dibangun (PHASE 2 — Auth, RBAC, Audit)

| Item | File / Lokasi | Keterangan |
|------|---------------|------------|
| Login/Logout | `app/Http/Controllers/Auth/LoginController.php` | `create` (form), `store` (validasi + session regenerate + audit), `destroy` (logout + audit) |
| AuditLogger | `app/Support/AuditLogger.php` | Helper statis `AuditLogger::log(action, modelType, modelId, changes, context)` — simpan aktor, IP, user agent |
| RBAC middleware | `app/Http/Middleware/CheckPermission.php` | Alias `permission` di `bootstrap/app.php`; cek `User::hasPermission()`; 403 jika tidak berwenang |
| User helpers | `app/Models/User.php` | `hasPermission()`, `roleName()` (role aktif → permission) |
| Seeder role→permission | `database/seeders/DatabaseSeeder.php` | `seedRolePermissions()`: super_admin=all(28), warehouse/production/purchasing/director/finance/qc sesuai PRD bag. 3; **finance TIDAK punya `inventory.adjust`** |
| Controllers | `app/Http/Controllers/` | `UserController` (CRUD + cegah hapus diri/last admin), `RoleController` (+`permissions()`), `AuditLogController` (filter) |
| Views (9 file) | `resources/views/` | `auth/login`, `users/{index,create,edit}`, `roles/{index,create,edit}`, `permissions/index`, `audit_logs/index` |
| Layout update | `resources/views/layouts/app.blade.php` | Navbar dropdown user + tombol keluar; sidebar Pengaturan difilter permission |
| Routes | `routes/web.php` | `/login` (guest), `/logout`, `/home` (auth); `users.*` (`permission:user.manage`), `roles.*` + `permissions` (`permission:role.manage`), `audit-logs` (`permission:audit.view`) |
| Migration fix | `2026_10_19_090005_create_audit_logs_table.php` | `model_type` → nullable (login/logout tanpa model) |
| Tests (5 file baru) | `tests/Feature/` | AuthTest (11), RbacTest (14), UserManagementTest (11), AuditLogTest (7); SmokeTest/ExampleTest disesuaikan auth |

**Hasil test PHASE 2:** 63 tests, 185 assertions, **semua lulus** (termasuk 12 DatabaseFoundation lama).

## Cara Menjalankan Aplikasi (Lokal)

```powershell
# 1. Install dependensi
composer install

# 2. Salin env dan atur database (sudah diatur di .env lokal)
copy .env.example .env
# pastikan di .env: DB_CONNECTION=mysql, DB_DATABASE=primainventory

# 3. Generate key
php artisan key:generate

# 4. Buat database di MySQL (nama: primainventory), lalu jalankan migration + seeder
php artisan migrate --seed

# 5. Jalankan server
php artisan serve

# 6. Buka http://localhost:8000/home
# Login: admin@primainventory.co.id / password
```

## Cara Menjalankan Test

```powershell
vendor\bin\phpunit
# atau
php artisan test
```

**Hasil PHASE 2:** 63 tests, 185 assertions, semua lulus (`php artisan test --compact`).

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

## Fase Berikutnya: PHASE 3 — Master Data

Rencana (lihat `docs/IMPLEMENTATION_ROADMAP.md`):
1. Baca acceptance criteria PHASE 3 di PRD bagian 11: *CRUD barang, kategori, satuan, supplier, gudang/lokasi, proyek opsional; validasi, search, filter, pagination. Kode unik; item terpakai tidak dihapus fisik; master nonaktif tidak dapat dipilih transaksi baru; CRUD tests lulus.*
2. Rencanakan routes, controllers, Form Requests, views per entitas.
3. Implementasi CRUD per entitas (master.view/create/edit/delete) dengan Gate/permission `master.*`.
4. Validasi kode unik, soft-nonaktif alih-alih hard delete untuk item terpakai.
5. Search + filter + pagination AdminLTE 3.
6. Tests: CRUD positif/negatif, kode duplikat, cegah hapus item terpakai, akses tanpa permission ditolak.
7. Update dokumentasi + commit per task.

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
