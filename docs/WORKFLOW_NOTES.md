# docs/WORKFLOW_NOTES.md — Catatan Alur Pengerjaan (Handover)

Tujuan: developer lain dapat melanjutkan pengembangan dari catatan ini tanpa mulai dari nol.

## Status Saat Ini (PHASE 3 Selesai)

- **Branch:** `master`
- **Fase saat ini:** PHASE 3 — Master Data ✅ (selesai)
- **Fase berikutnya:** PHASE 4 — RPO & Verifikasi

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

## Fase Berikutnya: PHASE 4 — RPO & Verifikasi

Rencana (lihat `docs/IMPLEMENTATION_ROADMAP.md`):
1. Baca acceptance criteria PHASE 4 di PRD bagian 11: *RPO multi-item dari Gudang/Produksi, nomor unik, submit, verifikasi, revisi, penolakan, komentar dan notifikasi. Validasi field wajib; pemohon melihat status; revisi memiliki alasan; nomor unik dalam request bersamaan; test alur lulus.*
2. Rencanakan migrations `purchase_requests`, `purchase_request_items`, `document_sequences`.
3. Implementasi nomor dokumen aman dari race condition (jangan `count()+1`).
4. Tests: alur RPO positif/negatif, revisi beralasan, akses tanpa permission ditolak.
5. Update dokumentasi + commit per task.

## Apa yang Sudah Dibangun (PHASE 3 — Master Data)

| Item | File / Lokasi | Keterangan |
|------|---------------|------------|
| Controllers (8) | `app/Http/Controllers/` | `ItemCategoryController`, `UnitController`, `ItemController`, `ItemUnitConversionController`, `SupplierController`, `WarehouseController`, `WarehouseLocationController`, `ProjectController` — CRUD + search/filter/pagination + validasi kode unik + proteksi hapus (soft delete; item/kategori/satuan/gudang terpakai diblokir) |
| Bugfix relasi | `app/Models/Unit.php`, `app/Models/ItemCategory.php` | `Unit::items()` → FK `default_unit_id`; `ItemCategory::items()` → FK `category_id` (sebelumnya FK default salah sehingga proteksi hapus tidak jalan) |
| Routes | `routes/web.php` | Prefix `/master`, proteksi `permission:master.view/create/edit/delete` per aksi |
| Views | `resources/views/master/` | index/create/edit AdminLTE 3 untuk categories, units, items (+konversi), suppliers, warehouses, locations, projects |
| Sidebar | `resources/views/layouts/app.blade.php` | Menu MASTER DATA (gate `master.view`) |
| Tests | `tests/Feature/MasterDataTest.php` (base+akses+kategori+satuan, 10), `MasterDataItemTest.php` (barang+konversi, 7), `MasterDataPartnerTest.php` (supplier+gudang/lokasi+proyek, 4) | 31 tests master data |

**Hasil test PHASE 3:** 85 tests, 291 assertions, **semua lulus** (`vendor/bin/phpunit --testsuite Feature --log-junit`, errors=0 failures=0).

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
