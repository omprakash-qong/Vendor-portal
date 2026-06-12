#!/usr/bin/env python3
"""
PDF Extraction Script — PyMuPDF + pdfplumber
Output: JSON to stdout
Usage: python3 extract_pdf.py <path_to_pdf>
"""
import sys
import json
import re

def is_machine_readable(path: str) -> tuple[bool, int]:
    """Check PDF has extractable text. Returns (is_readable, page_count)."""
    import fitz
    doc = fitz.open(path)
    page_count = len(doc)
    if page_count == 0:
        doc.close()
        return False, 0
    total_chars = sum(len(doc[i].get_text().strip()) for i in range(min(3, page_count)))
    doc.close()
    avg = total_chars / min(3, page_count)
    return avg >= 30, page_count


def extract_text_pymupdf(path: str) -> list[dict]:
    """Extract full text per page using PyMuPDF."""
    import fitz
    doc = fitz.open(path)
    pages = []
    for i, page in enumerate(doc):
        text = page.get_text()
        pages.append({"page": i + 1, "text": text.strip()})
    doc.close()
    return pages


def extract_tables_pdfplumber(path: str) -> list[dict]:
    """Extract all tables from PDF using pdfplumber."""
    import pdfplumber
    all_tables = []
    with pdfplumber.open(path) as pdf:
        for i, page in enumerate(pdf.pages):
            tables = page.extract_tables()
            for t_idx, raw_table in enumerate(tables):
                if not raw_table or len(raw_table) < 2:
                    continue
                cleaned = clean_table(raw_table)
                if not cleaned:
                    continue
                all_tables.append({
                    "page": i + 1,
                    "table_index": t_idx,
                    "headers": cleaned["headers"],
                    "rows": cleaned["rows"],
                })
    return all_tables


def clean_table(raw: list[list]) -> dict | None:
    """
    Normalize a raw pdfplumber table.
    - First non-empty row is treated as headers.
    - Empty cells become None.
    - Merges multi-line cell text.
    """
    if not raw:
        return None

    def clean_cell(v):
        if v is None:
            return None
        v = str(v).replace("\n", " ").strip()
        return v if v else None

    # Find first row that has at least 2 non-null cells → treat as header
    header_row = None
    data_start = 0
    for idx, row in enumerate(raw):
        cleaned = [clean_cell(c) for c in row]
        non_null = [c for c in cleaned if c]
        if len(non_null) >= 2:
            header_row = cleaned
            data_start = idx + 1
            break

    if header_row is None:
        return None

    # Fill None header slots with col index label
    headers = []
    for i, h in enumerate(header_row):
        headers.append(h if h else f"col_{i}")

    rows = []
    for row in raw[data_start:]:
        cleaned = [clean_cell(c) for c in row]
        # Skip rows that are entirely empty
        if not any(c for c in cleaned):
            continue
        # Pad or trim to header length
        while len(cleaned) < len(headers):
            cleaned.append(None)
        cleaned = cleaned[:len(headers)]
        row_dict = {}
        for h, v in zip(headers, cleaned):
            row_dict[h] = v
        rows.append(row_dict)

    if not rows:
        return None

    return {"headers": headers, "rows": rows}


