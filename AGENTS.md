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

PHASE 0 → PHASE 1 → PHASE 2 → … → PHASE 13. Lihat PRD untuk acceptance criteria masing-masing. Fase saat ini: PHASE 2 (selesai), berikutnya PHASE 3 — Master Data.
