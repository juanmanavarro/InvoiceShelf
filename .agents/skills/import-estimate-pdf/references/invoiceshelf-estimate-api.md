# InvoiceShelf Estimate Import Reference

## Local Runtime

- App URL: `https://invoiceshelf.ddev.site`
- Run PHP through DDEV: `ddev exec php ...`
- Run MySQL checks through DDEV: `ddev mysql -e "..."`
- API header: `company: <company_id>`
- Auth: Sanctum bearer token.

## Useful Models

- `App\Models\User`
- `App\Models\Customer`
- `App\Models\Estimate`
- `App\Models\TaxType`
- `App\Models\CustomField`

## Estimate Endpoint

`POST /api/v1/estimates`

Required payload shape:

```json
{
  "estimate_date": "2025-10-23",
  "expiry_date": "2025-11-23",
  "reference_number": "Project name",
  "customer_id": 9,
  "template_name": "estimate1",
  "discount_type": "fixed",
  "discount_val": 0,
  "discount": 0,
  "sub_total": 37500,
  "tax": 7875,
  "total": 45375,
  "notes": "",
  "exchange_rate": 1,
  "customFields": [
    {"id": 3, "value": "Galarza - opciones 1 y 2"},
    {"id": 1, "value": "30 días naturales"}
  ],
  "items": [
    {
      "item_id": null,
      "name": "Interior/Lineas rectas",
      "description": "Desarrollo opciones",
      "quantity": 1,
      "price": 15000,
      "discount_type": "fixed",
      "discount_val": 0,
      "discount": 0,
      "tax": 3150,
      "total": 15000,
      "taxes": [
        {"tax_type_id": 1, "name": "IVA", "percent": 21, "amount": 3150, "compound_tax": 0}
      ]
    }
  ],
  "taxes": [
    {"tax_type_id": 1, "name": "IVA", "percent": 21, "amount": 7875, "compound_tax": 0}
  ]
}
```

## Customer Creation

If the customer does not exist, create it via API before creating the estimate. Inspect existing customer tests/controllers for exact required fields in the current checkout:

```bash
sed -n '1,220p' tests/Feature/Admin/CustomerTest.php
sed -n '1,220p' app/Http/Requests/CustomerRequest.php
```

Then `POST /api/v1/customers` with the same auth and company header. Preserve:

- Legal/company name from PDF.
- Tax number/NIF/CIF.
- Billing address lines, city, province/state, postal code, and country.
- EUR currency when the PDF is in EUR.

## Common PDF Mapping

- `Proyecto` -> Estimate custom field `Proyecto`, and optionally `reference_number`.
- `Entrega` -> Estimate custom field `Entrega`.
- `Fecha de Presupuesto` -> `estimate_date`.
- `Válido hasta` -> `expiry_date`.
- `Nota:` paragraph -> `notes`.
- `Términos del Presupuesto` and following legal text -> do not import into notes unless the user explicitly asks.
