# Entity Relationship Diagram (ERD) — PHASE 1

Sumber kebenaran struktur database aplikasi Inventory & Purchasing PT Prima Mitrainti Sejati.

## Diagram ERD (Mermaid)

```mermaid
erDiagram
    USERS {
        bigint id PK
        string name
        string email UK
        string password
        string phone
        bigint department_id FK
        boolean is_active
        timestamp last_login_at
        timestamp email_verified_at
        timestamp remember_token
        timestamp created_at
        timestamp updated_at
    }
    DEPARTMENTS {
        bigint id PK
        string code UK
        string name
        text description
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    ROLES {
        bigint id PK
        string name UK
        string description
        timestamp created_at
        timestamp updated_at
    }
    PERMISSIONS {
        bigint id PK
        string name UK
        string description
        timestamp created_at
        timestamp updated_at
    }
    ROLE_USER {
        bigint role_id FK
        bigint user_id FK
    }
    PERMISSION_ROLE {
        bigint permission_id FK
        bigint role_id FK
    }
    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK
        string action
        string auditable_type
        bigint auditable_id
        json old_values
        json new_values
        string ip_address
        string user_agent
        string reference_type
        bigint reference_id
        text notes
        timestamp created_at
        timestamp updated_at
    }
    NOTIFICATIONS {
        bigint id PK
        string type
        string notifiable_type
        bigint notifiable_id
        text data
        timestamp read_at
        timestamp created_at
        timestamp updated_at
    }
    ATTACHMENTS {
        bigint id PK
        string uuid
        string attachable_type
        bigint attachable_id
        string file_name
        string original_name
        string mime_type
        bigint file_size
        string path
        string disk
        string category
        bigint uploaded_by FK
        timestamp created_at
        timestamp updated_at
    }
    ITEM_CATEGORIES {
        bigint id PK
        string code UK
        string name
        text description
        bigint parent_id FK
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    UNITS {
        bigint id PK
        string code UK
    WAREHOUSES ||--o{ WAREHOUSE_LOCATIONS : contains
```

## Catatan Desain PHASE 1

### Tabel Identitas & RBAC (7 tabel)
1. **departments** — Struktur organisasi (Direksi, Gudang, Produksi, Purchasing, Keuangan, QC, Administrasi)
2. **roles** — Role pengguna (super_admin, warehouse, production, purchasing, director, finance, qc)
3. **permissions** — Hak akses granular (module.action, misal: purchase_request.create)
4. **role_user** — Pivot: many-to-many User ↔ Role
5. **permission_role** — Pivot: many-to-many Role ↔ Permission
6. **audit_logs** — Log aktivitas kritis (aktor, aksi, waktu, old/new values, IP, user agent)
7. **notifications** — Notifikasi internal pengguna (Laravel database notifications)

### Tabel Attachment
8. **attachments** — File upload universal (bukti foto, dokumen, surat jalan, invoice)
   - Polymorphic: attachable_type + attachable_id (melekat ke model apapun)
   - Kolom `uploaded_by` → users.id (audit trail)
   - Kolom `category` → kategori file (surat_jalan, invoice, foto_barang, bukti_inspeksi, dll.)

### Tabel Master Data (8 tabel)
9. **item_categories** — Kategori item (Raw Material, Component, Packaging, Consumable, Spare Part, Finished Good, Other)
   - Self-referencing FK: parent_id (hierarki kategori)
10. **units** — Satuan ukur (PCS, KG, MTR, LTR, BOX, DOZ)
    - decimal_places: jumlah desimal untuk qty (misal: KG = 2 desimal)
11. **items** — Master item/barang
    - item_type: tipe item (material, component, finished_good, consumable, spare_part, other)
    - status: active, inactive, discontinued, blocked
    - safety_stock: ambang batas stok minimum
12. **item_unit_conversions** — Konversi satuan per item (misal: 1 BOX = 12 PCS)
    - multiplier: faktor konversi (from_unit × multiplier = to_unit)
13. **warehouses** — Master gudang (Main, Raw Material, Finished Goods, Quarantine)
14. **warehouse_locations** — Sub-lokasi dalam gudang (rack, aisle, shelf, bin)
15. **suppliers** — Master supplier/vendor
    - status: active, inactive, blocked, pending_review
    - payment_terms: syarat pembayaran (Net 30, COD, dll.)
16. **projects** — Master proyek (untuk RPO material proyek)
    - status: planned, active, on_hold, completed, cancelled
    - budget: anggaran proyek (opsional)

### Tabel Alter (1 tabel)
17. **users** — ALTER: tambah kolom `department_id` (FK → departments.id)

## Strategi Primary Key

- **Auto-incrementing bigint** (default Laravel) untuk semua tabel
- **attachments** memiliki kolom `uuid` (string 36) sebagai unique identifier untuk URL publik
- Nomor dokumen (RPO, PO, dll.) akan menggunakan **document_sequences** table dengan lock untuk mencegah race condition (PHASE 4+)

## Strategi Index

- **Unique index**: code (semua master), email (users), name (roles, permissions)
- **Composite unique**: item_unit_conversions (item_id, from_unit_id, to_unit_id)
- **Index**: foreign keys, kolom status, kolom is_active, kolom created_at (untuk audit logs)

## Strategi Soft Delete

- **PHASE 1**: Belum menggunakan soft delete untuk tabel master (hapus = hard delete)
- **Best practice**: Gunakan status inactive/cancelled alih-alih hard delete untuk master data
- **PHASE 2+**: Soft delete opsional untuk transaksi (RPO, PO, Receipt) yang dibatalkan

