# Data Dictionary — PHASE 1 (Database Foundation)

Kamus data lengkap untuk tabel-tabel yang dibangun di PHASE 1.
Dokumen ini dipelihara oleh agen AI setelah setiap fase selesai.

---

## 1. Tabel Identitas & RBAC

### 1.1 departments

Deskripsi: Struktur organisasi perusahaan (Direksi, Gudang, Produksi, dll.)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| code | varchar(10) | NO | - | UK | Kode departemen (DEPT001, DEPT002, dst.) |
| name | varchar(100) | NO | - | NO | Nama departemen (Direktorat, Gudang, Produksi, dll.) |
| description | text | YES | NULL | NO | Deskripsi tugas/fungsi departemen |
| is_active | boolean | NO | true | NO | Status aktif (true/false) |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

**Index:**
- PRIMARY KEY: id
- UNIQUE: code

**Relasi:**
- hasMany: users (department_id)
- hasMany: projects (department_id)

---

### 1.2 roles

Deskripsi: Role pengguna dalam sistem (RBAC)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| name | varchar(50) | NO | - | UK | Nama role (super_admin, warehouse, production, purchasing, director, finance, qc) |
| description | varchar(255) | YES | NULL | NO | Deskripsi role dan tanggung jawab |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

**Index:**
- PRIMARY KEY: id
- UNIQUE: name

**Relasi:**
- belongsToMany: users (via role_user)
- belongsToMany: permissions (via permission_role)

---

### 1.3 permissions

Deskripsi: Hak akses granular (module.action)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| name | varchar(100) | NO | - | UK | Nama permission (purchase_request.create, po.approve, dst.) |
| description | varchar(255) | YES | NULL | NO | Deskripsi permission |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

**Index:**
- PRIMARY KEY: id
- UNIQUE: name

**Format nama permission:**
- `module.action` (misal: `purchase_request.create`, `po.approve`, `stock.adjust`)
- `module.action.resource` (opsional, misal: `rpo.approve.department`)

**Relasi:**
- belongsToMany: roles (via permission_role)

---

### 1.4 role_user (pivot)

Deskripsi: Relasi many-to-many Users ↔ Roles

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| role_id | bigint(20) unsigned | NO | - | NO (composite PK) | FK → roles.id |
| user_id | bigint(20) unsigned | NO | - | NO (composite PK) | FK → users.id |

**Index:**
- PRIMARY KEY: role_id, user_id
- INDEX: user_id

**Relasi:**
- belongsTo: role
- belongsTo: user

---

### 1.5 permission_role (pivot)

Deskripsi: Relasi many-to-many Roles ↔ Permissions

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| permission_id | bigint(20) unsigned | NO | - | NO (composite PK) | FK → permissions.id |
| role_id | bigint(20) unsigned | NO | - | NO (composite PK) | FK → roles.id |

**Index:**
- PRIMARY KEY: permission_id, role_id
- INDEX: role_id

**Relasi:**
- belongsTo: permission
- belongsTo: role

---

### 1.6 audit_logs

Deskripsi: Log aktivitas kritis sistem (siapa, apa, kapan, perubahan value)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| user_id | bigint(20) unsigned | YES | NULL | NO | FK → users.id (actor yang melakukan aksi) |
| action | varchar(100) | NO | - | NO | Jenis aksi (create, update, delete, login, approve, reject, post, cancel, adjust, dll.) |
| auditable_type | varchar(255) | YES | NULL | NO | Nama model yang diaudit (App\Models\Item, dst.) |
| auditable_id | bigint(20) unsigned | YES | NULL | NO | ID record yang diaudit |
| old_values | json | YES | NULL | NO | Nilai lama sebelum update (untuk action=update) |
| new_values | json | YES | NULL | NO | Nilai baru setelah update/create |
| ip_address | varchar(45) | YES | NULL | NO | IP address actor (IPv4/IPv6) |
| user_agent | text | YES | NULL | NO | User agent browser |
| reference_type | varchar(255) | YES | NULL | NO | Nama model referensi (opsional, misal: PO untuk RPO approve) |
| reference_id | bigint(20) unsigned | YES | NULL | NO | ID referensi (opsional) |
| notes | text | YES | NULL | NO | Catatan tambahan (alasan reject, dll.) |
| created_at | timestamp | YES | NULL | NO | Waktu kejadian |
| updated_at | timestamp | YES | NULL | NO | Waktu update (jarang berubah) |

