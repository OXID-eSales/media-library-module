/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export function buildActionLinkParams(folderId, tab) {
    let params = '';
    if (tab) {
        params += `&tab=${tab}`;
    }
    if (folderId) {
        params += `&folderid=${folderId}`;
    }
    return params;
}

export function extractMediaPath(resourceLink) {
    const prefix = 'out/pictures/';
    return resourceLink.substring(resourceLink.indexOf(prefix) + prefix.length);
}

export function updateFileCount($dialog, delta) {
    const countElem = $('.dd-media-file-count', $dialog);
    countElem.text(parseInt(countElem.text(), 10) + delta);
}

export function filterMediaList($container, query) {
    const items = $('.dd-media-list-items > .row > .dd-media-col', $container);
    if (!query) {
        items.show();
    } else {
        items.each(function() {
            const $item = $('.dd-media-item', this);
            $item.data('file').includes(query) ? $(this).show() : $(this).hide();
        });
    }
}
