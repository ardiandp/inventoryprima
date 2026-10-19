# docs/OPEN_QUESTIONS.md — Pertanyaan Terbuka

Dipersiapkan sesuai PRD bagian 2 (Asumsi sementara) dan bagian 14 (deliverables).  
Semua pertanyaan ini harus diklarifikasi dengan pemilik proses sebelum fase terkait dimulai.

## Pertanyaan Terbuka dari PRD

1. Kode barang saat ini — apakah ada kode legacy yang harus di-migrasi atau dipertahankan?
2. Barang apa saja yang wajib QC (semua barang atau hanya kategori tertentu)?
3. Persetujuan retur dan penyesuaian stok — siapa yang berwenang approve (direktur, warehouse head, atau finance)?
4. Kebutuhan proyek dan BOM — apakah modul Work Order/Proyek diaktifkan di v1 (dicatat sebagai opsional di PRD)?
5. Pajak dan mata uang — apakah transaksi melibatkan mata uang asing atau PPN yang perlu dihitung?
6. Integrasi akuntansi — apakah perlu export ke sistem akunting eksternal atau cukup catatan tagihan/pembayaran sederhana?
7. Barcode — apakah diperlukan pencatatan stok via barcode scanner?
8. Delegasi approval — apakah approval Direktur bisa didelegasikan saat Direktur tidak aktif?
9. Toleransi perubahan harga/kuantitas — batas perubahan yang masih boleh tanpa approval ulang setelah PO disetujui?

## Catatan Agen

- Tambahkan pertanyaan baru di sini saat menemukan kekurangan spesifikasi durante implementasi.
- Tandai pertanyaan yang telah diklarifikasi dengan `[DISARIKAN: alasan + tanggal]`.
- Jangan lanjut ke fase yang bergantung pada pertanyaan belum terjawab tanpa keputusan pemilik proses.
