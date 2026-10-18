# SEO Audit Report

**Repo:** khadamaty (خدماتي) - Laravel 12 / Blade, server-rendered
**Live domain:** not set yet (config `site.canonical_host` is an intentional placeholder; `APP_URL=http://localhost`). Canonical and OG checks were audited against `localhost` and are flagged as a go-live TODO.
**Files audited:** 9 rendered page types (home, services index, about, prices, faq, contact, request-service, diagnose, reviews). This is a server-rendered app, so the audit ran against runtime HTML output, not static files.
**Score:** 96/100
**Generated:** 2026-10-18

## Headline findings

- The new Stage 1 pages (about, prices, faq, contact, request-service, diagnose, reviews) all pass the structural baseline: one `<h1>` each, a unique title and description, canonical, robots, Open Graph, Twitter and JSON-LD. Every JSON-LD block parses cleanly (0 errors across all 9 pages).
- Honesty rules hold. No fabricated ratings, customer counts or firm prices anywhere. The prices table shows clearly marked placeholder numbers, and the reviews page emits AggregateRating and Review schema only when real approved reviews exist (verified by seeding one approved review, confirming the schema appeared, then removing it).
- Robots behaviour is correct per page. The reviews page is `noindex,follow` while it is empty and is excluded from the sitemap. It flips to `index,follow` automatically once an approved review exists. All other new pages are `index,follow` and were added to `sitemap-pages.xml`.
- Internal linking improved sitewide. The header and footer now point to real page URLs (services, prices, about, faq, contact, request-service, diagnose, reviews) instead of home-only anchors, so every page links back to the hubs and home.
- Accessibility pass from web-design-guidelines applied: a skip link was added to the layout, the booking inputs carry `autocomplete`, placeholders end with an ellipsis, and user-generated review text is guarded with `break-words`.

## Top fix-forward items

All mechanical items surfaced in this stage were applied during the build, not left outstanding:

1. Header and footer navigation rewired from `#anchor` (home only) to named routes so links work from every page.
2. Static pages registered in `SitemapController::buildPages()` (indexable ones only; the empty reviews page is deliberately excluded).
3. Booking form extracted to a shared `x-booking-form` component with `autocomplete` on name and phone, used by both the home page and `/request-service`.

Remaining (content and config, for the site owner, not mechanical):

4. Set `CANONICAL_HOST` (and a real `APP_URL`) before go-live so canonical, OG, hreflang and the sitemap switch from `localhost` to the production non-www HTTPS host.
5. Replace the placeholder prices in `config/site.php` (packages) and in `resources/views/pages/prices.blade.php` (the guidance table) with real figures, and remove the placeholder note.
6. Add real approved reviews through the `reviews` table (status `approved`) as they come in. The page and schema switch on automatically.

## Full findings by category

### A. Structural baseline (39/40)

