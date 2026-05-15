# Deployment Guide for alwaysdata.net

This guide explains how to properly deploy the Sports Bottles project on alwaysdata.net shared hosting.

## Prerequisites

- Git repository pushed to GitHub/GitLab
- alwaysdata.net account with SSH access
- PHP 8.1+ with Composer support
- MySQL database

## Step 1: Initial Setup on alwaysdata.net

### 1.1 Connect via SSH

```bash
ssh your_username@your_username.alwaysdata.net
```

### 1.2 Navigate to your web root

```bash
cd /home/your_username/www
# Or wherever your domain is configured
```

### 1.3 Clone the repository

```bash
git clone https://github.com/your_username/sport_bottles.git .
```

**OR** if you want it in a subdirectory:

```bash
git clone https://github.com/your_username/sport_bottles.git sport_bottles
cd sport_bottles
```

### 1.4 Set web root to `/public`

This is **CRITICAL**. In alwaysdata.net control panel:

1. Go to **Web → Domains**
2. Click on your domain
3. Under **General Settings**, set the **Web root** to `/public`
4. Save

If your repo is in a subdirectory: `/sport_bottles/public`

---

## Step 2: Environment Configuration

### 2.1 Create `.env.local` with production settings

```bash
cat > .env.local << 'EOF'
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=your_long_random_secret_key_here
DEFAULT_URI=https://sports-bottles.alwaysdata.net
DATABASE_URL="mysql://user:password@host:port/dbname?serverVersion=8.0&charset=utf8mb4"
MAILER_DSN=smtp://user:password@host:port
STRIPE_SECRET_KEY=your_key
STRIPE_PUBLISHABLE_KEY=your_key
STRIPE_WEBHOOK_SECRET=your_key
EOF
```

**Get your database credentials from alwaysdata.net:**
- Go to **Databases → MySQL/MariaDB**
- Find your database and connection details
- Replace `user`, `password`, `host`, `port`, `dbname`

### 2.2 Permissions

```bash
chmod 644 .env.local
chmod 755 var/ public/ public/products_images/
```

---

## Step 3: Install Dependencies

### 3.1 Install Composer dependencies

```bash
composer install --no-dev --optimize-autoloader
```

If you get "Composer not found", try:

```bash
php composer install --no-dev --optimize-autoloader
```

Or download Composer:

```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

### 3.2 Create cache/log directories

```bash
mkdir -p var/cache var/log
chmod 755 var/cache var/log
```

---

## Step 4: Asset Mapper Setup

Your project uses **Symfony Asset Mapper** (not npm), so assets are compiled automatically by Symfony at runtime. **No build step needed!**

Just verify that `/public/assets/` and `/public/build/` exist after the deployment. If they don't exist yet, Symfony will generate them on first request.

**Optional:** Pre-warm the asset cache (recommended):

```bash
php bin/console asset-map:compile --env=prod
```

---

## Step 5: Database Migrations

### 5.1 Run migrations

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

### 5.2 (Optional) Load fixtures

```bash
php bin/console doctrine:fixtures:load --no-interaction --env=prod
```

---

## Step 6: Verify Deployment

### 6.1 Check the website

Visit: `https://sports-bottles.alwaysdata.net/`

**If CSS is missing:**
- Check `public/build/` exists and has files
- Clear browser cache (Ctrl+Shift+Del)
- Run: `php bin/console cache:clear --env=prod`

**If links don't work:**
- Verify `public/.htaccess` exists
- Enable mod_rewrite: Contact alwaysdata.net support if needed
- Check the web root is set to `/public`

**If database error:**
- Verify DATABASE_URL in `.env.local`
- Test connection: `php bin/console doctrine:database:create --if-not-exists`

---

## Step 7: Cache Clearing

```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

---

## Troubleshooting

### CSS/Images not loading

**Problem:** Files return 404 errors.

**Solution:**
1. Check that `public/.htaccess` exists
2. Run: `php bin/console asset-map:compile --env=prod`
3. Clear cache: `php bin/console cache:clear --env=prod`

### "Class not found" errors

**Problem:** Symfony can't find classes.

**Solution:**
```bash
composer dump-autoload --optimize
php bin/console cache:clear --env=prod
```

### Database connection fails

**Problem:** "Access denied" or "Connection refused"

**Solution:**
1. Double-check credentials in `.env.local`
2. Verify database user exists in alwaysdata.net panel
3. Check if database host is accessible (usually `localhost` for alwaysdata)

### 500 errors

**Problem:** Internal Server Error

**Solution:**
1. Check logs: `cat var/log/prod.log`
2. Enable debug temporarily: `APP_DEBUG=1` in `.env.local`
3. Reload: `php bin/console cache:clear --env=prod`

### mod_rewrite disabled

**Problem:** AllLinks 404, only homepage works

**Solution:**
1. Contact alwaysdata.net support: "Please enable mod_rewrite for Apache"
2. Or use an alternative: Ask them to configure Apache to treat `public/index.php` as the entry point

---

## Updating Your Site

### Pull latest changes

```bash
git pull
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
php bin/console cache:clear --env=prod
```

### If assets changed

Since this project uses Symfony Asset Mapper, assets are handled automatically. Just pull and clear cache:

```bash
git pull
php bin/console cache:clear --env=prod
```

---

## Security Notes

⚠️ **Never commit `.env.local` or sensitive values to Git!**

- `.env.local` should be created directly on the server
- Use alwaysdata.net's environment variable system if available
- Keep `APP_DEBUG=0` in production
- Regularly update dependencies: `composer update`

---

## Additional Help

- [Symfony Deployment](https://symfony.com/doc/current/deployment.html)
- [alwaysdata.net Support](https://www.alwaysdata.net/en/support.html)
- [Asset Mapper](https://symfony.com/doc/current/frontend/asset_mapper.html)
