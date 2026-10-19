# PRD — Aplikasi Inventori & Pengadaan Barang PT Prima Mitrainti Sejati

**Versi:** 1.0 (Draft implementasi bertahap)  
**Teknologi:** Laravel + MySQL + Blade + AdminLTE 3  
**Tujuan:** Panduan sistematis untuk agen AI/OpenCode; fase dikerjakan satu per satu.

> Instruksi agen: Jangan membangun semua fase sekaligus. Kerjakan hanya fase yang diminta, jalankan tests, laporkan hasil aktual, dan jangan lanjut sebelum acceptance criteria terpenuhi.

## 1. Ringkasan Produk

Aplikasi web internal untuk mengintegrasikan permintaan barang (RPO), verifikasi Purchasing, penawaran supplier, approval Direktur, Purchase Order (PO), pembayaran Finance, penerimaan Gudang, inspeksi QC, retur barang reject, pengendalian stok, dan pengeluaran material produksi/proyek.

Tujuan utama: mengurangi miss komunikasi, menyediakan status proses yang transparan, menjaga akurasi stok, melarang barang reject digunakan produksi, serta menyediakan laporan untuk pimpinan.

## 2. Keputusan Bisnis dan Asumsi

Keputusan yang telah ditetapkan:
- Permintaan barang berasal dari Gudang dan Produksi.
- Semua pengadaan wajib disetujui Direktur sebelum PO resmi diterbitkan.
- Gudang menjadi pencatat utama stok.
- Finance/Accounting terpisah dari Purchasing.
- Penerimaan diperiksa Gudang dan QC.
- Jumlah gudang belum diketahui; aplikasi harus mendukung satu atau banyak gudang.
- Stack: Laravel, MySQL, AdminLTE 3, Blade.

Asumsi sementara:
- Versi awal untuk satu perusahaan, bukan multi-tenant.
- Supplier belum memiliki akun login.
- Finance mencatat tagihan dan pembayaran, bukan akuntansi double-entry penuh.
- Pengadaan darurat tetap perlu approval; alur khusus menunggu SOP.
- Satu RPO dapat dipenuhi oleh beberapa PO; PO dapat diterima beberapa kali.
- Barang belum lolos pemeriksaan disimpan sebagai karantina dan tidak tersedia untuk produksi.
- Proyek/Work Order bersifat opsional sampai proses bisnis dikonfirmasi.
- Antarmuka Bahasa Indonesia dan waktu operasional Asia/Jakarta.

Pertanyaan terbuka wajib dicatat di `docs/OPEN_QUESTIONS.md`: kode barang saat ini, barang yang wajib QC, approval retur/penyesuaian stok, kebutuhan proyek/BOM, pajak/mata uang, integrasi akuntansi, barcode, delegasi approval, dan toleransi perubahan harga/kuantitas.

## 3. Role dan Hak Akses

- `super_admin`: mengelola konfigurasi dan akun; tidak otomatis berwenang menyetujui transaksi bisnis.
- `warehouse`: RPO untuk stok, penerimaan fisik, stok, transfer, pengeluaran, stock opname.
- `production`: RPO material proyek, melihat status, konfirmasi pemakaian/pengembalian.
- `purchasing`: verifikasi RPO, sourcing, quotation, PO, koordinasi supplier dan retur.
- `director`: approve/reject semua pengadaan, melihat laporan manajemen.
- `finance`: tagihan, jatuh tempo, pembayaran; tidak dapat mengubah saldo stok.
- `qc`: inspeksi kualitas, bukti pemeriksaan, status accepted/rejected.

Gunakan RBAC, middleware, Laravel Policies/Gates. Proteksi harus diterapkan pada route/controller, bukan hanya menyembunyikan menu. Pengaju tidak boleh menyetujui pengajuannya sendiri.

## 4. Alur Bisnis End-to-End

