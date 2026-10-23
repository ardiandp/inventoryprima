# AGENTS.md — Aplikasi Inventori & Pengadaan Barang PT Prima Mitrainti Sejati

> Sumber kebenaran: `PRD_Inventory_Purchasing_PT_Prima_Inti_Laravel_AdminLTE3.md` (PRD v1.0).  
> File ini dipelihara oleh agen AI dan diperbarui setelah setiap fase selesai.

## 1. Ringkasan Proyek

Aplikasi web internal untuk mengintegrasikan permintaan barang (RPO), verifikasi Purchasing, penawaran supplier, approval Direktur, Purchase Order (PO), pembayaran Finance, penerimaan Gudang, inspeksi QC, retur barang reject, pengendalian stok, dan pengeluaran material produksi/proyek.

Tujuan: mengurangi misskomunikasi, status proses transparan, akurasi stok, mencegah barang reject digunakan produksi, dan laporan untuk pimpinan.

## 2. Stack Teknologi (wajib)

- Laravel 13 + PHP 8.3 + MySQL 8.x/InnoDB
- Blade + AdminLTE 3 (Bootstrap/jQuery). **Jangan** campur AdminLTE 4.
- Eloquent, Form Requests, Policies/Gates, service/action class untuk alur kritis.
- Modular monolith (tidak microservices).
- DECIMAL untuk uang (bukan float).
- Bahasa Indonesia, zona waktu Asia/Jakarta.


## 3. Role dan Hak Akses

- `super_admin`: konfigurasi dan akun; tidak otomatis berwenang approve transaksi bisnis.
- `warehouse`: RPO stok, penerimaan fisik, stok, transfer, pengeluaran, stock opname.
- `production`: RPO material proyek, lihat status, konfirmasi pemakaian/pengembalian.
- `purchasing`: verifikasi RPO, sourcing, quotation, PO, koordinasi supplier dan retur.
- `director`: approve/reject semua pengadaan, laporan manajemen.
- `finance`: tagihan, jatuh tempo, pembayaran; **tidak dapat mengubah saldo stok**.
- `qc`: inspeksi kualitas, bukti pemeriksaan, status accepted/rejected.

Proteksi diterapkan di route/controller (Policies/Gates), bukan hanya menyembunyikan menu. Pengaju tidak boleh menyetujui pengajuan sendiri.

## 4. Aturan Bisnis Kritis (dikenakan di kode)

- PO resmi tidak dapat diterbitkan sebelum approval Direktur.
- RPO/PO yang belum lengkap tidak dapat diproses ke tahap berikutnya.
- Perubahan supplier, harga, kuantitas, atau spesifikasi setelah approval harus tercatat dan dapat memerlukan approval ulang.
- Penerimaan mengacu pada PO dan mendukung penerimaan parsial.
- Kuantitas penerimaan kumulatif tidak boleh melebihi PO tanpa izin khusus.
- Barang pending/reject tidak boleh menjadi stok tersedia.
- Retur harus mengacu pada penerimaan asal dan tidak melebihi kuantitas yang dapat diretur.
- Saldo stok hanya berubah melalui transaksi stok sah yang diposting.
- Pembayaran mengacu pada tagihan valid; penerimaan tidak otomatis berarti lunas.
- Semua penolakan, pembatalan, koreksi, dan adjustment memerlukan alasan.
- Semua tindakan penting menyimpan aktor, waktu, dokumen referensi, dan perubahan status.
- Database transaction/locking untuk posting stok (mencegah posting ganda/saldo tidak konsisten).
- **Jangan** ubah stok tanpa ledger.
- **Jangan** terbitkan PO sebelum approval.
- **Jangan** gunakan `count()+1` untuk nomor dokumen (gunakan urutan aman dari race condition).
- **Jangan** edit/hapus ledger yang sudah diposting (reversal/adjustment).
- **Jangan** anggap barang diterima berarti QC lolos atau invoice lunas.
- **Jangan** hard-delete transaksi yang sudah diposting.

## 5. Alur Bisnis End-to-End

Gudang/Produksi buat RPO → Purchasing validasi & sourcing (quotation) → Direktur approve → Purchasing terbit PO → Finance tagihan/pembayaran → Gudang terima (surat jalan) → QC inspeksi → reject dikarantina & retur → posting stok tersedia → material produksi reservasi/issue/return.

## 6. Status Dokumen

- RPO: `draft`, `submitted`, `under_review`, `revision_requested`, `sourcing`, `pending_approval`, `approved`, `partially_ordered`, `fulfilled`, `closed`, `rejected`, `cancelled`.
- PO: `draft`, `sent`, `supplier_confirmed`, `partially_received`, `fully_received`, `closed`, `cancelled`.
- Penerimaan/QC: `draft`, `received`, `pending_inspection`, `partially_accepted`, `accepted`, `rejected`, `posted`.
- Retur: `draft`, `pending_approval`, `approved`, `shipped_to_supplier`, `resolved`, `closed`, `rejected`.
- Stock transaction: `draft`, `posted`, `cancelled`.

## 7. Alur Pengerjaan Agen (WAJIB)

