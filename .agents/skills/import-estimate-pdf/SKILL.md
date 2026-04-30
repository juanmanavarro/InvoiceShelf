---
name: import-estimate-pdf
description: Import Spanish estimate/budget PDFs into the local InvoiceShelf app through its API. Use when asked to read a presupuesto PDF, recreate it as an estimate, map Proyecto and Entrega into custom fields, preserve line items/taxes/totals, create the customer if missing, or fix imported estimate data.
---

# Import Estimate PDF

## Goal

Create an InvoiceShelf estimate from a source PDF such as `resources/Presupuesto_58.pdf`, using the local app API instead of direct database writes whenever creating records.

## Workflow

1. Extract the PDF text.
   - Prefer `python3 ~/.codex/skills/import-estimate-pdf/scripts/extract_presupuesto.py <pdf>`.
   - The script also accepts a `.txt` file produced by `pdftotext -layout`.
   - Fallback: `pdftotext -layout <pdf> -`.
2. Review the extracted JSON against the PDF text.
   - Confirm customer name/NIF/address.
   - Confirm `Proyecto`, `Entrega`, dates, line items, note, subtotal, IVA, and total.
   - Do not import “Términos del Presupuesto”.
3. Inspect local InvoiceShelf data with DDEV.
   - Company header is usually `company: 1`.
   - Find or create the customer.
   - Find custom fields for `Estimate`: `Proyecto` and `Entrega`.
   - Find the IVA tax type, usually `IVA` at `21%`.
4. Create the estimate via `POST /api/v1/estimates`.
   - Generate a Sanctum token with DDEV/Tinker.
   - Do not send `estimate_number`; let InvoiceShelf generate it.
   - Send `customFields` for Proyecto and Entrega.
   - Send `notes` only for PDF “Nota:” content. Leave notes empty if the PDF only has terms.
   - Send line item totals as net line totals, not gross with tax.
5. Verify.
   - Read back `GET /api/v1/estimates/{id}` through the API.
   - Confirm edit-screen totals: sum of `items[].total` equals `sub_total`; `tax` and `total` match the PDF.
   - Confirm custom fields contain Proyecto and Entrega.
   - Run relevant tests if code changed.

## Commands

Use DDEV for this project:

```bash
ddev exec php artisan tinker --execute='echo App\\Models\\User::findOrFail(1)->createToken("estimate-import", ["*"])->plainTextToken;'
```

Search existing customer and fields:

```bash
ddev mysql -e "SELECT id, name, company_name, currency_id, company_id FROM customers WHERE name LIKE '%CLIENTE%' OR company_name LIKE '%CLIENTE%';"
ddev mysql -e "SELECT id, label, slug, type FROM custom_fields WHERE model_type = 'Estimate' ORDER BY id;"
ddev mysql -e "SELECT id, name, percent, company_id FROM tax_types WHERE name LIKE '%IVA%' OR percent = 21;"
```

Create a missing customer via API if absent. Follow existing customer payload conventions in the app/tests; at minimum preserve name/company name, tax number, address, country/city/postal data when present.

## Payload Rules

- Store money as integer cents: `150,00 EUR` -> `15000`.
- Header totals:
  - `sub_total`: PDF subtotal/base imponible in cents.
  - `tax`: PDF IVA amount in cents.
  - `total`: PDF total in cents.
- Line items:
  - `price`: line net amount in cents.
  - `quantity`: normally `1`.
  - `total`: net line amount, equal to `price * quantity - discount_val`.
  - `tax`: line IVA amount in cents.
  - `taxes[].amount`: line IVA amount.
- Whole-estimate `taxes[].amount` is the PDF IVA total.
- Use `reference_number` for the project name when helpful, but still set the `Proyecto` custom field.
- Use `customFields`:

```json
[
  {"id": 3, "value": "Proyecto extraido"},
  {"id": 1, "value": "Entrega extraida"}
]
```

Do not assume IDs blindly. Query `custom_fields` first; the IDs above are the current local app values.

## Verification Checklist

- API returns `201`.
- The created estimate has generated number like `PRESU-000003`.
- `items.length` matches the PDF.
- `items.reduce((sum, item) => sum + item.total, 0) === sub_total`.
- `fields` includes `Proyecto` and `Entrega` with extracted values.
- `notes` does not contain Proyecto, Entrega, or Términos del Presupuesto.
- If the queue is not sync, run `ddev exec php artisan queue:work --once --stop-when-empty` to generate the PDF media.

## References

Read `references/invoiceshelf-estimate-api.md` when unsure about the local API payload or database checks.
