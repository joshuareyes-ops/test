# child-test WordPress Child Theme

A lightweight, modern, and highly modular block-based child theme built on top of the parent **Twenty Twenty-Five** theme. 

Designed for performance and maintainability, this theme breaks away from the traditionally bloated `functions.php` file by splitting all theme logics into specialized, single-responsibility files inside the `inc/` directory.

---

## 📂 File Structure & Architecture

```text
child-test/
├── functions.php          # Bootstraps the modular includes
├── style.css              # Theme metadata and custom style overrides
├── theme.json             # Theme configurations, block styles, and palettes
├── README.md              # Theme documentation (this file)
├── inc/                   # Theme Core Logic Modules (PHP includes)
│   ├── customizer.php     # Customizer settings and controls setup
│   ├── enqueue.php        # Style and script registration and loading
│   ├── helpers.php        # Reusable utility and helper functions
│   └── hooks.php          # Core theme setups, action/filter hook callbacks
└── parts/                 # Block Template Parts (Gutenberg markup)
    └── header.html        # Elegant, colored responsive header template
```

---

## ⚙️ Module Breakdown

### 1. `functions.php` (Bootstrap)
Acts as the entry point of the theme. Instead of carrying direct functionality, it uses secure `require_once` statements to bootstrap the modular files from the `/inc` directory, maintaining a clean and easy-to-read root file.

### 2. `inc/enqueue.php` (Assets Loader)
Handles all scripts and style registrations and enqueues.
* Enqueues the parent theme's (`twentytwentyfive`) master stylesheet.
* Enqueues the child theme's `style.css` (inheriting dynamic parent styles and loading custom child modifications) with proper cache-busting versioning.

### 3. `inc/hooks.php` (Hooks & Setup)
Registers core theme features, actions, and filters:
* **Theme Support Setup (`after_setup_theme`)**:
  * `custom-logo`: Configures site logo capabilities (`250x250`px with dynamic flex sizing).
  * `post-thumbnails`: Enables featured images on posts and pages.
  * `html5`: Enables semantic, accessible markup for forms, lists, galleries, and comments.
  * `title-tag`: Lets WordPress handle `<title>` tag generations in the `<head>` dynamically.
  * `automatic-feed-links`: Generates post/comment RSS feed tags in the header.
  * `align-wide`: Declares full Gutenberg support for `alignwide` and `alignfull` layout options.
  * `responsive-embeds`: Auto-scales embedded external widgets and videos.
* **Body Class Hook (`body_class`)**: Injects a custom `child-test-theme` class to body tags for targeted CSS customization.

### 4. `inc/customizer.php` (Customizer Setup)
Provides a clean, modular namespace specifically for classic Customizer panels, sections, and controls.

### 5. `inc/helpers.php` (Helper Functions)
A dedicated namespace for clean utility functions (e.g., `child_test_get_current_year()`) to keep template logic simple and DRY.

### 6. `parts/header.html` (Template Part Overrides)
Overrides the default parent theme header part with a premium block-based design featuring:
* Premium off-white/cream background color (`accent-5`).
* Distinct purple bottom accent border line (`accent-3`).
* Dynamic flex layouts ensuring the logo, title, and customizable main navigation bar align properly and remain 100% responsive across devices.

---

## 🛠️ Usage and Customization

1. **Custom Styles**: Add custom styles directly in the root `style.css` file.
2. **Adding Custom Hooks**: Add any filter or action hooks directly inside `inc/hooks.php`.
3. **Block Theme Editing**: You can customize the layout, navigation, and colors of the premium Header directly inside the **WordPress Site Editor (FSE)**. The template part maps seamlessly to block selections.
