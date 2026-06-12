#!/usr/bin/env python3
"""
Website crawler using Crawl4AI — no LLM, CSS extraction only.
Usage: python3 crawl_website.py <url>
Output: JSON to stdout
"""
import sys
import json
import asyncio
import re
import os
import logging
from urllib.parse import urlparse, urljoin

# Suppress Crawl4AI and Playwright progress output — all debug/info goes to stderr
logging.basicConfig(stream=sys.stderr, level=logging.WARNING)
os.environ.setdefault("CRAWL4AI_VERBOSE", "0")

MAX_PAGES   = 500
MAX_DEPTH   = 3
RATE_LIMIT  = 1.0  # seconds between requests

PRODUCT_SIGNALS = [
    "product", "catalogue", "catalog", "item", "spec",
    "datasheet", "/p/", "/products/", "/equipment/",
    "/motors/", "/electric-motors/", "/gear-motors/", "/special-motors/",
    "/valves/", "/pumps/", "/compressors/", "/actuators/", "/gearbox/",
    "/instruments/", "/sensors/",
]

# Names that are navigation/site elements, not real products
BOGUS_NAMES = {
    "about us", "home", "contact", "contact us", "sitemap", "xml sitemap",
    "careers", "career opportunities", "resources", "industries", "blogs",
    "overview", "management", "facilities", "certifications", "sustainability",
    "gallery", "news", "events", "privacy policy", "terms", "404",
    "why us", "global presence", "milestones", "csr", "electric motors",
    "gear motors", "special motors", "motors",
}

# Words that indicate the "name" is actually marketing copy / a sentence
SENTENCE_SIGNALS = [
    "find applications", "advantage of", "versatility", "economy and ease",
    "robust performance", "sailing in", "for every requirement",
    "special needs require", "different speeds", "maintenance",
]

CATEGORY_KEYWORDS = {
    "Motors":      ["motor", "kw", "pole", "rpm", "frame", "induction", "ie2", "ie3", "ie4"],
    "Valves":      ["valve", "gate", "globe", "ball", "butterfly", "check", "needle"],
    "Pumps":       ["pump", "centrifugal", "submersible", "head", "impeller"],
    "Compressors": ["compressor", "screw", "piston", "fad", "compressed air"],
    "Instruments": ["sensor", "transmitter", "flow meter", "pressure gauge", "level", "temperature"],
    "Gearboxes":   ["gearbox", "reducer", "gear", "ratio", "output torque", "gear motor"],
    "Actuators":   ["actuator", "pneumatic cylinder", "linear actuator"],
}


def guess_category(text: str) -> str:
    t = text.lower()
    for cat, keywords in CATEGORY_KEYWORDS.items():
        if any(k in t for k in keywords):
            return cat
    return "Other"


def normalize_url(url: str) -> str:
    """Normalize URL for deduplication: strip fragment, trailing slash, force https."""
    p = urlparse(url)
    # Force https and strip fragment, then strip trailing slash
    return p._replace(scheme='https', fragment='').geturl().rstrip('/')


def is_product_url(url: str) -> bool:
    return any(s in url.lower() for s in PRODUCT_SIGNALS)


# URL path segments that denote a single product *detail* page (as opposed to
# a category/listing page). Generic e-commerce conventions, no site specifics.
DETAIL_SEGMENTS = ["/product/", "/products/", "/p/", "/item/", "/items/",
                   "/dp/", "/sku/", "/pd/", "/prod/"]


def is_detail_url(url: str) -> bool:
    """True if the URL looks like an individual product page: a detail segment
    followed by a non-empty slug (e.g. /products/fisher-ys)."""
    path = urlparse(url).path.lower()
    for seg in DETAIL_SEGMENTS:
        i = path.find(seg)
        if i >= 0 and len(path[i + len(seg):].strip("/")) >= 2:
            return True
    return False


# Common "results per page" parameter names. If the seed URL carries one
# (in its query or fragment), we raise it so the listing renders many products
# at once instead of just the first page. Generic — no site-specific names.
PAGE_SIZE_PARAMS = {"perpage", "per_page", "pagesize", "page_size", "pagesz",
                    "limit", "size", "count", "rows", "numresults", "take", "n"}


def maximize_page_size(url: str, target: int = 300) -> str:
    """Raise any results-per-page parameter in the URL's query/fragment to
    `target`, so a single render returns as many products as possible."""
    def bump(qs: str) -> str:
        if not qs or "=" not in qs:
            return qs
        out = []
        for part in qs.split("&"):
            if "=" in part:
                k, v = part.split("=", 1)
                if k.lower() in PAGE_SIZE_PARAMS:
                    out.append(f"{k}={target}")
                    continue
            out.append(part)
        return "&".join(out)

    p = urlparse(url)
    return p._replace(query=bump(p.query), fragment=bump(p.fragment)).geturl()


def emit_product(p: dict) -> None:
    """Stream one scraped product to stdout immediately so the caller can save
    it live (incremental import). Prefixed so noise can be filtered out."""
    try:
        sys.stdout.write("PRODUCT\t" + json.dumps(p, ensure_ascii=False, default=str) + "\n")
        sys.stdout.flush()
    except Exception:
        pass


