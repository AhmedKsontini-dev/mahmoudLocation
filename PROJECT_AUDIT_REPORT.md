# Project Audit Report

**Audit date:** 2026-10-25  
**Workspace:** `D:\symfony\LocationMahmoud`  
**Mode:** Read-only inspection; no tests/builds or automatic fixes were run.

## A. Executive summary

This is a Symfony 7.4 / PHP 8.2+ application for travel transfers, excursions, reservations, editorial content, and administration. Its principal organization is conventional: PHP application code under `src/`, Twig views under `templates/`, Doctrine migrations under `migrations/`, translations under `translations/`, and tests under `tests/`. Front-end assets are predominantly hand-served from `public/assetsFront/` and `public/assetsBack/`.

The most important findings are:

1. **Potentially sensitive configuration and database exports are tracked in Git.** `.env`, `.env.dev`, `.env.test`, and two SQL exports are tracked. Their contents were intentionally not read or reproduced. Treat this as a potentially serious data-exposure concern and review repository access/history and the files’ intended contents.
2. **Several large controllers and templates combine multiple responsibilities.** `ContactController.php` (722 lines), `BlogArticleController.php` (510), `ExcursionController.php` (407), and multiple 500–650-line Twig templates merit focused refactoring.
3. **Substantial page-specific JavaScript is embedded in Twig templates.** The transfer page and two reservation lists contain roughly 94–197 lines of inline JavaScript apiece.
4. **The application has overlapping asset pipelines.** AssetMapper/importmap and Webpack Encore are both configured, but no template uses `importmap()` or an Encore entrypoint. In addition, the configured `assets/app.js` imports a relative `assets/bootstrap.js` that is not present in the inventoried `assets/` tree, and imports the same stylesheet twice.
5. **Performance can degrade as data grows.** The home page’s excursion booking-data path queries once for destinations and then again per destination; some administrative indexes also load unbounded result sets.
6. **Generated test output is tracked, while the configured Playwright test directory is currently absent.** The working tree shows the e2e spec and screenshots as pre-existing deletions; `playwright.config.ts` still points to `./tests-e2e`.
7. **Many tests are present and should be retained.** The PHPUnit suite contains 28 test classes/files (plus bootstrap); no test file is classified as unnecessary merely because it is a test.

The worktree was already dirty when inspection began. This report describes the files currently present and records relevant pre-existing deletions; it does not attribute those edits to this audit.

## B. Technology stack and architecture

| Area | Evidence and role |
|---|---|
| Runtime/framework | PHP `>=8.2`; Symfony `7.4.*`; Twig; Symfony Forms, Security, Validator, Mailer, Translation, Messenger, and UX Turbo. |
| Persistence | Doctrine ORM 3.x, DoctrineBundle, Doctrine Migrations, PostgreSQL Compose service; entity attributes are mapped from `src/Entity/`. |
| Front end | Twig-rendered pages, custom JavaScript/CSS directly served from `public/assetsFront/` and `public/assetsBack/`, Symfony AssetMapper/importmap scaffolding, and a separately configured Webpack Encore toolchain. Tailwind is loaded from a CDN on several frontend pages. |
| Testing | PHPUnit 12 configuration in `phpunit.dist.xml`; 28 PHPUnit test files/classes and `tests/bootstrap.php`. Playwright is in `package.json`, configured in `playwright.config.ts`, but its configured `tests-e2e/` directory is absent in the current working tree. |
| Entry points | `public/index.php` → `src/Kernel.php`; routes use controller attributes loaded through `config/routes.yaml`; services are autowired from `src/` by `config/services.yaml`. |
| Deployment/development | `compose.yaml` defines PostgreSQL; `compose.override.yaml` adds development ports and Mailpit. Composer, npm, Encore, AssetMapper, and Docker configuration are present. |
| Localization | French, English, German, Spanish, and Italian message and validator catalogs under `translations/`; supported locales are also defined in `src/Config/LocaleConfig.php`. |

**Architecture summary:** a server-rendered Symfony monolith divided into admin and frontend controllers, Doctrine entities/repositories, form types, services, and Twig templates. This is a reasonable foundation, but responsibilities have accumulated in several controllers/templates and the front-end build story is not clearly consolidated.

## C. Complete project structure overview

- **Root configuration and tooling:** Composer manifests/lockfile, npm manifests/lockfile, Symfony Flex lock, Docker Compose files, PHPUnit and Playwright configs, `webpack.config.js`, and `importmap.php`. No root README was present in the inventory.
- **`src/`:** Symfony kernel, locale config, 24 controller classes, 37 Doctrine entity classes, enums, form types, repositories, security handlers, three services, and a Twig extension. `.gitignore` placeholders also occur in `src/Controller/`, `src/Entity/`, and `src/Repository/`.
- **`config/`:** bundles, service/routing entrypoints, package configuration, development/test routing, and a large generated `config/reference.php`.
- **`templates/`:** separate `Backend/` and `Frontend/` areas, shared layouts/components, registration/profile/security views, and email templates. Several long page templates contain substantial scripts.
- **`assets/`:** a small AssetMapper/Stimulus scaffold (`app.js`, controllers, `stimulus_bootstrap.js`, CSS, and `controllers.json`). `assets/vendor/` is present but ignored by `.gitignore`.
- **`public/`:** Symfony front controller, icons, custom backend/frontend assets, and uploaded media. Public uploads are application content, not disposable build output. Some image files are very large.
- **`migrations/`:** 25 timestamped Doctrine migrations and `.gitignore`.
- **`tests/`:** PHPUnit functional/service/entity tests and bootstrap.
- **`translations/`:** five message catalogs and five validator catalogs.
- **Root artifacts outside the application structure:** two similarly named SQL exports, a ZIP archive, a reproduction-named PHP script, and a Twig sample in `exemple de tableau/`. Their runtime use is not established.

**Generated/dependency/cache/output directories present:** `vendor/` (9,824 files), `node_modules/` (10,930 files), `var/` (3,699 files), `.phpunit.cache/`, `playwright-report/` (3 files), and `test-results/` (3 files). `public/build/` is absent. These were excluded from detailed source-code review except for their presence, tracked/ignored state, or configured role.