**Index:**
- PRIMARY KEY: id
- INDEX: user_id
- INDEX: action
- INDEX: auditable_type, auditable_id (polymorphic)
- INDEX: created_at

**Relasi:**
- belongsTo: user (actor)
- morphTo: auditable (polymorphic ke model apapun)
- morphTo: reference (polymorphic, opsional)

---

### 1.7 notifications

Deskripsi: Notifikasi internal pengguna (Laravel database notifications)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
- belongsTo: user (uploader)

---

## 2. Tabel Master Data

### 2.1 item_categories

Deskripsi: Kategori item/barang (Raw Material, Component, Packaging, dll.)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| code | varchar(20) | NO | - | UK | Kode kategori (CAT001, CAT002, dst.) |
| name | varchar(100) | NO | - | NO | Nama kategori (Raw Material, Component, Packaging, Consumable, Spare Part, Finished Good, Other) |
| description | text | YES | NULL | NO | Deskripsi kategori |
| parent_id | bigint(20) unsigned | YES | NULL | NO | FK → item_categories.id (hierarki kategori, self-referencing) |
| is_active | boolean | NO | true | NO | Status aktif (true/false) |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

**Index:**
- PRIMARY KEY: id
- UNIQUE: code
- INDEX: parent_id
- INDEX: is_active

**Relasi:**
- belongsTo: parent (self-referencing, parent_id)
- hasMany: children (self-referencing, parent_id)
- hasMany: items (category_id)

---

### 2.2 units

Deskripsi: Satuan ukur item (PCS, KG, MTR, dll.)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| code | varchar(10) | NO | - | UK | Kode satuan (PCS, KG, MTR, LTR, BOX, DOZ) |
| name | varchar(50) | NO | - | NO | Nama satuan (Pieces, Kilogram, Meter, Liter, Box, Dozen) |
| symbol | varchar(10) | YES | NULL | NO | Simbol satuan (pcs, kg, m, l, box, dz) |
| decimal_places | int(11) | NO | 0 | NO | Jumlah desimal untuk qty (0 = integer, 2 = 2 desimal) |
| description | text | YES | NULL | NO | Deskripsi satuan |
| is_active | boolean | NO | true | NO | Status aktif (true/false) |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

**Index:**
- PRIMARY KEY: id
- UNIQUE: code
- INDEX: is_active

**Relasi:**
- hasMany: items (default_unit_id)
- hasMany: item_unit_conversions (from_unit_id)
- hasMany: item_unit_conversions (to_unit_id)
|-------|------|----------|---------|--------|-----------|
| id | uuid | NO | - | PK | UUID unik (Laravel default) |
| type | varchar(255) | NO | - | NO | Tipe notifikasi (class name, misal: App\Notifications\POApproved) |
| notifiable_type | varchar(255) | NO | - | NO | Nama model penerima (App\Models\User) |
- hasMany: item_unit_conversions (to_unit_id)

---

### 2.3 items

Deskripsi: Master item/barang yang dikelola

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| code | varchar(50) | NO | - | UK | Kode item unik (ITM000001, dst.) |
| name | varchar(255) | NO | - | NO | Nama item |
| description | text | YES | NULL | NO | Deskripsi item |
| category_id | bigint(20) unsigned | NO | - | NO | FK → item_categories.id |
| default_unit_id | bigint(20) unsigned | NO | - | NO | FK → units.id (satuan default) |
| item_type | enum | NO | material | NO | Tipe item: material, component, finished_good, consumable, spare_part, other |
| safety_stock | decimal(15,4) | NO | 0 | NO | Ambang batas stok minimum |
| status | enum | NO | active | NO | Status: active, inactive, discontinued, blocked |
| specification | text | YES | NULL | NO | Spesifikasi teknis item |
| notes | text | YES | NULL | NO | Catatan tambahan |
| is_active | boolean | NO | true | NO | Status aktif (true/false) |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

