# WP Turbo

Universal plugin to make WordPress better, faster, safer.

---

## Usage

### Security

Add these lines to `wp-config.php` file

```
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

AuthName "admin + 1234"
AuthType Basic
AuthUserFile .htpasswd
Require valid-user
```

Add these lines to `.htpasswd` file

```
# admin + 1234
admin:$apr1$XdwSCQFU$NJclZS7Og0VzuDOF8nPla0';
```