def map_headers(headers: list[str]) -> dict[str, str]:
    """
    Map raw column headers to canonical field names.
    Returns {original_header: canonical_field}.
    """
    MAPPINGS = {
        # Power
        "power_kw": [
            "power", "rated power", "motor power", "kw", "kilowatt",
            "output power", "shaft power", "power (kw)", "power(kw)",
            "rated kw", "power kw", "output kw",
        ],
        "power_hp": [
            "hp", "horsepower", "bhp", "horse power", "power (hp)", "power(hp)",
        ],
        # Poles
        "poles": [
            "poles", "pole", "no. of poles", "number of poles", "no of poles",
            "4 pole", "6 pole", "2 pole", "8 pole",
        ],
        # Frame
        "frame_size": [
            "frame", "frame size", "iec frame", "frame no", "frame number",
            "motor frame", "iec", "frame (iec)",
        ],
        # Speed / RPM
        "rpm": [
            "rpm", "speed", "synchronous speed", "rated speed", "full load speed",
            "speed (rpm)", "rpm (50hz)", "synchronous rpm",
        ],
        # Voltage
        "voltage_v": [
            "voltage", "volt", "volts", "supply voltage", "rated voltage",
            "voltage (v)", "v", "operating voltage",
        ],
        # Current
        "current_a": [
            "current", "ampere", "amps", "rated current", "full load current",
            "flc", "current (a)", "fl amps",
        ],
        # Efficiency
        "efficiency_class": [
            "efficiency class", "ie class", "efficiency grade", "motor class",
            "energy class", "ie", "efficiency", "eff class",
        ],
        # IP Rating
        "ip_rating": [
            "ip", "ip rating", "ip class", "protection", "ingress protection",
            "protection class", "ip grade",
        ],
        # Size / DN
        "size_inch": [
            "size", "nps", "nominal size", "valve size", "pipe size",
            "nominal pipe size", "size (inch)", "size (\")", "inch", "in",
        ],
        "size_mm": [
            "dn", "nominal diameter", "size (mm)", "size (dn)", "diameter",
            "bore", "nominal bore", "nb",
        ],
        # Pressure
        "pressure_bar": [
            "pressure", "pressure (bar)", "rated pressure", "max pressure",
            "working pressure", "operating pressure", "bar", "design pressure",
        ],
        "pressure_psi": [
            "psi", "pressure (psi)", "psig",
        ],
        "pressure_class": [
            "class", "pressure class", "ansi class", "rating class",
            "pressure rating", "flange class",
        ],
        # Flow
        "flow_m3h": [
            "flow", "flow rate", "capacity", "rated flow", "flow (m3/h)",
            "m3/h", "m³/h", "flowrate", "q", "flow m3h",
        ],
        "flow_lpm": [
            "lpm", "l/min", "litre/min", "liter/min", "flow (lpm)",
        ],
        # Head
        "head_m": [
            "head", "total head", "head (m)", "discharge head", "tdh",
            "total dynamic head", "m head",
        ],
        # Valve specific
        "valve_type": [
            "valve type", "type", "valve", "style",
        ],
        "body_material": [
            "body material", "body", "material", "body mat", "construction",
            "casting material",
        ],
        "trim_material": [
            "trim", "trim material", "seat material", "disc material",
            "trim/seat",
        ],
        "end_connection": [
            "end connection", "connection", "end type", "flange type",
            "port connection", "ends",
        ],
        # Pump specific
        "pump_type": [
            "pump type", "type", "pump style",
        ],
        # Variant / model
        "variant_name": [
            "model", "model no", "model number", "part no", "part number",
            "catalogue no", "catalog no", "item", "item no", "product code",
            "code", "sku",
        ],
        # Series
        "series_name": [
            "series", "series name", "product series", "range",
        ],
    }

    result = {}
    for header in headers:
        if not header:
            continue
        norm = header.lower().strip().rstrip("*:").strip()
        matched = False
        for field, variants in MAPPINGS.items():
            for v in variants:
                if norm == v or norm.startswith(v) or v in norm:
                    result[header] = field
                    matched = True
                    break
            if matched:
                break
        if not matched:
            result[header] = None  # unmapped — goes into specifications JSON
    return result


def parse_numeric(val: str | None) -> float | None:
    """Extract first number from a cell value."""
    if val is None:
        return None
    val = str(val)
    # Remove common units
    val = re.sub(r'(?i)(kw|hp|bar|psi|rpm|v|a|m|mm|inch|"|\s)', '', val)
    m = re.search(r'[\d]+(?:[.,]\d+)?', val)
    if m:
        return float(m.group().replace(',', '.'))
    return None


def parse_int(val: str | None) -> int | None:
    n = parse_numeric(val)
    return int(n) if n is not None else None


