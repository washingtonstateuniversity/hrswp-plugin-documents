# HRSWP Documents

[![Build Status](https://travis-ci.org/washingtonstateuniversity/hrswp-plugin-documents.svg?branch=stable)](https://travis-ci.org/washingtonstateuniversity/hrswp-plugin-documents) [![Release Version](https://img.shields.io/github/v/release/washingtonstateuniversity/hrswp-plugin-documents)](https://github.com/washingtonstateuniversity/hrswp-plugin-documents/releases/latest) ![WordPress tested up to version 5.6.0](https://img.shields.io/badge/WordPress-v5.6.0%20tested-success.svg) [![code style: prettier](https://img.shields.io/badge/code_style-prettier-ff69b4.svg)](https://github.com/prettier/prettier) [![GPLv3 License](https://img.shields.io/github/license/washingtonstateuniversity/hrswp-plugin-documents)](https://github.com/washingtonstateuniversity/hrswp-plugin-documents/blob/stable/LICENSE.md)

## Overview

A WSU HRS WordPress plugin that helps to create and manage a document library.

## Description

This plugin creates a Document custom post type to help create and manage a document library. Documents provide a static permalink that serves a selected document, presumably a PDF or other download-only document. It also allows setting the static permalink to point to an external resource. Files uploaded to a Document post are added to the media library following the default WordPress process. 

## Installation

1. [Download the latest version from GitHub](https://github.com/washingtonstateuniversity/hrswp-plugin-documents/releases/latest) and rename the .zip file to: `hrswp-plugin-documents.zip`.
2. Either extract the files into your plugins directory via SFTP or navigate to the Plugins screen in the admin area of your site to upload it through the plugin uploader (steps 3-5).
3. Select Plugins > Add New and then select the "Upload Plugin" button.
4. Select "Browse" and locate the downloaded .zip file for the plugin (it **must** be a file in .zip format) on your computer. Select "Install Now."
5. You should receive a message that the plugin installed correctly. 
6. Select "Activate Plugin" or return to the plugins page to activate later.

### Updates

Please note that this plugin will not update automatically and will not notify of new available updates. It is your responsibility to make sure you stay up to date with the latest version.

## For Developers

The HRSWP Documents plugin development environment relies primarily on NPM and Composer. The `package.json` and `composer.json` configuration files manage necessary dependencies for testing and building the production version of the theme. The NPM scripts in `package.json` do most of the heavy lifting.

### Initial Setup

1. Clone the HRSWP Documents plugin to a directory on your computer.
2. Change into that directory.
3. Install the Composer dependencies.
4. Install the NPM dependencies.
5. Ensure PHP, CSS, and JS linting and coding standards checks are working -- this should exit with zero (0) errors.
6. If you plan to contribute changes to the HRSWP Documents plugin you're encouraged to follow the [Git feature branch workflow](https://www.atlassian.com/git/tutorials/comparing-workflows/feature-branch-workflow). Suggested changes should be made on a separate branch and a pull request opened to merge into the `stable` branch.

In a terminal:

~~~bash
git clone https://github.com/washingtonstateuniversity/hrswp-plugin-documents.git
cd hrswp-plugin-documents
composer install
npm install
npm test -s
git checkout -b new-feature
git push origin new-feature
~~~

### Build Commands

The following commands will handle basic build functions. (Remove the `-s` flag to show additional debug info.)

- `npm run build -s`: Remove old compiled files such as minified CSS, lint PHP and CSS, and then compile new versions.
- `npm test -s`: Check all PHP and CSS files for coding standards compliance.
- `npm run clean -s`: Remove old compiled files such as minified CSS.
- `npm run build:styles -s`: Compile CSS.

See the scripts section of `package.json` for additional available commands.

## HRSWP Documents Issues

Please submit bugs, fixes, and feature requests through [GitHub Issues](https://github.com/washingtonstateuniversity/hrswp-plugin-documents/issues). Please read (and adhere to) the guidelines for contributions detailed in the issue templates.

Read the [CHANGELOG.md](https://github.com/washingtonstateuniversity/hrswp-plugin-documents/blob/stable/CHANGELOG.md) to review release and update notes.

## License

HRSWP Plugin Documents.
Copyright (C) 2020 Washington State University

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
