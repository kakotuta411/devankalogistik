# API Endpoints

## Web Routes (auth required, readonly middleware)

### Shipments
| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /shipments | shipments.index | List shipments (filter, sort, paginate) |
| GET | /shipments/create | shipments.create | Create form |
| POST | /shipments | shipments.store | Create shipment |
| GET | /shipments/{id} | shipments.show | Detail |
| GET | /shipments/{id}/edit | shipments.edit | Edit form |
| PUT | /shipments/{id} | shipments.update | Update |
| DELETE | /shipments/{id} | shipments.destroy | Delete |
| GET | /shipments/export/csv | shipments.export.csv | Export CSV |
| GET | /shipments/export/pdf | shipments.export.pdf | Export PDF (preview support) |
| POST | /shipments/{id}/hold | shipments.hold | Hold shipment |
| POST | /shipments/{id}/cancel | shipments.cancel | Cancel shipment |

### Manifests
| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /manifests | manifests.index | List |
| GET | /manifests/create | manifests.create | Create form |
| POST | /manifests | manifests.store | Create manifest |
| GET | /manifests/{id} | manifests.show | Detail |
| GET | /manifests/{id}/edit | manifests.edit | Edit form |
| PUT | /manifests/{id} | manifests.update | Update |
| DELETE | /manifests/{id} | manifests.destroy | Delete |
| GET | /manifests/export/csv | manifests.export.csv | Export CSV |
| GET | /manifests/export/pdf | manifests.export.pdf | Export PDF |
| POST | /manifests/{id}/dispatch | manifests.dispatch | Dispatch (In Transit) |
| POST | /manifests/{id}/receive | manifests.receive | Receive (Arrived) |
| POST | /manifests/{id}/complete | manifests.complete | Complete (Delivered) |

### Invoices
| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /invoices | invoices.index | List |
| GET | /invoices/create | invoices.create | Create form |
| POST | /invoices | invoices.store | Create invoice |
| GET | /invoices/{id} | invoices.show | Detail |
| GET | /invoices/{id}/edit | invoices.edit | Edit form |
| PUT | /invoices/{id} | invoices.update | Update |
| DELETE | /invoices/{id} | invoices.destroy | Delete |
| GET | /invoices/export/csv | invoices.export.csv | Export CSV |
| GET | /invoices/export/pdf | invoices.export.pdf | Export PDF |
| POST | /invoices/{id}/mark-paid | invoices.mark-paid | Mark paid |
| POST | /invoices/{id}/mark-cancelled | invoices.mark-cancelled | Cancel |
| POST | /invoices/{id}/payment | invoices.payment | Record partial payment |

### Customers (API)
| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /api/customers/search?q= | api.customers.search | Search customers |
| POST | /api/customers | api.customers.store | Create customer |
| GET | /api/customers/{id}/shipments | api.customers.shipments | PO groups + resi list |

### Regions (API)
| Method | Path | Description |
|--------|------|-------------|
| GET | /api/regions/provinces | List provinces |
| GET | /api/regions/regencies?province= | List regencies by province |
| GET | /api/regions/districts?regency= | List districts by regency |
| GET | /api/regions/villages?district= | List villages by district |

### Print
| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /print/invoices/{id}/pdf | print.invoice | Print invoice PDF |
| GET | /print/shipments/{id}/label | print.shipment-label | Print koli label (multi-page) |
| GET | /print/shipments/{id}/resi | print.shipment-resi | Print resi detail (half-A4) |
| GET | /print/manifests/{id}/sheet | print.manifest-sheet | Print manifest sheet |
| GET | /print/shipments/list | print.shipment-list | Print shipment list |

### Purchase Orders
| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /purchase-orders | purchase-orders.index | List POs |
| GET | /purchase-orders/create | purchase-orders.create | Create form |
| POST | /purchase-orders | purchase-orders.store | Create PO |
| GET | /purchase-orders/{id} | purchase-orders.show | Detail |
| GET | /purchase-orders/{id}/edit | purchase-orders.edit | Edit form |
| PUT | /purchase-orders/{id} | purchase-orders.update | Update |

### Public Tracking (no auth, CORS enabled)
| Method | Path | Description |
|--------|------|-------------|
| GET | /api/tracking/search?resi= | Search by resi (min 3 chars) |
| GET | /api/tracking/{resi} | Tracking detail (limited PII) |

### Admin Only
| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET/POST | /admin/template-management | template-management | Print template settings |
| POST | /admin/template-management/upload | template-management.upload | Upload template |
| CRUD | /branches | branches.* | Branch management |
| CRUD | /users | users.* | User management |