**Current-worktree caveat:** before this audit, Git already reported many modified files and deletions, including `tests-e2e/excursion-creation.spec.ts`, its screenshots, and several root screenshots/HTML artifacts. It also reported untracked migrations, uploads, and tests. The report does not restore, modify, or remove any of them.

## D. Potentially unnecessary files

“Potentially unused” means no ordinary application reference was found in the inspected code/configuration; it is not proof that the item is disposable. Files whose contents could contain secrets or private data were not opened.

| File path | Purpose | Evidence | Status | Removal risk | Recommendation |
|---|---|---|---|---|---|
| `config/reference.php` | Symfony-generated PHP service/config shape reference | Header identifies it as auto-generated; 1,696 lines; no application reference found | Potentially unused by runtime; may support IDE/configuration tooling | Low–Medium | Investigate whether local IDE/tooling consumes it; keep until confirmed |
| `repro_excursion_form.php` | Name suggests a reproduction/debug script; exact purpose unknown | Root-level tracked PHP file; no application reference found. Contents intentionally not inspected | Needs manual verification | Medium | Investigate owner/workflow and any embedded configuration before deciding |
| `exemple de tableau/index.html.twig` | Appears to be an example table template | Located outside `templates/`; no application reference found | Potentially unused sample | Low | Keep if it is a design/example reference; otherwise consider removing after owner confirmation |
| `mahmoudlocation.sql` | Database export/backup (contents not inspected) | Root-level tracked SQL file, 49,707 bytes; no runtime reference found | Non-runtime; backup purpose unknown | High | Keep and securely catalog until contents, provenance, and recovery value are verified |
| `mahmoudlocation (1).sql` | Database export/backup (contents not inspected) | Similar root-level tracked SQL file, 178,051 bytes; name suggests another export, but files were not compared | Potentially redundant backup; not confirmed duplicate | High | Investigate provenance, compare securely, and consider data sensitivity before any removal |
| `stitch_tunisia_travel_mobility_platform (1).zip` | Unknown archive/reference asset | Root-level tracked ZIP, about 6.1 MB; archive contents not inspected; no code reference found | Purpose unverified | Medium | Identify owner and whether it is a required design/source archive |
| `public/assetsFront/images/Logo voyage en Tunisie _ Yassmine Guide.psd` | Editable logo/source artwork | About 6.2 MB; no textual reference found in templates/source/config; PNG logo is referenced by templates | Possibly a design source, not needed for page rendering | Medium | Keep if it is the master artwork; otherwise archive outside the deployed public tree |
| `playwright-report/index.html` | Generated Playwright HTML report | Tracked under a generated report directory; no runtime application use | Generated output | Low–Medium | Retain only while useful for review; prevent routine report output from accumulating in source control |
| `playwright-report/data/b5d32ead9eb883969f27a7470c528570b982ac51.md` | Generated Playwright report data | Tracked report output | Generated output | Low–Medium | Same as above; check for captured application content before publishing |
| `playwright-report/data/b706ce5c378c240a5052750c3f644a0c6f5306be.md` | Generated Playwright report data | Tracked report output | Generated output | Low–Medium | Same as above; check for captured application content before publishing |
| `test-results/.last-run.json` | Playwright run metadata | Tracked under test output directory | Generated output | Low | Usually disposable after preserving needed run state |
| `test-results/excursion-creation-Excursi-2bf83-n-excursion-with-all-fields-chromium/error-context.md` | Generated Playwright failure context | Tracked test output | Generated output; may hold useful failure evidence | Low–Medium | Preserve if needed for debugging; avoid committing routine output |
| `test-results/excursion-creation-Excursi-49be2-required-fields-are-missing-chromium/error-context.md` | Generated Playwright failure context | Tracked test output | Generated output; may hold useful failure evidence | Low–Medium | Preserve if needed for debugging; avoid committing routine output |

**Not candidates for removal:** PHPUnit tests, migrations, user uploads, icons, and referenced application assets. Their presence alone is not evidence of redundancy.

## E. Oversized and complex files

Line counts use physical file lines from the current working tree. Size alone is not a reason to split a file; recommendations below are based on its responsibilities and/or embedded logic.