**Index:**
- PRIMARY KEY: id
- UNIQUE: code
- INDEX: category_id
- INDEX: default_unit_id
- INDEX: is_active
- INDEX: status

**Relasi:**
- belongsTo: category (item_categories)
- belongsTo: defaultUnit (units)
- hasMany: item_unit_conversions
- hasMany: stock_transactions (PHASE 9)

---

### 2.4 item_unit_conversions

Deskripsi: Konversi satuan per item (misal: 1 BOX = 12 PCS)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| item_id | bigint(20) unsigned | NO | - | NO (composite UK) | FK → items.id |
| from_unit_id | bigint(20) unsigned | NO | - | NO (composite UK) | FK → units.id (satuan asal) |
| to_unit_id | bigint(20) unsigned | NO | - | NO (composite UK) | FK → units.id (satuan tujuan) |
| multiplier | decimal(15,6) | NO | - | NO | Faktor konversi (from_unit × multiplier = to_unit) |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

**Index:**
- PRIMARY KEY: id
- UNIQUE: item_id, from_unit_id, to_unit_id
- INDEX: item_id
- INDEX: from_unit_id
- INDEX: to_unit_id

**Relasi:**
- belongsTo: item
- belongsTo: fromUnit (units)
- belongsTo: toUnit (units)

**Contoh:**
- Item: Cable (item_id=1), from_unit: BOX (1), to_unit: PCS (2), multiplier: 12 → 1 BOX = 12 PCS
- Item: Screw (item_id=2), from_unit: KG (3), to_unit: PCS (2), multiplier: 100 → 1 KG = 100 PCS
| notifiable_id | bigint(20) unsigned | NO | - | NO | ID penerima |
| data | text | NO | - | NO | Data notifikasi (serialized JSON) |
| read_at | timestamp | YES | NULL | NO | Waktu dibaca (NULL = belum dibaca) |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan |
| updated_at | timestamp | YES | NULL | NO | Waktu update |

**Index:**
- PRIMARY KEY: id
- Item: Screw (item_id=2), from_unit: KG (3), to_unit: PCS (2), multiplier: 100 → 1 KG = 100 PCS

---

### 2.5 warehouses

Deskripsi: Master gudang (lokasi fisik penyimpanan)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| code | varchar(20) | NO | - | UK | Kode gudang (WH001, WH002, dst.) |
| name | varchar(100) | NO | - | NO | Nama gudang (Main Warehouse, Raw Material Warehouse, dst.) |
| address | text | YES | NULL | NO | Alamat lengkap gudang |
| city | varchar(100) | YES | NULL | NO | Kota |
| phone | varchar(20) | YES | NULL | NO | Nomor telepon gudang |
| email | varchar(100) | YES | NULL | NO | Email gudang |
| description | text | YES | NULL | NO | Deskripsi gudang |
| is_active | boolean | NO | true | NO | Status aktif (true/false) |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

**Index:**
- PRIMARY KEY: id
- UNIQUE: code
- INDEX: is_active

**Relasi:**
- hasMany: warehouse_locations
- hasMany: stock_balances (PHASE 9)

---

### 2.6 warehouse_locations

Deskripsi: Sub-lokasi dalam gudang (rack, aisle, shelf, bin)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| warehouse_id | bigint(20) unsigned | NO | - | NO | FK → warehouses.id |
| code | varchar(50) | NO | - | NO | Kode lokasi (A-01-01, B-02-03, dst.) |
| name | varchar(100) | NO | - | NO | Nama lokasi (Rack A, Aisle 1, dst.) |
| description | text | YES | NULL | NO | Deskripsi lokasi |
| is_active | boolean | NO | true | NO | Status aktif (true/false) |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

**Index:**
- PRIMARY KEY: id
- UNIQUE: warehouse_id, code
- INDEX: warehouse_id
- INDEX: is_active
- belongsTo: warehouse
- hasMany: stock_balances (PHASE 9)

---

### 2.7 suppliers

