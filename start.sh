#!/bin/bash
WORKSPACE="/home/runner/workspace"
WORDPRESS_DIR="$WORKSPACE/wordpress"
PLUGIN_DIR="$WORDPRESS_DIR/wp-content/plugins/tappy-cashback-pro"

if [ ! -d "$WORDPRESS_DIR" ]; then
    echo "Directory $WORDPRESS_DIR does not exist."
    exit 1
fi

# Sync plugin source files from root into the WordPress plugins directory
mkdir -p "$PLUGIN_DIR/includes"
cp "$WORKSPACE/tappy-cashback-pro.php" "$PLUGIN_DIR/" 2>/dev/null || true
cp "$WORKSPACE/uninstall.php" "$PLUGIN_DIR/" 2>/dev/null || true
cp "$WORKSPACE/includes/"*.php "$PLUGIN_DIR/includes/" 2>/dev/null || true
echo "Plugin files synced."

# Update WordPress URLs to match the current Replit domain
if [ -n "$REPLIT_DEV_DOMAIN" ]; then
    SITE_URL="https://$REPLIT_DEV_DOMAIN"
    php /tmp/wp-cli.phar option update siteurl "$SITE_URL" --path="$WORDPRESS_DIR" --allow-root 2>/dev/null || true
    php /tmp/wp-cli.phar option update home "$SITE_URL" --path="$WORDPRESS_DIR" --allow-root 2>/dev/null || true
    echo "WordPress URL set to: $SITE_URL"
fi

cd "$WORDPRESS_DIR"
php -S 0.0.0.0:5000 -t "$WORDPRESS_DIR"