| File path | Approx. lines | Main problem | Suggested refactoring | Priority |
|---|---:|---|---|---|
| `src/Controller/Admin/ContactController.php` | 722 | One controller handles inbox/folders, message composition/sending, attachments, status transitions, drafts, CSRF checks, and rendering | Extract message delivery and attachment lifecycle into services; keep HTTP/form orchestration in controller; move folder-specific views/queries behind focused helpers | High |
| `src/Controller/Admin/Blog/BlogArticleController.php` | 510 | Create/edit/delete, media upload/deletion, slug handling, content sanitization, form errors, and persistence are coupled; creation/edit flows repeat work | Extract article/media workflow into an application service and a dedicated upload helper; consolidate shared create/update preparation | High |
| `src/Controller/Admin/ExcursionController.php` | 407 | CRUD controller also manages translations, image processing, transaction flow, slug generation, cleanup, and form-error reporting | Extract translation/image/persistence workflow into focused services while leaving routing and response handling in the controller | High |
| `src/Entity/Excursion.php` | 691 | Large aggregate with many translated child collections and accessors; a dense domain object can make changes difficult to reason about | Review aggregate boundaries and move only genuinely separate domain behavior/entities; do not split getters/setters solely to reduce line count | Medium |
| `src/Repository/ExcursionRepository.php` | 358 | Multiple read models/filter queries plus nested booking-data assembly; one method issues another query per destination | Add a joined/batched projection for booking data and keep repository methods focused on one query/read model | Medium |
| `public/assetsBack/js/excursion/form.js` | 723 | Large dynamic-form behavior and media/collection interactions are in one script | Split into focused modules for collection rows, image handling, translation UI, and validation, keeping the existing backend asset organization | Medium |
| `public/assetsBack/css/admin.css` | 1,798 | Global admin stylesheet is large and likely mixes shared layout with page/component rules | Separate tokens/layout/shared components from page-specific styles; preserve the existing `assetsBack/css/` organization | Medium |
| `public/assetsBack/css/reservation-excursion/index.css` | 898 | Large page stylesheet | Split reusable table/status/action patterns from reservation-excursion-specific rules | Low–Medium |
| `templates/Frontend/Excursion/detail.html.twig` | 653 | Detail content, booking/presentation markup, and inline page behavior are together | Extract sections into Twig partials and move substantial page JavaScript to a frontend asset | Medium |
| `templates/Backend/ReservationExcursion/index.html.twig` | 630 | Large admin page plus about 197 lines of inline JavaScript for page actions | Extract table/filter/action partials and page behavior into `public/assetsBack/js/` | Medium |
| `templates/Frontend/Home/index.html.twig` | 531 | A single template contains hero, booking engine, marketing sections, and repeated presentation structure | Extract sections into `templates/Frontend/Components/` partials; keep page composition in the index | Medium |
| `templates/Backend/Contact/index.html.twig` | 578 | Mailbox/folder composition and interaction UI in a single large view | Extract folder/message-list/message-detail components; retain the current controller/view boundary | Medium |
| `templates/Backend/Excursion/form.html.twig` | 573 | Complex nested form presentation and conditional media/translation UI in one template | Extract translation, image, itinerary, and other repeated form sections into partials | Medium |
| `templates/Backend/Reservation/index.html.twig` | 571 | Large reservation list and about 178 lines of inline JavaScript | Extract row/action/modal partials and move behavior to a backend page asset | Medium |
| `templates/Frontend/Transfer/show.html.twig` | 426 | Booking/presentation template includes about 94 lines / 9.7k characters of JavaScript and 24 lines of CSS | Extract the booking behavior and any reusable styles into frontend assets; separate page sections into partials | Medium |
| `tests/AdminExcursionTranslationDuplicationTest.php` | 628 | Large test class likely covers many translation/form scenarios in one file | Keep the tests; if repeated fixtures/setup are confirmed, extract test helpers or split by behavior for readability | Low |

## F. CSS/JavaScript separation recommendations

Approximate sizes were measured from inline `<script>`/`<style>` blocks; linked CDN or external assets are not counted as embedded code.

| Current file | Embedded code | Suggested destination | Reason | Priority |
|---|---|---|---|---|
| `templates/Backend/ReservationExcursion/index.html.twig` | Inline JavaScript, ~197 lines / 8.4k characters | `public/assetsBack/js/reservation-excursion/index.js` | Interaction logic is substantial and belongs outside the template | Medium |
| `templates/Backend/Reservation/index.html.twig` | Inline JavaScript, ~178 lines / 7.6k characters | `public/assetsBack/js/reservation/index.js` | Keeps behavior testable and separates it from list markup | Medium |
| `templates/Frontend/Transfer/show.html.twig` | Inline JavaScript, ~94 lines / 9.7k characters; inline CSS, ~24 lines / 687 characters | `public/assetsFront/js/transfer-booking.js`; optionally `public/assetsFront/css/transfer.css` | Booking behavior is substantial; page styling should follow the existing frontend asset convention | Medium |
| `templates/Backend/Blog/Article/form.html.twig` | Inline JavaScript, ~91 lines / 4.7k characters | `public/assetsBack/js/blog/article-form.js` | Form/editor behavior is large enough to separate from Twig | Medium |
| `templates/Backend/Transfere/index.html.twig` | Inline JavaScript, ~96 lines / 3.1k characters | `public/assetsBack/js/transfere/index.js` | Page-specific interaction currently lives in the template | Low–Medium |
| `templates/Frontend/Liste_excursion/index.html.twig` | Inline JavaScript, ~60 lines / 2.4k characters | `public/assetsFront/js/excursion-list.js` | Filter/list interaction is substantial enough for the existing frontend JS directory | Low–Medium |
| `templates/Frontend/Excursion/detail.html.twig` | Inline JavaScript, ~55 lines / 2.7k characters | `public/assetsFront/js/excursion-detail.js` | Keeps booking/detail behavior separate from a large content template | Low–Medium |
| `templates/Backend/User/index.html.twig` | Inline JavaScript, ~62 lines / 2.6k characters | `public/assetsBack/js/user/index.js` | Page interaction can be maintained/tested independently | Low |
| `templates/Backend/Category/form.html.twig` | Inline JavaScript, ~53 lines / 2.4k characters | `public/assetsBack/js/category-form.js` | Form behavior is more than a small inline snippet | Low |
| `templates/Backend/Destination/form.html.twig` | Inline JavaScript, ~35 lines / 1.6k characters | `public/assetsBack/js/destination-form.js` | Separate if behavior changes or is shared; otherwise low priority | Low |
| `templates/Backend/User/edit.html.twig` | Inline CSS, ~59 lines / 1.7k characters | Existing `public/assetsBack/css/user/index.css` | The page already loads this stylesheet, which is the natural destination | Low |
| `templates/Backend/Transfere/show.html.twig` | Inline CSS, ~24 lines / 687 characters | Existing `public/assetsBack/css/transfere/form.css` | The page already loads this stylesheet | Low |
| `templates/Frontend/Contact/email.html.twig` | Inline CSS, ~98 lines / 2.6k characters | Keep inline, or use a tested email-compatible inliner workflow | Inline CSS is often necessary for email-client rendering; this is an appropriate exception | Keep |

Small inline snippets in the home/contact pages are not independently compelling extraction candidates. The external Tailwind/CDN `<script>` tags are linked scripts, not embedded JavaScript, but are noted under security/performance.

## G. Duplicate code and unused resources