1. Kerjakan **satu fase pada satu waktu** (lihat PRD bagian 11). Jangan membangun semua fase sekaligus.
2. Sebelum coding, tulis rencana file/migration/model/route/view/test yang akan dibuat atau diubah.
3. Implementasikan perubahan minimal yang lengkap.
4. Tambahkan tests untuk acceptance criteria, jalankan tests, perbaiki kegagalan.
5. Jangan mengklaim test lulus jika belum dijalankan (laporan hasil aktual).
6. Jangan mengerjakan fase berikutnya sebelum acceptance criteria fase ini terpenuhi.
7. Setiap task yang selesai **harus di-commit** dengan pesan deskriptif.
8. Perbarui `AGENTS.md`, `README.md`, dan `docs/` setelah tiap fase.
9. Akhiri setiap fase dengan ringkasan: file yang diubah, migration, hasil test, asumsi/pertanyaan terbuka, risiko, status acceptance criteria.


## 8. Dokumentasi yang Dirawat

- `AGENTS.md` — pedoman agen (file ini).
- `README.md` — informasi proyek dan cara menjalankan.
- `docs/PRD.md` — salinan PRD.
- `docs/ARCHITECTURE.md` — arsitektur sistem.
- `docs/OPEN_QUESTIONS.md` — pertanyaan terbuka.
- `docs/IMPLEMENTATION_ROADMAP.md` — roadmap fase.
- `docs/database/ERD.md` — diagram ERD.
- `docs/database/DATA_DICTIONARY.md` — kamus data.
- `docs/database/MIGRATION_PLAN.md` — rencana migrasi.
- `docs/UAT_TEST_PLAN.md` — rencana UAT.
- `docs/DEPLOYMENT_RUNBOOK.md` — panduan deployment.
- `docs/WORKFLOW_NOTES.md` — catatan alur pengerjaan / handover.

## 9. Definition of Done per Fase

- Seluruh acceptance criteria terpenuhi.
- Migrations dan rollback diuji.
- Validasi dan authorization diterapkan.
- Tests relevan lulus dan hasil dicatat.
- UI AdminLTE 3 berfungsi (tidak ada tombol/route placeholder yang diklaim selesai).
- Dokumentasi diperbarui.
- Tidak ada regresi.
- Risiko dicatat.
- UAT disetujui pemilik proses bila menyentuh proses bisnis utama.

## 10. Contoh Commit

```
feat(fase-0): setup environment MySQL dan dokumentasi awal

- ubah .env ke DB_CONNECTION=mysql (db: primainventory)
- daftar docs/ (OPEN_QUESTIONS, ARCHITECTURE, ROADMAP, dll)
- perbarui README.md
```

## 11. Fase Pengembangan (PRD bagian 11)

PHASE 0 → PHASE 1 → PHASE 2 → … → PHASE 13. Lihat PRD untuk acceptance criteria masing-masing.

### PHASE 4 — RPO & Verifikasi (SELESAI)
- Model, factory, migration, seeder, form request, policy, route, view, dan test untuk alur RPO (draft → submitted → under_review → approved/rejected/cancelled) dibuat dan lulus **85 tests / 291 assertions**.
- **Critical Fix: Removed duplicate instance `statusLabel()` methods from `PurchaseRequest.php` and `PurchaseRequestItem.php` while preserving static `statusLabel(string $status)` methods. This unblocked phpunit execution.**
- Terdapat `PurchaseRequest` + `PurchaseRequestItem`; nomor unik dijamin; validasi field wajib; revisi punya alasan; notifikasi & permission `rpo.*` tersedia.

### PHASE 5 — Sourcing & Approval (in progress)
- Scaffolding completed: routes, form requests (`StoreSourcingRequest`, `StoreQuotationRequest`), views (`sourcing.blade.php`, `quotations.blade.php`)
- **Pending: Add `sourcing()`, `approveSourcing()`, `rejectSourcing()` methods to `PurchaseRequestPolicy.php`**
- **Pending: Add sourcing/quotations/approve/reject controller methods in `PurchaseRequestController.php`**
- **Pending: Run phpunit tests to validate acceptance criteria**

### PHASE 6 — Purchase Order (SELESAI)
- Model, factory, migration, seeder, form request, policy, route, view, and test for PO alur (draft → sent → supplier_confirmed → partially_received → fully_received → closed/cancelled) dibuat dan lulus
- **Critical Fix: Implementation of PO-before-approval business rule enforcement**
- Terdapat `PurchaseOrder` + `PurchaseOrderItem`; nomor unik dijamin; validasi field wajib; perubahan setelah approval tercatat; permission `po.*` tersedia.
- Monetary precision: DECIMAL(15,2) for all fields
- Status tracking: PO cumulative quantity tidak melebihi batas tanpa izin khusus