def slug_to_name(url: str) -> str:
    """Derive a readable product name from the URL slug as last resort."""
    slug = urlparse(url).path.rstrip("/").split("/")[-1]
    return slug.replace("-", " ").replace("_", " ").title()


def extract_product_name(body: str, url: str) -> str:
    """
    Priority: H2 (most product sites put product name here) → H1 → title tag (strip site name) → URL slug.
    """
    # H2 first — many product sites use H2 as the product heading
    # Use only the FIRST H2 to avoid concatenating sub-headings
    h2 = re.search(r'<h2[^>]*>(.*?)</h2>', body, re.DOTALL | re.IGNORECASE)
    if h2:
        name = re.sub(r'<[^>]+>', '', h2.group(1)).strip()
        # Crawl4AI sometimes returns all H2s concatenated — take the first line only
        name = name.split('\n')[0].strip()
        if name and name.lower() not in BOGUS_NAMES and len(name) > 2 and len(name) < 80:
            return name

    # H1
    h1 = re.search(r'<h1[^>]*>(.*?)</h1>', body, re.DOTALL | re.IGNORECASE)
    if h1:
        name = re.sub(r'<[^>]+>', '', h1.group(1)).strip()
        if name and name.lower() not in BOGUS_NAMES and len(name) > 2:
            return name

    # Title tag — strip trailing " | Site Name" or " - Site Name"
    title_m = re.search(r'<title[^>]*>(.*?)</title>', body, re.DOTALL | re.IGNORECASE)
    if title_m:
        title = re.sub(r'<[^>]+>', '', title_m.group(1)).strip()
        # Strip " | ..." or " - ..." suffix (site branding)
        title = re.split(r'\s*[|\-–—]\s*', title)[0].strip()
        if title and title.lower() not in BOGUS_NAMES and len(title) > 2:
            return title

    # Fallback: derive from URL slug
    return slug_to_name(url)


def extract_meta_description(body: str) -> str:
    m = re.search(r'<meta[^>]+name=["\']description["\'][^>]+content="([^"]*)"', body, re.IGNORECASE)
    if not m:
        m = re.search(r'<meta[^>]+content="([^"]*)"[^>]+name=["\']description["\']', body, re.IGNORECASE)
    return m.group(1).strip() if m else ''


def extract_specs_from_text(text: str) -> dict:
    specs = {}

    # Power range: "0.09 kW to 1000 kW" — capture both min and max
    range_m = re.search(r'(\d+(?:\.\d+)?)\s*kW?\s+to\s+(\d+(?:\.\d+)?)\s*kW?', text, re.IGNORECASE)
    if range_m:
        specs['power_kw_min'] = float(range_m.group(1))
        specs['power_kw_max'] = float(range_m.group(2))
    else:
        m = re.search(r'(\d+(?:\.\d+)?)\s*(?:kw|KW|kW)', text)
        if m: specs['power_kw'] = float(m.group(1))

    # Poles
    m = re.search(r'(\d)\s*[-\s]?[Pp]ole', text)
    if m: specs['poles'] = int(m.group(1))

    # Voltage
    m = re.search(r'(\d{3})\s*[Vv](?:\b|AC)', text)
    if m: specs['voltage_v'] = int(m.group(1))

    # DN size
    m = re.search(r'DN\s*(\d+)', text, re.IGNORECASE)
    if m: specs['size_mm'] = int(m.group(1))

    # Pressure
    m = re.search(r'(\d+(?:\.\d+)?)\s*bar', text, re.IGNORECASE)
    if m: specs['pressure_bar'] = float(m.group(1))

    # Flow
    m = re.search(r'(\d+(?:\.\d+)?)\s*m[3³]/h', text, re.IGNORECASE)
    if m: specs['flow_m3h'] = float(m.group(1))

    # RPM
    m = re.search(r'(\d{3,4})\s*RPM', text, re.IGNORECASE)
    if m: specs['rpm'] = int(m.group(1))

    return specs


def extract_from_tables(tables_html: list) -> dict:
    specs = {}
    for table in tables_html:
        rows = re.findall(r'<tr[^>]*>(.*?)</tr>', table, re.IGNORECASE | re.DOTALL)
        for row in rows:
            cells = re.findall(r'<t[hd][^>]*>(.*?)</t[hd]>', row, re.IGNORECASE | re.DOTALL)
            cells = [re.sub(r'<[^>]+>', '', c).strip() for c in cells]
            if len(cells) >= 2 and cells[0] and cells[1]:
                key   = cells[0].lower().strip().rstrip(':')
                value = re.sub(r'\s+', ' ', cells[1]).strip()
                if key and value and len(key) < 60:
                    specs[key] = value
    return specs


def pick_product_image(media: dict) -> str | None:
    """Pick the real product image from crawl4ai media — skip logos/icons,
    prefer the highest relevance score then the largest width."""
    imgs = (media or {}).get("images", []) or []
    best = None  # (score, width, src)
    for im in imgs:
        src = (im.get("src") or "").strip()
        if not src or not re.search(r'\.(jpg|jpeg|png|webp)(\?|$)', src, re.IGNORECASE):
            continue
        low = src.lower() + " " + (im.get("alt") or "").lower()
        if any(bad in low for bad in ["logo", "icon", "placeholder", "avatar", "sprite", "favicon"]):
            continue
        try:
            width = int(im.get("width") or 0)
        except (TypeError, ValueError):
            width = 0
        score = im.get("score") or 0
        rank = (score, width)
        if best is None or rank > (best[0], best[1]):
            best = (score, width, src)
    return best[2] if best else None


