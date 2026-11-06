# Checkpoint — Current State

## Last Updated: 2026-11-06 (session 2)

## Completed Features
1. Shipment CRUD (create, edit, show, list) with customer dropdown, PO number, payment type
2. Manifest flow: Draft → Dispatched → Received → Completed (4 steps)
3. Invoice management: consolidated invoicing, manual costs, partial payment, PO grouping
4. Customer management: search, auto-create, region auto-fill
5. Public tracking: limited PII (resi, date, sender, receiver, timeline, receiver name + delivery date)
6. Print: resi detail (half-A4), koli label (multi-page), manifest sheet, invoice
7. Export: CSV + PDF (with preview modal) for shipments, manifests, invoices
8. RBAC: Admin, Head_of_Branch, Leader_Supervisor, Executive_Viewer (read-only), Staf
9. BranchScope: data isolation by branch
10. Region sync: 35 provinces, 514 regencies, 7,215 districts, 80,532 villages
11. Audit trail: all CRUD operations logged
12. Template management: per doc type settings (paper format, margins, logo)

## Hidden Menus (admin access via URL)
- Branches (/branches)
- Users (/users)
- Template Management (/admin/template-management)

## Pending
- Template redesign (user will provide HTML/PDF samples)
- Staf UI testing (create 5 shipments, 2 invoices, 3 manifests, print all docs)
- Cleanup unused code (purchase_orders table, assign method removed, adjust-koli/upload-pod endpoints)

## Recent Changes (2026-11-06 session 2)
- Customer search → dropdown (select) with auto-fill + region cascade
- Invoice create: PO groups with resi checkboxes (exclude invoiced POs, whole PO excluded)
- Print split: Print Resi (customer, half-A4) + Print Label (koli, multi-page with "KOLI X/Y")
- Invoice summary: all manual costs + total + tunggakan (outstanding)
- Phone inputs: digit-only (inputmode=numeric)
- Removed percentage discount (nominal only, step=1)
- Removed assign shipment feature (manifest + invoice)
- Auto-create customer on shipment creation
- Invoice edit form: updated with all new cost fields
- Documentation: docs/referensi/ (9 files + production deploy + agent manual)
- Invoice create: PO groups with resi checkboxes (exclude invoiced POs)
- Print split: Print Resi (customer) + Print Label (koli, multi-page)
- Invoice summary: all manual costs + total + tunggakan
- Phone inputs: digit-only
- Removed percentage discount (nominal only)
- Removed assign shipment feature
- Auto-create customer on shipment creation
