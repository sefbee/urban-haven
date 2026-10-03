# Urban Haven

Public property website and staff admin for Urban Haven Properties Ltd. (Dhaka). Laravel 13, PHP 8.4, MySQL 8, Blade, Alpine and Tailwind 4.

## Local setup

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed   # demo accounts and listings are only seeded in local/testing
composer run dev
```

Demo staff logins (local only): `owner@urbanhaven.test`, `editor@urbanhaven.test`, `sales@urbanhaven.test`, password `password`.

Tests use the `urban_haven_testing` MySQL database:

```bash
php artisan test --compact
vendor/bin/pint --dirty
```

## Roles

| Role | Can |
| --- | --- |
| Owner admin | Everything, including publishing, settings, staff, audit log and lead export |
| Content editor | Draft listings, projects, pages, articles and FAQs, and submit them for review. Cannot publish or see leads |
| Sales user | Work the leads and visits assigned to them |

## Production deploy

Server requirements: PHP 8.4 (gd, intl, pdo_mysql, zip), MySQL 8, Nginx, Supervisor, Node 22 for builds, `mysqldump`, and `gpg` if backups are encrypted.

1. Copy `deploy/nginx.conf` and `deploy/php-fpm.conf`, then set the real `server_name` and TLS certificate.
2. Create `.env` from `.env.example` with `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`, `SESSION_SECURE_COOKIE=true`, real `MAIL_*` credentials, `WHATSAPP_NUMBER` and any analytics IDs.
3. Run `deploy/deploy.sh main`. This builds assets, backs up, migrates, seeds roles and reference taxonomy, caches config, routes and views, restarts workers and runs `uh:health`.
4. Create the first owner with `php artisan uh:create-owner`. It prompts for the password; demo accounts are never seeded in production.
5. Install workers with `deploy/supervisor.conf` and the scheduler, backups and health checks with `crontab -u www-data deploy/crontab`.
6. Point an uptime monitor at `https://…/up`. It returns 200 only when the database and cache respond.

## Backups and restore

`deploy/backup.sh` runs nightly from cron. It dumps the database and archives `storage/app/private` and `storage/app/public` into `BACKUP_PATH`. Archives are encrypted when `BACKUP_GPG_RECIPIENT` is set, and anything older than `BACKUP_RETENTION_DAYS` (default 30) is deleted. Copy that directory off the server.

To restore:

```bash
gpg --decrypt db-STAMP.sql.gz.gpg | gunzip | mysql urban_haven
gpg --decrypt files-STAMP.tar.gz.gpg | tar -xz -C storage/app
php artisan optimize:clear
```

Do a test restore into a scratch database before launch and then once a quarter.

## Launch checklist

- [ ] `php artisan uh:health` passes on the server: database, cache, storage, queue backlog and mail.
- [ ] Settings → Email delivery → "Send test email" arrives in the sales inbox.
- [ ] Company name, phone, WhatsApp, address, office hours and consent text are set in Settings.
- [ ] Every published listing has a reference, price (or "on request"), area and a cover image with alt text. The publish checklist enforces this.
- [ ] Each location area that should get a landing page is active and has an owner-written intro. Areas without one return 404 and stay out of the sitemap.
- [ ] `robots.txt` allows crawling and `/sitemap.xml` lists the published pages. Submit the sitemap in Google Search Console.
- [ ] Analytics IDs are set and load only after cookie consent.
- [ ] Old-site URLs are imported under Redirects (CSV) and spot-checked.
- [ ] Each staff account can sign in, and unused accounts have been deactivated.
- [ ] A backup has run and a test restore has succeeded.

## UAT script

Run on staging with the content team and the sales desk:

1. Search by purpose, type, location and budget. Change filters and share the URL; the same results load.
2. Open a listing, submit an enquiry, then submit the same enquiry again within a minute. Only one lead is created. A later repeat creates a new lead marked as a repeat of the first.
3. Book a site visit from a listing and from a project page. Confirm it in Admin → Visits, then mark it completed with an outcome note.
4. As the editor, edit a live page and a listing. The public site is unchanged until the owner publishes. The editor has no publish buttons, and direct publish requests return 403.
5. As a sales user, open another user's lead by URL. Expect 404 or 403.
6. Move a lead through all 7 stages. Closing as Lost requires a loss reason.
7. Save and compare listings on a phone, then open the shortlist page.
8. Export leads as the owner. Check the CSV opens safely in Excel and that the export appears in the audit log.