def extract_intro_description(md: str) -> str | None:
    """Pull the real product description (the 'Introduction' section) from the
    page markdown, stopping at the next section heading."""
    if not md:
        return None
    i = md.lower().find("introduction")
    if i < 0:
        return None
    rest = md[i + len("introduction"):]
    cut = len(rest)
    for sect in ["specification", "application", "features", "related product",
                 "enquiry", "downloads", "datasheet", "contact"]:
        j = rest.lower().find(sect)
        if 0 <= j < cut:
            cut = j
    text = rest[:cut]
    text = re.sub(r'\[([^\]]+)\]\([^)]*\)', r'\1', text)   # md links → text
    text = re.sub(r'[*_#>`|]', ' ', text)
    text = re.sub(r'\s+', ' ', text).strip()
    return text[:1000] or None


def normalize_product(raw: dict, source_url: str) -> dict | None:
    name = (raw.get("product_name") or "").strip()
    # Collapse whitespace (common when scraped from multi-line HTML)
    name = re.sub(r'\s+', ' ', name).strip()
    if not name or len(name) < 3:
        return None

    # Reject obvious non-products
    if name.lower() in BOGUS_NAMES:
        return None
    # Reject site taglines / marketing sentences
    name_lower = name.lower()
    if any(sig in name_lower for sig in SENTENCE_SIGNALS):
        return None
    # Reject if name looks like a site tagline (contains | or is very long)
    if ' | ' in name or len(name) > 120:
        return None
    # Reject if it reads like a sentence (ends with period and is > 40 chars)
    if len(name) > 40 and name.endswith('.'):
        return None

    full_text = name + " " + (raw.get("description") or "")
    specs = {}

    raw_specs = raw.get("specifications") or {}
    if isinstance(raw_specs, dict):
        specs.update(raw_specs)
    elif isinstance(raw_specs, list):
        for item in raw_specs:
            k = (item.get("key") or "").strip().rstrip(":")
            v = (item.get("value") or "").strip()
            if k and v:
                specs[k] = v
        full_text += " " + " ".join(str(v) for v in specs.values())

    # Regex extraction supplements table data
    regex_specs = extract_specs_from_text(full_text + " " + " ".join(str(v) for v in specs.values()))
    for k, v in regex_specs.items():
        if k not in specs:
            specs[k] = v

    # Derive brand / model from a "Brand - Model" style name (e.g. "Supremo - IE3 Motors").
    brand = (raw.get("brand") or "").strip()
    model = (raw.get("model_number") or "").strip()
    if (not brand or not model) and re.search(r'\s[-–—]\s', name):
        parts = re.split(r'\s[-–—]\s', name, 1)
        if len(parts) == 2 and parts[0].strip() and parts[1].strip():
            brand = brand or parts[0].strip()
            model = model or parts[1].strip()

    return {
        "product_name": name,
        "model_number": model or None,
        "brand":        brand or None,
        "category":     guess_category(full_text),
        "description":  ((raw.get("description") or raw.get("og_description") or "").strip()[:500]) or None,
        "source_url":   source_url,
        "image_url":    (raw.get("og_image") or raw.get("image_url") or None),
        "datasheet_url":raw.get("datasheet_url") or None,
        "specifications": specs,
    }


