# EasyFix -> Micronet API Integration

This document is the compact handoff spec for integrating `easyfix.mv` with `micronet.mv`.

## Auth
Use a static bearer token.

```http
Authorization: Bearer YOUR_OPENCLAW_API_TOKEN
Accept: application/json
Content-Type: application/json
```

## Base URL
```text
https://micronet.mv/api
```

## Core Rules
- Customer identity is primarily matched by `phone`.
- `job_type` for EasyFix jobs must be `easyfix`.
- Create customer and job first.
- Add quote/invoice items to the job.
- Convert to invoice only after items are present.
- Payments are pushed separately.

## External Reference Fields
Micronet can now store EasyFix references:
- `easyfix_user_id` on customer
- `easyfix_job_id` on job
- `easyfix_quote_id` on job
- `easyfix_invoice_id` on job

---

## 1. Create Customer
**POST** `/customers`

### Request
```json
{
  "name": "Mariyam A.",
  "phone": "7771234",
  "email": "mariyam@example.com",
  "address": "Hulhumale Phase 1",
  "notes": "EasyFix customer",
  "category": "easyfix",
  "easyfix_user_id": "ef_user_1024"
}
```

### Response
```json
{
  "created": true,
  "message": "Customer created successfully.",
  "data": {
    "id": 123,
    "name": "Mariyam A.",
    "phone": "7771234",
    "email": "mariyam@example.com",
    "address": "Hulhumale Phase 1",
    "gst_number": null,
    "category": "easyfix",
    "notes": "EasyFix customer",
    "easyfix_user_id": "ef_user_1024"
  }
}
```

### Duplicate handling
If the phone already exists, Micronet returns the existing customer instead of creating a duplicate.

---

## 2. Create Job
**POST** `/jobs`

### Request
```json
{
  "job_type": "easyfix",
  "customer_name": "Mariyam A.",
  "customer_phone": "7771234",
  "easyfix_user_id": "ef_user_1024",
  "easyfix_job_id": "ef_job_9001",
  "title": "Ceiling fan replacement",
  "problem_description": "Fan not working",
  "customer_notes": "Call before arrival",
  "search_note": "Blue house behind STO",
  "location": "Hulhumale Phase 1",
  "priority": "normal",
  "scheduled_at": "2026-06-05 14:00:00",
  "due_date": "2026-06-10"
}
```

### Response
```json
{
  "message": "Job created.",
  "job_id": 140,
  "job_type": "easyfix",
  "status": "scheduled",
  "easyfix_job_id": "ef_job_9001",
  "customer": {
    "id": 123,
    "name": "Mariyam A.",
    "phone": "7771234"
  },
  "print": {
    "quotation_api": "https://micronet.mv/api/jobs/140/quotation",
    "invoice_api": "https://micronet.mv/api/jobs/140/invoice"
  }
}
```

### Notes
- Required: `job_type`
- Required customer identity: either `customer_id` or `customer_name` + `customer_phone`
- Allowed `job_type`: `moto`, `ac`, `it`, `easyfix`
- Allowed `priority`: `urgent`, `high`, `normal`, `low`

---

## 3. Add Quote / Invoice Items
**POST** `/jobs/{id}/items`

Use this for quote lines before invoice conversion.

### Request by identifier
```json
{
  "identifier": "FAN-001",
  "quantity": 1,
  "unit_price": 450.00
}
```

### Request by inventory item
```json
{
  "inventory_item_id": 123,
  "quantity": 1,
  "unit_price": 450.00
}
```

### Response
```json
{
  "message": "Item added.",
  "item": {
    "id": 991,
    "item_name": "Ceiling Fan",
    "quantity": 1,
    "unit_price": "450.00",
    "subtotal": "450.00"
  }
}
```

---

## 4. Convert Job to Invoice
**POST** `/jobs/{id}/convert-invoice`

Use after job items are ready.

