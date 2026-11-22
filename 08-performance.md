# 08 - Performance & Smooth Navigation

Goal: pages feel instant, navigation between pages does not flash or reload, and data-heavy screens (score grids, class lists, dashboards) stay fast on a small free server.

> Filament and Livewire method names below follow Filament 3.x. Confirm each against the installed version's docs before using it (a few options were added in later 3.x releases).

## 1. Performance Budgets (targets to test against)

| Measure | Target |
|---------|--------|
| Server response for a list/table page | under 500 ms (warm) |
| Database queries per page | 20 or fewer, and **no N+1** |
| Rows per table page | 10-25 default, max 50 |
| Score entry grid | save in a single transaction, one query batch (upsert), not per row |
| Dashboard widgets | read precomputed data, never recompute grades on page load |
| JS/CSS | one built Vite bundle per panel, no unused libraries |

## 2. Smooth Navigation (the "feels like an app" part)

1. **Turn on Filament SPA mode** on all three panels. Page changes then swap content without a full reload, which suits top navigation well.
   ```php
   ->spa()   // add to Admin, Teacher, Student panel providers
   ```
   If your Filament version supports prefetching, enable it too (`->spa(hasPrefetching: true)`) so links load as the user hovers. Test any custom scripts/widgets afterwards, because SPA mode keeps the page alive between navigations.
2. **Keep the top navigation short** (7 items or fewer, already a rule). Fewer items means less to render per page.
3. **Disable what you do not use:**
   - `->globalSearch(false)` on the Student panel (students do not need it); on other panels, set `$isGloballySearchable = false` on resources that should not be searched.
   - Turn off or slow database-notification polling if notifications are not used.
4. **Loading feedback:** use Filament's built-in loading states and `->deferLoading()` on tables so the page shell appears first and the rows fill in.
5. **Persist the user's context:** `->persistFiltersInSession()` and `->persistSortInSession()` on tables so returning to a list keeps the same class/period filter.

## 3. Faster Data Loading (database)

1. **Eager load in every resource.** Never let a table column trigger a query per row.
   ```php
   public static function getEloquentQuery(): Builder
   {
       return parent::getEloquentQuery()
           ->with(['student.user:id,name', 'activity:id,title,max_score']);
   }
   ```
2. **Catch N+1 during development:** in `AppServiceProvider::boot()`:
   ```php
   Model::preventLazyLoading(! app()->isProduction());
   ```
   A lazy load then throws an exception in local/test runs, so tests fail instead of the site slowing down.
3. **Use the indexes in `docs/03-database-design.md`** and add an index for every column used in `where`, `orderBy`, or a filter on a large table (`scores`, `attendances`, `grades`).
4. **Select only needed columns** for big lists; use `withCount()`/`withAvg()` instead of loading related rows to count them.
5. **Searchable selects, not preloaded ones,** for big tables (students): use `->searchable()` with a limited search query; avoid `->preload()` on `students` or `scores`.
6. **Read precomputed data.** Grades and performance are stored in `grades` and `performance_summaries` (written by the recalculation job). Dashboards and the Student panel read these tables only.
7. **Paginate everything** and cap page size; never `->get()` an unbounded table.

## 4. Caching

| What | How |
|------|-----|
| Framework boot | `php artisan optimize` on every deploy (config, routes, events, views) |
| Filament | `php artisan filament:optimize` (and `icons:cache`) on deploy, if your version has it |
| Dashboard aggregates | `Cache::remember("dash.teacher.{$id}", 60, ...)`; clear the key from the recalculation job |
| Reference data (grade scales, performance levels, grading components) | Cache forever, clear on save (they rarely change) |
| Permissions | Leave Spatie/Shield permission caching on; do **not** build your own (see `docs/06-roles-and-permissions.md`) |
| Cache driver | `file` or `database` on free hosting (no free Redis); switch to Redis when you move to paid hosting |
| PHP | Enable **OPcache** in production `php.ini` |

## 5. Livewire / Filament Forms (score entry grid)

1. Do not sync every keystroke to the server: use `->live(onBlur: true)` or `->live(debounce: 500)` on score inputs.
2. Do not store large collections in public Livewire properties (they are serialized on every request); load rows through the table/query or computed properties.
3. Save all scores for an activity in **one transaction with one upsert**, then dispatch one recalculation job per student (batched), not one per score.
4. Widgets: leave them lazy-loaded (Filament default), and set `protected static ?string $pollingInterval = null;` (or `'60s'`) so stats widgets do not poll every few seconds.

## 6. Assets and Frontend

1. Always deploy with `npm run build` (never the dev server). Vite file names are hashed, so browsers can cache them for a long time.
2. Use one small custom theme per panel (`resources/css/filament/{panel}/theme.css`); keep Tailwind content paths accurate so unused CSS is purged.
3. Student photos/avatars: resize on upload (for example, 256 px), store as WebP/JPEG, and lazy-load images below the fold.
4. Avoid extra JS libraries; use Filament's built-in charts and components.

## 7. Hosting and Network

- **Choose a region near your users.** Latency is a bigger cause of "slow" than code. A host in the US is noticeably slower for users in Asia.
- **Free-tier cold starts are real:** hosts that sleep when idle (Render) and databases that auto-suspend (Neon, Supabase free) add seconds to the first request. The Oracle VM path has no sleeping.
- **Put Cloudflare (free plan) in front of the site** to cache static assets, compress responses, and serve HTTP/3. Only static assets are cached; never cache authenticated pages.
- Enable gzip or Brotli and long cache headers for `/build/*` in Nginx/Caddy.
- Keep the app and database in the same region and use persistent connections.

## 8. Measure, Do Not Guess

- Local: use a debug toolbar (for example Laravel Debugbar, **dev only**, requires approval as a new package) or the query log to count queries per page.
- Tests: add a Pest test per heavy page that visits it with seeded data and asserts it does not trigger lazy loading (Section 3.2) and stays within the query budget.
- Production: check response times in server logs; investigate any page over budget before adding features.

## 9. Checklist for Every New Resource or Page

- [ ] Eager loads relations in `getEloquentQuery()`
- [ ] Table uses `->deferLoading()`, pagination, and persisted filters
- [ ] Indexes exist for its filters and sorts
- [ ] No grade or performance calculation on page load (reads computed tables)
- [ ] Heavy actions (exports, PDFs, recalculation) are queued or batched
- [ ] Widgets are lazy and not polling aggressively
- [ ] Query count and lazy-loading test added