1. Gudang membuat RPO untuk replenishment stok atau Produksi membuat RPO untuk proyek/Work Order.
2. RPO berisi item, spesifikasi, kuantitas, satuan, tanggal kebutuhan, alasan, prioritas, dan referensi proyek jika ada.
3. Purchasing memvalidasi kelengkapan, stok tersedia, stok teralokasi, stok dalam perjalanan, dan kebutuhan bersih.
4. Purchasing mencatat dan membandingkan penawaran supplier.
5. Direktur menyetujui atau menolak dengan alasan. Semua pengadaan harus disetujui sebelum PO.
6. Purchasing menerbitkan PO dari RPO yang approved, mengirim ke supplier, dan mencatat konfirmasi serta jadwal.
7. Finance memproses uang muka/tagihan/pembayaran sesuai termin. Status pembayaran terpisah dari status penerimaan.
8. Gudang mencatat jumlah aktual, surat jalan, kondisi, dan selisih saat barang datang.
9. QC memeriksa kualitas. Hanya kuantitas yang lolos pemeriksaan yang dapat masuk stok tersedia.
10. Barang reject dikarantina, dibuat retur/klaim, dan penyelesaian dicatat sebagai replacement/refund/credit note.
11. Material produksi dipesan, direservasi, dikeluarkan, dipakai, dan sisa material dikembalikan melalui transaksi terpisah.

## 5. Status Dokumen

RPO: `draft`, `submitted`, `under_review`, `revision_requested`, `sourcing`, `pending_approval`, `approved`, `partially_ordered`, `fulfilled`, `closed`, `rejected`, `cancelled`.

PO: `draft`, `sent`, `supplier_confirmed`, `partially_received`, `fully_received`, `closed`, `cancelled`. Status pembayaran harus terpisah.

Penerimaan/QC: `draft`, `received`, `pending_inspection`, `partially_accepted`, `accepted`, `rejected`, `posted`.

Retur: `draft`, `pending_approval`, `approved`, `shipped_to_supplier`, `resolved`, `closed`, `rejected`.

Stock transaction: `draft`, `posted`, `cancelled`. Transaksi posted tidak diedit/dihapus; koreksi melalui reversal/adjustment.

## 6. Modul Fungsional

1. Autentikasi, pengguna, role, permission.
2. Master barang, kategori, satuan/konversi, supplier, gudang/lokasi, proyek.
3. Dashboard berdasarkan role.
4. RPO dan verifikasi Purchasing.
5. Penawaran supplier dan perbandingan.
6. Approval Direktur.
7. PO dan cetak PDF.
8. Penerimaan barang dan surat jalan.
9. QC, karantina, reject.
10. Retur dan klaim supplier.
11. Invoice/tagihan, pembayaran, jatuh tempo.
12. Inventory ledger, saldo, transfer, stock opname.
13. Permintaan dan pengeluaran material produksi.
14. Laporan, ekspor, notifikasi, audit log, lampiran.

Tidak termasuk baseline: portal supplier, aplikasi mobile native, AI forecasting, akuntansi penuh, integrasi bank/ERP, MRP/BOM lanjutan, multi-company/tenant.

## 7. Aturan Bisnis Kritis

- PO resmi tidak dapat diterbitkan sebelum approval Direktur.
- RPO/PO yang belum lengkap tidak dapat diproses ke tahap berikutnya.
- Perubahan material pada supplier, harga, kuantitas, atau spesifikasi setelah approval harus tercatat dan dapat memerlukan approval ulang.
- Penerimaan mengacu pada PO dan mendukung penerimaan parsial.
- Kuantitas penerimaan kumulatif tidak boleh melebihi PO tanpa izin khusus yang terkonfigurasi.
- Barang pending/reject tidak boleh menjadi stok tersedia.
- Retur harus mengacu pada penerimaan asal dan tidak melebihi kuantitas yang dapat diretur.
- Saldo stok hanya berubah melalui transaksi stok sah yang diposting.
- Pembayaran mengacu pada tagihan valid; penerimaan tidak otomatis berarti lunas.
- Semua penolakan, pembatalan, koreksi, dan adjustment memerlukan alasan.
- Semua tindakan penting menyimpan aktor, waktu, dokumen referensi, dan perubahan status.
- Gunakan database transaction/locking untuk mencegah posting ganda atau saldo tidak konsisten.

## 8. Teknologi dan Arsitektur

