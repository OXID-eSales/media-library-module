# Change Log for Media Library Module

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [5.1.0] - 2026-05-13

### Added
- Changes from 4.2.0

## [5.0.0] - 2026-04-09

### Changed
- Updated to work with OXID eShop 7.5.x
- Minimum PHP version is now 8.3, tested up to PHP 8.5
- `MediaRepository` and `PreloadMediaRepository` uses `QueryBuilderFactoryInterface` instead of Connection
- Removed Bootstrap 3 CSS classes from templates
- Replaced Font Awesome with Bootstrap Icons

### Added
- Media library search field now matches against media ID in addition to filename

## [4.2.0] - 2026-05-13

### Added
- SVG upload content validation: rejects files containing scripts, foreign objects, `on*` event handlers, or `javascript:`/`data:` URLs
- MIME-type validation on every upload: rejects files whose sniffed content type does not match the declared extension
- Raster-image content validation for `jpg`, `jpeg`, `gif`, `png`, `webp`, `avif`: files that do not parse as a valid image are rejected
- `FileFormatRegistry` mapping allowed extensions to accepted MIME types; integrators can register additional formats via service configuration
- `ContentValidatorInterface` for per-format content checks; tagged services are auto-discovered by the upload chain
- `FilePathInterface::getExtension()` returns the lowercased file extension

### Changed
- Apply consistent filename sanitization across upload and rename
- `composer.json` now declares `ext-dom`

