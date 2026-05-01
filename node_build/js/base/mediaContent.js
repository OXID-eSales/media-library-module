/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import Dropzone from "dropzone";
import {ddh} from './helper.js';

export default class MediaContent {
    constructor(fileManager, uiRenderer, dragDropHandler, dataStore) {
        this.fm = fileManager;
        this.ui = uiRenderer;
        this.dd = dragDropHandler;
        this.store = dataStore;
    }

    loadMediaContent(dialog, folderId, tab) {
        this.fm.loadFiles(folderId, tab).then((html) => {
            if (!dialog) return;

            if (dialog.classList.contains('dd-media-wrapper')) {
                const content = dialog.querySelector('.dd-content');
                if (content) content.innerHTML = html;
            } else {
                const body = dialog.querySelector('.modal-body');
                if (body) body.innerHTML = html;
            }

            this._updateMediaState(dialog);
            this._initFolderActions(dialog, folderId);
            this._initRemoveAction(dialog);
            this._initRenameAction(dialog);
            this._initItemClickHandlers(dialog);
            this._initDropzone(dialog);
            this._initSearch(dialog);
            this._loadMoreMediaContent(0);
        });
    }

    refreshMedia(id) {
        const media = document.querySelector('.dd-media');
        if (!media) {
            return;
        }

        let dialog = media.closest('.modal');
        const activeTab = document.querySelector('.dd-media-tabs .tab-pane.active');
        const tab = activeTab ? activeTab.id : null;
        const loader = '<div class="dd-dialog-loader"></div>';

        if (dialog) {
            const modalBody = dialog.querySelector('.modal-body');
            modalBody.innerHTML = loader;
        } else {
            dialog = document.querySelector('.dd-media-wrapper');
            const content = dialog.querySelector('.dd-content');
            content.innerHTML = loader;
        }

        this.loadMediaContent(dialog, id, tab);
    }

    _updateMediaState(dialog) {
        const mediaEl = dialog.querySelector('.dd-media');
        this.currentFolderId = this.store.get(mediaEl, 'folderid');
        this.fm.resourceLink = this.store.get(mediaEl, 'medialink');

        const prefix = 'out/pictures/';
        this.currentPath = this.fm.resourceLink.substring(this.fm.resourceLink.indexOf(prefix) + prefix.length);

        const items = dialog.querySelectorAll('.dd-media-item[data-id]');
        items.forEach(el => {
            this.ui.updateMediaState(el);
        });

        const actionEls = dialog.querySelectorAll('.dd-media-remove-action, .dd-media-move-action, .dd-media-rename-action');
        actionEls.forEach(el => el.disabled = true);
    }

