/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * A file or folder shown in the media list. Mirrors the PHP FrontendMedia DTO.
 */
export type MediaItem = {
    id: string;
    file: string;
    filetype: string;
    /** Size in bytes, 0 for folders. */
    filesize: number;
    /** Thumbnail url, empty for folders and files without a thumbnail. */
    thumb: string;
    /** "<width>x<height>", empty for non-images. */
    imagesize: string;
};