## Relasi Utama

### Identitas & RBAC
- **users** → **departments** (many-to-one): Setiap user belongs to satu department
- **users** ↔ **roles** (many-to-many via role_user): Satu user bisa punya banyak role
- **roles** ↔ **permissions** (many-to-many via permission_role): Satu role punya banyak permission
- **users** → **audit_logs** (one-to-many): Setiap log punya actor yang melakukan aksi
- **users** → **notifications** (one-to-many): Laravel database notifications (notifiable morph)

### Master Data
- **item_categories** → **items** (one-to-many): Satu kategori punya banyak item
- **item_categories** → **item_categories** (self-referencing): Hierarki kategori (parent → child)
- **units** → **items** (one-to-many via default_unit_id): Satu unit dipakai sebagai default unit
- **items** ↔ **units** (many-to-many via item_unit_conversions): Konversi satuan per item
- **warehouses** → **warehouse_locations** (one-to-many): Satu gudang punya banyak lokasi
- **departments** → **projects** (one-to-many): Satu departemen punya banyak proyek
- **users** → **attachments** (one-to-many via uploaded_by): Siapa yang upload file

### Polymorphic Attachment
- **attachments** → **[RPO, PO, Receipt, QC, Return, StockTransaction]** (polymorphic)
  - attachable_type + attachable_id menunjuk ke model apapun
  - Contoh: surat_jalan melekat ke GoodsReceipt, foto reject melekat ke QC Inspection

## Checklist Tabel (PHASE 1)

- [x] departments
- [x] roles
- [x] permissions
- [x] role_user (pivot)
- [x] permission_role (pivot)
- [x] audit_logs
- [x] notifications (Laravel database notifications)
- [x] attachments (polymorphic)
- [x] item_categories
- [x] units
- [x] items
- [x] item_unit_conversions
- [x] warehouses
- [x] warehouse_locations
- [x] suppliers
- [x] projects
- [x] users (ALTER: add department_id)

**Total: 16 tabel baru + 1 tabel alter (users)**

## Tabel di Fase Berikutnya

Lihat `docs/database/MIGRATION_PLAN.md` untuk tabel di PHASE 4-13:
- Pengadaan: purchase_requests, purchase_request_items, supplier_quotes, purchase_orders, approvals, document_sequences
- Penerimaan/QC/Retur: goods_receipts, goods_receipt_items, qc_inspections, supplier_returns
- Keuangan: supplier_invoices, payments, payment_allocations
- Stok & Produksi: stock_transactions, stock_balances, stock_transfers, stock_counts, material_reservations, material_issues, material_returns

## Referensi

- PRD: `PRD_Inventory_Purchasing_PT_Prima_Inti_Laravel_AdminLTE3.md` bagian Database Schema
- Data Dictionary: `docs/database/DATA_DICTIONARY.md`
- Migration Plan: `docs/database/MIGRATION_PLAN.md`

---

**Catatan**: ERD ini diperbarui setelah PHASE 1 (Database Foundation).
        string name
        string symbol
        int decimal_places
        text description
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    ITEMS {
        bigint id PK
        string code UK
        string name
        text description
        bigint category_id FK
        bigint default_unit_id FK
        string item_type
        decimal safety_stock
        string status
        text specification
        text notes
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    ITEM_UNIT_CONVERSIONS {
        bigint id PK
        bigint item_id FK
        bigint from_unit_id FK
        bigint to_unit_id FK
        decimal multiplier
        timestamp created_at
        timestamp updated_at
    }
    WAREHOUSES {
        bigint id PK
        string code UK
        string name
        text address
        string city
        string phone
        string email
        text description
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    WAREHOUSE_LOCATIONS {
        bigint id PK
        bigint warehouse_id FK
        string code
        string name
        text description
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    SUPPLIERS {
        bigint id PK
        string code UK
        string name
        text address
        string city
        string province
        string postal_code
        string phone
        string email
        string website
        string npwp
        string pic_name
        string pic_phone
        string pic_email
        text payment_terms
        text notes
        string status
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    PROJECTS {
        bigint id PK
        string code UK
        string name
        text description
        bigint department_id FK
        date start_date
        date end_date
        decimal budget
        string status
        text notes
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    USERS ||--o{ DEPARTMENTS : belongs_to
    USERS ||--o{ ROLE_USER : has_many
    USERS ||--o{ AUDIT_LOGS : creates
    USERS ||--o{ ATTACHMENTS : uploads
    ROLES ||--o{ ROLE_USER : has_many
    ROLES ||--o{ PERMISSION_ROLE : has_many
    PERMISSIONS ||--o{ PERMISSION_ROLE : has_many
    DEPARTMENTS ||--o{ PROJECTS : owns
    DEPARTMENTS ||--o{ USERS : has_many
    ITEM_CATEGORIES ||--o{ ITEMS : categorizes
    ITEM_CATEGORIES ||--o{ ITEM_CATEGORIES : parent_child
    UNITS ||--o{ ITEMS : measures
    UNITS ||--o{ ITEM_UNIT_CONVERSIONS : from_unit
    UNITS ||--o{ ITEM_UNIT_CONVERSIONS : to_unit
    ITEMS ||--o{ ITEM_UNIT_CONVERSIONS : has_many
    WAREHOUSES ||--o{ WAREHOUSE_LOCATIONS : contains
```
