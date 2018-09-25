# WSUWP Help Docs

Contributors: admturner, washingtonstateuniversity\
Requires at least: 4.0\
Tested up to: 4.9\
Stable tag: 0.4.1\
Requires PHP: 5.3\
License: GPLv2 or later\
License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description

This plugin creates a Help custom post type. The Help posts cannot be viewed or queried from the front end of the site. They're intended to provide help, guidance, and instructions for logged in site users. The plugin generates two new areas in the WP Admin area:

1. A subpage under the Tools section where registered site users with Editor capabilities and up can manage Help posts.
2. A Help dashboard under the main Dashboard section where registered site users (with at least "read" permissions) can view Help posts.

## For Developers

<!-- @todo Explain the directory structure, build process, and build and testing tools. -->

### Initial Setup

1. Install the NPM dependencies.
2. Install the Composer dependencies.
3. Ensure PHP coding standards are properly sniffed.
4. Ensure Sass files are properly linted.

In a terminal:

~~~
npm install
composer install
npm run phpcs
npm run lintscss
~~~

### Browser Support

The WSUWP Help Docs plugin uses [Browserlist](https://github.com/browserslist/browserslist) to help monitor feature support. It aims provide a reasonably fast and fully usable experience on older browsers while progressively enhancing the user experience on more modern browsers.

Specifically, this plugin aims to support all browsers with greater than 1% global usage (based on data from [Can I Use](http://caniuse.com/)), as well as IE 8-11, and the Firefox Extended Support Release (ESR). The Browserlist configuration, defined in `package.json` is:

~~~
"browserslist": [
  "> 1%",
  "ie 8-11",
  "Firefox ESR"
],
~~~

Use the [Browserlist online demo](http://browserl.ist/) (search for `> 1%,ie 8-11,Firefox ESR`) to review the current list of mobile and desktop browsers this resolves to.

### Bugs and/or Fixes for WSUP Help Docs

Please submit bugs and/or fixes to: [GitHub Issues](https://github.com/washingtonstateuniversity/wsuwp-plugin-help-docs/issues). Please read (and adhere to) the guidelines for contributions detailed in [CONTRIBUTING.md](https://github.com/washingtonstateuniversity/wsuwp-plugin-help-docs/blob/master/CONTRIBUTING.md).

To view release & update notes, read the [CHANGELOG.md](https://github.com/washingtonstateuniversity/wsuwp-plugin-help-docs/blob/master/CHANGELOG.md).
