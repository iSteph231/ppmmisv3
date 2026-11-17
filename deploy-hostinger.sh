set -e

cp public/index.php index.php
cp public/.htaccess .htaccess

sed -i "s|__DIR__.'/../vendor/|__DIR__.'/vendor/|g" index.php
sed -i "s|__DIR__.'/../bootstrap/|__DIR__.'/bootstrap/|g" index.php
sed -i "s|__DIR__.'/../storage/|__DIR__.'/storage/|g" index.php

for dir in css js images build; do
    if [ -d "public/$dir" ]; then
        mkdir -p "$dir"
        cp -a "public/$dir/." "$dir/"
    fi
done

php artisan optimize:clear