    _initSearch(dialog) {
        const form = dialog.querySelector('.dd-media-search-form');
        form.addEventListener('submit', e => e.preventDefault());

        const input = form.querySelector('input');
        input.addEventListener('keyup', e => {
            const searchValue = e.target.value.toLowerCase();
            const items = dialog.querySelectorAll('.dd-media-list-items > .row > .dd-media-col');

            items.forEach(item => {
                const mediaItem = item.querySelector('.dd-media-item');
                const fileName = this.store.get(mediaItem, 'file') || '';
                const mediaId = this.store.get(mediaItem, 'id') || '';
                if (!searchValue || fileName.toLowerCase().includes(searchValue) || mediaId.toLowerCase().includes(searchValue)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    _initItemClickHandlers(dialog) {
        const multiple = this.store.get(dialog, 'multiple');
        const media = dialog.querySelector('.dd-media');
        media.addEventListener('click', (e) => {
            const target = e.target.closest('.dd-media-item');
            if (this.dd.dragMoved || !target) {
                return;
            }
            e.preventDefault();

            if (target.closest('.dz-error')) {
                return;
            }

            if (multiple && e.ctrlKey) {
                target.classList.toggle('active');
            } else {
                dialog.querySelectorAll('.dd-media-item.active').forEach(el => el.classList.remove('active'));
                target.classList.add('active');
            }

            let detailsItem;
            if (target.classList.contains('active')) {
                detailsItem = target;
            } else {
                detailsItem = dialog.querySelector('.dd-media-item.active');
            }

            if (detailsItem) {
                const itemData = this.store.getAll(detailsItem);
                itemData.url = this.fm.resourceLink + this.store.get(detailsItem, 'file');
                itemData.type = itemData.filetype;
                itemData.size = itemData.filesize;

                const thumb = detailsItem.querySelector('.dd-media-thumb');
                if (thumb) {
                    itemData.preview = thumb.getAttribute('src');
                }

                this.ui.showItemDetails(itemData, dialog);
            }

            const activeItems = dialog.querySelectorAll('.dd-media-list-items > .row > .dd-media-col > .active');
            if (!activeItems.length) {
                this.ui.showItemDetails(false, dialog);
                dialog.querySelectorAll('.dd-media-remove-action, .dd-media-move-action, .dd-media-rename-action')
                    .forEach(el => el.disabled = true);
            } else if (activeItems.length > 1) {
                dialog.querySelectorAll('.dd-media-remove-action, .dd-media-move-action')
                    .forEach(el => el.disabled = false);
                dialog.querySelectorAll('.dd-media-rename-action')
                    .forEach(el => el.disabled = true);
            } else {
                dialog.querySelectorAll('.dd-media-remove-action, .dd-media-move-action, .dd-media-rename-action')
                    .forEach(el => el.disabled = false);
            }
        });

        media.addEventListener('dblclick', (e) => {
            const target = e.target.closest('.dd-media-item');
            e.preventDefault();

            if (this.store.get(target, 'filetype') === 'directory') {
                this.refreshMedia(this.store.get(target, 'id'));
            } else {
                const submit = dialog.querySelector('.dd-media-submit');
                submit && (submit.click());
            }
        });
    }

    _initRenameAction(dialog) {
        const renameBtn = dialog.querySelector('.dd-media-rename-action');

        renameBtn.addEventListener('click', (e) => {
            const activeItem = dialog.querySelector('.dd-media-item.active');
            const btn = e.currentTarget;

            if (!btn.disabled && activeItem) {
                ddh.prompt(
                    ddh.translate('DD_MEDIA_RENAME_FILE_FOLDER'),
                    (val) => {
                        const currentFile = this.store.get(activeItem, 'file');
                        if (val !== currentFile) {
                            this.fm.renameFile(this.store.get(activeItem, 'id'), val)
                                .then((resultJson) => {
                                    this.store.setMultiple(activeItem, {
                                        id: resultJson.id,
                                        file: resultJson.name,
                                        url: this.fm.resourceLink + resultJson.name
                                    });

                                    const labelSpan = activeItem.querySelector('.dd-media-item-label span');
                                    labelSpan.textContent = resultJson.name;

                                    this.ui.showItemDetails(this.store.getAll(activeItem), dialog);
                                })
                                .catch(err => {
                                    ddh.alert(ddh.translate(err.message));
                                });
                        }
                    },
                    undefined,
                    this.store.get(activeItem, 'file')
                );
            }
        });
    }

    _initRemoveAction(dialog) {
        const removeBtn = dialog.querySelector('.dd-media-remove-action');
        removeBtn.addEventListener('click', (e) => {
            const items = dialog.querySelectorAll('.dd-media-item.active');

            if (removeBtn.disabled || !items.length) {
                return;
            }

            let sConfirmMsg = 'DD_MEDIA_REMOVE_CONFIRM';
            if (items.length > 1) {
                sConfirmMsg = 'DD_MEDIA_REMOVE_MANY_CONFIRM';
            } else if (this.store.get(items[0], 'filetype') === 'directory') {
                sConfirmMsg = 'DD_MEDIA_REMOVE_FOLDER_CONFIRM';
            }

            ddh.confirm(ddh.translate(sConfirmMsg), () => {
                items.forEach(item => item.classList.add('dd-media-item-removing'));

                const deleteIDs = Array.from(items).map(item => this.store.get(item, 'id'));
                const mediaEl = dialog.querySelector('.dd-media');
                const folderId = mediaEl ? this.store.get(mediaEl, 'folderid'): null;

                this.fm.removeFiles(deleteIDs, folderId).then((response) => {
                    if (response.success) {
                        const fileCountEl = dialog.querySelector('.dd-media-file-count');
                        if (fileCountEl) {
                            fileCountEl.textContent = parseInt(fileCountEl.textContent, 10) - items.length;
                        }
                        items.forEach(item => {
                            const parent = item.parentElement;
                            if (parent) parent.remove();
                        });

                        removeBtn.disabled = true;

                        const remainingItems = dialog.querySelectorAll('.dd-media-list-items > .row > .dd-media-col');
                        if (!remainingItems.length) {
                            const mediaList = dialog.querySelector('.dd-media-list');
                            mediaList.classList.add('empty');
                        }

                        const detailsForm = dialog.querySelector('.dd-media-details-form');
                        detailsForm.style.display = 'none';
                    } else if (response.msg) {
                        items.forEach(item => item.classList.remove('dd-media-item-removing'));
                        ddh.alert(ddh.translate(response.msg));
                    }
                });
            }, null, true);
        });
    }

    _initDropzone(dialog) {
        Dropzone.autoDiscover = false;
        const mediaEl = dialog.querySelector('.dd-media');
        if (!mediaEl) {
            return;
        }

        const self = this;
        const previewsContainer = dialog.querySelector('.dd-media-list-items > .row');
        const previewTemplateEl = dialog.querySelector('.dd-media-list-items .dd-media-dz-helper');

        new Dropzone(mediaEl, {
            url: this.fm.uploadUrl(this.store.get(mediaEl, 'folderid')),
            parallelUploads: 10,
            previewsContainer: previewsContainer,
            previewTemplate: previewTemplateEl ? previewTemplateEl.innerHTML : '',
            clickable: dialog.querySelector('.dd-media-upload'),
            hiddenInputContainer: mediaEl,

            init: function () {
                this.on('addedfile', function () {
                    const mediaList = dialog.querySelector('.dd-media-list');
                    mediaList.classList.remove('empty');

                    const mediaTabBtn = dialog.querySelector('.dd-media-tabs .nav-tabs button[data-bs-target="#mediaList"]');
                    mediaTabBtn.click();

                    const listItems = dialog.querySelector('.dd-media-list-items');
                    if (listItems && previewsContainer) {
                        listItems.scrollTop = previewsContainer.offsetHeight;
                    }
                });

                this.on('success', (file, response) => {
                    const itemEl = file.previewElement.querySelector('.dd-media-item');
                    if (itemEl) {
                        const thumbEl = itemEl.querySelector('.dd-media-thumb');
                        thumbEl.src = response.thumb;
                        self.store.setMultiple(itemEl, {
                            id: response.id,
                            file: response.file,
                            filetype: response.filetype,
                            filesize: response.filesize,
                            imagesize: response.imagesize || '',
                            thumb: response.thumb,
                        });
                        itemEl.dataset.filetype = response.filetype;
                        itemEl.click();
                        self.dd.makeMovable(itemEl);
                    }

                    const fileCountEl = dialog.querySelector('.dd-media-file-count');
                    if (fileCountEl) {
                        const count = parseInt(fileCountEl.textContent, 10) || 0;
                        fileCountEl.textContent = count + 1;
                    }
                });

                this.on('complete', (file) => {
                    const itemEl = file.previewElement.querySelector('.dd-media-item');
                    if (itemEl) {
                        if (!file.type.match(/image\.*/)) {
                            itemEl.querySelector('.dd-media-thumb').style.display = 'none';
                            itemEl.querySelector('.dd-media-icon-file').style.display = '';
                            itemEl.querySelector('.dd-media-icon-folder').style.display = 'none';
                            itemEl.classList.add('no-thumb');
                        }

                        itemEl.querySelector('.dd-media-item-label').style.display = '';
                    }
                });
            },

            error: function (file, responseJson) {
                const previewEl = file.previewElement;
                if (previewEl) {
                    previewEl.classList.add('dz-error');
                    const errorEl = previewEl.querySelector('.dd-media-item-error');
                    if (errorEl) {
                        errorEl.style.display = '';
                        errorEl.textContent = ddh.translate(responseJson.error);
                    }
                }
            }
        });
    }

    _initFolderActions(dialog, folderId) {
        const folderActionBtn = dialog.querySelector('.dd-media-folder-action');
        const folderUpActionBtn = dialog.querySelector('.dd-media-folder-up-action');

        folderActionBtn.addEventListener('click', () => {
            ddh.prompt(ddh.translate('DD_MEDIA_ADD_FOLDER'), (val) => {
                this.fm.addFolder(val)
                    .then((res) => {
                        if (res.id) {
                            this.ui.addMediaItem({
                                id: res.id,
                                file: res.name,
                                filetype: 'directory',
                                filesize: 0,
                                thumb: null,
                                imagesize: ''
                            });

                            const mediaList = dialog.querySelector('.dd-media-list');
                            mediaList.classList.remove('empty');
                            const fileCountEl = dialog.querySelector('.dd-media-file-count');
                            fileCountEl.textContent = parseInt(fileCountEl.textContent, 10) + 1;
                        }
                    })
                    .catch((err) => {
                        ddh.alert(ddh.translate(err.responseJSON?.error || 'Error creating folder'));
                    });
            });
        });

        if (folderId) {
            folderActionBtn.style.display = 'none';
            folderUpActionBtn.addEventListener('click', () => {
                this.refreshMedia();
            });
        } else {
            folderUpActionBtn.disabled = true;
        }
    }

    _loadMoreMediaContent(page = 0) {
        this.fm.fetchMoreFiles(page, this.currentFolderId).then(data => {
            if (data.files?.length) {
                data.files.forEach(file => {
                    this.ui.addMediaItem({
                        id: file.id,
                        file: file.file,
                        filetype: file.filetype,
                        filesize: file.filesize,
                        thumb: file.thumb || false,
                        imagesize: file.imageSize || null
                    });
                });
            }
            if (data.more) {
                this._loadMoreMediaContent(page + 1);
            }
        });
    }
}
