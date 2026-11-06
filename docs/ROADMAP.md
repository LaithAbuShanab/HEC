# HEC Roadmap

Source of truth: `docs/client/HEC.docx`. Phases follow section 21; done-criteria follow section 22.

## Phase 0 — Foundation
- [x] Laravel 13 + Inertia 3 React starter, Pest
- [x] Filament 5 panel + Spatie packages + Horizon
- [x] CLAUDE.md, ROADMAP, missing-data list
- [x] Removed starter-kit customer accounts (out of scope §4); admin auth is Filament only
- [x] Push to GitHub (`LaithAbuShanab/HEC`)
- [x] Tests run on MySQL `hec_testing` with a guard against any non-testing database
- [ ] CI (GitHub Actions): pint, phpstan, pest, tsc, build
- [x] Inertia SSR enabled + verified (headline, nav and categories present in server HTML)
- [x] Design system: brand tokens, self-hosted IBM Plex Sans Arabic + Space Grotesk with metric fallbacks, motion language (weld ring, laser text, sparks, coil)

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

## Phase 2 — Admin (Filament) ✅
- [x] Product resource (tabs: general / specs / variants / media / files / SEO / source & review), bulk availability
- [x] Categories, units, attributes, synonyms, content resources
- [x] Redirects resource + global redirect middleware (301/302/410, loop and chain protection)
- [x] Company settings page (phones, WhatsApp, email, address, map, hours, socials) with per-field client confirmation
- [x] Quote requests: status workflow, internal notes, signed attachment download, export (permission-gated)
- [x] Roles admin/editor/sales (policies), MFA for admin
- [x] Users resource (deactivate instead of delete, MFA reset), activity log viewer, trash/restore on content
- [x] Role-based dashboard: quote stats, 30-day trend, sources, latest quotes, catalog health, products needing work, top and zero-result searches
- [x] CSV/XLSX import/export of products (slug key, blank cell keeps value, cannot bypass publication rules)

## Phase 2 review ✅
- [x] Composite indexes for listings, media lookups, quote queues and search insights (verified with EXPLAIN, rollback-safe)
- [x] N+1 removed, per-request option caching, query budget tests for key screens
- [x] MFA enforced for all staff (the per-role closure was never applied by Filament)
- [x] Brand palette built by hand (Filament's generated 600 shade was too light)
- [x] Navigation order: Sales, Catalog, Content, SEO, System
- [x] Arabic/English panel with per-user language switch, full EN translation with parity test
- [x] LTR values isolated with <bdi> (emails, phones, sizes, paths), readable labels instead of raw keys
- [x] Auto slug from English name, SEO character counters, unit suffixes, global search for products and quotes

## Phase 3 — Public frontend
- [x] Layout: header (search, quote CTA, language switch keeping record), footer, floating WhatsApp/call (non-blocking)
- [x] Pages: home, products, category (+filters), product, services(+detail), industries, guides(+detail), work, FAQ, contact, about, privacy, terms
- [x] Empty sections hidden from menus
- [x] Home page (Forge concept) with published-only sections
- [x] Branded 404/410/500 pages in the URL language
- [x] WebP image sizes, sanitized rich text, per-page SEO (canonical, hreflang, JSON-LD)
- [x] Product: spec table with units, variants, availability + updated date, "contact to confirm specs" fallback
- [x] Arabic-aware search (normalization + synonyms + FULLTEXT), clear no-result state
- [ ] WCAG 2.2 AA pass

## Phase 4 — Request for quote ✅
- [x] Server-side quote list (cookie token, 30-day prune), edit qty/unit/size/notes, free-text item, owner-scoped items
- [x] Form + attachments (real MIME check, private disk) + honeypot + rate limit + idempotency key
- [x] Transactional save → reference `HEC-YYYY-NNNNNN` → success page (noindex, reference kept out of the URL)
- [x] Queued notification with 6 tries and backoff; failure stored and resendable from admin
- [x] WhatsApp prefilled link (product name, URL, chosen size only)
- [x] Feature tests for every section-22 quote scenario, plus a Playwright run of the full flow

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
