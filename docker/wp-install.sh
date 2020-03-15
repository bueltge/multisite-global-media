#!/bin/sh

rm -f "$PWD/wp-config.php"
source "$PWD/wp-content/plugins/multisite-global-media/.env"

wp config create \
  --dbname="$DB_NAME" \
  --dbuser="$DB_USER" \
  --dbpass="$DB_PASSWORD" \
  --dbhost=mariadb \
  --force \
  --extra-php <<PHP
define('WP_DEBUG', true);
define('SCRIPT_DEBUG', true);
PHP
wp core install \
  --url=$PROJECT_BASE_URL \
  --title="$PROJECT_NAME" \
  --admin_user="$WP_ADMIN_USER" \
  --admin_password="$WP_ADMIN_PASSWORD" \
  --admin_email="$WP_ADMIN_EMAIL"\
  --skip-email
wp core multisite-convert
wp plugin activate woocommerce-blocks
wp plugin activate woocommerce-rest-api
wp plugin activate woocommerce
wp plugin activate multisite-global-media --network
wp plugin install wordpress-importer --activate
wp import wp-content/plugins/woocommerce/sample-data/sample_products.xml --authors=skip
