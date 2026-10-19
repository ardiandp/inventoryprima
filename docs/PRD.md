# docs/PRD.md — Salinan PRD

Salinan lengkap PRD tersedia di root repositori:

`PRD_Inventory_Purchasing_PT_Prima_Inti_Laravel_AdminLTE3.md`

File ini dijadikan referensi agar dokumentasi di `docs/` mudah diakses dalam satu lokasi.

Ringkasan PRD v1.0:

- **Produk:** Aplikasi Inventori & Pengadaan Barang PT Prima Mitrainti Sejati
- **Teknologi:** Laravel + MySQL + Blade + AdminLTE 3
- **Scope:** RPO, sourcing, approval Direktur, PO, penerimaan, QC, retur, finance, stok, material produksi
- **Fase:** PHASE 0 (Discovery & Setup) s.d. PHASE 13 (Security, UAT, Deployment)
- **Aturan wajib:** satu fase pada satu waktu; jangan ubah stok tanpa ledger; jangan terbitkan PO sebelum approval; jangan gunakan `count()+1` untuk nomor dokumen; jangan edit/hapus ledger posted; penerimaan bukan berarti QC lolos atau invoice lunas.

Silakan buka file PRD di root untuk detail lengkap (alur bisnis, role, status dokumen, aturan bisnis kritis, arsitektur, dan acceptance criteria tiap fase).