def normalize_rows(mapped_headers: dict, rows: list[dict]) -> list[dict]:
    """
    Convert raw row dicts into structured variant records using mapped headers.
    Numeric fields are coerced; unmapped fields go into 'specifications'.
    """
    NUMERIC_FLOAT = {"power_kw", "power_hp", "size_inch", "size_mm",
                     "pressure_bar", "pressure_psi", "flow_m3h", "flow_lpm",
                     "head_m", "current_a"}
    NUMERIC_INT   = {"poles", "voltage_v", "rpm"}

    variants = []
    for row in rows:
        record = {}
        specs  = {}
        for orig_header, value in row.items():
            canonical = mapped_headers.get(orig_header)
            if canonical is None:
                if value:
                    specs[orig_header] = value
                continue
            if canonical in NUMERIC_FLOAT:
                record[canonical] = parse_numeric(value)
            elif canonical in NUMERIC_INT:
                record[canonical] = parse_int(value)
            else:
                record[canonical] = value.strip() if value else None

        # HP → kW conversion
        if record.get("power_hp") and not record.get("power_kw"):
            record["power_kw"] = round(record["power_hp"] * 0.7457, 2)

        # PSI → bar conversion
        if record.get("pressure_psi") and not record.get("pressure_bar"):
            record["pressure_bar"] = round(record["pressure_psi"] * 0.0689476, 2)

        # LPM → m³/h conversion
        if record.get("flow_lpm") and not record.get("flow_m3h"):
            record["flow_m3h"] = round(record["flow_lpm"] / 1000 * 60, 3)

        # Skip rows with no meaningful data
        meaningful = {k: v for k, v in record.items() if v is not None}
        if not meaningful:
            continue

        if specs:
            record["specifications"] = specs

        # Build a variant_name from power+poles+frame if no model field
        if not record.get("variant_name"):
            parts = []
            if record.get("power_kw"):
                parts.append(f"{record['power_kw']}kW")
            if record.get("poles"):
                parts.append(f"{record['poles']}P")
            if record.get("frame_size"):
                parts.append(str(record["frame_size"]))
            if record.get("size_inch"):
                parts.append(f"{record['size_inch']}\"")
            if record.get("size_mm"):
                parts.append(f"DN{int(record['size_mm'])}" if record['size_mm'] == int(record['size_mm']) else f"DN{record['size_mm']}")
            if parts:
                record["variant_name"] = " ".join(parts)

        variants.append(record)

    return variants


def detect_category(pages: list[dict], tables: list[dict]) -> str:
    """Guess product category from text + table headers."""
    all_text = " ".join(p["text"] for p in pages).lower()
    headers  = " ".join(
        " ".join(str(h) for h in t["headers"] if h)
        for t in tables
    ).lower()
    combined = all_text + " " + headers

    rules = [
        (["motor", "motors", "ie1", "ie2", "ie3", "ie4", "frame size", "poles"], "Electric Motors"),
        (["valve", "valves", "gate valve", "globe valve", "ball valve", "butterfly"], "Valves"),
        (["pump", "pumps", "centrifugal", "submersible", "head m"], "Pumps"),
        (["compressor", "compressors", "screw compressor"], "Compressors"),
        (["blower", "blowers"], "Blowers"),
        (["gearbox", "gearboxes", "reducer", "gear reducer"], "Gearboxes"),
        (["transformer", "transformers"], "Transformers"),
        (["instrument", "transmitter", "sensor", "flow meter"], "Instruments"),
    ]
    for keywords, category in rules:
        if any(kw in combined for kw in keywords):
            return category
    return "General"


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Usage: extract_pdf.py <path>"}))
        sys.exit(1)

    path = sys.argv[1]

    try:
        readable, page_count = is_machine_readable(path)
    except Exception as e:
        print(json.dumps({"error": f"Cannot open PDF: {str(e)}"}))
        sys.exit(1)

    if not readable:
        print(json.dumps({
            "error": "scanned_pdf",
            "message": "This PDF appears to be scanned or image-based. Please upload a Digital PDF, Excel, or CSV."
        }))
        sys.exit(1)

    # Extract text
    pages = extract_text_pymupdf(path)
    full_text = "\n".join(p["text"] for p in pages)

    # Extract tables
    tables = extract_tables_pdfplumber(path)

    # Map headers and normalize rows for each table
    structured_tables = []
    for tbl in tables:
        header_map = map_headers(tbl["headers"])
        variants   = normalize_rows(header_map, tbl["rows"])
        structured_tables.append({
            "page":         tbl["page"],
            "table_index":  tbl["table_index"],
            "headers":      tbl["headers"],
            "header_map":   header_map,
            "raw_rows":     tbl["rows"],
            "variants":     variants,
        })

    category = detect_category(pages, tables)

    output = {
        "page_count":        page_count,
        "full_text":         full_text,
        "tables":            structured_tables,
        "detected_category": category,
        "total_variants":    sum(len(t["variants"]) for t in structured_tables),
    }

    print(json.dumps(output, ensure_ascii=False, default=str))


if __name__ == "__main__":
    main()
