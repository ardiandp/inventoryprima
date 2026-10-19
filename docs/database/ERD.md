# docs/database/ERD.md — Entity Relationship Diagram

Sumber: PRD bagian 9 (Rancangan Entitas Database).  
ERD ini direpresentasikan dalam bentuk daftar entitas dan relasi utama (bisa diganti dengan diagram visual nanti).

## Entitas

### Identitas & Audit
- `users` — pengguna sistem
- `departments` — departemen
- `roles` — role (super_admin, warehouse, production, purchasing, director, finance, qc)
- `permissions` — permission/izin
- `role_user` — pivot users ↔ roles
- `permission_role` - pivot roles ↔ permissions
- `audit_logs` — catatan tindakan penting (aktor, waktu, dokumen referensi, perubahan status)
- `notifications` — notifikasi sistem
- `attachments` — lampiran file

### Master Data
- `item_categories` — kategori barang
- `units` — satuan
- `items` — barang
- `item_unit_conversions` — konversi satuan
- `warehouses` — gudang
- `warehouse_locations` — lokasi rak/gudang
- `suppliers` — supplier
- `projects` — proyek
- `work_orders` — work order (opsional)

### Pengadaan
- `purchase_requests` (RPO) — permintaan pembelian
- `purchase_request_items` — item RPO
- `supplier_quotes` — penawaran supplier
- `supplier_quote_items` — item penawaran
- `purchase_orders` (PO) — purchase order
- `purchase_order_items` — item PO
- `approvals` / `approval_steps` — langkah approval
- `document_sequences` — urutan nomor dokumen (aman race condition)

### Penerimaan / QC / Retur
- `goods_receipts` — penerimaan barang
- `goods_receipt_items` — item penerimaan
- `qc_inspections` — inspeksi kualitas
- `qc_inspection_items` — item inspeksi (jika diperlukan)
- `supplier_returns` — retur ke supplier
- `supplier_return_items` — item retur

### Keuangan
- `supplier_invoices` — tagihan supplier
- `payments` — pembayaran
- `payment_allocations` — alokasi pembayaran ke tagihan

### Stok & Produksi
- `stock_transactions` — transaksi stok (ledger, sumber kebenaran)
- `stock_balances` — saldo stok per item/lokasi/status (ringkasan, direkonsiliasi)
- `stock_transfers` — transfer stok antar gudang
- `stock_transfer_items` — item transfer
- `stock_counts` — stock opname
- `stock_count_items` — item opname
- `material_reservations` — reservasi material produksi
- `material_issues` — pengeluaran material
- `material_issue_items` — item pengeluaran
- `material_returns` — pengembalian sisa material
- `material_return_items` — item pengembalian

## Relasi Utama

- users → departments, roles (RBAC)
- items → item_categories, units
- purchase_requests → users (pemaju), projects (opsional)
- purchase_request_items → purchase_requests, items
- supplier_quotes → purchase_requests, suppliers
- purchase_orders → purchase_requests, suppliers
- purchase_order_items → purchase_orders, items
- goods_receipts → purchase_orders, warehouses
- goods_receipt_items → goods_receipts, items
- qc_inspections → goods_receipts
- supplier_returns → goods_receipts
- supplier_invoices → purchase_orders
- payments → supplier_invoices (via payment_allocations)
- stock_transactions → items, warehouses, goods_receipts (referensi), purchase_orders (referensi)
- stock_balances → items, warehouses

## Aturan

- Unique index untuk kode dan nomor dokumen
- Index untuk FK, status, tanggal, item, supplier, gudang, proyek
- Foreign key delete restriction untuk transaksi
- Validasi kuantitas
- Database transaction pada posting stok
