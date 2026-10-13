# Artista

The **JSN Artista 2 Pro** Joomla template and the **Sun Framework** it runs on, kept working on current Joomla after JoomlaShine shut down.

JoomlaShine's last releases (Sun Framework 2.2.33, Artista 2 Pro 2.1.3) were built for Joomla 3 and depended on JoomlaShine's servers for licensing, updates and sample data. Artista 3 runs on Joomla 4.4, 5 and 6 without those servers.

## Requirements

| | Supported | Tested |
|---|---|---|
| Joomla | 4.4, 5.x, 6.x | 4.4.14, 5.4.9, 6.1.4 |
| PHP | 8.1 or later (Joomla 6 itself needs 8.3) | 8.3 |
| Database | MySQL / MariaDB | MariaDB 10.11 |

The Joomla 5 and 6 "Behaviour - Backward Compatibility" plugins are **not** needed. Tests ran with them switched off.

Joomla 4.4 stopped receiving security updates in October 2025. Use it only as a step on the way to Joomla 5 or 6.

## Install

1. Download `pkg_artista_v<version>.zip` from [Releases](https://github.com/suklamc/Artista/releases).
2. In the Joomla administrator: **System → Install → Extensions**, then upload the zip.

The package installs the **System - JSN Sun Framework** plugin and the **jsn_artista2_pro** template. The internal names are unchanged, so an existing site keeps its template styles and layouts.

## Moving a Joomla 3 site to Artista

Do this on a copy of the site first, not on the live site.

1. **Back up** files and database, for example with Akeeba Backup.
2. In Joomla 3.10, go to **Extensions → Plugins** and **disable "System - JSN Sun Framework"**. Its Joomla 3 code can stop Joomla 4's administrator from loading. The front end shows an error from now until step 4, so plan for that window.
3. **Upgrade Joomla 3.10 to 4.4** with Joomla's updater. Joomla 3.10's pre-update check lists extensions that have no Joomla 4 version. See Joomla's [3.x to 4.x migration guide](https://docs.joomla.org/Joomla_3.x_to_4.x_Step_by_Step_Migration).
4. **Install Artista** (see above). It updates the plugin and template in place and re-enables the plugin. It also removes the files Artista no longer ships (sample data, licence and Joomla 3 code). Your `css/custom/custom.css`, the generated style CSS and the framework's `backups/` folder are left alone.
5. Open your Artista template style. All tabs should be available and saving should work. Then check the main page types on the front end.
6. **Continue to Joomla 5.4 or 6** with Joomla's updater.

Other extensions are separate. JoomlaShine's PageBuilder, ImageShow, Uniform and PowerAdmin are not part of Artista and have no Joomla 4+ releases. Check every other extension the site uses for Joomla 4/5/6 versions before upgrading.

## What changed from JoomlaShine's release

**Fixed**
- The template style editor no longer hangs on the loading screen. It waited on JoomlaShine's servers.
- The lifetime Pro licence is recorded locally, so all Pro tabs work and saving no longer resets Pro-only settings (responsive support, branding link).
- Updated for Joomla 4.4, 5 and 6 and PHP 8.1 and later (tested on PHP 8.3). The changes are listed in [CHANGELOG.md](CHANGELOG.md).
- Bootstrap's JavaScript updated from 3.3.6 to 3.4.1. It works with the jQuery that Joomla 4+ ships and fixes Bootstrap 3's known XSS issues.

**Removed**, because each depended on JoomlaShine's servers:
- Online sample data, the JoomlaShine account login, live chat, promotions, usage tracking and uninstall feedback.
- JoomlaShine's update and download screens. Updates now come through Joomla's own updater, from this repository.
- Links to joomlashine.com. That domain no longer belongs to JoomlaShine.

**Changed**
- Joomla 3 overrides for login, registration, profile and Smart Search were removed, so Joomla's own layouts are used. They use Bootstrap 5 class names while Artista's CSS is Bootstrap 3, so a few core pages (login form, pagination, article navigation) may look plainer. Check them on staging.
- Module chromes (default, well, lightbox, richbox) are now Joomla 4 layout files.

## Updates

The package registers this update feed with Joomla:

```
https://raw.githubusercontent.com/suklamc/Artista/main/updates/pkg_artista.xml
```

New versions then appear in **System → Update → Extensions** like any other extension.

To publish a new version:

1. Raise the version in `src/pkg_artista.xml`, `src/plugins/system/sunfw/sunfw.xml`, `src/templates/jsn_artista2_pro/templateDetails.xml` and `SUNFW_VERSION` in `sunfw.defines.php`.
2. Run `build/build.sh`. It writes the zips and a `.sha512` file to `dist/`.
3. Create a GitHub release tagged `v<version>` and attach `pkg_artista_v<version>.zip`.
4. Update `updates/pkg_artista.xml` with the new version, download URL and sha512, then push.

## Building

```
build/build.sh
```

This produces `dist/pkg_artista_v<version>.zip`, the file to install, plus the plugin and template zips inside it.

## Licence and credits

GNU General Public License v2 or later (see [LICENSE](LICENSE)).

The Sun Framework and JSN Artista 2 Pro were created by JoomlaShine.com. Their copyright notices are kept in the source files. Maintained since 2026 by Artriva Studios.

Bundled libraries keep their own licences: Bootstrap 3.4.1 (MIT), scssphp 0.6.5 (MIT, patched for PHP 8), Chosen (MIT), React, jQuery plugins, Font Awesome 4.