- **Unreferenced asset candidate:** a textual reference scan across templates/source/config/assets found no use of `public/assetsFront/images/Logo voyage en Tunisie _ Yassmine Guide.psd`. The PNG logo is referenced. The PSD may still be the editable master and should not be discarded without owner confirmation.
- **Possible duplicate exports:** `mahmoudlocation.sql` and `mahmoudlocation (1).sql` have similar names but different sizes and timestamps. They were not opened or compared; duplication is not confirmed.
- **Asset pipeline overlap:** AssetMapper/importmap (`config/packages/asset_mapper.yaml`, `importmap.php`, `assets/`) and Encore (`webpack.config.js`, `config/packages/webpack_encore.yaml`, npm Encore packages) are both configured. Templates contain no active `importmap()`/Encore entrypoint calls; custom public assets are loaded directly. Decide which pipeline is authoritative before adding more dependencies or build logic.
- **Entrypoint inconsistency:** `assets/app.js` imports `./bootstrap.js`, but no `assets/bootstrap.js` exists in the inventoried tree. It also imports `./styles/app.css` twice and imports Bootstrap as a namespace without using the binding. The single Encore entry points at this file. No build was run, so this is a static concern; verify the intended pipeline before relying on that entry.
- **Playwright mismatch:** Playwright is configured with `testDir: './tests-e2e'`, but that directory is absent in the current worktree. Git already reports the e2e spec and screenshots as deleted. `package.json` has no dedicated Playwright test script. The report/results directories are present and tracked.
- **Generated files:** `vendor/`, `node_modules/`, `var/`, `assets/vendor/`, and `.phpunit.cache/` are present. The first three are ignored/generated dependency or runtime directories; `assets/vendor/` is ignored by the repository rules. Their presence is not a reason to remove source assets or tests.
- **Large media:** some tracked public uploads exceed 10 MB; the editable PSD is about 6.2 MB and the displayed PNG logo about 1.6 MB. Optimize delivery or move large source/original content out of the public deployment when appropriate, but verify image usage before removal.
- **Repeated status-mail flow:** `ReservationMailerService` has similar recipient/sender validation and send/error handling for transfer and excursion reservation status changes. Some differences are domain-specific; consider extracting only genuinely common behavior to avoid a brittle abstraction.
- **Tests are valuable:** the PHPUnit tests cover admin CRUD, forms, reservations, mail, pricing, and frontend flows. No test was identified for removal. Refactor setup only if duplication is verified.

## H. Architecture and maintainability

