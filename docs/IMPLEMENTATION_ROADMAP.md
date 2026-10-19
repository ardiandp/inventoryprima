# docs/IMPLEMENTATION_ROADMAP.md — Roadmap Implementasi

Berdasarkan PRD bagian 11 (Fase Pengembangan dan Acceptance Criteria).

## Urutan Fase (wajib satu per satu)

| Fase | Nama | Fokus Utama |
|------|------|-------------|
| PHASE 0 | Discovery & Setup | Repository, PHP/Laravel, MySQL, paket, konvensi; dokumentasi; AdminLTE 3 layout; test runner |
| PHASE 1 | ERD, Data Dictionary, Database Foundation | ERD, data dictionary, migrations identity/RBAC/master/audit, factories, seeders |
| PHASE 2 | Auth, RBAC, Audit | Login/logout, manajemen user, role/permission, policies, dashboard awal, audit log |
| PHASE 3 | Master Data | CRUD barang, kategori, satuan, supplier, gudang/lokasi, proyek opsional |
| PHASE 4 | RPO & Verifikasi | RPO multi-item, nomor unik, submit, verifikasi, revisi, penolakan, komentar, notifikasi |
| PHASE 5 | Sourcing & Approval | Quotation multi-supplier, perbandingan, approval Direktur, versi data, approval ulang |
| PHASE 6 | PO | PO dari RPO approved, item/harga/supplier/termin, PDF, konfirmasi supplier |
| PHASE 7 | Receiving & QC | Penerimaan parsial, surat jalan, kuantitas aktual, QC, posting stok atomik |
| PHASE 8 | Retur Supplier | Retur dari penerimaan asal, approval, replacement/refund/credit note |
| PHASE 9 | Finance | Invoice/tagihan, jatuh tempo, uang muka, pembayaran sebagian, alokasi |
| PHASE 10 | Inventory Ledger | Ledger, saldo, kartu stok, transfer, stock opname, adjustment, opening balance |
| PHASE 11 | Produksi/Proyek | Permintaan material, reservasi, issue, pemakaian, pengembalian sisa |
| PHASE 12 | Dashboard & Laporan | Dashboard per role, laporan, filter, ekspor, notifikasi |
| PHASE 13 | Security, UAT, Deployment | E2E, security, concurrency, backup/restore, UAT, deployment HTTPS |

## Status

- **PHASE 0 — Discovery & Setup:** ✅ SELESAI
- **PHASE 1 — ERD, Data Dictionary, Database Foundation:** ✅ SELESAI
- **PHASE 2 — Auth, RBAC, Audit:** ✅ SELESAI (63 tests / 185 assertions lulus)
- **Fase selanjutnya:** PHASE 3 — Master Data

## Aturan

- Jangan membangun semua fase sekaligus.
- Setiap fase harus memenuhi acceptance criteria sebelum melanjut ke fase berikutnya.
- Setelah tiap fase, tulis ringkasan, daftar file, hasil test, risiko, dan status acceptance criteria.
