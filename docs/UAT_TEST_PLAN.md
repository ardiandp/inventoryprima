# docs/UAT_TEST_PLAN.md — Rencana Ujian Terima Pengguna

Berdasarkan PRD bagian 12 (Rencana Pengujian).

## Unit Test

- total PO
- saldo invoice
- open quantity
- available/reserved/quarantine
- status transitions

## Feature Test

- akses role
- RPO
- sourcing
- approval
- PO
- penerimaan
- QC
- retur
- invoice/pembayaran
- transfer
- stock opname
- issue/return material

## Integration Test

1. RPO → sourcing → approval → PO → receiving → QC → ledger
2. Reject → quarantine → retur → replacement
3. PO → invoice → pembayaran parsial → lunas
4. Produksi → reservasi → issue → pemakaian → return

## Security / Non-Functional

- akses URL langsung (guest diblokir, role tidak berwenang ditolak meskipun membuka URL langsung)
- CSRF
- XSS
- IDOR
- validasi upload
- mass assignment
- nomor dokumen bersamaan (race condition)
- posting stok atomik/idempotent
- performa daftar
- backup/restore

## Kriteria Lulus UAT

- Semua test otomatis lulus
- Skenario kritis UAT disetujui pemilik proses
- Tidak ada isu security kritis/tinggi terbuka
- Restore diuji
- Dokumentasi deployment dan penggunaan tersedia