Deskripsi: Master supplier/vendor

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| code | varchar(20) | NO | - | UK | Kode supplier (SUP001, SUP002, dst.) |
| name | varchar(255) | NO | - | NO | Nama supplier/perusahaan |
| address | text | YES | NULL | NO | Alamat lengkap |
| city | varchar(100) | YES | NULL | NO | Kota |
| province | varchar(100) | YES | NULL | NO | Provinsi |
| postal_code | varchar(10) | YES | NULL | NO | Kode pos |
| phone | varchar(20) | YES | NULL | NO | Nomor telepon |
| email | varchar(100) | YES | NULL | NO | Email |
| website | varchar(255) | YES | NULL | NO | Website |
| npwp | varchar(30) | YES | NULL | NO | Nomor Pokok Wajib Pajak |
| pic_name | varchar(100) | YES | NULL | NO | Nama Person in Charge |
| pic_phone | varchar(20) | YES | NULL | NO | Telepon PIC |
| pic_email | varchar(100) | YES | NULL | NO | Email PIC |
| payment_terms | text | YES | NULL | NO | Syarat pembayaran (Net 30, COD, 50% DP, dst.) |
| notes | text | YES | NULL | NO | Catatan tambahan |
| status | enum | NO | active | NO | Status: active, inactive, blocked, pending_review |
| is_active | boolean | NO | true | NO | Status aktif (true/false) |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

**Index:**
- PRIMARY KEY: id
- UNIQUE: code
- INDEX: is_active
- INDEX: status

**Relasi:**
- hasMany: purchase_orders (PHASE 4)

---

### 2.8 projects

Deskripsi: Master proyek (untuk RPO material proyek)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| code | varchar(20) | NO | - | UK | Kode proyek (PRJ001, PRJ002, dst.) |
| name | varchar(255) | NO | - | NO | Nama proyek |
| description | text | YES | NULL | NO | Deskripsi proyek |
| department_id | bigint(20) unsigned | NO | - | NO | FK → departments.id (departemen pemilik proyek) |
| start_date | date | YES | NULL | NO | Tanggal mulai proyek |
| end_date | date | YES | NULL | NO | Tanggal selesai proyek |
| budget | decimal(15,2) | YES | NULL | NO | Anggaran proyek (opsional) |
| status | enum | NO | planned | NO | Status: planned, active, on_hold, completed, cancelled |
| notes | text | YES | NULL | NO | Catatan tambahan |
| is_active | boolean | NO | true | NO | Status aktif (true/false) |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

- belongsTo: department
- hasMany: purchase_requests (PHASE 4)

---

## 3. Tabel Alter

### 3.1 users (ALTER)

Deskripsi: Tabel users bawaan Laravel, ditambah kolom `department_id`

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| name | varchar(255) | NO | - | NO | Nama lengkap |
| email | varchar(255) | NO | - | UK | Email (login) |
| email_verified_at | timestamp | YES | NULL | NO | Waktu verifikasi email |
| password | varchar(255) | NO | - | NO | Password (hashed) |
| phone | varchar(20) | YES | NULL | NO | Nomor telepon |
| department_id | bigint(20) | YES | NULL | NO | **FK → departments.id (DITAMBAHKAN DI PHASE 1)** |
| is_active | boolean | NO | true | NO | Status aktif (true/false) |
| last_login_at | timestamp | YES | NULL | NO | Waktu login terakhir |
| remember_token | varchar(100) | YES | NULL | NO | Token "remember me" |
| created_at | timestamp | YES | NULL | NO | Waktu pembuatan record |
| updated_at | timestamp | YES | NULL | NO | Waktu update terakhir |

**Index (setelah ALTER):**
- PRIMARY KEY: id
- UNIQUE: email
- INDEX: department_id (DITAMBAHKAN)
- INDEX: is_active (DITAMBAHKAN)

**Relasi (setelah ALTER):**
- belongsTo: department (DITAMBAHKAN)
- belongsToMany: roles (via role_user)
- hasMany: audit_logs
- hasMany: notifications (morph)
- hasMany: attachments (uploaded_by)

---

## Aturan Umum Database

### Tipe Data
- **Uang (harga, total, budget)**: DECIMAL(15,2) — JANGAN FLOAT
- **Kuantitas (qty, stock)**: DECIMAL(15,4) — mendukung desimal (misal: 1.5 KG)
- **Multiplier konversi**: DECIMAL(15,6) — presisi tinggi
- **Boolean**: tinyint(1) atau boolean (Laravel default)
- **Timestamp**: timestamp dengan timezone Asia/Jakarta
- **Date**: date (tanpa time)