async def crawl_with_crawl4ai(url: str) -> dict:
    try:
        for _log_name in ("crawl4ai", "crawl4ai.async_webcrawler", "asyncio"):
            logging.getLogger(_log_name).setLevel(logging.ERROR)

        from crawl4ai import AsyncWebCrawler, CrawlerRunConfig
        from crawl4ai.extraction_strategy import JsonCssExtractionStrategy

        try:
            from playwright.async_api import async_playwright
            async with async_playwright() as pw:
                browser = await pw.chromium.launch(headless=True)
                await browser.close()
        except Exception:
            return await crawl_fallback(url)

        SCHEMA = {
            "name": "Product",
            "baseSelector": "body",
            "fields": [
                # H2 first (most vendor sites use H2 for product name), then H1
                {"name": "product_name",  "selector": "h2, h1, [class*='product-title'], [class*='product-name']", "type": "text"},
                {"name": "model_number",  "selector": "[class*='model'], [class*='part-no'], [class*='sku'], [class*='catalog']", "type": "text"},
                {"name": "brand",         "selector": "[class*='brand'], [class*='manufacturer']", "type": "text"},
                {"name": "description",   "selector": "meta[name='description']", "type": "attribute", "attribute": "content"},
                {"name": "image_url",     "selector": "img[class*='product'], .product img, [class*='product-image'] img, article img", "type": "attribute", "attribute": "src"},
                {"name": "og_image",      "selector": "meta[property='og:image']", "type": "attribute", "attribute": "content"},
                {"name": "og_description","selector": "meta[property='og:description']", "type": "attribute", "attribute": "content"},
                {"name": "datasheet_url", "selector": "a[href*='datasheet'], a[href*='.pdf']", "type": "attribute", "attribute": "href"},
                {"name": "specifications","selector": "table tr", "type": "nested_list",
                 "fields": [
                     {"name": "key",   "selector": "th:first-child, td:first-child", "type": "text"},
                     {"name": "value", "selector": "td:last-child",      "type": "text"},
                 ]}
            ]
        }

        strategy = JsonCssExtractionStrategy(SCHEMA)
        config   = CrawlerRunConfig(
            extraction_strategy=strategy,
            word_count_threshold=10,
            wait_for_images=False,
        )

        products, pages_crawled, errors = [], 0, []
        seen = set()
        seen_product_urls = set()  # dedup products by source URL
        base_domain = urlparse(url).netloc
        start_norm = normalize_url(url)
        seen.add(start_norm)
        queue = [(url, 0)]

        async with AsyncWebCrawler(verbose=False) as crawler:
            while queue and pages_crawled < MAX_PAGES:
                current_url, depth = queue.pop(0)
                norm_url = normalize_url(current_url)
                if depth > MAX_DEPTH:
                    continue

                try:
                    result = await crawler.arun(url=current_url, config=config)
                    pages_crawled += 1

                    if result.extracted_content and norm_url not in seen_product_urls:
                        seen_product_urls.add(norm_url)

                        # Real product image: prefer crawl4ai's scored media (skips logo),
                        # then og:image as a fallback.
                        meta = getattr(result, "metadata", None) or {}
                        best_image = pick_product_image(getattr(result, "media", None)) \
                            or meta.get("og:image") or meta.get("twitter:image")

                        # Real description: the "Introduction" section from the page content,
                        # NOT the generic og:description blurb.
                        md = result.markdown if isinstance(result.markdown, str) else getattr(result.markdown, "raw_markdown", "")
                        intro_desc = extract_intro_description(md or "") or meta.get("og:description") or meta.get("description")

                        # Spec table parsed straight from the page HTML (reliable).
                        page_html = getattr(result, "html", "") or getattr(result, "cleaned_html", "") or ""
                        table_specs = extract_from_tables(
                            re.findall(r'<table[^>]*>.*?</table>', page_html, re.IGNORECASE | re.DOTALL)
                        )

                        raw_data = json.loads(result.extracted_content)
                        items = raw_data if isinstance(raw_data, list) else [raw_data]
                        for item in items:
                            if table_specs:
                                merged = dict(table_specs)
                                existing = item.get("specifications")
                                if isinstance(existing, dict):
                                    for k, v in existing.items():
                                        merged.setdefault(k, v)
                                item["specifications"] = merged
                            p = normalize_product(item, current_url)
                            if p:
                                if best_image:
                                    p["image_url"] = best_image
                                if intro_desc:
                                    p["description"] = intro_desc
                                products.append(p)

                    if depth < MAX_DEPTH and result.links:
                        for link in result.links.get("internal", []):
                            href = link.get("href", "")
                            if href and is_product_url(href):
                                full_url = href if href.startswith("http") else urljoin(current_url, href)
                                fn = normalize_url(full_url)
                                if urlparse(full_url).netloc == base_domain and fn not in seen:
                                    seen.add(fn)
                                    queue.append((full_url, depth + 1))

                    await asyncio.sleep(RATE_LIMIT)

                except Exception as e:
                    errors.append({"url": current_url, "error": str(e)})

        # Final dedup by normalized source URL
        deduped, seen_final = [], set()
        for p in products:
            key = normalize_url(p["source_url"])
            if key not in seen_final:
                seen_final.add(key)
                deduped.append(p)

        return {"products": deduped, "pages_crawled": pages_crawled, "errors": errors}

    except ImportError:
        return await crawl_fallback(url)