### Request
```json
{
  "due_date": "2026-06-10",
  "approval_method": "signed_copy",
  "customer_notes": "Approved by customer",
  "search_note": "Blue house behind STO",
  "easyfix_quote_id": "ef_quote_4001",
  "easyfix_invoice_id": "ef_invoice_5001"
}
```

### Response
```json
{
  "message": "Invoice prepared successfully.",
  "job_id": 140,
  "invoice_number": "JOB-00140",
  "status": "invoiced",
  "payment_status": "unpaid",
  "easyfix_quote_id": "ef_quote_4001",
  "easyfix_invoice_id": "ef_invoice_5001",
  "invoice_api": "https://micronet.mv/api/jobs/140/invoice",
  "quotation_api": "https://micronet.mv/api/jobs/140/quotation"
}
```

### Rules
- Job must already have at least one item.
- `approval_method` allowed values:
  - `not_applicable`
  - `po`
  - `signed_copy`
- If `approval_method = po`, then `po_number` is required.

---

## 5. Update Job Status
**PATCH** `/jobs/{id}/status`

### Request
```json
{
  "status": "in_progress",
  "notes": "Technician dispatched from EasyFix"
}
```

### Response
```json
{
  "message": "Job status updated.",
  "job_id": 140,
  "status": "in_progress"
}
```

### Allowed statuses
- `new`
- `scheduled`
- `in_progress`
- `waiting_parts`
- `completed`
- `cancelled`

### Suggested EasyFix -> Micronet mapping
- `requested` -> `new`
- `approved` -> `scheduled`
- `assigned` -> `scheduled` or `in_progress`
- `completed` -> `completed`
- `cancelled` -> `cancelled`

---

## 6. Record Payment
**POST** `/jobs/{id}/payments`

### Request
```json
{
  "amount": 450.00,
  "method": "transfer",
  "reference": "TXN-883722"
}
```

### Response
```json
{
  "message": "Payment recorded successfully.",
  "job_id": 140,
  "payment_id": 880,
  "payment_status": "paid",
  "paid_amount": 450,
  "balance_amount": 0
}
```

### Notes
- Supports full or partial payment.
- Micronet updates linked sales workflow status too.

---

## 7. Render Quotation / Invoice HTML
These endpoints return document HTML from Micronet.

### Quotation
**GET** `/jobs/{id}/quotation`

### Invoice
**GET** `/jobs/{id}/invoice`

### Example response
```json
{
  "job_id": 140,
  "type": "invoice",
  "number": "JOB-00140",
  "html": "<html>...</html>"
}
```

---

## 8. Search / Lookup Helpers
### Search customers
**GET** `/customers/search?q=7771234`

### Get customer
**GET** `/customers/{id}`

### Get job
**GET** `/jobs/{id}`

---

## 9. Error Handling
Recommended EasyFix behavior:
- Save locally first.
- Push to Micronet via queue/background job.
- Retry on network failures / HTTP 5xx.
- Log and flag repeated failures for manual review.

Typical API responses:
- `401` invalid bearer token
- `422` validation failure / business rule failure
- `404` missing record
- `500` unexpected server error

### Example validation error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "job_type": ["The selected job type is invalid."]
  }
}
```

---

## 10. Recommended Sync Order
1. Create or find customer
2. Create job
3. Push line items
4. Convert to invoice
5. Render quotation/invoice HTML if needed
6. Push status updates as work progresses
7. Push payments when received

---

## 11. Not Currently Included
These are not part of the current Micronet API contract:
- webhooks
- attachment/file sync
- OAuth
- sandbox endpoint
- quote approval webhook callbacks

---

## 12. Minimal Working Example Flow
```text
POST   /api/customers
POST   /api/jobs
POST   /api/jobs/{id}/items
POST   /api/jobs/{id}/convert-invoice
PATCH  /api/jobs/{id}/status
POST   /api/jobs/{id}/payments
GET    /api/jobs/{id}/invoice
```
