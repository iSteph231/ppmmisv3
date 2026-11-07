set -e

# Restore Laravel's public entry point
cp public/index.php index.php
cp public/.htaccess .htaccess

# Adjust Laravel paths for the current web root
sed -i "s|__DIR__.'/../vendor/|__DIR__.'/vendor/|g" index.php
sed -i "s|__DIR__.'/../bootstrap/|__DIR__.'/bootstrap/|g" index.php
sed -i "s|__DIR__.'/../storage/|__DIR__.'/storage/|g" index.php

# Publish updated frontend assets
for dir in css js images build; do
    if [ -d "public/$dir" ]; then
        mkdir -p "$dir"
        cp -a "public/$dir/." "$dir/"
    fi
done

# Clear Laravel caches
php artisan optimize:clear