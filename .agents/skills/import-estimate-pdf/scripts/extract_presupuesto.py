#!/usr/bin/env python3
"""Extract a Spanish presupuesto PDF into reviewable JSON.

This script is intentionally conservative. It extracts likely fields and line
items, but the agent must review the output against the PDF text before calling
the InvoiceShelf API.
"""

from __future__ import annotations

import argparse
import json
import re
import subprocess
from datetime import date
from pathlib import Path


MONTHS = {
    "ene": 1,
    "enero": 1,
    "feb": 2,
    "febrero": 2,
    "mar": 3,
    "marzo": 3,
    "abr": 4,
    "abril": 4,
    "may": 5,
    "mayo": 5,
    "jun": 6,
    "junio": 6,
    "jul": 7,
    "julio": 7,
    "ago": 8,
    "agosto": 8,
    "sep": 9,
    "sept": 9,
    "septiembre": 9,
    "oct": 10,
    "octubre": 10,
    "nov": 11,
    "noviembre": 11,
    "dic": 12,
    "diciembre": 12,
}


def read_source(source: Path) -> str:
    if source.suffix.lower() == ".txt":
        return source.read_text()

    result = subprocess.run(
        ["pdftotext", "-layout", str(source), "-"],
        check=True,
        text=True,
        stdout=subprocess.PIPE,
    )
    return result.stdout


def money_to_cents(value: str) -> int:
    normalized = value.replace(".", "").replace(",", ".")
    return int(round(float(normalized) * 100))


def parse_spanish_date(value: str) -> str | None:
    match = re.search(r"(\d{1,2})/([A-Za-zÁÉÍÓÚÜÑáéíóúüñ.]+)/(\d{4})", value)
    if not match:
        return None
    day = int(match.group(1))
    month_key = match.group(2).lower().strip(".")
    year = int(match.group(3))
    month = MONTHS.get(month_key)
    if not month:
        return None
    return date(year, month, day).isoformat()


def clean_line(value: str) -> str:
    return re.sub(r"\s+", " ", value).strip()


def extract_header(text: str) -> dict[str, object]:
    project = re.search(r"Proyecto\s+(.+)", text)
    delivery = re.search(r"Entrega\s+(.+)", text)
    estimate_date = re.search(r"Fecha de Presupuesto\s+(.+)", text)
    expiry_date = re.search(r"V[aá]lido hasta\s+(.+)", text)

    customer_name = None
    customer_tax_number = None
    lines = [clean_line(line) for line in text.splitlines() if clean_line(line)]
    for index, line in enumerate(lines):
        if "Proyecto" in line and index > 0:
            customer_name = clean_line(line.split("Proyecto")[0])
            if not customer_name:
                customer_name = clean_line(lines[index - 1].split("Proyecto")[0])
            if index + 1 < len(lines):
                customer_tax_number = clean_line(lines[index + 1].split("Entrega")[0])
            break

    return {
        "customer": {
            "name": customer_name,
            "tax_number": customer_tax_number,
        },
        "project": clean_line(project.group(1)) if project else None,
        "delivery": clean_line(delivery.group(1)) if delivery else None,
        "estimate_date": parse_spanish_date(estimate_date.group(1)) if estimate_date else None,
        "expiry_date": parse_spanish_date(expiry_date.group(1)) if expiry_date else None,
    }


def extract_totals(text: str) -> dict[str, int | None]:
    patterns = {
        "sub_total": r"Subtotal\s+([\d.]+,\d{2})\s+EUR",
        "taxable_base": r"Base Imponible\s+([\d.]+,\d{2})\s+EUR",
        "tax": r"IVA\s+21%\s+([\d.]+,\d{2})\s+EUR",
        "total": r"Total\s+([\d.]+,\d{2})\s+EUR",
    }
    totals: dict[str, int | None] = {}
    for key, pattern in patterns.items():
        match = re.search(pattern, text)
        totals[key] = money_to_cents(match.group(1)) if match else None
    return totals


def extract_note(text: str) -> str:
    match = re.search(
        r"Nota:\s*(.+?)(?:\s+Subtotal|\s+Base Imponible|\s+IVA\s+\d+%|\s+T[ée]rminos del Presupuesto:)",
        text,
        flags=re.S,
    )
    return clean_line(match.group(1)) if match else ""


def extract_items(text: str) -> list[dict[str, object]]:
    items: list[dict[str, object]] = []
    amount_re = re.compile(r"(.+?)\s+([\d.]+,\d{2})\s+EUR$")
    section = text.split(" Tarea", 1)[-1]
    section = re.split(r"\n\s*(?:Nota:|Subtotal|T[ée]rminos del Presupuesto:)", section, maxsplit=1)[0]
    pending_description: list[str] = []

    for raw_line in section.splitlines():
        line = clean_line(raw_line)
        if not line or line in {"Descripción Total", "Descripcion Total"}:
            continue
        match = amount_re.search(line)
        if not match:
            pending_description.append(line)
            continue

        before_amount = clean_line(match.group(1))
        amount = money_to_cents(match.group(2))
        parts = re.split(r"\s{2,}", raw_line.strip())
        parts = [clean_line(part) for part in parts if clean_line(part)]

        if len(parts) >= 3:
            name = parts[0]
            description = " ".join(parts[1:-1])
        elif len(parts) == 2 and pending_description:
            name = parts[0]
            description = " ".join(pending_description)
        else:
            name = before_amount
            description = " ".join(pending_description)

        items.append(
            {
                "name": name,
                "description": clean_line(description),
                "price": amount,
                "quantity": 1,
            }
        )
        pending_description = []

    return items


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("source", type=Path, help="PDF file or pdftotext -layout output")
    parser.add_argument("--text", action="store_true", help="Also include raw extracted text")
    args = parser.parse_args()

    text = read_source(args.source)
    data = {
        "source": str(args.source),
        **extract_header(text),
        "totals": extract_totals(text),
        "note": extract_note(text),
        "items": extract_items(text),
    }
    if args.text:
        data["text"] = text

    print(json.dumps(data, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
