/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

const mediaCache = {};

export function setMediaUrl(id, url) {
    mediaCache[id] = url;
}

export function getMediaUrl(id) {
    return mediaCache[id] || null;
}

export function preloadMediaUrls(map) {
    Object.assign(mediaCache, map);
}
