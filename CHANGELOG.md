# HRSWP Documents Changelog

Author: Adam Turner  
Author: Washington State University  
URI: https://github.com/washingtonstateuniversity/hrswp-plugin-documents

<!--
Changelog formatting (http://semver.org/)

## Major.MinorAddorDeprec.Bugfix YYYY-MM-DD

### Features
### Enhancements
### Bug Fixes
### Experiments
### Deprecations
### Code quality
### Documentation
### Build Tooling
### Project Management
-->

## 1.1.0-alpha.cf70caf (:construction: 2020-10-27)

### Features

- Close #21 allow setting external URL as document location.

### Enhancements

- Allow setting feature image to override document thumbnail. (cf70caf)
- Close #19 spruce up editor screen display with document thumbnail, title, and creation date. (51a1a71)
- Change copy button from file URL to permalink. (51a1a71)
- Add taxonomies to documents CPT. (936a738)
- Remove revision tracking since it only catches title. (e0537f1)
- Hide from nav menus UI. (3112d16)

### Bug Fixes

- Fix #17 flush rewrite rules on activation. (f3381f5)

## 1.0.0 (2020-07-15)

### Features

- ✨ Create the Document Select block with block build pipeline and script includes. (e3c9dc8)
- Create the HRSWP Documents (`hrswp_documents`) custom post type with post meta to track the selected file and template redirect method to serve the file in place of the default template. (b3e8d4f)
- ✨ Add plugin load and lifecycle management functions. (09c9df5)

### Enhancements

- Increment WP version requirement and tested to up to 5.5. (a8f525e)

### Documentation

- 📝 Add readme, license, and changelog files.

### Build Tooling

- 👷 Set Travis to track any `stage/*` branch.
- Add the Classnames npm dev-dependency. (e3c9dc8)
- Add `eslint-plugin-jest` npm dev-dependency. (3b720d0)
- Add build tool configuration and script files. (9a330e8)
- 👷 Add Travis CI configuration. (2e71a28)

### Project Management

- ✅ Add Github issue and pull request templates. (3e5f2a9)
- Add Git configuration files.
