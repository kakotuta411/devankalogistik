# Laravel VPS Deployment

Deploy a Laravel application to a VPS with proper rollback support, correct file permissions, and cache management.

## When to use this skill

Use this skill when:
- Deploying a Laravel app to a new VPS or after a code change
- Fixing deployment errors on an existing production server
- Setting up CI/CD or manual deployment workflows

## Procedure

Follow these steps in order. Do not skip verification steps.

### 1. Pre-deployment checks
- Confirm the local git branch is up to date with `origin/master`
- Confirm the commit hash you want to deploy with `git log --oneline -5`
- Check the remote state: `git fetch origin && git log --oneline origin/master -3`

### 2. Push to GitHub
- Push with SSH: `git push origin master`
- Verify the commit is on GitHub: `git fetch origin && git log --oneline origin/master -3`

### 3. SSH to VPS
- Connect: `ssh ubuntu@43.173.50.217` (or your VPS IP)
- Verify the repository is at the correct commit: `cd /var/www/devanka-logistik && git log --oneline -3`

### 4. Install PHP DOM extension
- Missing `ext-dom` causes `composer install` to fail when installing dompdf
- Run: `sudo apt update && sudo apt install php8.3-xml`
- Verify: `php -m | grep dom` must show `dom`

### 5. Fix storage permissions
- The `storage/` and `bootstrap/cache/` directories must be writable by PHP
- Run: `sudo chown -R www-data:www-data /var/www/devanka-logistik/storage`
- Run: `sudo chown -R www-data:www-data /var/www/devanka-logistik/bootstrap/cache`
- Run: `sudo chmod -R 775 /var/www/devanka-logistik/storage`
- Run: `sudo chmod -R 775 /var/www/devanka-logistik/bootstrap/cache`

### 6. Install Composer dependencies
- Run: `sudo composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader`
- Watch for `ext-dom` errors — this means the PHP extension is missing

### 7. Clear application cache
- Use `-u www-data` to run as the web server user (avoids permission errors)
- Run: `sudo -u www-data php artisan config:clear`
- Run: `sudo -u www-data php artisan route:clear`
- Run: `sudo -u www-data php artisan view:clear`
- Run: `sudo -u www-data php artisan cache:clear`

### 8. Restart services
- Run: `sudo systemctl restart php8.3-fpm`
- Run: `sudo systemctl restart nginx`

### 9. Verify
- Open the site in a browser
- Check for: `SyntaxError: Unexpected token '<'` (means Blade template is corrupted)
- Check for: `Throwable` / `Line 0` errors (means a PHP error, often from missing extension or permission)

## User preferences
- The user prefers to understand the process, not just have it done. Explain the 'why' behind each step.
- The user prefers concise, direct answers in Indonesian.
- The user prefers verification-then-proceed approach — always verify state before making changes.

## Reference files
- Read `references/deployment-checklist.md` for a step-by-step checklist.
- Read `references/common-errors.md` for common Laravel deployment errors and fixes.