1. **Controller responsibilities are broad.** Admin controllers handle request flow alongside domain orchestration, persistence transactions, file operations, and error presentation. Introduce narrow application services for workflows with multiple side effects, especially contact messages, blog media, and excursion media/translations.
2. **Presentation templates are doing too much.** Large Twig views mix markup, page-level control flow, and sizable JavaScript. Extract reusable sections/rows into Twig partials and page behavior into the existing public asset folders.
3. **Repository read models need scaling review.** `ExcursionRepository::findBookingData()` obtains destinations and then queries excursions for each destination. `HomeController` calls it for the booking UI. A grouped/joined query or one batched projection would avoid query count growing with destinations.
4. **Some administrative lists are unbounded.** `BlogArticleController::index()` uses `findBy()` without a limit, and `ExcursionController::index()` builds a result query without pagination. Add pagination when expected record volume warrants it; assess other list controllers similarly.
5. **Asset strategy is ambiguous.** The application’s working pages primarily use direct `asset()` paths in `public/`; AssetMapper and Encore scaffolding coexist without active template entrypoints. Document and standardize the intended production build/deployment path.
6. **Naming is inconsistent but domain-specific.** `Transfere`/`TransfereController` and `Liste_excursion` coexist with English names and `ReservationExcursion`. Avoid a disruptive rename; use consistent names for new code and consider a separately planned migration only if the inconsistency causes real friction.
7. **The frontend is partly data-driven and partly hard-coded.** `HomeController` provides static vehicle/destination/review content alongside repository data. Decide whether marketing content is intentionally static or should be managed/localized; do not infer unused status from hard-coded values alone.
8. **No circular dependency was established.** Service autowiring is broad (`App\` resource), which is a normal Symfony pattern. The audit found no evidence sufficient to claim a circular dependency.

## I. Security and performance observations

These are evidence-based observations, not a penetration test. Potential risks are explicitly distinguished from confirmed exploitability.

### Security

- **High priority — tracked environment files:** `.env`, `.env.dev`, and `.env.test` are tracked. Their contents were not inspected or reproduced. Confirm they contain no live credentials or other sensitive values; review access/history and rotate anything that may have been exposed. `.gitignore` excludes local variants but not these tracked files.
- **High priority — tracked database exports:** `mahmoudlocation.sql` and `mahmoudlocation (1).sql` are tracked. Contents were not inspected. Confirm they do not contain personal, production, or credential data and restrict/remove them from repository history if required by data-handling policy.
- **Configuration concern — Compose:** `compose.yaml` contains a default database credential setting. The value is intentionally omitted. Ensure non-local deployments require secure configuration and do not inherit development defaults; `compose.override.yaml` also publishes development service ports.
- **Potential stored HTML risk:** `templates/Frontend/Excursion/detail.html.twig` renders translated excursion description with `|raw`. No excursion-specific HTML sanitizer was found in the inspected source search. Because this is rich HTML, verify the full write/import path and sanitize with a well-defined allow-list before public rendering if content can be edited or imported from less-trusted sources.
- **Mitigation requiring coverage confirmation:** blog content is rendered as raw HTML in a Twig view, but `BlogArticle` has a sanitizer and the admin article write flow calls it. Verify all legacy/import paths use the sanitizer and that the rich-text policy is tested. This is not a confirmed exploit from the evidence reviewed.
- **Inline JSON context:** `templates/Frontend/Home/index.html.twig` emits repository data into inline JavaScript using `json_encode|raw`. Confirm encoding is safe for a script context (including `<`/closing-script sequences) or move the payload to a safe serialized endpoint/data mechanism. This is a context-hardening concern, not proof of an exploitable input path.
- **Positive controls observed:** `config/packages/security.yaml` protects `/admin` with `ROLE_ADMIN`; `ProfileController` has `#[IsGranted('ROLE_USER')]`; form login CSRF is enabled. The admin contact controller also performs explicit CSRF checks on state-changing operations.
- **Large upload limits:** `public/.user.ini` allows 256 MB uploads and a 260 MB POST body at the PHP layer. Application forms may impose narrower limits; confirm reverse-proxy and application limits are appropriately bounded to reduce resource-exhaustion exposure.
- **External runtime scripts:** multiple frontend pages load Tailwind from `cdn.tailwindcss.com` and fonts from Google Fonts. This adds third-party availability, privacy, and supply-chain dependency; use a controlled local build or review integrity/CSP policy for production.

### Performance

- `ExcursionRepository::findBookingData()` performs destination-level iteration with per-destination excursion queries, creating a potential N+1/query-count growth pattern on the home page.
- `BlogArticleController::index()` and the admin excursion index load unbounded article/excursion collections. Pagination will reduce memory and response-time growth.
- Several public images are very large (individual excursion photos exceed 10 MB), increasing transfer time and bandwidth; provide appropriately sized/compressed variants.
- The extensive templates and large admin CSS/JS files increase maintenance and cache invalidation complexity; split by page/component where this improves loading and ownership.

## J. Prioritized action plan

### 1. High priority

1. Review tracked environment files and database exports for sensitive content without exposing values; rotate any potentially exposed secrets and follow repository-history/data-retention policy.
2. Verify the Compose default credential configuration is never used in a deployed environment, and review which ports are published outside local development.
3. Establish a safe HTML policy for public excursion descriptions and verify every blog/excursion content write and import path.
4. Verify script-context-safe serialization for home-page JSON data.
5. Decide the authoritative asset pipeline and resolve the missing `assets/bootstrap.js` import before relying on the configured Encore entry.
6. Restore, replace, or intentionally retire the Playwright suite; keep `playwright.config.ts` and the actual e2e test directory consistent.

### 2. Medium priority

1. Extract contact-mailbox sending/attachment workflows, blog article media lifecycle, and excursion workflow orchestration from large controllers.
2. Batch excursion booking-data retrieval and paginate unbounded admin lists.
3. Move substantial inline JavaScript from reservation, transfer, blog, and excursion Twig pages into the established `public/assetsBack/js/` and `public/assetsFront/js/` organization.
4. Split large templates into named Twig partials; split `admin.css` into shared and page-level styles.
5. Reduce image transfer sizes and place non-runtime source artwork outside the deployed public tree if it is only a design master.
6. Decide whether tracked Playwright output and test failure contexts are intended deliverables; avoid routine generated-output commits.

### 3. Low priority

1. Check whether `config/reference.php`, the root reproduction script, sample Twig file, and ZIP archive have active owner/tooling value before pruning.
2. Remove the duplicate stylesheet import in `assets/app.js` and clarify whether the unused Bootstrap namespace import is intentional once the pipeline is chosen.
3. Move the small inline styles on existing backend pages into their already-loaded stylesheets.
4. Keep the test suite; extract fixtures or split unusually large test classes only when repeated setup/behavior is confirmed.
5. Adopt consistent names for new code while leaving existing domain names stable unless a planned migration is justified.

## K. Detailed file-by-file inventory

The following inventory covers the first-party application/configuration files and directories discovered. For repeated entity/form/repository naming patterns, the role is indicated by the file’s domain name; dependency/generated internals and individual uploaded-media contents are summarized rather than expanded.

### Root files

| File | Role |
|---|---|
| `.editorconfig` | Editor formatting defaults |
| `.gitignore` | Excludes local/runtime/dependency/build paths |
| `.env`, `.env.dev`, `.env.test` | Environment configuration paths; contents intentionally not inspected |
| `compose.yaml` | PostgreSQL service and named volume |
| `compose.override.yaml` | Development port publication and Mailpit service |
| `composer.json`, `composer.lock` | PHP dependency manifest and lockfile |
| `package.json`, `package-lock.json` | JavaScript dependency manifest and lockfile |
| `symfony.lock` | Symfony Flex recipe state |
| `importmap.php` | AssetMapper/importmap entrypoint/dependency map |
| `webpack.config.js` | Encore output and `app` entry configuration |
| `phpunit.dist.xml` | PHPUnit test suite/bootstrap/configuration |
| `playwright.config.ts` | Playwright configuration; points to absent `tests-e2e/` in current tree |
| `repro_excursion_form.php` | Unverified root PHP utility; content not inspected |
| `mahmoudlocation.sql`, `mahmoudlocation (1).sql` | Uninspected database exports |
| `stitch_tunisia_travel_mobility_platform (1).zip` | Uninspected archive/reference file |
| `exemple de tableau/index.html.twig` | Example Twig file outside the application template directory |

`bin/console` is the Symfony CLI entrypoint; `bin/phpunit` is the PHPUnit executable.

### `assets/`

- `assets/app.js` — AssetMapper/Encore application entrypoint; imports `./bootstrap.js` and `./styles/app.css` twice.
- `assets/controllers.json` — Symfony UX controller/entrypoint configuration.
- `assets/stimulus_bootstrap.js` — Stimulus initialization scaffold.
- `assets/controllers/csrf_protection_controller.js` — Stimulus CSRF protection controller.
- `assets/controllers/hello_controller.js` — Example Stimulus controller.
- `assets/styles/app.css` — Bootstrap CSS import.
- `assets/vendor/` — ignored AssetMapper-installed vendor assets; present locally.

### `config/`

- `config/bundles.php` — Bundle/environment registration.
- `config/preload.php` — PHP preload configuration.
- `config/reference.php` — Large Symfony-generated service/config shape reference; investigate tooling use.
- `config/routes.yaml` — Imports attribute-routed controllers.
- `config/routes/framework.yaml` — Framework routes.
- `config/routes/security.yaml` — Security routes.
- `config/routes/web_profiler.yaml` — Development profiler routes.
- `config/services.yaml` — Parameters, autowiring/autoconfiguration, application services.
- `config/packages/asset_mapper.yaml` — AssetMapper paths/missing-import mode.
- `config/packages/cache.yaml` — Cache configuration.
- `config/packages/csrf.yaml` — CSRF configuration.
- `config/packages/debug.yaml` — Debug configuration.
- `config/packages/doctrine.yaml` — DBAL/ORM mapping and environment-specific cache setup.
- `config/packages/doctrine_migrations.yaml` — Migration configuration.
- `config/packages/framework.yaml` — Framework/secret/session config.
- `config/packages/mailer.yaml` — Mailer configuration.
- `config/packages/messenger.yaml` — Messenger configuration.
- `config/packages/monolog.yaml` — Logging configuration.
- `config/packages/notifier.yaml` — Notifier configuration.
- `config/packages/property_info.yaml` — Property info configuration.
- `config/packages/routing.yaml` — Routing configuration.
- `config/packages/security.yaml` — Authentication, firewall, and access control.
- `config/packages/test/mailer.yaml` — Test mailer configuration.
- `config/packages/translation.yaml` — Translation configuration.
- `config/packages/twig.yaml` — Twig configuration.
- `config/packages/ux_turbo.yaml` — UX Turbo configuration.
- `config/packages/validator.yaml` — Validator configuration.
- `config/packages/web_profiler.yaml` — Profiler configuration.
- `config/packages/webpack_encore.yaml` — Encore build/manifest configuration.

### `src/`

- `src/Kernel.php` — Symfony kernel.
- `src/Config/LocaleConfig.php` — Supported-locale configuration.
- `src/Controller/Admin/Blog/BlogArticleController.php` — Admin blog article CRUD/media.
- `src/Controller/Admin/Blog/BlogCategoryController.php` — Admin blog category CRUD.
- `src/Controller/Admin/CategoryController.php` — Admin excursion/category CRUD.
- `src/Controller/Admin/ContactController.php` — Admin contact mailbox, message operations, and mail.
- `src/Controller/Admin/CurrencyRateController.php` — Admin currency-rate CRUD.
- `src/Controller/Admin/DashboardController.php` — Admin dashboard.
- `src/Controller/Admin/DestinationController.php` — Admin destination CRUD.
- `src/Controller/Admin/ExcursionController.php` — Admin excursion CRUD/workflow.
- `src/Controller/Admin/ReservationController.php` — Admin transfer reservation operations.
- `src/Controller/Admin/ReservationExcursionController.php` — Admin excursion reservation operations.
- `src/Controller/Admin/SaisonController.php` — Admin seasonal-pricing CRUD.
- `src/Controller/Admin/Transfere/TransfereController.php` — Admin transfer CRUD.
- `src/Controller/Admin/UserController.php` — Admin user management.
- `src/Controller/Frontend/Blog/BlogController.php` — Public blog pages.
- `src/Controller/Frontend/Contact/ContactController.php` — Public contact submission.
- `src/Controller/Frontend/CurrencyController.php` — Public currency endpoint/page behavior.
- `src/Controller/Frontend/Excursion/DetailExcursionController.php` — Public excursion details.
- `src/Controller/Frontend/Excursion/ListeExcursionController.php` — Public excursion listing/filtering.
- `src/Controller/Frontend/Home/HomeController.php` — Home page and booking data.
- `src/Controller/Frontend/Profile/ProfileController.php` — Authenticated profile page.
- `src/Controller/Frontend/Transfer/TransferController.php` — Public transfer reservation flow.
- `src/Controller/Frontend/Transfere/TransfereController.php` — Public transfer-related controller.
- `src/Controller/Security/Authentication/SecurityController.php` — Login/logout.
- `src/Controller/Security/Registration/RegistrationController.php` — Registration.
- `src/Entity/BlogArticle.php`, `BlogArticleImage.php`, `BlogArticleTranslation.php`, `BlogArticleVideo.php` — Blog article aggregate/media/translations.
- `src/Entity/BlogCategory.php`, `BlogCategoryTranslation.php` — Blog category and translations.
- `src/Entity/Category.php`, `CategoryTranslation.php` — Excursion category and translations.
- `src/Entity/Contact.php`, `ContactMessage.php` — Public contact and mailbox messages.
- `src/Entity/CurrencyRate.php` — Currency rate.
- `src/Entity/Destination.php`, `DestinationTranslation.php` — Destination and translations.
- `src/Entity/Excursion.php`, `ExcursionTranslation.php` — Excursion aggregate and translations.
- `src/Entity/ExcursionBadge.php`, `ExcursionBadgeTranslation.php` — Excursion badges.
- `src/Entity/ExcursionFaq.php`, `ExcursionFaqTranslation.php` — Excursion FAQs.
- `src/Entity/ExcursionFeature.php`, `ExcursionFeatureTranslation.php` — Excursion features.
- `src/Entity/ExcursionImage.php`, `ExcursionImageTranslation.php` — Excursion images and translations.
- `src/Entity/ExcursionInclusion.php`, `ExcursionInclusionTranslation.php` — Excursion inclusions.
- `src/Entity/ExcursionItinerary.php`, `ExcursionItineraryTranslation.php` — Excursion itineraries.
- `src/Entity/PickupRegion.php`, `PickupRegionTranslation.php` — Pickup regions and translations.
- `src/Entity/Reservation.php`, `ReservationTransfere.php`, `ReservationExcursion.php` — Reservation domain entities.
- `src/Entity/Review.php` — Review entity.
- `src/Entity/Saison.php` — Seasonal pricing entity.
- `src/Entity/Transfere.php`, `TransfereTranslation.php` — Transfer and translations.
- `src/Entity/User.php` — User/authentication entity.
- `src/Enum/ContactMessageStatus.php`, `ReservationStatus.php`, `ReservationTransfereType.php` — Domain status/type enums.
- `src/Form/AdminContactMessageType.php` — Admin message form.
- `src/Form/BlogArticleImageType.php`, `BlogArticleVideoType.php`, `BlogArticleTranslationType.php`, `BlogArticleType.php` — Blog article and nested media forms.
- `src/Form/BlogCategoryTranslationType.php`, `BlogCategoryType.php` — Blog category forms.
- `src/Form/CategoryTranslationType.php`, `CategoryType.php` — Category forms.
- `src/Form/ContactFormType.php` — Public contact form.
- `src/Form/CurrencyRateType.php` — Currency form.
- `src/Form/DestinationTranslationType.php`, `DestinationType.php` — Destination forms.
- `src/Form/EventListener/AlignSubmittedTranslationsByLocaleListener.php` — Translation form event listener.
- `src/Form/ExcursionBadgeTranslationType.php`, `ExcursionBadgeType.php` — Badge forms.
- `src/Form/ExcursionFaqTranslationType.php`, `ExcursionFaqType.php` — FAQ forms.
- `src/Form/ExcursionFeatureTranslationType.php`, `ExcursionFeatureType.php` — Feature forms.
- `src/Form/ExcursionImageTranslationType.php`, `ExcursionImageType.php` — Image forms.
- `src/Form/ExcursionInclusionTranslationType.php`, `ExcursionInclusionType.php` — Inclusion forms.
- `src/Form/ExcursionItineraryTranslationType.php`, `ExcursionItineraryType.php` — Itinerary forms.
- `src/Form/ExcursionTranslationType.php`, `ExcursionType.php` — Excursion forms.
- `src/Form/RegistrationFormType.php` — Registration form.
- `src/Form/ReservationExcursionAdminType.php`, `ReservationExcursionType.php` — Excursion reservation forms.
- `src/Form/ReservationTransfereFormType.php` — Transfer reservation form.
- `src/Form/SaisonType.php` — Seasonal pricing form.
- `src/Form/TransfereTranslationType.php`, `TransfereType.php` — Transfer forms.
- `src/Form/UserType.php` — User form.
- `src/Repository/BlogArticleImageRepository.php`, `BlogArticleRepository.php`, `BlogArticleTranslationRepository.php`, `BlogArticleVideoRepository.php` — Blog article/media persistence queries.
- `src/Repository/BlogCategoryRepository.php`, `BlogCategoryTranslationRepository.php` — Blog category queries.
- `src/Repository/CategoryRepository.php`, `CategoryTranslationRepository.php` — Category queries.
- `src/Repository/ContactMessageRepository.php`, `ContactRepository.php` — Contact/mailbox queries.
- `src/Repository/CurrencyRateRepository.php` — Currency queries.
- `src/Repository/DestinationRepository.php`, `DestinationTranslationRepository.php` — Destination queries.
- `src/Repository/ExcursionBadgeRepository.php`, `ExcursionBadgeTranslationRepository.php` — Badge queries.
- `src/Repository/ExcursionFaqRepository.php`, `ExcursionFaqTranslationRepository.php` — FAQ queries.
- `src/Repository/ExcursionFeatureRepository.php`, `ExcursionFeatureTranslationRepository.php` — Feature queries.
- `src/Repository/ExcursionImageRepository.php`, `ExcursionImageTranslationRepository.php` — Image queries.
- `src/Repository/ExcursionInclusionRepository.php`, `ExcursionInclusionTranslationRepository.php` — Inclusion queries.
- `src/Repository/ExcursionItineraryRepository.php`, `ExcursionItineraryTranslationRepository.php` — Itinerary queries.
- `src/Repository/ExcursionRepository.php`, `ExcursionTranslationRepository.php` — Excursion queries/read models.
- `src/Repository/PickupRegionRepository.php`, `PickupRegionTranslationRepository.php` — Pickup-region queries.
- `src/Repository/ReservationExcursionRepository.php`, `ReservationRepository.php`, `ReservationTransfereRepository.php` — Reservation queries.
- `src/Repository/ReviewRepository.php` — Review queries.
- `src/Repository/SaisonRepository.php` — Seasonal pricing queries.
- `src/Repository/TransfereRepository.php`, `TransfereTranslationRepository.php` — Transfer queries.
- `src/Repository/UserRepository.php` — User queries.
- `src/Security/AuthenticationFailureHandler.php`, `AuthenticationSuccessHandler.php` — Login result handlers.
- `src/Service/CurrencyService.php` — Currency-rate logic.
- `src/Service/ReservationMailerService.php` — Reservation status notification mail.
- `src/Service/TransferePriceCalculator.php` — Transfer price calculations.
- `src/Twig/CurrencyExtension.php` — Currency Twig extension.

### `templates/`

- `templates/base.html.twig`, `baseFront.html.twig` — Backend/basic and frontend base layouts.
- `templates/Backend/base.html.twig` — Admin layout.
- `templates/Backend/Components/_header.html.twig`, `_sidebar.html.twig` — Admin shared components.
- `templates/Backend/Blog/Article/form.html.twig`, `index.html.twig` — Admin article editor/list.
- `templates/Backend/Blog/Category/form.html.twig`, `index.html.twig` — Admin blog category editor/list.
- `templates/Backend/Category/form.html.twig`, `index.html.twig` — Category editor/list.
- `templates/Backend/Contact/index.html.twig`, `new_message.html.twig`, `show.html.twig` — Mailbox list/compose/detail.
- `templates/Backend/CurrencyRate/form.html.twig`, `index.html.twig` — Currency editor/list.
- `templates/Backend/Dashboard/index.html.twig` — Admin dashboard.
- `templates/Backend/Destination/form.html.twig`, `index.html.twig` — Destination editor/list.
- `templates/Backend/Excursion/form.html.twig`, `index.html.twig`, `show.html.twig` — Excursion editor/list/detail.
- `templates/Backend/Reservation/edit.html.twig`, `index.html.twig`, `show.html.twig`, `voucher.html.twig` — Transfer reservation views/voucher.
- `templates/Backend/ReservationExcursion/edit.html.twig`, `index.html.twig`, `show.html.twig` — Excursion reservation views.
- `templates/Backend/Saison/form.html.twig`, `index.html.twig` — Seasonal pricing editor/list.
- `templates/Backend/Transfere/form.html.twig`, `index.html.twig`, `show.html.twig` — Transfer admin views.
- `templates/Backend/User/_table_content.html.twig`, `edit.html.twig`, `index.html.twig` — User list/editor partials.
- `templates/Frontend/Blog/index.html.twig`, `show.html.twig` — Public blog list/article.
- `templates/Frontend/Components/_auth_modal.html.twig`, `_footer.html.twig`, `_navbar.html.twig` — Shared frontend components.
- `templates/Frontend/Contact/email.html.twig`, `index.html.twig` — Contact email and page.
- `templates/Frontend/Excursion/detail.html.twig` — Public excursion detail.
- `templates/Frontend/Home/index.html.twig` — Public home page.
- `templates/Frontend/Liste_excursion/index.html.twig` — Public excursion listing.
- `templates/Frontend/Transfer/show.html.twig` — Public transfer booking page.
- `templates/emails/reservation_cancelled.html.twig`, `reservation_confirmed.html.twig`, `reservation_excursion_cancelled.html.twig`, `reservation_excursion_confirmed.html.twig` — Reservation notification emails.
- `templates/profile/index.html.twig` — Authenticated profile.
- `templates/registration/register.html.twig` — Registration.
- `templates/security/login.html.twig` — Login.

### `public/`

- `public/index.php` — HTTP front controller.
- `public/.user.ini` — PHP upload/post-size settings.
- `public/favicon.ico`, `favicon.png`, `apple-touch-icon.png` — Browser/app icons.
- `public/assetsBack/css/` — Admin global, section/table, currency-rate, excursion, reservation, season, transfer, and user stylesheets.
- `public/assetsBack/js/admin.js`, `contact-mailbox.js`, `excursion/form.js` — Admin shared, mailbox, and excursion-form behavior.
- `public/assetsFront/css/auth.css`, `excursions.css`, `home.css` — Frontend styles.
- `public/assetsFront/js/auth-modal.js`, `excursion-gallery.js`, `home.js`, `navbar.js`, `tailwind.config.js` — Frontend page/component behavior and Tailwind configuration.
- `public/assetsFront/images/destination-placeholder.svg`, `logo-yassmine-guide.png`, and the PSD logo source — Frontend images/artwork.
- `public/uploads/blog/`, `public/uploads/excursions/`, `public/uploads/transferts/` — Application media uploads; individual files were inventoried by path but not opened.

### `migrations/`

Doctrine schema history: `Version20260913175740.php`, `Version20260914090000.php`, `Version20260916100000.php`, `Version20260916100001.php`, `Version20260916100002.php`, `Version20260917000000.php`, `Version20260917000001.php`, `Version20260917000002.php`, `Version20260917221431.php`, `Version20260922195025.php`, `Version20260922213414.php`, `Version20260925201421.php`, `Version20260925202015.php`, `Version20260926173308.php`, `Version20260926174012.php`, `Version20260926174219.php`, `Version20261009161636.php`, `Version20261009170000.php`, `Version20261009201852.php`, `Version20261015102542.php`, `Version20261015111200.php`, `Version20261017162200.php`, `Version20261018120000.php`, `Version20261020000000.php`, `Version20261021000000.php`, plus `migrations/.gitignore`. These are deployment/schema history and should not be removed based on age or naming.

### `tests/`

`tests/bootstrap.php` initializes the test environment. The test files are:

`AdminContactComposeTest.php`, `AdminExcursionCategoryDestinationTest.php`, `AdminExcursionDeletionTest.php`, `AdminExcursionFormSimplificationTest.php`, `AdminExcursionTranslationDuplicationTest.php`, `AdminReservationExcursionManagementTest.php`, `AdminReservationManagementTest.php`, `AdminReservationVoucherTest.php`, `AdminTransfereFormTest.php`, `AdminTransferePaginationTest.php`, `AdminUserManagementTest.php`, `BlogArticleFormErrorsTest.php`, `BlogCategoryCreationTest.php`, `BlogCategoryTranslationFormTest.php`, `CurrencyServiceTest.php`, `Entity/BlogArticleTest.php`, `ExcursionImageUploadTest.php`, `FrontendExcursionBookingTest.php`, `FrontendExcursionCategoryFilterTest.php`, `FrontendExcursionTotalPriceTest.php`, `FrontendTransferBookingTest.php`, `MailboxFlowTest.php`, `PublicContactSubmissionTest.php`, `ReservationMailerServiceTest.php`, `TransferePriceCalculatorTest.php`, `TransferHomeSearchTest.php`, `TransferReservationValidationTest.php`, and `TransferSeasonalPricingTest.php`.

### `translations/`

- `messages.de.yaml`, `messages.en.yaml`, `messages.es.yaml`, `messages.fr.yaml`, `messages.it.yaml` — User-facing message catalogs.
- `validators.de.yaml`, `validators.en.yaml`, `validators.es.yaml`, `validators.fr.yaml`, `validators.it.yaml` — Validation catalogs.

## L. Final recommendations

- **Keep:** domain entities, repositories, migrations, referenced assets, user-uploaded content, and the PHPUnit test suite. Preserve the existing generated report data until its debugging value and any captured content are reviewed.
- **Investigate before removal:** the root reproduction PHP script, Twig example, generated Symfony reference file, ZIP archive, PSD artwork, and both SQL exports. No item in this list is confirmed safe to delete.
- **Refactor:** the three largest admin workflow controllers, large page templates, substantial inline scripts, and global admin stylesheet. Address the excursion home-page query pattern and unbounded admin lists as data grows.
- **Resolve first:** tracked environment/export sensitivity, safe public HTML/inline JSON handling, the Compose configuration concern, the inconsistent asset entrypoint, and the missing Playwright test directory.
- **Audit limitations:** `vendor/`, `node_modules/`, `var/`, browser report internals, uploaded binary contents, SQL dump contents, ZIP contents, environment-file contents, and the reproduction script contents were not inspected. The inventory and reference scans covered first-party paths, but not every source/test/migration file was read line by line. No tests/builds were run because this was explicitly read-only and such commands can generate or update files.
- **Final safety check:** no pre-existing project file was edited, deleted, renamed, or moved during this audit. The only file created for the task is `PROJECT_AUDIT_REPORT.md`.