async def crawl_fallback(url: str) -> dict:
    """Fallback: basic HTML fetch + regex extraction when Crawl4AI is unavailable."""
    import urllib.request

    products, errors = [], []
    seen = set()
    seen_product_urls = set()  # dedup products by source URL
    start_norm = normalize_url(url)
    seen.add(start_norm)
    queue = [(url, 0)]
    pages_crawled = 0
    base_domain = urlparse(url).netloc

    req_headers = {
        "User-Agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        "Accept": "text/html,application/xhtml+xml",
        "Accept-Language": "en-US,en;q=0.9",
    }

    while queue and pages_crawled < 50:
        current_url, depth = queue.pop(0)
        norm_url = normalize_url(current_url)
        if depth > 2:
            continue

        try:
            req  = urllib.request.Request(current_url, headers=req_headers)
            resp = urllib.request.urlopen(req, timeout=15)
            body = resp.read().decode('utf-8', errors='replace')
            pages_crawled += 1

            # Only extract product data from product-signal URLs (once per URL)
            if is_product_url(current_url) and norm_url not in seen_product_urls:
                seen_product_urls.add(norm_url)
                name        = extract_product_name(body, current_url)
                description = extract_meta_description(body)
                specs       = extract_specs_from_text(body)
                tables      = re.findall(r'<table[^>]*>.*?</table>', body, re.IGNORECASE | re.DOTALL)
                specs.update(extract_from_tables(tables))

                # Prefer og:image (most reliable), then a product <img>.
                og = re.search(r'<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']', body, re.IGNORECASE) \
                     or re.search(r'<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']', body, re.IGNORECASE)
                img = re.search(r'<img[^>]+(?:class="[^"]*product[^"]*"|id="[^"]*product[^"]*")[^>]+src="([^"]+)"', body, re.IGNORECASE)
                if not img:
                    img = re.search(r'<img[^>]+src="(https?://[^"]+\.(?:jpg|jpeg|png|webp))"', body, re.IGNORECASE)
                image_url = (og.group(1) if og else None) or (img.group(1) if img else None)

                # PDF datasheet link
                pdf = re.search(r'href="(https?://[^\s"]*\.pdf)"', body, re.IGNORECASE)
                datasheet_url = pdf.group(1) if pdf else None

                p = normalize_product({
                    "product_name": name,
                    "description":  description,
                    "specifications": specs,
                    "image_url":    image_url,
                    "datasheet_url": datasheet_url,
                }, current_url)
                if p:
                    products.append(p)

            # Discover product links
            if depth < 2:
                links = re.findall(r'href="(https?://[^"]+)"', body)
                for link in links:
                    parsed = urlparse(link)
                    clean  = parsed._replace(fragment='').geturl()
                    fn     = normalize_url(clean)
                    if (parsed.netloc == base_domain
                            and is_product_url(clean)
                            and fn not in seen):
                        seen.add(fn)
                        queue.append((clean, depth + 1))

            await asyncio.sleep(RATE_LIMIT)

        except Exception as e:
            errors.append({"url": current_url, "error": str(e)})

    # Final dedup by normalized source URL
    deduped, seen_final = [], set()
    for p in products:
        key = normalize_url(p["source_url"])
        if key not in seen_final:
            seen_final.add(key)
            deduped.append(p)

    return {"products": deduped, "pages_crawled": pages_crawled, "errors": errors}


# ─────────────────────────────────────────────────────────────────────────
# Generic browser-driven crawler
#
# Works on any site, including JavaScript single-page catalogues. It renders
# each page in a real headless browser, removes consent/cookie overlays,
# scrolls to trigger lazy-loaded content, clicks generic "view more / show
# more" expanders, then reads product fields and specification label/value
# pairs from tables, definition lists, and two-column blocks alike.
#
# No LLM, no API keys, no site-specific rules — deterministic DOM reads only.
# ─────────────────────────────────────────────────────────────────────────

# JS: strip consent/cookie banners and full-screen modal backdrops that block
# clicks — conservatively, so page content is never removed by mistake.
_JS_REMOVE_OVERLAYS = r"""()=>{
  // 1) known consent / cookie banners (by id/class/aria), safe to remove
  const sel=['#onetrust-consent-sdk','#onetrust-banner-sdk','.onetrust-pc-dark-filter',
    '#truste-consent-track','[id*="cookie-banner"]','[class*="cookie-banner"]',
    '[id*="cookieConsent"]','[class*="cookie-consent"]','[id*="gdpr"]','[class*="gdpr"]',
    '[aria-label*="cookie" i]','[class*="consent-banner"]'];
  sel.forEach(s=>{try{document.querySelectorAll(s).forEach(e=>e.remove());}catch(_){}});
  // 2) genuine full-screen fixed backdrops/modals (cover most of the viewport)
  document.querySelectorAll('body *').forEach(e=>{
    const s=getComputedStyle(e);
    if((s.position==='fixed'||s.position==='absolute') && parseInt(s.zIndex||0)>=1000){
      const r=e.getBoundingClientRect();
      if(r.width>=innerWidth*0.6 && r.height>=innerHeight*0.6) e.remove();
    }
  });
  document.documentElement.style.overflow='auto';
  if(document.body) document.body.style.overflow='auto';
}"""

# JS: click generic "show more / view more / see all / load more" toggles.
_JS_EXPAND = r"""()=>{
  const re=/^(view|show|see|read|load)\s+(more|all)|more\s+(specifications|details)|all\s+specifications$/i;
  let n=0;
  [...document.querySelectorAll('a,button,span,div')].forEach(e=>{
    if(e.children.length===0 && re.test(e.textContent.trim())){try{e.click();n++;}catch(_){}}
  });
  return n;
}"""

# JS: harvest label/value specification pairs from real spec structures —
# <table> rows and <dl> definition lists — ignoring header/nav/footer chrome.
# Structured tables/lists are reliable; we do NOT scrape arbitrary two-column
# layouts here (that catches nav menus). Div-based specs are matched against
# the known field labels in _JS_KNOWN instead.
_JS_SPECS = r"""()=>{
  const out={};
  const clean=s=>(s||'').replace(/\s+/g,' ').trim();
  const chrome=e=>e.closest('header,nav,footer,[role="navigation"],[class*="menu"],[class*="breadcrumb"]');
  const ok=(k,v)=>k && v && k.length<=45 && k.length>=2 && v.length<=400
                  && !/[.]$/.test(k) && k.split(' ').length<=6 && k!==v;
  document.querySelectorAll('table tr').forEach(tr=>{
    if(chrome(tr))return;
    const c=[...tr.children].map(td=>clean(td.textContent));
    if(c.length>=2 && ok(c[0],c[1])) out[c[0]]=out[c[0]]||c[1];
  });
  document.querySelectorAll('dl').forEach(dl=>{
    if(chrome(dl))return;
    const dts=dl.querySelectorAll('dt'), dds=dl.querySelectorAll('dd');
    for(let i=0;i<dts.length && i<dds.length;i++){
      const k=clean(dts[i].textContent), v=clean(dds[i].textContent);
      if(ok(k,v)) out[k]=out[k]||v;
    }
  });
  return out;
}"""

