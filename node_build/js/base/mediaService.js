/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

const localMediaCache = {};

let mediaCache = localMediaCache;

/**
 * Media urls are shared between realms (editor frame, overlay iframe), each of which evaluates
 * this module separately. Handing in a store object that lives outside of the module lets all of
 * them read and write the same media urls.
 *
 * @param {Object|null} sharedMediaCache
 */
export function setMediaUrlStore(sharedMediaCache) {
    mediaCache = sharedMediaCache || localMediaCache;
}

/**
 * @deprecated use setMediaUrlStore() instead. Note that the given object is used as the store
 *   itself, it is not copied into a module private cache anymore.
 *
 * @param {Object|null} sharedMediaCache
 */
export function preloadMediaUrls(sharedMediaCache) {
    setMediaUrlStore(sharedMediaCache);
}

export function setMediaUrl(id, url) {
    mediaCache[id] = url;
}

export function getMediaUrl(id) {
    return mediaCache[id] || null;
}
