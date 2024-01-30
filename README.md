# Next.js WordPress Plugin

A plugin to help configure WordPress for use as a headless CMS.

This plugin is a companion to the [Next.js WordPress Theme](https://github.com/gregrickaby/nextjs-wordpress-theme) and is intended to be used within the [Next.js WordPress project](https://github.com/gregrickaby/nextjs-wordpress).

---

## Download

There are a few ways to aquire this plugin:

### 1) Composer

This is the preferred method of installation.

```bash
composer require gregrickaby/nextjs-wordpress-plugin
```

### 2) WP CLI

```bash
wp plugin install https://github.com/gregrickaby/nextjs-wordpress-plugin/archive/refs/heads/main.zip --activate
```

### 3) Download

Download the [latest release](https://github.com/gregrickaby/nextjs-wordpress-plugin/archive/refs/heads/main.zip) (.zip) and upload it like any other WordPress plugin.

---

### Activate

Once installed, activate the plugin.

---

## Configuration

There's no configuration necessary. For additional project configuration, please see [the instructions](https://github.com/gregrickaby/nextjs-wordpress?tab=readme-ov-file#6-configure-wordpress) in the [Next.js WordPress](https://github.com/gregrickaby/nextjs-wordpress) repository.

---

## Revalidation

If your Custom Post Types and front-end routes differ from this plugin, you'll need to edit `src/classes/Revalidation.php` to match your project.

> ⚠️ Editing a plugin directly means you'll need to re-apply your changes after a plugin update occurs. If you do need to customize, consider forking this plugin and making your changes there.

```php
// src/classes/Revalidation.php

/**
 * Configure the $slug based on your post types and front-end routing.
 */
switch ( $post_type ) {
 case 'post': // post type
  $slug = "/blog/{$post_name}"; // front-end route
  break;
 case 'book': // book post type
  $slug = "/books/{$post_name}"; // front-end route
  break;
 default:
  $slug = $post_name;
  break;
}
```

---

### Preview Link

To change the preview links, edit `src/classes/Links.php`.

```php
// src/classes/Links.php

 public function set_headless_preview_link( string $link, WP_Post $post ): string {

  // Return the original link if the frontend URL or preview secret are not defined.
  if ( ! $this->frontend_url || ! $this->preview_secret ) {
   return $link;
  }

  // Update the preview link to point to the front-end.
  return add_query_arg(
   [ 'secret' => $this->preview_secret ],
   esc_url_raw( "{$this->frontend_url}/preview/{$post->ID}" ) // <-- edit this to match your front-end routing!
  );
 }
```

---

## Support

If you find something wrong, please [open an issue](https://github.com/gregrickaby/nextjs-wordpress-plugin/issues/new). I will do my best to respond in a timely manner.

---

## License

This plugin is licensed under the [MIT License](LICENSE).

---