### Constraint
- **Unique**: code (semua master), email (users), name (roles, permissions), uuid (attachments)
- **Foreign Key**: Semua relasi harus menggunakan FK constraint
- **Delete Restriction**: Default RESTRICT (jangan CASCADE untuk transaksi)
- **NOT NULL**: Kolom wajib diisi (code, name, FK utama)
- **DEFAULT**: Kolom opsional dengan default (is_active=true, status='active')

### Index Strategy
- **Primary Key**: auto-increment bigint
- **Unique Index**: code, email, name, uuid
- **Composite Unique**: item_unit_conversions (item_id, from_unit_id, to_unit_id)
- **Foreign Key Index**: Semua kolom FK harus di-index
- **Query Pattern Index**: status, is_active, created_at (untuk audit_logs), category (attachments)

### Soft Delete
- **PHASE 1**: Belum menggunakan soft delete (hapus = hard delete)
- **Best practice**: Gunakan status inactive/cancelled untuk master data
- **PHASE 2+**: Pertimbangkan soft delete untuk transaksi yang dibatalkan

---

## Referensi

- ERD: `docs/database/ERD.md`
- Migration Plan: `docs/database/MIGRATION_PLAN.md`
- PRD: `PRD_Inventory_Purchasing_PT_Prima_Inti_Laravel_AdminLTE3.md`

---

**Catatan**: Data dictionary ini diperbarui setelah PHASE 1 (Database Foundation).
Tabel di PHASE 4-13 akan ditambahkan setelah implementasi masing-masing fase.
**Index:**
- PRIMARY KEY: id
- UNIQUE: code
- INDEX: department_id
- INDEX: is_active
- INDEX: status

**Relasi:**
- belongsTo: department
- hasMany: purchase_requests (PHASE 4)

**Relasi:**
- belongsTo: warehouse
- hasMany: stock_balances (PHASE 9)
- INDEX: notifiable_type, notifiable_id (polymorphic)
- INDEX: read_at

**Relasi:**
- morphTo: notifiable (polymorphic, biasanya User)

---

### 1.8 attachments

Deskripsi: File upload universal (bukti foto, dokumen, surat jalan, invoice, dll.)

| Kolom | Tipe | Nullable | Default | Unique | Deskripsi |
|-------|------|----------|---------|--------|-----------|
| id | bigint(20) unsigned | NO | auto_increment | PK | ID unik |
| uuid | char(36) | NO | - | UK | UUID untuk URL publik (mencegah enumeration) |
| attachable_type | varchar(255) | NO | - | NO | Nama model pemilik (App\Models\GoodsReceipt, dst.) |
| attachable_id | bigint(20) unsigned | NO | - | NO | ID model pemilik |
| file_name | varchar(255) | NO | - | NO | Nama file di storage (hashed, misal: abc123.pdf) |
| original_name | varchar(255) | NO | - | NO | Nama file asli dari user (misal: surat-jalan-001.pdf) |
| mime_type | varchar(100) | NO | - | NO | MIME type file (application/pdf, image/jpeg, dst.) |
| file_size | bigint(20) | NO | - | NO | Ukuran file dalam bytes |
| path | varchar(255) | NO | - | NO | Path relatif di storage disk (misal: attachments/2026/10/) |
| disk | varchar(50) | NO | public | NO | Storage disk (public, local, s3, dst.) |
| category | varchar(50) | YES | NULL | NO | Kategori file (surat_jalan, invoice, foto_barang, bukti_inspeksi, foto_reject, spk, kontrak, dst.) |
| uploaded_by | bigint(20) unsigned | NO | - | NO | FK → users.id (siapa yang upload) |
| created_at | timestamp | YES | NULL | NO | Waktu upload |
| updated_at | timestamp | YES | NULL | NO | Waktu update |

**Index:**
- PRIMARY KEY: id
- UNIQUE: uuid
- INDEX: attachable_type, attachable_id (polymorphic)
- INDEX: uploaded_by
- INDEX: category

**Relasi:**
- morphTo: attachable (polymorphic ke model apapun)
- belongsTo: user (uploader)
- belongsToMany: roles (via permission_role)
