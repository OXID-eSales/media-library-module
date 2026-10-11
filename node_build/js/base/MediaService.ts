/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import type {MediaUrlStore} from './types/MediaUrlStore.ts';

const localMediaCache: MediaUrlStore = {};

let mediaCache: MediaUrlStore = localMediaCache;

/**
 * Media urls are shared between realms (editor frame, overlay iframe), each of which evaluates
 * this module separately. Handing in a store object that lives outside of the module lets all of
 * them read and write the same media urls.
 */
export function setMediaUrlStore(sharedMediaCache: MediaUrlStore | null | undefined): void {
    mediaCache = sharedMediaCache || localMediaCache;
}

/**
 * @deprecated use setMediaUrlStore() instead. Note that the given object is used as the store
 *   itself, it is not copied into a module private cache anymore.
 */
export function preloadMediaUrls(sharedMediaCache: MediaUrlStore | null | undefined): void {
    setMediaUrlStore(sharedMediaCache);
}

export function setMediaUrl(id: string, url: string): void {
    mediaCache[id] = url;
}

export function getMediaUrl(id: string): string | null {
    return mediaCache[id] || null;
}
