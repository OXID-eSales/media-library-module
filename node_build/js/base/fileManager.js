/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import { buildActionLinkParams } from './mediaUtils.js';

export default class FileManager {
    constructor(actionLink = '', resourceLink = '') {
        this.actionLink = actionLink;
        this.resourceLink = resourceLink;
    }

    setActionLink(url) { this.actionLink = decodeURI(url); }
    setResourceLink(url) { this.resourceLink = decodeURI(url); }

    formatFileSize(size) {
        size = parseInt(size);
        const names = ['tb', 'gb', 'mb', 'kb', 'b'];
        while (size > 1024 && names.length) {
            size = Math.round((size / 1024) * 100) / 100;
            names.pop();
        }
        return size + ' ' + names.pop();
    }

    loadMediaContent(folderId, tab) {
        const params = buildActionLinkParams(folderId, tab);
        return $.get(this.actionLink + 'cl=ddoemedia_view' + params);
    }

    fetchMoreFiles(page, folderId) {
        const start = page * 18;
        return $.get(
            `${this.actionLink}cl=ddoemedia_view&fnc=moreFiles&start=${start}&folderid=${folderId}`
        );
    }

    moveFile(sourceId, targetId, file, folder, thumb) {
        return $.post(this.actionLink + 'cl=ddoemedia_view&fnc=movefile', {
            sourceid: sourceId, targetid: targetId, file, folder, thumb
        });
    }

    renameFile(id, newName) {
        return $.ajax({
            type: 'POST',
            url: this.actionLink + 'cl=ddoemedia_view&fnc=rename',
            data: { id, newname: newName }
        });
    }

    removeFiles(ids, folderId) {
        const qs = ids.join('&ids[]=');
        return $.get(
            `${this.actionLink}cl=ddoemedia_view&fnc=remove&ids[]=${qs}&folderid=${folderId}`
        );
    }

    addFolder(name) {
        return $.post(this.actionLink + 'cl=ddoemedia_view&fnc=addFolder', { name });
    }

    uploadUrl(folderId) {
        return (
            this.actionLink + 'cl=ddoemedia_view&fnc=upload&folderid=' + folderId
        );
    }
}