- Title: present and unique on every new page, format `{page} | خدماتي`, all within the length budget (roughly 33 to 46 Arabic characters once the misleading UTF-8 byte count is halved).
- Meta description: present and unique on every page. The reviews description is on the short side (around 58 characters), but that page is noindexed while empty, so it does not matter until reviews land.
- H1: exactly one per page on all 9 audited pages.
- Canonical: present and absolute on every page, built from `canonical_host` or `APP_URL`, query string stripped.
- Open Graph and Twitter: both present on every page (og:title, og:description, og:type, og:url, og:image and the summary_large_image card).
- JSON-LD: present and valid on every page. Validation parsed 4 blocks on most pages and 5 on faq and services, with 0 parse errors.
- Language, charset and viewport: all present via the shared layout.
- Mixed content: none in output (dev is http://localhost; production will be forced to HTTPS by the canonical middleware).

### B. AI search readiness (20/20)

- BreadcrumbList: present on every new nested page, with absolute URLs.
- FAQPage: on the home page and the dedicated faq page, from a single shared source in `config/site.faqs`.
- ItemList: on the services hub, built from the published services.
- Organization and HVACBusiness (LocalBusiness): present on every page via the layout. LocalBusiness still prints only real fields (placeholder phone and address omitted).
- Review and AggregateRating: emitted only from real approved reviews. Confirmed absent while the table is empty, and present once an approved review exists.
- Heading hierarchy: sensible on every page (h1 then section h2s, card h3s). The diagnose wizard uses h2 for the step prompt and h3 for the causes and recommendation.

### C. Linking and structure (20/20)

- Internal links use clean URLs (no `.html`).
- Header and footer link every page to the hubs, the booking flow and home. The diagnose recommendation deep-links to `/request-service?service=...` which preselects the matching service.
- Anchor text is descriptive (service names, page names, "شوف التوصية", "التفاصيل"), never "click here".

### D. Content basics (12/15)

- No raster images were added; every icon is inline SVG marked `aria-hidden`, so there is no CLS risk. The `x-img` component remains available for future images with explicit dimensions and lazy loading.
- No keyword stuffing. Each H1 carries the page's primary intent.
- The diagnose and reviews pages are progressive-enhancement friendly: the diagnose symptom content is real HTML (crawlable) and the wizard is a JS layer on top, with a `<noscript>` fallback that reveals all results.
- Thin by design: the prices table numbers are placeholders, and the reviews page is empty (and noindexed) until real reviews arrive. Neither is padded to hit a word count.

### E. Infrastructure (5/5)

- `robots.txt`: dynamic. In production it allows crawling, disallows `/admin` and `/vendor-dashboard`, and points to `/sitemap.xml`. Outside production it returns `Disallow: /`.
- `sitemap.xml`: the pages sub-sitemap now lists home plus the six indexable static pages (about, prices, faq, contact, request-service, diagnose) alongside the content-type indexes. The empty reviews page is correctly excluded.
- Redirects: `config/redirects.php` map still applied via the `Route::fallback`, returning 301 for mapped old slugs and a real 404 otherwise.

## Pages that scored highest

- Services hub (`/services`): ItemList and Breadcrumb schema, published services from the database, clean meta.
- FAQ (`/faq`): FAQPage and Breadcrumb schema from a shared source, indexable.
- Diagnose (`/diagnose`): real crawlable content plus an accessible stepped wizard, deep-linking into the booking flow.

## Pages that scored lowest

- Reviews (`/reviews`): empty by design, correctly noindexed and out of the sitemap, so the low content score here is expected and safe. It upgrades itself once approved reviews exist.
- Prices (`/prices`): strong structure, but the figures are placeholders until the owner sets real numbers.

## What this audit did NOT check

- Backlinks (off-site)
- Rankings (out of scope)
- Core Web Vitals (run Lighthouse separately)
- Content quality and E-E-A-T depth
- Schema validity in Google's Rich Results Test (run that manually against the extracted JSON-LD before go-live)

---

# SEO Audit Report — Stage 2 (Service pages)

**Scope:** 9 service detail pages at `/services/{slug}`, the `/services` hub, home internal links, sitemap and redirects.
**Audited against:** runtime HTML output (server-rendered), via the local dev server.
**Stage 2 score:** 98/100
**Generated:** 2026-10-18

## Headline findings

- All 9 service pages pass the structural baseline: exactly one `<h1>`, a unique `<title>` under 60 characters, a unique meta description, canonical, robots, Open Graph, Twitter and JSON-LD. Six JSON-LD blocks per page (Organization, LocalBusiness, WebSite global, plus Service, FAQPage, BreadcrumbList per page) and all parse cleanly.
- Publishing is content-gated. A service is `is_published` (and `index,follow`, in the sitemap) only when `Service::hasCompleteContent()` is true (body + includes + symptoms + steps + faqs). An incomplete service stays unpublished, returns 404, is `noindex,follow` and is excluded from the sitemap. Verified: when only 3 were complete, `sitemap-services.xml` listed exactly those 3 and the other 6 returned 404.
- Honesty rules hold. No ratings, customer counts or firm prices. Every price, duration and warranty detail is a visible `TODO` placeholder the owner fills in. No "cheapest" or "lifetime warranty" style claims. Brand framing is "بنشتغل على كل الماركات" with no agency or partnership claim.
- Internal linking is strong. Each service page links to related services (published only), the areas hub and individual area chips, the brands hub, the services hub (breadcrumb) and home (breadcrumb). The home service cards and a "كل الخدمات" link point to the published detail pages; the `/services` hub lists all published services.
- Old Stage 1 service slugs 301-redirect to the Stage 2 slugs (registered as explicit routes so they take precedence over the dynamic `/services/{service}` route): `maintenance`, `cleaning`, `freon-charge`, `ac-install`, `repair`.

## Checks applied during this stage (mechanical)

1. Meta descriptions brought into the 120 to 160 character band for `ac-maintenance`, `ac-gas-refill` and `ac-leak-repair` (were 116, 119 and 100).
2. Per-service `seo_title` and `seo_description` overrides wired through the controller and `partials/meta`, falling back to name and excerpt.
3. `areaServed` in the Service schema populated from the real service areas in `config/site.php`.

## Full findings by category

- A. Structural baseline: pass. Titles 43 to 56 chars; descriptions 120 to 151 chars; one H1 each; canonical, OG, Twitter, robots all present.
- B. AI search readiness: pass. BreadcrumbList on every (nested) service page, Organization on home, Service and FAQPage per page, all fields populated (no placeholder values in schema).
- C. Linking and structure: pass. Clean URLs (no `.html`), related and hub and home links on every page, descriptive anchor text ("التفاصيل", service names), no broken in-repo links.
- D. Content basics: pass. 500 to 800 words of unique copy per published service, no duplicated paragraphs between services, no keyword stuffing, H1 carries the primary term. No images on these pages (nothing to alt-text).
- E. Infrastructure: pass. `robots.txt` blocks all in non-production and allows all plus points to the sitemap in production (correct gate). `sitemap.xml` and `sitemap-services.xml` include only published services.

## Remaining (owner / config, not mechanical)

- Set `CANONICAL_HOST` and a real `APP_URL` before go-live (canonical, OG, hreflang and sitemap are on `localhost` now).
- Fill the `price_note`, `duration_note` and warranty `TODO`s in `database/seeders/data/services.php` (then re-seed) with real figures.
- Add `public/images/og-image.png` for the default share image.

## What this stage did NOT check

- Backlinks, rankings, Core Web Vitals, content quality/E-E-A-T depth, and Google Rich Results Test validity (run manually against the extracted JSON-LD before go-live).

---

# المرحلة 3 — المناطق والماركات

**Generated:** 2026-10-18
**Pages added:** 2 hubs (`/areas`, `/brands`) and 28 detail pages (17 areas, 11 brands). Audited against runtime HTML output, same as earlier stages.
**Stage score:** 97/100 (same deductions as before: `localhost` canonical and the owner TODOs below).

## Headline findings

- All published area and brand pages pass the structural baseline: one `<h1>` each, a unique title (about 40 to 50 characters) and description (about 125 to 150 characters), plus canonical, robots, Open Graph, Twitter and JSON-LD. Every JSON-LD block parses cleanly (6 blocks per detail page, 5 per hub, 0 errors).
- Publishing is gated on complete content, same pattern as services. 17 of 17 areas and 11 of 11 brands now have full unique content and are `index,follow`. The earlier sample verified that an incomplete record stays `noindex` and out of the sitemap, so the gate behaviour is confirmed.
- Schema is correct and honest. Each area and brand emits a `Service` block. Area pages carry `areaServed` set to the area name, with no fabricated branch address (the global `HVACBusiness` block has no `PostalAddress`). `FAQPage` and `BreadcrumbList` are present on every detail page, `ItemList` and `BreadcrumbList` on the hubs. No agency or authorised-dealer claims anywhere; brands use the "بنصلّح كل الماركات" and "مش توكيل" wording.
- Internal linking is dense and two-way. Area to services and brands, brand to services and areas, service to areas (chips now link) and brands. Header, footer and home were updated with المناطق and الماركات entry points, and published areas link from the home and footer chips.
- Redirects and de-duplication done. Five old area slugs (cairo, giza, heliopolis, helwan, shubra) 301-redirect to `/areas`; verified 301 on request. No duplicate area or brand slugs.

## Full findings by category (Stage 3)

- A. Structural baseline: pass. Titles and descriptions unique and within range; one H1 each; canonical, OG, Twitter, robots all present on every published page.
- B. AI search readiness: pass. BreadcrumbList on every nested page, Organization on home, Service plus FAQPage per detail page, ItemList on hubs. All schema fields populated (no placeholder values); `eta_note` TODO is body copy, not schema.
- C. Linking and structure: pass. Clean URLs (no `.html`), hub and home and related links on every page, descriptive anchor text (area and brand and service names, not "click here"), no broken in-repo links, old slugs covered by 301s in `config/redirects.php`.
- D. Content basics: pass. 250 to 400 words of unique copy per published area and brand, no duplicated paragraphs between records, no keyword stuffing, H1 carries the primary term. Brand logos are intentionally text-only for now (letter placeholder), so no missing alt text.
- E. Infrastructure: pass. `sitemap.xml` now lists `sitemap-areas.xml` (4, then 17 after completion) and `sitemap-brands.xml` (2, then 11), published records only. `robots.txt` unchanged and correct.

## Remaining (owner / config, not mechanical)

- Set `CANONICAL_HOST` and a real `APP_URL` before go-live (canonical, OG and sitemap are on `localhost` now).
- Fill the `eta_note` (approximate arrival time per area) and the warranty / parts-source `TODO`s in `database/seeders/data/areas.php` and `database/seeders/data/brands.php`, then re-seed.
- Optionally upload brand logos to `public/images/brands/` and set the `logo` field; the pages switch from the letter placeholder automatically.
