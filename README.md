# WP Turbo

Universal plugin to make WordPress better, faster, safer. Feel free to use.

---

## Advantages

- separate and modifiable files for custom style and script code
- enable CSV mime type for uploading on dashboard
- add phone number, Facebook and Instagram URL
- add month-based image as background on login page
- change default e-mail sender name and address to `blogname` name and `admin_email`
- `phpinfo()` result in dashboard
- logging plugin updates
- add user registration date column for Users dashboard page
- add user last login function and column for Users dashboard page
- enable SKU-based search for WooCommerce
- exclude featured products from products loop
- simple `dump($variable, $isExit)` function for debug
- title, description and keywords meta for WooCommerce product categories

---

## Usage

Run this code on server to prevent follow style and script changes:

```
git update-index --assume-unchanged local/style.css
git update-index --assume-unchanged local/script.js
```

### Multisite

This plugin is multisite compatible, just need to add this line to `wp-config.php` to enable WordPress Multisite (WPMU).

```php
define('WP_ALLOW_MULTISITE', true);
```

### Debug

Change `define( 'WP_DEBUG', false );` line with below solution for better debugging to be able to set `WP_DEBUG` constants to `true` separately from any user. Change IP list in array with yours (for example with home address' IP and workplace's IP).

```php
if (in_array($_SERVER['REMOTE_ADDR'], ['128.0.0.1', '1.2.3.4']))
    define( 'WP_DEBUG', true );
else
    define( 'WP_DEBUG', false );
```

### Speed optimization

#### WP Cron

WordPress "cron job" is not a real cron-job, it runs on every page load. Official info from documentation: https://developer.wordpress.org/plugins/cron/

> WP-Cron works by checking, on every page load, a list of scheduled tasks to see what needs to be run. Any tasks due to run will be called during that page load.

So to make faster the loading, disable this function by adding this line into `wp-config.php`:

```php
define('DISABLE_WP_CRON', true);
```

And to make cron-jobs manually, add this line into server's cron by changing `domain.tld` part to yours:

```shell
wget -q -O - "https://[domain.tld]/wp-cron.php?doing_wp_cron" >/dev/null 2>&1
```

### Security

Add these lines to `wp-config.php` file

```php
define('WP_POST_REVISIONS', 1); // set max post revision to 1
// SECURITY SETTINGS
define('DISALLOW_FILE_EDIT', true); // disable themes' and plugins' file editor
define('DISABLE_WP_CRON', true); // disable WP cron, use wp-cron-multisite.php instead
define('WP_AUTO_UPDATE_CORE', true); // enable all core updates, including minor and major
define('WP_CONTENT_DIRECTORY', 'content');
define('WP_CONTENT_DIR', ABSPATH . WP_CONTENT_DIRECTORY); // rename wp-content folder and redefine wp-content path
define('WP_CONTENT_URL', 'http' . (isset($_SERVER['HTTPS']) ? 's://' : '://') . $_SERVER['HTTP_HOST'] .'/' . WP_CONTENT_DIRECTORY);
```

Add these lines to `.htaccess` file

```
# protect wp-login.php
<Files wp-login.php>
	AuthType Basic
	AuthName "admin + admin"
	AuthUserFile [PATH]/.htpasswd
	Require valid-user
</Files>
<Files admin-ajax.php>
    Order allow,deny
    Allow from all
    Satisfy any
</Files>
order deny,allow
deny from all
<files ~ ".(xml|css|jpe?g|png|gif|js)$">
    allow from all
</files>
```

Add these lines to `.htpasswd` file

```
# admin + 1234
admin:$apr1$upnl829c$E9mKGBbblTEDNeXH9SiBb/';
```
