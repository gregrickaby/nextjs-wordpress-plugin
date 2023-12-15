# Next.js WordPress Plugin

A plugin to help turn WordPress into a headless CMS. This plugin is a companion to the [Next.js WordPress Theme](https://github.com/gregrickaby/nextjs-wordpress-theme) and is intended to be used within the [Next.js WordPress project](https://github.com/gregrickaby/nextjs-wordpress).

---

## Download

There are a few ways to aquire this plugin.

### The Old Fashioned Way

Download the [latest release](https://github.com/gregrickaby/nextjs-wordpress-plugin/archive/refs/heads/main.zip) (.zip) and upload it like any other WordPress plugin.

### Composer

```bash
composer require gregrickaby/nextjs-wordpress-plugin:dev-main
```

### WP CLI

```bash
wp plugin install https://github.com/gregrickaby/nextjs-wordpress-plugin/archive/refs/heads/main.zip --activate
```

Once installed, you'll need to activate the plugin.

---

## Configuration

You'll need to add a few constants to your `wp-config.php` file.

```php
// The URL of your Next.js frontend. Include the trailing slash.
define( 'NEXTJS_FRONTEND_URL', 'https://nextjswp.com/' );

// Any random string. This must match the .env variable in the Next.js frontend.
define( 'NEXTJS_PREVIEW_SECRET', 'preview' );

// Any random string. This must match the .env variable in the Next.js frontend.
define( 'NEXTJS_REVALIDATION_SECRET', 'revalidate' );
```

---
