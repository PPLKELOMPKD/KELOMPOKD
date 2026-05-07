#!/bin/bash
# =============================================================
# SIKARA - Production Deployment Script
# Jalankan script ini di server setelah git pull
# =============================================================

set -e  # Hentikan script jika ada error

echo ""
echo "========================================"
echo "   SIKARA - Production Deployment"
echo "========================================"
echo ""

# 1. Install/update PHP dependencies
echo "📦 [1/7] Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
echo "✅ Done."
echo ""

# 2. Run database migrations
echo "🗃️  [2/7] Running database migrations..."
php artisan migrate --force
echo "✅ Done."
echo ""

# 3. Clear all caches
echo "🧹 [3/7] Clearing all caches..."
php artisan optimize:clear
echo "✅ Done."
echo ""

# 4. Re-cache config & routes for performance
echo "⚡ [4/7] Caching config and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Done."
echo ""

# 5. Set correct file permissions
echo "🔒 [5/7] Setting file permissions..."
chmod -R 755 storage bootstrap/cache
echo "✅ Done."
echo ""

# 6. Test email configuration
echo "📧 [6/7] Checking mail configuration..."
MAIL_PASS=$(grep -E "^MAIL_PASSWORD=" .env | cut -d'=' -f2 | tr -d '"' | tr -d "'")
if [ -z "$MAIL_PASS" ] || [ "$MAIL_PASS" = "app_password_16_karakter_disini" ]; then
    echo "⚠️  WARNING: MAIL_PASSWORD belum diisi di .env!"
    echo "   Forgot password tidak akan bekerja sampai MAIL_PASSWORD diisi."
    echo "   Buat App Password Gmail di: https://myaccount.google.com/apppasswords"
else
    echo "✅ MAIL_PASSWORD sudah dikonfigurasi."
fi
echo ""

# 7. Done!
echo "🎉 [7/7] Deployment selesai!"
echo ""
echo "========================================"
echo "   Checklist Setelah Deployment:"
echo "========================================"
echo ""
echo "   □ Pastikan .env sudah diisi:"
echo "     - MAIL_USERNAME=email@gmail.com"
echo "     - MAIL_PASSWORD=16-char-app-password"
echo "     - MAIL_FROM_ADDRESS=email@gmail.com"
echo ""
echo "   □ Test email dengan:"
echo "     php artisan mail:test emailkamu@gmail.com"
echo ""
echo "   □ Test forgot password di:"
echo "     https://sikara.naufalmohy.me/forgot-password"
echo ""
echo "========================================"
