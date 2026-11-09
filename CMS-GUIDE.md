# LogicsGrid CMS

Laravel 12 + Filament 4 admin panel on MySQL. Every page, service, project, blog post, menu, contact detail and SEO field on the website is edited here. The website (repo root) reads it all from this app's API.

## Run locally (XAMPP)

1. Start **MySQL** in the XAMPP control panel.
2. Start the CMS: `C:\xampp\php\php.exe cms/artisan serve --host=127.0.0.1 --port=8000`
3. Start the website: `npm run dev` (http://localhost:5173)
4. Admin panel: http://127.0.0.1:8000/admin. The login is `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `cms/.env`.

## What is where in the admin

| Menu | Controls |
| --- | --- |
| Site settings | Company name, logo, favicon, contact details, WhatsApp, hours, stats, social links, header and footer menus, budget ranges, shared FAQs, default call to action, default SEO, Google Analytics, Search Console, robots.txt |
| Content → Pages | Every page's hero (headline, text, cube labels or an image), all sections via the page builder (add, remove, reorder, edit), and SEO |
| Content → Services | The six services: summary, audience, problems, capabilities, steps, stack, FAQs, related services, SEO |
| Content → Projects | Case studies: details, cover image, screenshots, features, scope, approved results and quotes, SEO |
| Content → Care plans / Technologies | Plan cards; technology groups |
| Blog → Posts / Categories | Articles with rich text, images, categories, scheduling, SEO |
| Leads → Enquiries | Every contact and free-audit form submission, with status and notes |
| SEO → Redirects | 301/302 redirects from old URLs |

Sitemap (`/sitemap.xml`) and `robots.txt` on the website are generated from the CMS.

## Commands

- Fresh database with the original site content: `C:\xampp\php\php.exe cms/artisan migrate:fresh --seed`
- Re-import only the original content (overwrites matching records): `C:\xampp\php\php.exe cms/artisan db:seed --class=SiteContentSeeder`
- Refresh the website's offline fallback after editing content: `npm run cms:snapshot` (used only if the CMS is unreachable)

## Deploying on Dokploy

One Dokploy project with three services. Both apps use **Build type: Dockerfile**.

1. **MySQL** database service. Note its internal host, database, user and password.
2. **CMS** application from the `logicsgrid-backend` repo. Domain e.g. `admin.logicsgrid.com`, container port **80**.
   - Environment: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://admin.logicsgrid.com`, `APP_KEY` (from `php artisan key:generate --show`), `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT=3306`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `FILESYSTEM_DISK=public`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`.
   - Volume: mount a volume at `/var/www/html/storage/app/public` (uploaded images).
   - On start it runs migrations; on the very first start (no users yet) it also creates the admin user and imports the site content.
3. **Website** application from the `logicsgrid-frontend` repo. Main domain, container port **3000**.
   - Build argument: `VITE_CMS_URL=https://admin.logicsgrid.com`. Deploy the CMS first; redeploy the website if this URL changes.

## Deploying elsewhere

- Deploy `cms/` as a normal Laravel app (PHP 8.2+, MySQL). Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `DB_*`, `FILESYSTEM_DISK=public`, then run `php artisan migrate --force`, `php artisan storage:link`, `php artisan filament:optimize`.
- Build the website with `VITE_CMS_URL` set to the CMS's public URL: `npm ci && npm run build`. It builds a standalone Node server; run it with `PORT=3000 npm start` (Node 20+) behind Nginx or a process manager such as PM2.

## Tests

- CMS: `C:\xampp\php\php.exe cms/artisan test` (API, publishing rules, sitemap, enquiries, every admin screen, create/edit/upload).
- Website: `npx tsc --noEmit`, `npx eslint src`, `npm run build`.
