# child-astra WordPress Child Theme

A premium, lightweight, and modular child theme designed for the **Astra** parent theme framework. 

This theme is optimized for structure, maintainability, and clean code separation by organizing custom functions, hooks, customizers, and style enqueues into single-responsibility PHP includes.

---

## 📂 File Structure & Architecture

```text
child-astra/
├── functions.php          # Bootstraps the modular includes
├── header.php             # Overridden premium header with top bar alert and custom hooks
├── style.css              # Child theme metadata and customization overrides
├── readme.txt             # Changelog and general theme metadata
├── README.md              # Theme architecture and developer documentation (this file)
└── inc/                   # Theme Core Logic Modules (PHP includes)
    ├── customizer.php     # Customizer settings and controls setup
    ├── enqueue.php        # Astra-standard parent and child stylesheet enqueuer
    ├── helpers.php        # Reusable custom helper and utility functions
    └── hooks.php          # Core theme setups, action/filter hook callbacks
```

---

## ⚙️ Module Breakdown

### 1. `functions.php` (Bootstrap)
Serves as the entry point of the child theme. It utilizes secure `require_once` statements to bootstrap the modular files from the `/inc` directory, keeping the root folder exceptionally clean and manageable.

### 2. `header.php` (Template Override)
Overrides Astra's default `header.php` file to inject:
* **Custom Dynamic Top Bar Alert**: High-impact promotional notification bar styled with an animated, linear gradient background that runs natively with your `child_astra_get_current_year()` helper utility.
* **Child Theme Action Hooks**:
  * `do_action( 'child_astra_before_header' )` — Fired before the parent theme's header templates render.
  * `do_action( 'child_astra_after_header' )` — Fired immediately after the parent theme's header templates complete rendering.

### 3. `inc/enqueue.php` (Official Astra Enqueuer)
Handles stylesheet loading using the recommended Astra developer enqueuing hooks:
* Enqueues the child theme's `style.css` using `wp_enqueue_style`.
* Sets `'astra-theme-css'` as a dependency to guarantee parent theme assets load first.
* Configures the priority of `add_action( 'wp_enqueue_scripts', ... )` to `15` to ensure custom child CSS styling overrides are loaded last.

### 4. `inc/hooks.php` (Hooks & Setup)
Registers core theme features, actions, and filters:
* **Theme Support Setup (`after_setup_theme`)**:
  * `custom-logo`: Configures site logo capabilities with dynamic sizing.
  * `post-thumbnails`: Enables featured images on posts and pages.
  * `html5`: Switches markup systems to output fully semantic HTML5.
  * `title-tag`: Lets WordPress manage `<title>` headers in `<head>` dynamically.
  * `automatic-feed-links`: Generates post/comment RSS feed tags in the header.
* **Body Class Hook (`body_class`)**: Injects a custom `child-astra-theme` class to body tags for targeted CSS customization.

### 5. `inc/customizer.php` (Customizer Setup)
Provides a clean, modular namespace specifically for classic Customizer panels, sections, and controls.

### 6. `inc/helpers.php` (Helper Functions)
Contains helper/utility functions (e.g., `child_astra_get_current_year()`) to keep templates DRY and clean.

---

## 🛠️ Usage and Customization

1. **Custom Styles**: Add custom styling rules directly inside `style.css`.
2. **Modular Extension**: Add any filter or action hooks inside `inc/hooks.php`.
3. **Helper Functions**: Define new utility helpers inside `inc/helpers.php` to keep themes modular.
4. **Child Action Hooks**: Hook your custom content, widgets, scripts, or banners into `child_astra_before_header` or `child_astra_after_header` inside `inc/hooks.php`.