- Laravel versi stabil yang kompatibel dengan PHP/environment proyek saat implementasi; verifikasi versi sebelum setup.
- MySQL 8.x/InnoDB, foreign keys, migrations, indexes.
- Blade + AdminLTE 3; gunakan Bootstrap/jQuery sesuai AdminLTE 3. Jangan mencampur AdminLTE 4 atau mengganti stack tanpa keputusan.
- Eloquent, Form Requests, Policies/Gates, service/action class untuk alur kritis.
- Modular monolith; tidak perlu microservices.
- Gunakan DECIMAL untuk uang, bukan float.
- Ledger stok sebagai sumber transaksi; `stock_balances` hanya ringkasan yang dapat direkonsiliasi.
- Nomor dokumen unik harus aman dari race condition; jangan menggunakan `count()+1`.
- Simpan file di Laravel Storage, validasi upload, dan lindungi akses lampiran.
- Rahasia hanya di environment variables; siapkan HTTPS, backup, logging, dan prosedur restore.
- Jangan hard-delete transaksi yang sudah diposting.

## 9. Rancangan Entitas Database

Identity & audit: `users`, `departments`, `roles`, `permissions`, pivot RBAC, `audit_logs`, `notifications`, `attachments`.

Master: `item_categories`, `units`, `items`, `item_unit_conversions`, `warehouses`, `warehouse_locations`, `suppliers`, `projects`, `work_orders` (opsional).

Pengadaan: `purchase_requests`, `purchase_request_items`, `supplier_quotes`, `supplier_quote_items`, `purchase_orders`, `purchase_order_items`, `approvals`/`approval_steps`, `document_sequences`.

Penerimaan/QC/retur: `goods_receipts`, `goods_receipt_items`, `qc_inspections`, `qc_inspection_items` (jika perlu), `supplier_returns`, `supplier_return_items`.

Finance: `supplier_invoices`, `payments`, `payment_allocations`.

Stok/produksi: `stock_transactions`, `stock_balances`, `stock_transfers`, `stock_transfer_items`, `stock_counts`, `stock_count_items`, `material_reservations`, `material_issues`, `material_issue_items`, `material_returns`, `material_return_items`.

Agen wajib membuat ERD dan data dictionary final sebelum transaksi dikembangkan. Tambahkan unique index untuk kode dan nomor dokumen; index untuk FK, status, tanggal, item, supplier, gudang, proyek; foreign key delete restriction untuk transaksi; validasi kuantitas; dan transaksi database pada posting stok.

## 10. Navigasi AdminLTE 3

- Dashboard
- Master Data: Barang, Kategori, Satuan & Konversi, Supplier, Gudang & Lokasi, Proyek/Work Order
- Permintaan Pengadaan: Buat RPO, RPO Saya, Semua RPO, Verifikasi, Sourcing & Penawaran, Approval
- Purchasing: PO, Pengiriman Supplier
- Warehouse: Penerimaan, QC, Stok/Kartu Stok, Transfer, Pengeluaran Material, Pengembalian Material, Stock Opname, Karantina/Reject
- Retur Supplier
- Finance: Tagihan, Pembayaran, Jatuh Tempo
- Laporan
- Notifikasi
- Pengaturan: Pengguna, Role & Permission, Nomor Dokumen, Audit Log

Filter menu menurut permission, dan lindungi endpoint secara server-side.

## 11. Fase Pengembangan dan Acceptance Criteria

### PHASE 0 — Discovery & Setup
Periksa repository, PHP/Laravel, MySQL, paket dan konvensi. Buat `AGENTS.md`, README, `docs/OPEN_QUESTIONS.md`, arsitektur dan roadmap. Siapkan Laravel, MySQL, AdminLTE 3 layout, test runner.
Acceptance: aplikasi berjalan lokal, koneksi DB dan migration command berfungsi, layout AdminLTE tampil, smoke test lulus, setup terdokumentasi.

### PHASE 1 — ERD, Data Dictionary, Database Foundation
Buat ERD konseptual/fisik, data dictionary, status/transisi, migrations identity/RBAC/master/audit, factories dan seeders.
Acceptance: fresh migration dan rollback diuji; FK, unique constraints, index, ERD dan data dictionary tersedia.

