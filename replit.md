# Tappy Cashback Pro

A WordPress plugin that provides a cashback system for WooCommerce stores.

## Project Structure

This is a **WordPress plugin**, not a standalone application. It runs inside a full WordPress + WooCommerce installation set up in the `wordpress/` directory.

```
/                               ← Plugin source files (original repo)
├── tappy-cashback-pro.php      ← Main plugin file
├── uninstall.php               ← Cleanup on uninstall
├── includes/                   ← Plugin class files
│   ├── class-admin-list-table.php
│   ├── class-admin-page.php
│   ├── class-antifraud.php
│   ├── class-checkout.php
│   ├── class-cron.php
│   ├── class-database.php
│   ├── class-generator.php
│   ├── class-install.php
│   ├── class-myaccount.php
│   └── class-settings.php
├── start.sh                    ← Startup script (runs PHP built-in server)
├── wordpress/                  ← Full WordPress installation
│   ├── wp-config.php           ← WordPress config (uses SQLite, dynamic URL)
│   ├── wp-content/
│   │   ├── database/           ← SQLite database files (wordpress.db)
│   │   ├── plugins/
│   │   │   ├── tappy-cashback-pro/   ← Plugin copy (from root)
│   │   │   ├── woocommerce/          ← WooCommerce plugin
│   │   │   └── sqlite-database-integration/  ← SQLite adapter
│   │   └── themes/
│   │       └── twentytwentyfive/     ← Active theme
```

## Setup

- **PHP**: 8.2 (via Nix)
- **Database**: SQLite (via WordPress SQLite Database Integration plugin)
- **Server**: PHP built-in development server on port 5000
- **WP-CLI**: `/tmp/wp-cli.phar` (downloaded at setup)

## WordPress Admin

- URL: `/wp-admin/`
- Username: `admin`
- Password: `admin123`

## Plugin Features

- Automatic cashback generation when orders are marked "Completed"
- Global configurable cashback percentage
- Optional expiry in days
- Automatic balance application at checkout
- Anti-fraud: cancels cashback on refunded/cancelled orders
- Complete history in My Account → Cashback
- Dedicated database table (`wp_tappy_cashback`)
- HPOS compatible
- Optimized for Redis/Object Cache
- Auto-expiry via cron with configurable intervals
- Admin interface using WP_List_Table
- Concurrency control for duplicate cashback prevention

## Configuration

WooCommerce → Settings → General:
- Enable Cashback
- Percentage (%)
- Expiry (days) — leave empty for no expiry
- Cron check interval (1h, 3h, 6h, 12h, or 24h)

## Running

The workflow "Start application" runs `bash start.sh` which:
1. Updates WordPress siteurl/home options to match the current `$REPLIT_DEV_DOMAIN`
2. Starts the PHP built-in server serving the `wordpress/` directory on port 5000

## Plugin Deployment Note

The plugin source files live at the repo root. A copy is placed at:
`wordpress/wp-content/plugins/tappy-cashback-pro/`

After editing plugin source files, run:
```bash
cp -r /home/runner/workspace/includes /home/runner/workspace/wordpress/wp-content/plugins/tappy-cashback-pro/
cp /home/runner/workspace/tappy-cashback-pro.php /home/runner/workspace/wordpress/wp-content/plugins/tappy-cashback-pro/
```

## Known Limitations

- WooCommerce Action Scheduler has a SQL compatibility issue with SQLite (JOIN UPDATE syntax). This causes 500 errors on background queue processing (`/wp-admin/admin-ajax.php?action=as_async_request_queue_runner`) but does not affect core cashback functionality.
- For production, a real MySQL/MariaDB database should be used.
