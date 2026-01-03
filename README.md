# 2 Star Car Wash — Admin & Public Site

Lightweight PHP/MySQL booking app used by 2 Star Car Wash. This repository contains the public site (`index2.html`), admin dashboard (`Admin/index.html`) and a small set of PHP API endpoints.

## Quick start (local)

1. Ensure you have PHP and MySQL installed.
2. Create the `carwash` database and the required tables. Example (adjust credentials):

```bash
mysql -u root -p
CREATE DATABASE IF NOT EXISTS carwash;
USE carwash;
SOURCE sql/20251224_create_customers.sql;
SOURCE sql/20251224_insert_services.sql; # or sql/20251224_seed_services.sql
SOURCE api/sql/bookings.sql;
```

3. Configure DB credentials and admin key in `api/config.php` (currently contains example credentials).
	- For production, create a secure config outside the webroot (recommended path: `/var/www/secure/config.php`) or set environment variables. A sample is provided at `secure/config.php.example` — copy it and edit values, then place at `/var/www/secure/config.php` and ensure it is not world-readable.

4. Start local PHP server from repository root:

```bash
php -S localhost:8000 -t .
```

5. Open the site in your browser:
- Public: http://localhost:8000/index2.html
- Admin:  http://localhost:8000/Admin/index.html

## Important files

- `index2.html` — public booking UI. Uses `js/` and `css/style.css`.
- `css/style.css` — main styles (includes responsive rules and the fixed bottom booking bar).
- `Admin/index.html` — admin dashboard (customers, services, bookings).
- `api/config.php` — central config for DB and `$ADMIN_KEY` (rotate before production).

### API endpoints

- `api/get_services.php` — GET list of services (id, name, price, duration).
- `api/manage_service.php` — POST add/update service (requires `admin_key`).
- `api/customer_details.php` — GET customers list with `total_bookings` and `last_visit` (used by Admin `loadCustomers()`).
- `api/booking.php` — POST booking submissions from the public site.

Admin endpoints (in `Admin/`):
- `Admin/update_booking_status.php` — POST {id, status} to update booking status.
- `Admin/update_customer.php` — POST create/update customer (requires `admin_key`).
- `Admin/customer_details.php` — returns a specific customer's profile and booking history (used by admin modal).

## Notes & behavior

- The bottom booking bar was added to `index2.html` and styled in `css/style.css`. It is fixed, animated (slide up), responsive, and wired to submit the booking form using `requestSubmit()` (fallback to clicking the hidden submit button).
- Customers are stored in `customers` table (see `sql/20251224_create_customers.sql`). Bookings are in `bookings` table. The app uses a soft-delete (`is_deleted` flag) for customers.
- Admin UI currently stores `admin_key` in a hidden input for convenience; move this out of the client for production.

## Next steps you might want

- Add paging and search to the Admin customers table for large datasets.
- Integrate SMS/WhatsApp/email (Twilio, WhatsApp Business API) for admin notifications.
- Move secrets into environment variables or a server-only config and remove them from client HTML.

## HTTPS with Let's Encrypt (quick guide)

1. Install certbot and the webserver plugin (example for nginx on Ubuntu):

```bash
sudo apt update
sudo apt install certbot python3-certbot-nginx
```

2. Ensure your site is served by nginx and reachable on port 80, then run:

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

3. Certbot will automatically obtain and install certificates and configure HTTPS. Renewals are automatic via systemd timers.

Sample `nginx` server block (replace root and server_name):

```nginx
server {
	listen 80;
	server_name yourdomain.com www.yourdomain.com;
	root /var/www/your-site-root;
	index index2.html index.html;
	location / { try_files $uri $uri/ =404; }
	location ~ \.php$ { fastcgi_pass unix:/run/php/php8.1-fpm.sock; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; include fastcgi_params; }
}
```

After certbot runs, nginx will be configured to use the Let's Encrypt certificates.


If you want, I can add instructions to run migrations automatically or wire up a small script to bootstrap the DB.
