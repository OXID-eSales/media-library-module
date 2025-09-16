/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import '../../scss/base.scss'
import { Modal } from "bootstrap";
import { ddh } from './helper.js';

import FileManager from './fileManager.js';
import UIRenderer from './uiRenderer.js';
import DragDropHandler from './dragDropHandler.js'
import MediaContent from "./mediaContent.js";
import DataStore from "./dataStore.js";

class MediaLibraryClass {
    static VERSION = '1.0.0';

    ctrlKeyPressed = false;
    currentPath = '';
    currentFolderId = '';

    constructor() {
        this.store = new DataStore();
        this.fm = new FileManager();
        this.dd = new DragDropHandler(this.fm, this.store);
        this.ui = new UIRenderer(this.fm, this.dd, this.store);
        this.mediaContent = new MediaContent(this.fm, this.ui, this.dd, this.store);

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

    /**
     * Opens the media library dialog.
     *
     * Usage:
     *   // With options
     *   MediaLibrary.open({ filter: 'images', multiple: true }, (param) => {
     *       // callback
     *   });
     *
     *   // Without options
     *   MediaLibrary.open((param) => {
     *       // callback
     *   });
     *
     * @param {Object} [options={}] - Configuration options.
     * @param {string|null} [options.filter=null] - Optional filter (e.g. 'images', 'videos').
     * @param {boolean} [options.multiple=false] - Allow selecting multiple items.
     * @param {Function} callback - Function called when an item (or items) is selected.
     */
    open(options = {}, callback) {
        if (typeof options === 'function') {
            callback = options;
            options = {};
        }

        let { filter = null, multiple = false } = options || {};

        const actions = [{
            label: ddh.translate('DD_CANCEL'),
            attributes: { 'data-bs-dismiss': 'modal' }
        }, {
            label: ddh.translate('DD_APPLY'),
            css: ['btn btn-primary dd-media-submit'],
            action: (dialog) => {
                this._getApplyAction(dialog, filter, multiple, callback);
                Modal.getInstance(dialog).hide();
            }
        }];

        if (multiple) {
            actions.unshift({
                html: `<span class="text-muted" style="font-style: italic; margin-right: 15px;">${ddh.translate('DD_MEDIA_MULTIPLE_INFO')}</span>`,
                css: ['dd-media-multiple-info']
            });
        }

        const dialog = ddh._dialog({
            title: ddh.translate('DD_MEDIA_DIALOG'),
            message: '<div class="dd-dialog-loader"></div>',
            buttons: actions,
            size: 'lg',
            backdrop: true,
        });

        this.store.set(dialog, 'multiple', multiple);
        this.store.set(dialog, 'filter', filter);
        this.mediaContent.loadMediaContent(dialog);
    }

    /**
     * Initializes the media library and overlay.
     *
     * Usage:
     *   // With options
     *   MediaLibrary.init({ filter: 'images', multiple: true }, (param) => {
     *       // callback
     *   });
     *
     *   // Without options
     *   MediaLibrary.init((param) => {
     *       // callback
     *   });
     *
     * @param {Object} [options={}] - Configuration options.
     * @param {RegExp|string|null} [options.filter=null] - Optional filter for media MIME type.
     * @param {boolean} [options.multiple=false] - Allow selecting multiple media items.
     * @param {Function} [callback] - Function called when media is applied.
     */
    init(options = {}, callback) {
        if (typeof options === 'function') {
            callback = options;
            options = {};
        }

        const { filter = null, multiple = false } = options;
        const ui = this;
        const dialog = document.querySelector('.dd-media-wrapper');

        this.store.set(dialog, 'multiple', multiple);
        this.store.set(dialog, 'filter', filter);

        // Communicate with Overlay
        if (top.basefrm && top.basefrm.OverlayInstance) {
            top.basefrm.OverlayInstance.onContentLoad(function () {
                const self = this;
                const overlay = self.$overlay[0];
                const existingApply = overlay.querySelector('.dd-overlay-dialog-footer .dd-overlay-dialog-apply');
                if (existingApply) {
                    existingApply.remove();
                }

                if (typeof callback !== 'function' && self.overlayContext) {
                    callback = function (id, file, fullpath) {
                        self.overlayContext.invoke('editor.insertImage', fullpath, function ($image) {
                            top.basefrm.mediaUrls[id] = fullpath;
                            $image.css('max-width', '100%');
                            $image.attr('src', fullpath);
                            $image.attr('data-source', 'media');
                            $image.attr('data-id', id);
                            $image.addClass('dd-wysiwyg-media-image');
                        });
                    };
                }

                // Create and insert apply button
                const applyButton = document.createElement('button');
                applyButton.type = 'button';
                applyButton.className = 'dd-overlay-dialog-button dd-overlay-dialog-apply';
                applyButton.textContent = ddh.translate('DD_APPLY');

                applyButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    ui._getApplyAction(dialog, filter, multiple, callback);
                    self.hideOverlay();
                });

                const footer = overlay.querySelector('.dd-overlay-dialog-footer');
                if (footer) {
                    footer.prepend(applyButton);
                }
            });
        }

        this.mediaContent.loadMediaContent(dialog);
    };

    refreshMedia(id) {
        this.mediaContent.refreshMedia(id);
    }

    addMediaItem(id, file, filetype, filesize, thumb, imagesize) {
        this.ui.addMediaItem({ id, file, filetype, filesize, thumb: (thumb || false), imagesize: (imagesize || null) });
    }

    _getApplyAction(dialogEl, filter, multiple, callback) {
        const items = dialogEl.querySelectorAll('.dd-media-item.active');
        const mediaEl = dialogEl.querySelector('.dd-media');
        const folderName = mediaEl?.dataset.foldername || '';
        const resourceLink = this.fm.resourceLink;

        if (!items.length) return [];

        let blTypeNotAllowed = false;
        const files = [];

        items.forEach((el) => {
            const filetype = this.store.get(el, 'filetype');

            if (
                filter !== null &&
                ((typeof filter === 'string' && filter !== filetype) ||
                    (filter instanceof RegExp && !filetype?.match(filter)))
            ) {
                blTypeNotAllowed = true;
            } else {
                files.push({
                    id: this.store.get(el, 'id'),
                    file: (folderName ? folderName + '/' : '') + this.store.get(el, 'file'),
                    url: resourceLink + this.store.get(el, 'file'),
                    type: filetype
                });
            }
        });

        if (blTypeNotAllowed) {
            ddh.alert(ddh.translate('DD_MEDIA_FILETYPE_NOT_ALLOWED'));
            return [];
        }

        if (typeof callback === 'function') {
            if (multiple) {
                callback.call(dialogEl, files);
            } else {
                if (files.length) {
                    const item = files[0];
                    callback.call(dialogEl, item.id, item.file, item.url, item.type);
                } else {
                    callback.call(dialogEl, false);
                }
            }
        }

        return files;
    }
}

export const MediaLibrary = new MediaLibraryClass();
window.MediaLibrary = MediaLibrary;
export { ddh };