# JS: for a known set of spec labels, find each label as a leaf element
# (outside header/nav/footer) and read its adjacent value. This is how
# div-based specification layouts are read — keyed off labels the app already
# defines per category, so navigation chrome is never mistaken for specs.
_JS_KNOWN = r"""(LABELS)=>{
  const out={};
  const want=new Set(LABELS);
  const clean=s=>(s||'').replace(/\s+/g,' ').trim();
  // One pass over all leaf elements: keep those whose text is a wanted label,
  // then read the adjacent value (skipping header/nav/footer chrome).
  const els=document.querySelectorAll('*');
  for(let i=0;i<els.length;i++){
    const e=els[i];
    if(e.children.length!==0) continue;
    const t=clean(e.textContent);
    if(!want.has(t) || (t in out)) continue;
    if(e.closest('header,nav,footer,[role="navigation"],[class*="menu"],[class*="breadcrumb"]')) continue;
    let v=''; const sib=e.nextElementSibling, p=e.parentElement;
    if(sib && sib.children.length<=4) v=clean(sib.textContent);
    if(!v && p && p.nextElementSibling) v=clean(p.nextElementSibling.textContent);
    if(v && v!==t && v.length<=400) out[t]=v;
  }
  return out;
}"""


# JS: click a generic "next page" pagination control. Returns true if it
# clicked one that wasn't disabled. Covers rel=next, aria-labels, and the
# common Next / › / » / → glyphs — no site-specific selectors.
_JS_NEXT_PAGE = r"""()=>{
  const cand=[...document.querySelectorAll('a,button,[role="button"],li')];
  const isDisabled=e=>e.disabled||e.getAttribute('aria-disabled')==='true'||/disabled/i.test(e.className||'');
  let el=cand.find(e=>{
    const al=(e.getAttribute('aria-label')||'').toLowerCase().trim();
    const t=(e.textContent||'').trim().toLowerCase();
    return e.rel==='next' || al==='next' || al==='next page' || al.endsWith(' next page')
        || t==='next' || t==='›' || t==='»' || t==='→' || t==='>';
  });
  if(!el) el=cand.find(e=>/(^|\s)next(\s|$)/i.test(e.getAttribute('aria-label')||''));
  if(el && !isDisabled(el)){ try{el.scrollIntoView({block:'center'}); el.click(); return true;}catch(_){} }
  return false;
}"""


async def _prep_page(page):
    """Render aids: remove overlays, nudge lazy content in, expand "view more".

    Scrolls *gently* in steps (not a slam to document bottom) because some
    single-page apps virtualize and unmount mid-page sections — like spec
    blocks — once they leave the viewport, which would erase what we want.
    """
    try:
        await page.evaluate(_JS_REMOVE_OVERLAYS)
        # Expand "view more / show more" toggles so full spec blocks render.
        # We deliberately do NOT slam-scroll to the document bottom: some SPAs
        # virtualize and unmount mid-page sections (spec blocks) once they leave
        # the viewport, which would erase exactly what we need.
        await page.evaluate(_JS_EXPAND)
    except Exception:
        pass
    await page.wait_for_timeout(1500)


# Known per-category field labels, supplied by the app (config/category_fields.php).
# Populated at startup from an optional CLI arg; empty means "unknown".
KNOWN_FIELDS: dict = {}        # {category: {label: canonical_key}}


def _all_known_labels() -> list:
    labels = set()
    for fields in KNOWN_FIELDS.values():
        labels.update(fields.keys())
    return sorted(labels)


def _best_category(found_labels) -> str | None:
    """Pick the category whose label set best matches the found spec labels."""
    best, best_n = None, 0
    fl = set(found_labels)
    for cat, fields in KNOWN_FIELDS.items():
        n = len(fl & set(fields.keys()))
        if n > best_n:
            best, best_n = cat, n
    return best if best_n >= 2 else None


