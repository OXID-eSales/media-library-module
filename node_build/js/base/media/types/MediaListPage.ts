/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import type {MediaItem} from '../../types/MediaItem.ts';

/**
 * One page of a folder, mirrors the MediaController::moreFiles() response.
 */
export type MediaListPage = {
    files: MediaItem[];
    /** True when there are more files after this page. */
    more: boolean;
};
