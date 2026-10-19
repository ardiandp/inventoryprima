# docs/database/DATA_DICTIONARY.md — Kamus Data

Tabel dan kolom utama (ringkasan). Detail kolom lengkap ditetapkan saat membuat migration masing-masing tabel.

## Master

| Tabel | Kolom Utama | Keterangan |
|-------|-------------|------------|
| items | id, code, name, category_id, unit_id, is_active | code unik; barang nonaktif tidak dipakai transaksi baru |
| item_categories | id, code, name, is_active | |
| units | id, code, name, is_active | |
| warehouses | id, code, name, is_active | satu atau banyak gudang |
| suppliers | id, code, name, contact, is_active | |
| projects | id, code, name, is_active | opsional |

## Pengadaan

| Tabel | Kolom Utama | Keterangan |
|-------|-------------|------------|
| purchase_requests | id, rpo_number, user_id, status, needed_date, priority, reason, project_id | status: draft..cancelled |
| purchase_request_items | id, purchase_request_id, item_id, qty, unit, specification | |
| supplier_quotes | id, purchase_request_id, supplier_id, quote_number, valid_until | |
| supplier_quote_items | id, supplier_quote_id, item_id, qty, unit_price | DECIMAL untuk harga |
| purchase_orders | id, po_number, purchase_request_id, supplier_id, status, promised_date | PO hanya dari RPO approved |
| purchase_order_items | id, purchase_order_id, item_id, qty, unit_price | DECIMAL |
| approvals | id, document_type, document_id, user_id, action, reason, created_at | histori approval lengkap |
| document_sequences | id, prefix, last_number, date | nomor dokumen aman race condition |

## Penerimaan / QC / Retur

| Tabel | Kolom Utama | Keterangan |
|-------|-------------|------------|
| goods_receipts | id, receipt_number, purchase_order_id, warehouse_id, received_date, status | status: draft..posted |
| goods_receipt_items | id, goods_receipt_id, purchase_order_item_id, qty_received, condition | |
| qc_inspections | id, goods_receipt_id, inspector_id, status, inspected_at | accepted/rejected/quarantine |
| supplier_returns | id, return_number, goods_receipt_id, status, reason | |

## Keuangan

| Tabel | Kolom Utama | Keterangan |
|-------|-------------|------------|
| supplier_invoices | id, invoice_number, purchase_order_id, amount, due_date, status | DECIMAL untuk uang |
| payments | id, payment_number, payment_date, amount, proof_file | |
| payment_allocations | id, payment_id, supplier_invoice_id, amount | |

## Stok & Produksi

| Tabel | Kolom Utama | Keterangan |
|-------|-------------|------------|
| stock_transactions | id, txn_number, item_id, warehouse_id, type, qty, status, reference_type, reference_id | posted tidak diedit/dihapus |
| stock_balances | id, item_id, warehouse_id, location_id, qty_available, qty_reserved, qty_quarantine | ringkasan, direkonsiliasi |
| stock_transfers | id, transfer_number, from_warehouse_id, to_warehouse_id, status | |
| stock_counts | id, count_number, warehouse_id, count_date, status | stock opname |
| material_reservations | id, project_id, item_id, qty, status | reservasi tidak melebihi available |
| material_issues | id, issue_number, project_id, issue_date, status | mengurangi stok |
| material_returns | id, return_number, material_issue_id, return_date, status | menambah stok sesuai pemeriksaan |

## Aturan Umum

- Semua uang: DECIMAL
- Semua transaksi stok: database transaction + locking saat posting
- Transaksi posted tidak hard-delete; koreksi via reversal/adjustment
- Setiap tindakan penting mencatat aktor, waktu, dokumen referensi, perubahan status