async def _scrape_product(page, source_url: str) -> dict | None:
    """Read product fields + spec pairs from the current rendered page."""
    data = await page.evaluate(r"""()=>{
      const clean=s=>(s||'').replace(/\s+/g,' ').trim();
      const meta=n=>{const e=document.querySelector(n); return e?e.getAttribute('content'):null;};
      const chrome=e=>e&&e.closest('header,nav,footer');
      let h=[...document.querySelectorAll('h1,h2')].find(e=>!chrome(e)&&clean(e.textContent).length>2);
      const ogimg=meta("meta[property='og:image']");
      let img=null,area=0;
      document.querySelectorAll('img').forEach(i=>{
        const s=i.currentSrc||i.src||''; if(!s||/logo|icon|sprite|placeholder|favicon|avatar/i.test(s))return;
        if(chrome(i))return;
        const a=(i.naturalWidth||i.width||0)*(i.naturalHeight||i.height||0);
        if(a>=area){area=a;img=s;}
      });
      return {
        name: clean(h?h.textContent:'') || clean(document.title),
        description: meta("meta[property='og:description']")||meta("meta[name='description']")||'',
        image: img||ogimg||null,
      };
    }""")

    # Structured specs (tables / definition lists) are reliable everywhere;
    # known-label div specs cover sites that render specs as plain blocks.
    structured = await page.evaluate(_JS_SPECS)
    known_div = {}
    known = _all_known_labels()
    if known:
        known_div = await page.evaluate(_JS_KNOWN, known) or {}

    merged = dict(structured)
    for k, v in known_div.items():
        merged.setdefault(k, v)

    name = (data.get("name") or "").strip()
    if not name or len(name) < 3 or name.lower() in BOGUS_NAMES:
        return None

    # Reject listing / category pages: they carry a grid of product links. A
    # genuine detail page links to few (its "related" items at most).
    detail_links = await page.evaluate(
        "(segs)=>{const a=[...document.querySelectorAll('a[href]')].map(e=>{try{return new URL(e.href).pathname.toLowerCase()}catch(_){return ''}}); const hit=new Set(); a.forEach(p=>{for(const s of segs){const i=p.indexOf(s); if(i>=0 && p.slice(i+s.length).replace(/\\/+$/,'').length>=2){hit.add(p);break;}}}); return hit.size;}",
        DETAIL_SEGMENTS,
    )
    if (detail_links or 0) > 6 and not is_detail_url(source_url):
        return None

    # A real product page either matches a known category's fields (≥2) or
    # exposes a genuine spec table. Navigation/listing pages do neither.
    category = _best_category(merged.keys()) if KNOWN_FIELDS else None
    if category:
        specs = merged                       # mapper sorts canonical vs extra
    elif len(structured) >= 2:
        specs = structured
        category = guess_category(name + " " + (data.get("description") or "") + " " + " ".join(structured.keys()))
    else:
        return None

    return {
        "product_name":  re.sub(r"\s+", " ", name)[:200],
        "model_number":  None,
        "brand":         None,
        "category":      category,
        "description":   (data.get("description") or "").strip()[:1000] or None,
        "source_url":    source_url,
        "image_url":     data.get("image"),
        "datasheet_url": None,
        "specifications": specs,
    }


