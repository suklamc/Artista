# Changelog

## 3.0.0 (2026-10-13)

First Artista release: JoomlaShine's Sun Framework 2.2.33 and JSN Artista 2 Pro 2.1.3, maintained for Joomla 4.4, 5 and 6. Tested on Joomla 4.4.14, 5.4.9 and 6.1.4 with PHP 8.3, with the backward-compatibility plugins switched off.

### Fixed
- Template style editor stuck on the loading screen. Its main data request asked JoomlaShine's servers for account and licence details, and the failure stopped the editor from rendering.
- Lifetime Pro licence recorded locally. All Pro tabs open and every style can be saved. Previously, saving reset Pro-only settings (responsive support off, branding link on).
- Language files failed to load on Joomla 4+ (strict INI parsing), so the editor showed raw keys.
- Front-end and editor errors on Joomla 4/5/6: legacy `J*` classes, the event system, menu item params, a removed database column, error and offline page assets, admin page handling.
- PHP 8 errors and deprecations: `count()` on null, null passed to string functions, dynamic properties, the bundled scssphp compiler.
- `css/custom/custom.css` loads whenever it contains CSS. It used to depend on a checksum only JoomlaShine's installer stored.
- Bootstrap's JavaScript updated from 3.3.6 to 3.4.1 (works with jQuery 3; fixes CVE-2018-14040, CVE-2018-14041, CVE-2018-14042 and CVE-2019-8331). Fixed a jQuery 3 error in the bundled Waypoints script.

### Removed
- Online sample data (the local default-structure install stays).
- JoomlaShine account login, licence validation, live chat, promotions and discounts, usage tracking, uninstall feedback and client-information reporting.
- JoomlaShine's update/download screens and file-integrity check. Updates come through Joomla's updater from this repository.
- Links to joomlashine.com, including front-end back-links. A site's own branding text still shows when enabled.
- Joomla 3 core layout overrides (framework overrides, and the template's login, registration, profile and Smart Search overrides). Joomla's own layouts are used instead.
- Unused libraries: JoomlaShine's HTTP and archive libraries, httpclient, php-zip.

### Added
- `pkg_artista` package with an install check for PHP 8.1+ and Joomla 4.4+.
- Joomla update feed (`updates/pkg_artista.xml`).
- Cleanup on install/update of files no longer shipped, so sites upgraded from JoomlaShine's release don't keep the removed code. Site files are not touched.
- Module chromes as Joomla 4 layouts; Chosen bundled (no longer in Joomla core).
