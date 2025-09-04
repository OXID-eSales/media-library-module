/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

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

    loadFiles(folderId, tab) {
        let params = new URLSearchParams();
        if (tab) {
            params.append('tab', tab);
        }
        if (folderId) {
            params.append('folderid', folderId);
        }

        const url = `${this.actionLink}cl=ddoemedia_view&${params.toString()}`;
        return fetch(url).then(res => res.text());
    }

    fetchMoreFiles(page, folderId) {
        const start = page * 18;
        const url = `${this.actionLink}cl=ddoemedia_view&fnc=moreFiles&start=${start}&folderid=${folderId}`;
        return fetch(url).then(res => res.json());
    }

    moveFile(sourceId, targetId, file, folder, thumb) {
        const url = `${this.actionLink}cl=ddoemedia_view&fnc=movefile`;
        const body = new URLSearchParams({ sourceid: sourceId, targetid: targetId, file, folder, thumb });
        return fetch(url, { method: 'POST', body, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
            .then(res => res.json());
    }

    renameFile(id, newName) {
        const url = `${this.actionLink}cl=ddoemedia_view&fnc=rename`;
        const body = new URLSearchParams({ id, newname: newName });
        return fetch(url, { method: 'POST', body, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => {
                        throw new Error(err.error);
                    });
                }
                return res.json();
            });
    }

    removeFiles(ids, folderId) {
        const qs = ids.map(id => `ids[]=${encodeURIComponent(id)}`).join('&');
        const url = `${this.actionLink}cl=ddoemedia_view&fnc=remove&${qs}&folderid=${folderId}`;
        return fetch(url).then(res => res.json());
    }

    addFolder(name) {
        const url = `${this.actionLink}cl=ddoemedia_view&fnc=addFolder`;
        const body = new URLSearchParams({ name });
        return fetch(url, { method: 'POST', body, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => {
                        throw new Error(err.error);
                    });
                }
                return res.json();
            });
    }

    uploadUrl(folderId) {
        return `${this.actionLink}cl=ddoemedia_view&fnc=upload&folderid=${folderId}`;
    }
}