async def generic_browser_crawl(url: str, max_products: int = 300) -> dict:
    from playwright.async_api import async_playwright

    MAX_PRODUCTS = max_products
    MAX_LISTING_PAGES = 60          # safety cap on pagination clicks
    base_domain = urlparse(url).netloc
    products, errors = [], []
    product_urls = set()

    def same_site(u: str) -> bool:
        return urlparse(u).netloc == base_domain

    # selector for clickable product entries (anchors or SPA card headings)
    CARD_SEL = "a[href], h2, h3, h4, [role='heading'], [class*='title'], [class*='name']"
    # exclude header/nav/footer chrome so we click product cards, not menus
    CARD_FILTER = "e.children.length===0 && !e.closest('header,nav,footer,[role=\"navigation\"]') && e.textContent.trim().length>3 && e.textContent.trim().length<90"

    async with async_playwright() as pw:
        browser = await pw.chromium.launch(headless=True)
        page = await browser.new_page()
        seed_norm = normalize_url(url)

        # Maximize the listing page size so one render yields every product,
        # not just the first page.
        seed_url = maximize_page_size(url)
        try:
            await page.goto(seed_url, wait_until="domcontentloaded", timeout=60000)
            await page.wait_for_timeout(9000)   # large listings need time to populate
            await _prep_page(page)
        except Exception as e:
            errors.append({"url": seed_url, "error": "seed: " + str(e)})

        # The seed itself might be a single product page.
        try:
            p = await _scrape_product(page, url)
            if p:
                products.append(p)
                product_urls.add(seed_norm)
                emit_product(p)
        except Exception:
            pass

        # Path A — real anchor links (static / multi-page catalogues). Visit
        # genuine detail pages first so the budget isn't spent on category
        # pages, then fall back to broader product-signal links. Paginate
        # through "Next" so every page's products are gathered, not just page 1.
        detail, other, detail_seen = [], [], set()

        async def gather_anchors():
            try:
                hrefs = await page.evaluate("()=>[...document.querySelectorAll('a[href]')].map(a=>a.href)")
            except Exception:
                return 0
            added = 0
            for h in dict.fromkeys(hrefs):
                if not same_site(h) or normalize_url(h) == seed_norm:
                    continue
                nu = normalize_url(h)
                if is_detail_url(h):
                    if nu not in detail_seen:
                        detail_seen.add(nu)
                        detail.append(h)
                        added += 1
                elif is_product_url(h):
                    other.append(h)
            return added

        try:
            await gather_anchors()
            for _ in range(MAX_LISTING_PAGES):
                if len(detail) >= MAX_PRODUCTS:
                    break
                clicked = await page.evaluate(_JS_NEXT_PAGE)
                if not clicked:
                    break
                await page.wait_for_timeout(3000)        # let the next page render
                try:
                    await page.evaluate(_JS_REMOVE_OVERLAYS)
                except Exception:
                    pass
                if await gather_anchors() == 0:
                    break                                 # no new products → done
            log_n = len(detail)
        except Exception:
            pass

        anchor_urls = detail + other

        async def visit_and_scrape(durl):
            await page.goto(durl, wait_until="domcontentloaded", timeout=30000)
            await page.wait_for_timeout(4500)   # JS catalogues need time to render
            await _prep_page(page)
            return await _scrape_product(page, durl)

        for durl in anchor_urls:
            if len(products) >= MAX_PRODUCTS:
                break
            nu = normalize_url(durl)
            if nu in product_urls:
                continue
            product_urls.add(nu)
            try:
                # Hard cap per page so one hung/slow page can't freeze the crawl.
                p = await asyncio.wait_for(visit_and_scrape(durl), timeout=45)
                if p:
                    products.append(p)
                    emit_product(p)
            except Exception as e:
                errors.append({"url": durl, "error": str(e)})

        # Path B — SPA grids that navigate on click (no usable anchors). Click
        # each card, scrape the detail it opens, then step back. One render of
        # the listing is reused for all cards (no costly reloads).
        if len(products) < 3:
            try:
                await page.goto(url, wait_until="domcontentloaded", timeout=60000)
                await page.wait_for_timeout(3000)
                await _prep_page(page)
                count = await page.evaluate(
                    "(a)=>[...document.querySelectorAll(a.sel)].filter(e=>(" + CARD_FILTER + ")).length",
                    {"sel": CARD_SEL},
                )
            except Exception:
                count = 0

            misses = 0
            for i in range(min(count, MAX_PRODUCTS * 2)):
                if len(products) >= MAX_PRODUCTS:
                    break
                # Stop once we're clearly past the product grid: many clicks in
                # a row that opened nothing new (header links done, footer next).
                if misses >= (6 if products else 18):
                    break
                try:
                    clicked = await page.evaluate(
                        "(a)=>{const els=[...document.querySelectorAll(a.sel)].filter(e=>(" + CARD_FILTER + ")); if(a.i>=els.length)return false; els[a.i].scrollIntoView(); els[a.i].click(); return true;}",
                        {"sel": CARD_SEL, "i": i},
                    )
                    if not clicked:
                        continue
                    await page.wait_for_timeout(4500)   # allow detail page to render
                    cur = page.url
                    nu = normalize_url(cur)
                    found = False
                    if same_site(cur) and is_detail_url(cur) and nu != seed_norm and nu not in product_urls:
                        product_urls.add(nu)
                        await _prep_page(page)
                        p = await _scrape_product(page, cur)
                        if p:
                            products.append(p)
                            emit_product(p)
                            found = True
                    misses = 0 if found else misses + 1
                    # back to the listing for the next card
                    await page.go_back(wait_until="domcontentloaded", timeout=60000)
                    await page.wait_for_timeout(1500)
                    await page.evaluate(_JS_REMOVE_OVERLAYS)
                except Exception:
                    try:
                        await page.goto(url, wait_until="domcontentloaded", timeout=60000)
                        await page.wait_for_timeout(2500)
                        await page.evaluate(_JS_REMOVE_OVERLAYS)
                    except Exception:
                        break

        await browser.close()

    # dedup by source url
    deduped, seen_final = [], set()
    for p in products:
        k = normalize_url(p["source_url"])
        if k not in seen_final:
            seen_final.add(k)
            deduped.append(p)

    return {"products": deduped, "pages_crawled": len(product_urls), "errors": errors}


async def main(url: str) -> dict:
    # Primary: the real-browser engine. It renders JS catalogues, expands
    # "view more" spec sections, and is bounded (≤25 products) so it always
    # returns promptly. It handles both static (anchor) and SPA (click) sites.
    browser_ok = True
    try:
        from playwright.async_api import async_playwright
        async with async_playwright() as pw:
            b = await pw.chromium.launch(headless=True)
            await b.close()
    except Exception:
        browser_ok = False

    if browser_ok:
        try:
            result = await generic_browser_crawl(url)
        except Exception as e:
            result = {"products": [], "pages_crawled": 0, "errors": [{"url": url, "error": str(e)}]}
        if result.get("products"):
            return result

    # Fallback: legacy crawl4ai / static HTML path.
    try:
        return await crawl_with_crawl4ai(url)
    except Exception:
        return {"products": [], "pages_crawled": 0, "errors": [{"url": url, "error": "crawl failed"}]}


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Usage: crawl_website.py <url>"}))
        sys.exit(1)

    target_url = sys.argv[1]

    # Optional 4th arg: a JSON file of known category fields
    # ({category: {label: canonical_key}}) so the browser engine can read
    # div-based spec layouts and tell product pages from navigation.
    if len(sys.argv) >= 4 and sys.argv[3]:
        try:
            with open(sys.argv[3]) as f:
                loaded = json.load(f)
            if isinstance(loaded, dict):
                KNOWN_FIELDS.update(loaded)
        except Exception:
            pass

    # Crawl4AI's rich library writes directly to fd 1 bypassing sys.stdout.
    # We write the JSON result to a temp file instead of stdout so the
    # caller (PHP shell_exec) can read it without noise contamination.
    import tempfile
    out_file = None
    if len(sys.argv) >= 3:
        out_file = sys.argv[2]   # PHP passes output file path as 2nd arg

    result = asyncio.run(main(target_url))
    json_out = json.dumps(result, ensure_ascii=False, default=str)

    if out_file:
        with open(out_file, 'w') as f:
            f.write(json_out)
    else:
        print(json_out)