### Security
- Reject path-traversal characters (`/`, `\`, `..` segments, null bytes) in upload filenames

### Note for integrators
- The upload validator chain now adds `MimeTypeValidator` and `ContentValidatorDispatcher` after `FileExtensionValidator`. Modules that fully replace `UploadedFileValidatorChainInterface` need to list both validators in the same order to retain the upload-content protection.
- `FilePathInterface` adds a new method `getExtension()`. Modules implementing this interface themselves need to add the method, returning the lowercased file extension.

### Fixed
- Ctrl+click multi-select was not working due to wrong event button check.

## [4.1.0] - 2025-11-10

### Added
- new JS Media service to store and map media urls

## [4.0.0] - 2025-10-15

### Added
- "Fallback" image feature
- "oeMediaUrl" twig function to get URL to the media by ID, also uses Fallback image if requested one is not found.
- `MediaIdByPathFacadeInterface::getMediaIdByPath` to calculate Media id by media path or url
- `MediaFacadeInterface` for accessing media objects in other modules
- Exception thrown on JsonResponse creation if the json encoding fails, instead of returning an empty string
- Supported PHP range added to PHPStan configuration for better compatibility ensurance
- Update phpstan level 6 -> 8
- Support for alternative (alt) text per language for media items.
- New database table for storing alt texts per language.
- Media detail view: Language selector and alt text field with AJAX save, defaulting to shop language.
- DTO for alt text data.
- Repository for managing alt text persistence and retrieval.
- Twig function/helper for retrieving alt text in templates.
- Temporal `ViewConfig::formJsFileUrl` method to append file modification date to js file paths, which prevents caching issues after updates.

### Changed
- Update to Bootstrap 5
- Migrated from less to sass
- Constructors with "iterator" promoted properties where the type is important now using regular properties
  - `ThumbnailGeneratorAggregate`
  - `DocumentNameValidatorChain`
  - `UploadedFileValidatorChain`
- Update phpstan level 6 -> 8
- Media library javascript refactored and split to different classes:
  - DataStore class – manages metadata for DOM elements, with support for memory-only and persistent (DOM dataset) storage.
  - MediaContent class – encapsulates loading and rendering of media items.
  - FileManager class – API interaction and resource management for media.
  - UIRenderer class – handles UI rendering logic for media items and dialogs.
  - DragDropHandler class – manages drag & drop actions within the media library.
- Replaced most jQuery DOM manipulation with native DOM APIs.
- Public js method `open([filter], [multiple], callback)` changed to `open(options = {}, callback)` where `options` is an object ({ filter, multiple }).
- Public js method `init([filter], [multiple], callback)` changed to `init(options = {}, callback)` where `options` is an object ({ filter, multiple }).
- Media repository and DTOs updated to support alt text per language.
- Added alt text rendering for images in all shortcodes that use media items via `getMediaAltText`.

### Fixed
- MediaRepositoryInterface::getMediaById return should not be null possible, as exception is thrown in this case
- Issues reported by phpstan level increase
- Wrong language calculated by default in `MediaLangJs` controller, now it uses active template language

### Removed
- Overlay functionality removed, use the Modal instead.

## [3.0.1] - Unreleased

### Fixed
- Development recipe to not require the media library module twice

## [3.0.0] - 2025-04-10

### Added
- Support of PHP 8.4

### Changed
- Removed Grunt entirely and migrated all build tasks to Vite
- Migrated all JavaScript files to ES Modules
- jQuery updated to v3.7.1 version

### Removed
- External libraries from vendor directory. They are installed using Node.js now
- Completely removed Grunt and its dependencies from the project

## [2.1.1] - 2024-10-23

### Fixed
- Merged the fixes from v2.0.1

## [2.1.0] - 2024-10-14

### Changed
- Upgrade phpunit to 11.x

### Removed
- Support of PHP 8.1

## [2.0.1] - 2024-10-23

### Fixed
- Fixed empty error box for errorous add folder and rename folder actions

## [2.0.0] - 2024-10-10

### Added
- New methods for building the paths to the thumbnail easier: `ThumbnailResourceInterface`: `getPathToThumbnailFile`, `getUrlToThumbnailFile`
- New response strategy `ResponseInterface::errorResponseAsJson`
- New module setting added to handle allowed file extensions `ModuleSettingsInterface::getAllowedExtensions`
- Validations for File: Cannot start from dot, Cannot be empty string. Extensions checked to be from the allowed list. [#0007025](https://bugs.oxid-esales.com/view.php?id=7025)
- Validations for Directory: Cannot start from dot, cannot be empty string.
- Validations for Uploaded file: All regular file validations + Checking if file was successfully uploaded at all. [#0006785](https://bugs.oxid-esales.com/view.php?id=6785)
- New method to get uploaded file data `UIRequestInterface::getUploadedFile`, also `UploadedFileInterface` data type.

### Changed
- Improved the element layout to show errors with better visibility.
- Improved the way errors handled from controllers to user interface during upload, addfolder and rename actions.
- `ModuleSettingsInterface` moved to `Settings` domain/namespace.
- `NamingServiceInterface::sanitizeFilename` method input parameter renamed to `$fileName`

### Fixed
- Use correct interface for shop id calculation
- Reformat js and styles for better readability
- The thumbnail generation process doesn't explode anymore if something goes wrong, like - the origin is missing [#0006785](https://bugs.oxid-esales.com/view.php?id=6785)

### Removed
- `validateFileName` method in NamingServiceInterface. This part extracted to Validation domain, and now expanded to handle various cases.

## [1.0.0] - 2024-03-12

Module extracted from wysiwyg module, and used by it now

### Added
- Folder functionality for media library
- Possibility to rename of images
- Check for allowed file types during upload [PR-19](https://github.com/OXID-eSales/ddoe-wysiwyg-editor-module/pull/19)
- All file types enabled in config.inc.php are uploadable now including to not only images

### Changed
- Thumbnails are generated on demand and the type of thumbnail file is matching the original image type
- Alternative image directory setting renamed to fit its functionality: Alternative image URL

[5.1.0]: https://github.com/OXID-eSales/media-library-module/compare/v5.0.0..v5.1.0
[5.0.0]: https://github.com/OXID-eSales/media-library-module/compare/v4.1.0..v5.0.0
[4.2.0]: https://github.com/OXID-eSales/media-library-module/compare/v4.1.0..v4.2.0
[4.1.0]: https://github.com/OXID-eSales/media-library-module/compare/v4.0.0..v4.1.0
[4.0.0]: https://github.com/OXID-eSales/media-library-module/compare/v3.0.1..v4.0.0
[3.0.1]: https://github.com/OXID-eSales/media-library-module/compare/v3.0.0..v3.0.1
[3.0.0]: https://github.com/OXID-eSales/media-library-module/compare/v2.1.1..v3.0.0
[2.1.1]: https://github.com/OXID-eSales/media-library-module/compare/v2.1.0..v2.1.1
[2.1.0]: https://github.com/OXID-eSales/media-library-module/compare/v2.0.0..v2.1.0
[2.0.1]: https://github.com/OXID-eSales/media-library-module/compare/v2.0.0..v2.0.1
[2.0.0]: https://github.com/OXID-eSales/media-library-module/compare/v1.0.0..v2.0.0
[1.0.0]: https://github.com/OXID-eSales/media-library-module/compare/f18ab07..v1.0.0
