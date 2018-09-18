# WSUWP Help Docs Changelog

Author: Adam Turner
Author: Washington State University
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

## 0.3.1 (unreleased)

### Fixed

* Incorrect `post_type` definition in `uninstall.php`.
* Corrected incorrect text domains.

### Changed

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