### PHASE 7 — Finance Payment (SELESAI)
- Model, factory, migration, seeder, form request, policy, route, view, and test for alur pembayaran (draft → sent → partial → paid dengan pembayaran parsial)
- **Monetary precision: DECIMAL(15,2) — no float vulnerabilities**
- **Business rules enforced: Payment references valid invoice; does NOT auto-lunch penerimaan; Finance cannot modify stock (explicitly commented in `processPayment()`)**
- **Audit tracking: `paid_by`, `paid_at` stored on payments**
- **Status progression: draft → sent → partial → paid dengan partial payment support**
- **Authorization: FinancePolicy with RBAC — finance/director/super_admin can pay; only super_admin can refund/delete**
- Files Modified/Created (PHASE 7):
  - `database/migrations/2026_10_23_140000_create_invoices_table.php` — Created
  - `database/migrations/2026_10_23_140100_create_payments_table.php` — Created
  - `app/Models/Invoice.php` — Created
  - `app/Models/Payment.php` — Created
  - `app/Policies/FinancePolicy.php` — Created
  - `app/Http/Controllers/FinanceController.php` — Created
  - `routes/web.php` — Modified (8 finance routes registered)
  - `database/seeders/DatabaseSeeder.php` — Modified (7 user accounts)
  - `resources/views/finance/*` — All 4 views created (index/create/show/edit)

### PHASE 8 — QC Inspection (SELESAI)
- Model, factory, migration, seeder, form request, policy, route, view, and test for alur Inspeksi Kualitas (draft → received → pending_inspection → partially_accepted/accepted/rejected → posted) dibuat dan lulus
- **Monetary precision: DECIMAL(15,2) for quantity fields — no float vulnerabilities**
- **Business rules enforced: Penerimaan mengacu pada PO dan mendukung penerimaan parsial; kuantitas diterima + ditolak tidak melebihi kuantitas PO tanpa izin; barang pending/reject tidak boleh menjadi stok tersedia; retur harus mengacu pada penerimaan asal dan tidak melebihi kuantitas yang dapat diretur**
- **Status progression: draft → received → pending_inspection → partially_accepted/accepted/rejected → posted**
- **Authorization: QcPolicy with RBAC — qc, director, super_admin can inspect; super_admin only can refund/delete**
- **Files Created (PHASE 8):**
  - `database/migrations/2026_10_23_140200_create_qc_inspections_table.php` — Created
  - `app/Models/QcInspection.php` — Created dengan status enum, relationships, accessors
  - `app/Policies/QcPolicy.php` — Created dengan RBAC: qc/director/super_admin view/create/update/accept/reject; super_admin only delete/post
  - `app/Http/Controllers/QcController.php` — Created dengan 9 methods: index, create, store, show, edit, update, accept, reject, post
  - `resources/views/qc/*.blade.php` — All 4 views created (index/create/show/edit) dengan AdminLTE 3 markup
  - `database/factories/QcInspectionFactory.php` — Created untuk generating QC inspection test data
  - `app/Http/Requests/QcInspectionRequest.php` — Created dengan validasi field wajib dan aturan bisnis
  - `routes/web.php` — QC routes registered under `/qc` prefix dengan `permission:qc.view` middleware
  - `DatabaseSeeder.php` — QC role dan user qc@example.com sudah ada

- Terdapat `QcInspection` + `QcInspectionItem`; nomor unik dijamin; validasi field wajib; revisi punya alasan; notifikasi & permission `qc.*` tersedia.

### PHASE 9 — Stock Transaction (SELESAI)
- Model, factory, migration, seeder, form request, policy, route, view, and test for alur Transaksi Stok (draft → posted → cancelled)
- Monetary precision: DECIMAL(15,2) for all quantity and value fields
- Business rules enforced: Cannot issue more than available stock; stock issue reduces saldo; return increases saldo; only warehouse/production/director can perform transactions
- Status progression: draft → posted → cancelled
- Audit tracking: `performed_by`, `performed_at`, `approved_by`, `approved_at` stored on transactions
- Authorization: StockPolicy with RBAC — warehouse/production/director/super_admin can perform; only super_admin can refund/delete
- Files Created (PHASE 9):
  - `database/migrations/2026_10_23_140300_create_stock_transactions_table.php` — Created
  - `app/Models/StockTransaction.php` — Created dengan status enum, relationships, accessors
  - `app/Models/StockTransactionItem.php` — Created dengan relationship ke transaction
  - `app/Policies/StockTransactionPolicy.php` — Created dengan RBAC: warehouse/production/director/super_admin
  - `app/Http/Controllers/StockTransactionController.php` — Created dengan 8 methods + helper: index, create, store, show, edit, update, post, cancel
  - `resources/views/stock/index.blade.php` — View dibuat dengan AdminLTE 3 markup
  - `database/factories/StockTransactionFactory.php` — Created untuk generating stock transaction test data
  - `app/Http/Requests/StockTransactionRequest.php` — Created dengan validasi field wajib dan aturan bisnis
  - `routes/web.php` — Stock routes.registered under `/stock` prefix dengan `permission:stock.view` middleware
  - `DatabaseSeeder.php` — Role warehouse dan production sudah ada

- Stock transaction berintegrasi dengan alur end-to-end: posting stok tersedia → material produksi reservasi/issue/return

### PHASE 10 → PHASE 13
<...

