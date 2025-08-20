/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import '../../scss/base.scss'

import { ddh } from './helper.js';
import {
    extractMediaPath,
    filterMediaList,
    updateFileCount
} from './mediaUtils.js';
import Dropzone from "dropzone";

import FileManager from './fileManager.js';
import UIRenderer from './uiRenderer.js';
import DragDropHandler from './dragDropHandler.js'

class MediaLibraryClass {
    static VERSION = '1.0.0';

    ctrlKeyPressed = false;
    currentPath = '';
    currentFolderId = '';

    constructor() {
        this.fm = new FileManager();
        this.dd = new DragDropHandler(this.fm);
        this.ui = new UIRenderer(this.fm, this.dd);

        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.keyCode === 224 || e.keyCode === 91 || e.keyCode === 93) {
                this.ctrlKeyPressed = true;
            }
        });

        document.addEventListener('keyup', (e) => {
            if (e.ctrlKey || e.keyCode === 224 || e.keyCode === 91 || e.keyCode === 93) {
                this.ctrlKeyPressed = false;
            }
        });
    }

    setActionLink(url) {
        this.fm.setActionLink(url);
    }

    setResourceLink(url) {
        this.fm.setResourceLink(url);
    }

    _loadItemDetails(file, $dialog) {
        if (typeof file === 'undefined') {
            file = false;
        }

        if (typeof $dialog === 'undefined') {
            $dialog = $('.dd-media').first().closest('.modal');
        }

        this.ui.showItemDetails(file, $dialog);
    };

    /**
     * @param options
     * @param callback
     */
    open(options = {}, callback) {
        let { filter = null, multiple = false, appendToHtml = false } = options || {};

        const actions = [{
            label: ddh.translate('DD_CANCEL'),
            attributes: { 'data-dismiss': 'modal' }
        }, {
            label: ddh.translate('DD_APPLY'),
            css: ['btn btn-primary dd-media-submit'],
            action: ($dialog) => { this.getApplyAction($dialog, filter, multiple, callback); $dialog.modal('hide'); }
        }];

        if (multiple) {
            actions.unshift({
                html: `<span class="text-muted" style="font-style: italic; margin-right: 15px;">${ddh.translate('DD_MEDIA_MULTIPLE_INFO')}</span>`,
                css: ['dd-media-multiple-info']
            });
        }

        const $dialog = ddh._dialog({
            title: ddh.translate('DD_MEDIA_DIALOG'),
            message: '<div class="dd-dialog-loader"></div>',
            buttons: actions,
            size: 'lg',
            backdrop: true,
            appendToHtml
        });

        $dialog.data('media-options', { multiple, filter });
        this._loadMediaContent($dialog);
    }

    /**
     * @param options
     */
    init(options = {}) {
        const { filter = null, multiple = false } = options;
        const $dialog = $('.dd-media-wrapper');
        $dialog.data('media-options', { multiple, filter });
        this._loadMediaContent($dialog);
    }

    refreshMedia(id) {
        const $media = $('.dd-media');
        if (!$media.length) {
            return;
        }

        let $dialog = $media.closest('.modal');
        let tab = $('.dd-media-tabs .tab-pane.active').attr('id');

        if ($dialog.length) {
            $('.modal-body', $dialog).html('<div class="dd-dialog-loader"></div>');
        } else {
            $dialog = $('.dd-media-wrapper');
            $('.dd-content', $dialog).html('<div class="dd-dialog-loader"></div>');
        }

        this._loadMediaContent($dialog, id, tab);
    }

    addMediaItem(id, file, filetype, filesize, thumb, imagesize) {
        this.ui.addMediaItem({ id, file, filetype, filesize, thumb: (thumb || false), imagesize: (imagesize || null) });
    }

    _loadMediaContent($dialog, folderId, tab) {
        console.log(folderId)
        this.fm.loadMediaContent(folderId, tab).done((html) => {
            if ($dialog.is('.dd-media-wrapper')) {
                $('.dd-content', $dialog).html(html);
            } else {
                $('.modal-body', $dialog).html(html);
            }

            this._updateMediaState($dialog);
            this._initFolderActions($dialog, folderId);
            this._initRemoveAction($dialog);
            this._initRenameAction($dialog);
            this._initItemClickHandlers($dialog);
            this._initDropzone($dialog);
            this._initSearch($dialog);
            this._loadMoreMediaContent(0);
        });
    };

    _updateMediaState($dialog) {
        this.currentFolderId = $('.dd-media', $dialog).data('folderid');

        const resourceLink = $('.dd-media', $dialog).data('medialink');
        this.setResourceLink(resourceLink);

        this.currentPath = extractMediaPath(resourceLink);

        $('.dd-media-item[data-id]', $dialog).each(function () {
            this.ui.updateMediaState($(this));
        });

        $('.dd-media-remove-action, .dd-media-move-action, .dd-media-rename-action', $dialog).prop('disabled', true);
    }

    _initSearch($dialog) {
        $('.dd-media-search-form').on('submit', function (e) {
            e.preventDefault();
            return false;
        });

        $('.dd-media-search-form input').on('keyup', function (e) {
            e.preventDefault();
            const val = $(e.target).val();
            filterMediaList($dialog, val);
        });
    }

    _initItemClickHandlers($dialog) {
        const mediaOptions = $dialog.data('media-options');

        $('.dd-media', $dialog).on('click', '.dd-media-item', (e) => {
            e.preventDefault();

            const $target = $(e.currentTarget);
            if ($target.parent('.dz-error').length) {
                return;
            }

            if (mediaOptions && mediaOptions.multiple && this.ctrlKeyPressed) {
                $target.toggleClass('active');
            } else {
                $('.dd-media-item', $dialog).removeClass('active');
                $target.addClass('active');
            }

            let $detailsItem = null;

            if ($target.hasClass('active')) {
                $detailsItem = $target;
            } else {
                $detailsItem = $target.parent().siblings().find('.dd-media-item.active').first();
            }

            if ($detailsItem && $detailsItem.length) {
                const itemData = $detailsItem.data();

                itemData.url = this.fm.resourceLink + $detailsItem.data('file');
                itemData.type = itemData.filetype;
                itemData.size = itemData.filesize;

                if ($('.dd-media-thumb', $detailsItem).length) {
                    itemData.preview = $('.dd-media-thumb', $detailsItem).attr('src');
                }

                this._loadItemDetails(itemData, $dialog);
            }

            const $activeItems = $('.dd-media-list-items > .row > .dd-media-col > .active', $dialog);

            if (!$activeItems.length) {
                this._loadItemDetails(false, $dialog);
                $('.dd-media-remove-action, .dd-media-move-action, .dd-media-rename-action').prop('disabled', true);
            } else {
                if ($activeItems.length > 1) {
                    $('.dd-media-remove-action, .dd-media-move-action').prop('disabled', false);
                    $('.dd-media-rename-action').prop('disabled', true);
                } else {
                    $('.dd-media-remove-action, .dd-media-move-action, .dd-media-rename-action').prop('disabled', false);
                }
            }
        })
        .on('dblclick', '.dd-media-item', (e) => {
            e.preventDefault();

            const $target = $(e.currentTarget);

            if ($target.data('filetype') === 'directory') {
                // open folder
                this.refreshMedia($target.data('id'));
            } else {
                $('.dd-media-submit', $dialog).trigger('click');
            }
        });
    }

    _initRenameAction($dialog) {
        $('.dd-media-rename-action', $dialog).on('click', (e) => {
            const $item = $('.dd-media-item.active', $dialog);
            const $btn = e.currentTarget;

            if (!$($btn).prop('disabled') && $item.length === 1) {
                ddh.prompt(
                    ddh.translate('DD_MEDIA_RENAME_FILE_FOLDER'),
                    (val) => {
                        const activeItem = $('.dd-media-item.active', $dialog);

                        if (val !== activeItem.data('file')) {
                            this.fm.renameFile(activeItem.data('id'), val)
                                .done((resultJson) => {
                                    activeItem.data('file', resultJson.name);
                                    activeItem.data('id', resultJson.id);

                                    activeItem.data('url', this.fm.resourceLink + resultJson.name);

                                    $('.dd-media-item-label span', activeItem)
                                        .text(resultJson.name);

                                    this._loadItemDetails(activeItem.data(), $dialog);
                                })
                                .fail((result) => {
                                    ddh.alert(ddh.translate(result.responseJSON.error));
                                });
                        }
                    },
                    undefined,
                    $item.data('file')
                );
            }
        });
    }

    _initRemoveAction($dialog) {
        $('.dd-media-remove-action', $dialog).on('click', (e) => {
            const $item = $('.dd-media-item.active', $dialog);
            const $btn = $(e.currentTarget);

            if (!$btn.prop('disabled') && $item.length) {
                let sConfirmMsg = 'DD_MEDIA_REMOVE_CONFIRM';

                if ($item.length > 1) {
                    sConfirmMsg = 'DD_MEDIA_REMOVE_MANY_CONFIRM';
                } else if ($item.data('filetype') === 'directory') {
                    sConfirmMsg = 'DD_MEDIA_REMOVE_FOLDER_CONFIRM';
                }

                ddh.confirm(ddh.translate(sConfirmMsg), () => {
                    $item.addClass('dd-media-item-removing');

                    const deleteIDs = $item.map(function () {
                        return $(this).data('id');
                    }).get();

                    const folderId = $('.dd-media', $dialog).data('folderid');

                    this.fm.removeFiles(deleteIDs, folderId).done((response) => {
                        if (response.success) {
                            updateFileCount($dialog, -$item.length);

                            $item.each(function () {
                                $(this).parent().remove();
                            });

                            $btn.prop('disabled', true);

                            if (!$('.dd-media-list-items > .row > .dd-media-col', $dialog).length) {
                                $('.dd-media-list', $dialog).addClass('empty');
                            }

                            $('.dd-media-details-form', $dialog).hide();
                        } else if (response.msg) {
                            $item.each(function () {
                                $(this).removeClass('dd-media-item-removing');
                            });

                            ddh.alert(ddh.translate(response.msg));
                        }
                    });
                }, null, true);
            }
        });
    }

    _initDropzone($dialog) {
        Dropzone.autoDiscover = false;

        const folderId = $('.dd-media', $dialog).data('folderid');
        const uploadUrl = this.fm.uploadUrl(folderId);
        const self = this;

        $('.dd-media', $dialog).dropzone({
            url: uploadUrl,
            parallelUploads: 10,
            previewsContainer: $('.dd-media-list-items > .row', $dialog)[0],
            previewTemplate: $('.dd-media-list-items .dd-media-dz-helper', $dialog).html(),
            clickable: $('.dd-media-upload', $dialog)[0],
            hiddenInputContainer: $('.dd-media', $dialog)[0],

            init: function () {
                this.on('addedfile', function () {
                    $('.dd-media-list', $dialog).removeClass('empty');
                    $('.dd-media-tabs .nav-tabs button[data-bs-target="#mediaList"]', $dialog).tab('show');
                    $('.dd-media-list-items', $dialog)
                        .scrollTop($('.dd-media-list-items > .row', $dialog).height());
                });

                this.on('success', (file, response) => {
                    $('.dd-media-item', file.previewElement).find('.dd-media-thumb').attr('src', response.thumb);
                    $('.dd-media-item', file.previewElement).data({
                        id: response.id,
                        file: response.file,
                        filetype: response.filetype,
                        filesize: response.filesize,
                        imagesize: response.imagesize || null,
                        thumb: response.thumb
                    }).trigger('click');

                    self.dd.makeMovable($('.dd-media-item', file.previewElement));
                    updateFileCount($dialog, +1);
                });

                this.on('complete', function (file) {
                    if (!file.type.match(/image\.*/)) {
                        $('.dd-media-thumb', file.previewElement).hide();
                        $('.dd-media-icon-file', file.previewElement).show();
                        $('.dd-media-icon-folder', file.previewElement).hide();
                        $('.dd-media-item', file.previewElement).addClass('no-thumb');
                    }
                    $('.dd-media-item-label', file.previewElement).show();
                });
            },

            error: function (file, responseJson) {
                $(file.previewElement)
                    .addClass('dz-error')
                    .find('.dd-media-item-error')
                    .show()
                    .text(ddh.translate(responseJson.error));
            }
        });
    }

    _initFolderActions($dialog, folderId) {
        $('.dd-media-folder-action', $dialog).on('click', () => {
            ddh.prompt(ddh.translate('DD_MEDIA_ADD_FOLDER'), (val) => {
                if (!val) return;

                this.fm.addFolder(val)
                    .done((res) => {
                        if (res.id) {
                            this.ui.addMediaItem({
                                id: res.id,
                                file: res.name,
                                filetype: 'directory',
                                filesize: 0,
                                thumb: null,
                                imagesize: ''
                            });
                            $('.dd-media-list', $dialog).removeClass('empty');
                            updateFileCount($dialog, +1);
                        }
                    })
                    .fail((err) => {
                        ddh.alert(ddh.translate(err.responseJSON?.error || 'Error creating folder'));
                    });
            });
        });

        if (folderId) {
            $('.dd-media-folder-action').hide();
            $('.dd-media-folder-up-action', $dialog).on('click', () => {
                this.refreshMedia();
            });
        } else {
            $('.dd-media-folder-up-action', $dialog).prop('disabled', true);
        }
    }

    _loadMoreMediaContent(page) {
        const startPage = page ?? 0;

        this.fm.fetchMoreFiles(startPage, this.currentFolderId).then(data => {
            if (data.files && data.files.length) {
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
                this._loadMoreMediaContent(startPage + 1);
            }
        });
    }

    getApplyAction($dialog, filter, multiple, callback) {
        const $items = $('.dd-media-item.active', $dialog);
        const folderName = $('.dd-media', $dialog).data('foldername');
        const resourceLink = this.fm.resourceLink;

        if (!$items.length || typeof callback !== 'function') {
            return;
        }

        let blTypeNotAllowed = false;
        const files = [];

        $items.each((_, el) => {
            const $el = $(el);
            const filetype = $el.data('filetype');

            if (
                filter !== null &&
                ((typeof filter === 'string' && filter !== filetype) ||
                    (filter instanceof RegExp && !filetype?.match(filter)))
            ) {
                blTypeNotAllowed = true;
            } else {
                files.push({
                    id: $el.data('id'),
                    file: (folderName ? folderName + '/' : '') + $el.data('file'),
                    url: resourceLink + $el.data('file'),
                    type: filetype
                });
            }
        });

        if (blTypeNotAllowed) {
            ddh.alert(ddh.translate('DD_MEDIA_FILETYPE_NOT_ALLOWED'));
            return;
        }

        if (multiple) {
            callback.call($dialog, files);
        } else {
            if (files.length) {
                var item = files[0];
                callback.call($dialog, item.id, item.file, item.url, item.type);
            } else {
                callback.call($dialog, false);
            }
        }
    }
}

export const MediaLibrary = new MediaLibraryClass();
window.MediaLibrary = MediaLibrary;
export { ddh };
