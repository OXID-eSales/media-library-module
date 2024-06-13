# Change Log for Media Library Module

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.1.0] - Unreleased

### Added
- New methods for building the paths to the thumbnail easier: `ThumbnailResourceInterface`: `getPathToThumbnailFile`, `getUrlToThumbnailFile`
- New response strategy `ResponseInterface::errorResponseAsJson`

### Fixed
- Use correct interface for shop id calculation
- Reformat js and styles for better readability
- The thumbnail generation process doesn't explode anymore if something goes wrong, like - the origin is missing [#0006785](https://bugs.oxid-esales.com/view.php?id=6785)

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

[1.0.1]: https://github.com/OXID-eSales/media-library-module/compare/v1.0.0..v1.0.1
[1.0.0]: https://github.com/OXID-eSales/media-library-module/compare/f18ab07..v1.0.0