### PHASE 2 — Auth, RBAC, Audit
Login/logout, user management, role/permission, policies, dashboard awal, audit log.
Acceptance: guest diblokir; role tidak berwenang ditolak meskipun membuka URL langsung; Finance tidak dapat mengubah stok; test akses positif/negatif lulus.

### PHASE 3 — Master Data
CRUD barang, kategori, satuan, supplier, gudang/lokasi, proyek opsional; validasi, search, filter, pagination.
Acceptance: kode unik; item yang telah dipakai transaksi tidak dihapus fisik; master nonaktif tidak dapat dipilih transaksi baru; CRUD tests lulus.

### PHASE 4 — RPO & Verifikasi
RPO multi-item dari Gudang/Produksi, nomor unik, submit, verifikasi, revisi, penolakan, komentar dan notifikasi.
Acceptance: validasi field wajib; pemohon melihat status; revisi memiliki alasan; nomor unik dalam request bersamaan; test alur lulus.

### PHASE 5 — Sourcing & Approval
Quotation multi-supplier, perbandingan, rekomendasi, approval Direktur, versi data yang disetujui, approval ulang saat perubahan material.
Acceptance: hanya approver dapat menyetujui; alasan penolakan wajib; pengaju tidak menyetujui sendiri; histori approval lengkap.

### PHASE 6 — PO
PO hanya dari RPO approved; item, harga, supplier, termin, tanggal janji, PDF, konfirmasi supplier, revisi dan pembatalan.
Acceptance: PO dari RPO belum approved ditolak; nomor PO aman dari race condition; PO terkirim tidak diedit diam-diam; total dan PDF sesuai.

### PHASE 7 — Receiving & QC
Penerimaan parsial, surat jalan, kuantitas aktual, QC, status accepted/rejected/quarantine, posting stok atomik.
Acceptance: penerimaan merujuk PO valid; over-receipt dicegah; barang pending/reject tidak masuk available; posting ganda dicegah; test partial/accepted/reject lulus.

### PHASE 8 — Retur Supplier
Retur dari penerimaan asal, kuantitas dan alasan, approval, dokumen retur, shipment, replacement/refund/credit note.
Acceptance: retur tidak melebihi kuantitas yang dapat diretur; barang karantina tidak dapat di-issue; penyelesaian terhubung ke dokumen asal.

### PHASE 9 — Finance
Invoice/tagihan, jatuh tempo, uang muka, pembayaran sebagian, alokasi pembayaran, bukti opsional.
Acceptance: saldo tagihan benar; pembayaran sebagian tidak menandai lunas; role Finance tidak mengubah stok; tests nominal dan permission lulus.

### PHASE 10 — Inventory Ledger
Ledger, saldo per item/lokasi/status, kartu stok, transfer, stock opname, adjustment, opening balance.
Acceptance: semua saldo dapat ditelusuri; posted transaction tidak diedit/dihapus; transfer tidak menggandakan stok; ledger dan summary dapat direkonsiliasi; saldo negatif dicegah secara default.

### PHASE 11 — Produksi/Proyek
Permintaan material, reservasi, issue, pemakaian, pengembalian sisa, laporan per proyek.
Acceptance: reservasi tidak melebihi available tanpa override; issue mengurangi stok; return menambah stok sesuai pemeriksaan; laporan cocok dengan ledger.

### PHASE 12 — Dashboard & Laporan
Dashboard per role; laporan stok, RPO, approval, PO, supplier, receiving, retur, pembayaran, proyek; filter, ekspor, notifikasi.
Acceptance: angka cocok dengan sumber; filter berlaku pada ekspor; data dibatasi permission; pagination dan query efisien.

### PHASE 13 — Security, UAT, Deployment
End-to-end, security, concurrency, backup/restore, UAT seluruh role, migrasi data, SOP, deployment HTTPS, logging, backup.
Acceptance: semua tests lulus; skenario kritis UAT disetujui; tidak ada isu security kritis/tinggi terbuka; restore diuji; dokumentasi deployment dan penggunaan tersedia.

## 12. Rencana Pengujian

Unit: total PO, saldo invoice, open quantity, available/reserved/quarantine, status transitions.

