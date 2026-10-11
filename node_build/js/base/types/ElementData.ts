/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import type {MediaItem} from './MediaItem.ts';

/**
 * Values kept in memory per DOM element: media item data on .dd-media-item, dialog settings on the dialog.
 */
export type ElementData = MediaItem & {
    url: string;
    multiple: boolean;
};
