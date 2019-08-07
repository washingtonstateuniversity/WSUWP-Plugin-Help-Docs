# WSUWP Help Docs Changelog

Authors: Adam Turner, Washington State University\
URI: https://github.com/washingtonstateuniversity/wsuwp-plugin-help-docs

<!--
Changelog formatting (http://semver.org/):

## Major.MinorAddorDeprec.Bugfix YYYY-MM-DD

### To Do (for upcoming changes)
### Security (in case of fixed vulnerabilities)
### Fixed (for any bug fixes)
### Changed (for changes in existing functionality)
### Added (for new features)
### Deprecated (for once-stable features removed in upcoming releases)
### Removed (for deprecated features removed in this release)
-->

## 1.0.0-alpha-3 (:construction: WIP 2019-08-07)

### Changed

- :art: Integrate WP block styles to facilitate using the block editor to write help docs and clean up nav menu.
- Convert uninstall from a singleton class to namespaced functions.
- :recycle: Refactor plugin setup methods and increment PHP minimum version and WP tested version.
- :warning: Fix php lint issues following phpcs upgrade.
- :recycle: Convert Sass to CSS (next).
- Update git attributes and ignore files for new config files.
- Convert stylelint config to json and use WordPress config.
- :wrench: Replace old phpcs ruleset with updated version.
- :warning: Update package.json lint issues.
- :heavy_plus_sign: :heavy_minus_sign: Overhaul Composer dependencies to use updated phpcs packages and script commands.
- :wrench: Overhaul NPM dependencies and scripts to use a build environment targeted to the WordPress block editor (using WP recommended babel, eslint, and related packages) and postCSS instead of Sass.

### Added

- Register block scripts in the main setup file.
- Initial block editor script to add list styles.
- A webpack config file modeled on the Gutenberg scripts package configuration.
- A config file for the postCSS package.
- A config file for the NPM package json lint tool using the WP recommended configuration.
- A config file for ES Lint using the WP recommended configuration.
- An editorconfig file to help unify coding styles.

## 0.7.1 (2019-04-29)

### Changed

- :arrow_up: Upgrade NPM dev dependencies.

## 0.7.0 (2018-10-17)

### Added

* Styles for a documentation steps list component.

## 0.6.0 (2018-09-28)

### Changed

* Update NPM dependencies.

### Added

* Custom post updated messages for the Help custom post type.

## 0.5.0 (2018-09-27)

### Changed

* The WSUWP_Help_Docs_Updater class $slug property is now public and static so that the flush transient method can run from outside the class.

### Added

* WSUWP_Help_Docs_Updater method to delete the `update_plugin_{slug}` transient so that it can be called from the plugin deactivation hook (allowing a method to force a check for new updates).

## 0.4.1 (2018-09-27)

### Fixed

* Remove the "v" from the GitHub version number for more reliable version comparison.
* Corrected plugin repo username.

### Changed

* Format WSUWP_Help_Docs_Updater changelog output with `the_content` filter.
* Use plugin metadata for the required version fields when fetching plugin info in the WSUWP_Help_Docs_Updater class.
* Move WP required version, tested version, and PHP version meta to plugin head matter.
* Update WSUWP_Help_Docs_Updater class documentation.
* Clean up debugging and phpcs issues in WSUWP_Help_Docs_Updater class.
* The `update_pluign_{slug}` transient now not only stores the GitHub response of a successful request, but also an error placeholder (for 60 minutes) on a failed request. This guarantees we only ping the GitHub API at most once an hour, helping to prevent spamming the service if something breaks on our end.
* Fine-tuned error handling in `WSUWP_Help_Docs_Updater->get_repository_details()` to warn of failed requests.
* We only need the latest release from GitHub, so specify `/latest` in the request URI instead of requesting all releases (which means we also don't need the `is_array` > `current()` conditional on the API response).
* Replace `current( explode(` method to build the plugin slug with `$slug` class property.
* Use `transient_update_plugins` hook instead of `pre_set_site_transient_update_plugins`.
* Load the updater class in the main plugin file and setup class and define the base credentials in the main plugin file.

### Added

* A `.gitattributes` file to ignore development files on GitHub's zip export.
* WSUWP_Help_Docs_Updater method to ensure updated plugin files are in the correct place and the plugin reactivated if it was already active.
* WSUWP_Help_Docs_Updater method to add additional header fields to the `get_plugin_data` function call.
* WSUWP_Help_Docs_Updater method to handle displaying errors when the connection fails.
* Method to add a "view details" link to the row meta on the plugins admin screen.
* Dedicated `$slug` property in `WSUWP_Help_Docs_Updater` class to fetch slug separate from basename.
* Method for retrieving plugin details from the GitHub API and displaying on the plugins admin screen.
* Method for checking and updating the WP `update_plugins` transient with information from the GitHub API.
* Updater class to check GitHub repo for newer plugin version and install it if found.

### Removed

* The `deploy` npm script in favor of GitHub exports.

## 0.3.1 (2018-09-18)

### Fixed

* README language and typo.
* Incorrect `post_type` definition in `uninstall.php`.
* Corrected incorrect text domains.

### Changed

* Limit access to get admin url function.
* Move plugin initialization trigger from the class to a WP action from the main plugin file .
* Increase help document header font size.
* Use `add_query_arg` instead of manual concatenation to build Help document URLs in `set_page_link()`.
* Updated data list styles so that data titles stand out more and to improve legibility.

### Added

* Function to unset the "helplink" shortcode on plugin deactivation.
* "Anchor" parameter to the insert link shortcode to allow linking to specific anchors within a page.
* Shortcode for Help post types to insert links to other help documents, to fix #2 (normal links between help documents are missing the required URL nonce, so they fail).

## 0.2.3 (2018-09-12)

### Fixed

* `README.md` typos and URL formatting.

## 0.2.2 (2018-09-11)

### Fixed

* Rename to site-agnostic plugin.
* Some phpcs linting corrections.

### Changed

* Clean up gitignore file.
* Use `0` instead of `1` as the default help document placeholder ID to avoid collisions.
* Updated default help document (dashboard homepage) to provide more information to users with Editor+ permissions.
* Set several properties to non-static since they don't really need to be accessible outside of the class.
* Some naming and verification clarifications.

### Added

* Option to store the default help document ID.
* Methods to set, update, and get the default help document (dashboard homepage).
* Help document metabox to set a given document as the default.
* Dashboard widget on the main admin dashboard page to display recently updated help documents and links to view and manage documents.
* Styles to allow for nested hierarchical menus in the Help page nav menu.
* Deploy NPM script in `package.json` to create a production build version.
* Custom syntax highlighting.

### Removed

* The static `$default_help_doc` property, in favor of a WP option.

## 0.1.0 (2018-08-28)

### Added

* Base styling for the plugin admin dashboard page.
* Extension of the `Walker_Page` class to fix output of `wp_list_pages()` on the admin dashboard page.
* Template to display the plugin admin dashboard page.
* Plugin setup and uninstall classes.
* Base plugin loader and index placeholder.
* Plugin documentation and licensing files.
* Build tools and configuration.
* Initial config files.