Feature: akses role, RPO, sourcing, approval, PO, penerimaan, QC, retur, invoice/pembayaran, transfer, stock opname, issue/return material.

Integration:
1. RPO → sourcing → approval → PO → receiving → QC → ledger.
2. Reject → quarantine → retur → replacement.
3. PO → invoice → pembayaran parsial → lunas.
4. Produksi → reservasi → issue → pemakaian → return.

Security/non-functional: akses URL langsung, CSRF, XSS, IDOR, validasi upload, mass assignment, nomor dokumen bersamaan, posting stok atomik/idempotent, performa daftar, backup/restore.

## 13. UI Standard AdminLTE 3

Layout konsisten: navbar, sidebar, content header, breadcrumb, content wrapper, footer. Daftar memiliki pencarian/filter/pagination/status badge dan action berdasarkan permission. Form menampilkan validasi inline, pesan sukses/gagal, dan konfirmasi tindakan berisiko. Detail dokumen menampilkan timeline dan tautan dokumen terkait. Jangan menampilkan tombol yang belum berfungsi. Gunakan responsive layout dan jangan mengedit template inti AdminLTE secara langsung; buat partial/layout aplikasi sendiri.

## 14. Deliverables Agen AI

Wajib dibuat/dirawat:
- `AGENTS.md`
- `README.md`
- `docs/PRD.md`
- `docs/ARCHITECTURE.md`
- `docs/OPEN_QUESTIONS.md`
- `docs/IMPLEMENTATION_ROADMAP.md`
- `docs/database/ERD.md` atau diagram ERD
- `docs/database/DATA_DICTIONARY.md`
- `docs/database/MIGRATION_PLAN.md`
- `docs/UAT_TEST_PLAN.md`
- `docs/DEPLOYMENT_RUNBOOK.md`
- Migrations, models, factories, seeders, policies, Form Requests, services/actions, controllers, routes, views, tests.

Setelah tiap fase, agen melaporkan ringkasan, daftar file, migrations, test dan hasil aktual, asumsi/pertanyaan terbuka, risiko, acceptance criteria, dan memastikan tidak mengerjakan fase berikutnya.

## 15. Prompt untuk OpenCode / Agen AI

PROMPT AWAL:
“Baca `AGENTS.md` dan `docs/PRD.md` secara menyeluruh. Periksa repository dan environment sebelum mengubah file. Buat rencana kerja untuk PHASE 0 saja. Jangan mengimplementasikan fase lain. Pertahankan Laravel + MySQL + Blade + AdminLTE 3. Catat konflik, asumsi, dan pertanyaan terbuka. Setelah implementasi jalankan test yang relevan dan laporkan hasil aktual.”

PROMPT SETIAP FASE:
“Implementasikan hanya PHASE [NOMOR/NAMA] dari `docs/PRD.md`. Periksa dependensi dan kondisi repository. Sebelum coding, tulis rencana file/migration/model/route/view/test yang akan dibuat atau diubah. Implementasikan perubahan minimal yang lengkap. Tambahkan tests untuk acceptance criteria, jalankan tests, perbaiki kegagalan yang disebabkan perubahan ini, dan jangan mengklaim test lulus jika belum dijalankan. Jangan mengerjakan fase berikutnya. Akhiri dengan ringkasan file, hasil test, risiko, keputusan terbuka, dan status acceptance criteria.”

Aturan wajib: jangan membangun semua fase sekaligus; jangan menghapus/reset data produksi; jangan commit secret; jangan bypass policy; jangan ubah stok tanpa ledger; jangan terbitkan PO sebelum approval; jangan gunakan count()+1 untuk nomor dokumen; jangan edit/hapus ledger posted; jangan menganggap barang diterima berarti QC lolos atau invoice lunas.

## 16. Definition of Done

Fase selesai jika seluruh acceptance criteria terpenuhi, migrations/rollback diuji, validasi dan authorization diterapkan, tests relevan lulus dan hasil dicatat, UI AdminLTE 3 berfungsi, tidak ada tombol/route placeholder yang diklaim selesai, dokumentasi diperbarui, regresi tidak terjadi, risiko dicatat, dan UAT disetujui pemilik proses bila menyentuh proses bisnis utama.

