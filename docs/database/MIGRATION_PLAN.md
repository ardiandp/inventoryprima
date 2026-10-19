# docs/database/MIGRATION_PLAN.md — Rencana Migrasi

Urutan pembuatan migration harus mematuhi dependensi foreign key (tabel rujukan dibuat sebelum tabel yang merujuknya).

## Urutan Migrasi (fase 0–1)

1. `create_users_table` (ada bawaan Laravel)
2. `create_departments_table`
3. `create_roles_table`
4. `create_permissions_table`
5. `create_role_user_table`
6. `create_permission_role_table`
7. `create_audit_logs_table`
8. `create_notifications_table`
9. `create_attachments_table`
10. `create_item_categories_table`
11. `create_units_table`
12. `create_items_table`
13. `create_item_unit_conversions_table`
14. `create_warehouses_table`
15. `create_warehouse_locations_table`
16. `create_suppliers_table`
17. `create_projects_table`
18. `create_work_orders_table` (opsional)
19. `create_document_sequences_table`
20. `create_purchase_requests_table`
21. `create_purchase_request_items_table`
22. `create_supplier_quotes_table`
23. `create_supplier_quote_items_table`
24. `create_purchase_orders_table`
25. `create_purchase_order_items_table`
26. `create_approvals_table`
27. `create_goods_receipts_table`
28. `create_goods_receipt_items_table`
29. `create_qc_inspections_table`
30. `create_supplier_returns_table`
31. `create_supplier_return_items_table`
32. `create_supplier_invoices_table`
33. `create_payments_table`
34. `create_payment_allocations_table`
35. `create_stock_transactions_table`
36. `create_stock_balances_table`
37. `create_stock_transfers_table`
38. `create_stock_transfer_items_table`
39. `create_stock_counts_table`
40. `create_stock_count_items_table`
41. `create_material_reservations_table`
42. `create_material_issues_table`
43. `create_material_issue_items_table`
44. `create_material_returns_table`
45. `create_material_return_items_table`

## Aturan Migration

- Foreign key: `onDelete('restrict')` untuk transaksi
- Unique index untuk kode dan nomor dokumen
- Index untuk FK, status, tanggal, item, supplier, gudang, proyek
- Validasi kuantitas di model/database
- Rollback diuji untuk setiap migration

## Perintah

```
php artisan migrate          # jalankan semua migration
php artisan migrate:rollback # rollback terakhir
php artisan migrate:fresh    # reset dan jalankan ulang (development)
```
