# Change Log for Media Library Module

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [4.0.0] - Unreleased

### Added
- Exception thrown on JsonResponse creation if the json encoding fails, instead of returning an empty string
- Supported PHP range added to PHPStan configuration for better compatibility ensurance
- Update phpstan level 6 -> 8

### Changed
- Update to Bootstrap 5
- Migrated from less to sass
- Constructors with "iterator" promoted properties where the type is important now using regular properties
  - `ThumbnailGeneratorAggregate`
  - `DocumentNameValidatorChain`
  - `UploadedFileValidatorChain`

### Fixed
- MediaRepositoryInterface::getMediaById return should not be null possible, as exception is thrown in this case
- Issues reported by phpstan level increase
- Wrong language calculated by default in `MediaLangJs` controller, now it uses active template language

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

[4.0.0]: https://github.com/OXID-eSales/media-library-module/compare/v3.0.0..v4.0.0
[3.0.0]: https://github.com/OXID-eSales/media-library-module/compare/v2.1.1..v3.0.0
[2.1.1]: https://github.com/OXID-eSales/media-library-module/compare/v2.1.0..v2.1.1
[2.1.0]: https://github.com/OXID-eSales/media-library-module/compare/v2.0.0..v2.1.0
[2.0.1]: https://github.com/OXID-eSales/media-library-module/compare/v2.0.0..v2.0.1
[2.0.0]: https://github.com/OXID-eSales/media-library-module/compare/v1.0.0..v2.0.0
[1.0.0]: https://github.com/OXID-eSales/media-library-module/compare/f18ab07..v1.0.0
