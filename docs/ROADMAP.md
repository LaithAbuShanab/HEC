# HEC Roadmap

Source of truth: `docs/client/HEC.docx`. Phases follow section 21; done-criteria follow section 22.

## Phase 0 — Foundation
- [x] Laravel 13 + Inertia 3 React starter, Pest
- [x] Filament 5 panel + Spatie packages + Horizon
- [x] CLAUDE.md, ROADMAP, missing-data list
- [x] Removed starter-kit customer accounts (out of scope §4); admin auth is Filament only
- [x] Push to GitHub (`LaithAbuShanab/HEC`)
- [ ] CI (GitHub Actions): pint, phpstan, pest, tsc, build
- [ ] Inertia SSR enabled + verified (view-source shows product content)
- [ ] Design system: tokens from logo (gold, sky blue, navy, steel grey), Arabic/Latin fonts, motion language

## Phase 1 — Data model & seed
- [x] Locale routing `/ar`, `/en`, root language page (no geo redirect)
- [x] Migrations/models/enums: categories, products, category_product (primary flag), product_variants,
      attributes + attribute_values, sale_units, related_products, historical_specs
- [x] Content: services, guides, industries, work_items, faqs, pages
- [x] Quotes: quote_carts(+items), quote_requests(+items, attachments)
- [x] SEO/system: redirects, search_synonyms, search_logs
- [x] Seed 9 categories, sale units, synonyms
- [x] Seed all 43 appendix products (`database/data/catalog.json`); ambiguous → draft + missing list
- [x] `php artisan hec:product-register` → register of the 43 items (id, location, review status)

## Phase 2 — Admin (Filament)
- [x] Product resource (tabs: general / specs / variants / media / files / SEO / source & review), bulk availability
- [x] Categories, units, attributes, synonyms, content resources
- [x] Redirects resource + global redirect middleware (301/302/410, loop and chain protection)
- [x] Company settings page (phones, WhatsApp, email, address, map, hours, socials) with per-field client confirmation
- [x] Quote requests: status workflow, internal notes, signed attachment download, export (permission-gated)
- [x] Roles admin/editor/sales (policies), MFA for admin
- [x] Users resource (deactivate instead of delete, MFA reset), activity log viewer, trash/restore on content
- [x] Role-based dashboard: quote stats, 30-day trend, sources, latest quotes, catalog health, products needing work, top and zero-result searches
- [ ] CSV/XLSX import/export of products

## Phase 3 — Public frontend
- [ ] Layout: header (search, quote CTA, language switch keeping record), footer, floating WhatsApp/call (non-blocking)
- [ ] Pages: home, products, category (+filters), product, services(+detail), industries, guides(+detail), work, FAQ, contact, about, privacy, terms
- [ ] Empty sections hidden from menus
- [ ] Product: spec table with units, variants, availability + updated date, "contact to confirm specs" fallback
- [ ] Arabic-aware search (normalization + synonyms + FULLTEXT), clear no-result state
- [ ] WCAG 2.2 AA pass

## Phase 4 — Request for quote
- [ ] Server-side quote list, edit qty/variant, free-text item
- [ ] Form + attachments + honeypot/Turnstile + rate limit + idempotency
- [ ] Transactional save → reference `HEC-YYYY-NNNNN` → success page (noindex)
- [ ] Queued notification with retries; failure surfaced in admin
- [ ] WhatsApp prefilled link (product name, URL, chosen specs only)
- [ ] Feature tests for every section-22 quote scenario

## Phase 5 — SEO & analytics
- [ ] Meta/canonical/hreflang component; JSON-LD (Organization, LocalBusiness, BreadcrumbList, Product w/o fake Offer, Article)
- [ ] Sitemap (published only, real lastmod), robots.txt, redirects, 404/410
- [ ] Filter URL policy documented
- [ ] GA4 events (view_product, site_search, add_to_quote, remove_from_quote, quote_form_start, quote_submit_success, whatsapp_click, phone_click, catalog_download), deduped, no PII, consent
- [ ] Performance: responsive WebP, LCP preload, lazy below fold, mobile lab report

## Phase 6 — Content
- [ ] Core pages ar/en, 5 guides, FAQ (pending client approval)

## Phase 7 — Launch & handover
- [ ] Staging (basic auth + noindex), production, daily backups + documented restore test
- [ ] Search Console + GA4 under company accounts
- [ ] Security pass (Strix), Playwright E2E, handover package (section 23), training
