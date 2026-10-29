/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import type {MediaListPage} from './types/MediaListPage.ts';
import type {MediaActionResult} from './types/MediaActionResult.ts';
import type {RenamedMedia} from './types/RenamedMedia.ts';
import type {CreatedFolder} from './types/CreatedFolder.ts';
import type {AltTexts, AltTextSaveResult} from './types/AltTexts.ts';

export const MediaRequestInterface = Symbol('MediaRequestInterface');

/**
 * Requests to the admin media controllers. A failed request rejects with an Error,
 * its message is the error the shop sent (mostly a translation key).
 */
export interface MediaRequestInterface {
    /**
     * Returns the html of the media dialog content. An empty folder id is the root folder.
     */
    loadFiles(folderId: string, tab: string): Promise<string>;

    /**
     * Returns the files of a folder page, the first page is 0.
     */
    fetchMoreFiles(page: number, folderId: string): Promise<MediaListPage>;

    moveFile(fileId: string, targetFolderId: string): Promise<MediaActionResult>;

    renameFile(id: string, newName: string): Promise<RenamedMedia>;

    removeFiles(ids: string[]): Promise<MediaActionResult>;

    addFolder(name: string): Promise<CreatedFolder>;

    /**
     * Returns the url the upload form posts the files to.
     */
    getUploadUrl(folderId: string): string;

    getAltTexts(objectId: string): Promise<AltTexts>;

    saveAltTexts(objectId: string, altTexts: AltTexts): Promise<AltTextSaveResult>;
}
