/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
import '../../scss/base.scss'
import AltAttributeManager from "./altAttribute.js";

export default class UIRenderer {
    constructor(fileManager, dragDropHandler, dataStore) {
        this.fm = fileManager;
        this.dd = dragDropHandler;
        this.store = dataStore;
        this.altAttributeManager = new AltAttributeManager();
    }

    showItemDetails(file, dialog = document.querySelector('.dd-media')?.closest('.modal')) {
        if (!dialog) return;

        const form = dialog.querySelector('.dd-media-details-form');
        if (!form) return;

        if (!file) {
            form.style.display = 'none';
            this.altAttributeManager.altTexts = {};
            return;
        }

        const previewIcon = form.querySelector('.dd-media-details-preview-icon');
        const dirIcon = form.querySelector('.dd-media-details-dir-icon');
        const preview = form.querySelector('.dd-media-details-preview');
        const urlEl = form.querySelector('.dd-media-url');
        const nameEl = form.querySelector('.dd-media-details-name');
        const infosEl = form.querySelector('.dd-media-details-infos');
        const idEl = form.querySelector('.dd-media-details-id');
        const inputUrl = form.querySelector('.dd-media-details-input-url');
        const linkUrl = form.querySelector('.dd-media-details-link-url');

        if (file.preview) {
            previewIcon.style.display = 'none';
            dirIcon.style.display = 'none';
            preview.src = file.preview;
            preview.style.display = '';
            urlEl.style.display = '';
        } else {
            if (file.filetype === 'directory') {
                dirIcon.style.display = '';
                previewIcon.style.display = 'none';
                urlEl.style.display = 'none';
            } else {
                dirIcon.style.display = 'none';
                previewIcon.style.display = '';
                urlEl.style.display = '';
            }
            preview.style.display = 'none';
        }

        const fileInfo = file.imagesize
            ? `${file.imagesize} | ${this.fm.formatFileSize(file.filesize)}`
            : '';

        nameEl.textContent = file.file;
        infosEl.textContent = fileInfo;
        idEl.textContent = file.id;
        inputUrl.value = file.url;
        linkUrl.setAttribute('href', file.url);
        form.style.display = '';

        if(file.filetype !== 'directory') {
            form.querySelector('#alt-attribute-wrapper').classList.replace('d-none', 'd-block');
            form.dataset.mediaId = file.id;
            form.dataset.mediaType = 'file';
            this.altAttributeManager.loadAltTexts(file.id, form, this.fm.actionLink);
            this.altAttributeManager.bindAltTextEvents(form, this.fm.actionLink);
        } else {
            form.querySelector('#alt-attribute-wrapper').classList.replace('d-block', 'd-none');
            this.altAttributeManager.altTexts = {};
        }
    }

    addMediaItem({ id, file, filetype, filesize, thumb, imagesize }) {
        const template = document.querySelector('.dd-media-list-items .dd-media-dz-helper > div');
        const wrap = template.cloneNode(true);
        const item = wrap.querySelector('.dd-media-item');
        this.store.setMultiple(item, { id, file, filetype, filesize, imagesize });
        item.dataset.filetype = filetype;

        const thumbEl = wrap.querySelector('.dd-media-thumb');
        const iconFile = wrap.querySelector('.dd-media-icon-file');
        const iconFolder = wrap.querySelector('.dd-media-icon-folder');

        if (!thumb) {
            thumbEl.style.display = 'none';
            if (filetype === 'directory') {
                iconFile.style.display = 'none';
                iconFolder.style.display = '';
            } else {
                iconFile.style.display = '';
                iconFolder.style.display = 'none';
            }
            item.classList.add('no-thumb');
        } else {
            thumbEl.src = thumb;
            thumbEl.style.display = '';
            item.classList.remove('no-thumb');
        }

        const label = item.querySelector('.dd-media-item-label');
        label.style.display = '';
        const span = label.querySelector('span');
        span.textContent = file;
        const container = document.querySelector('.dd-media-list-items > .row');
        container.appendChild(wrap);

        this.dd.makeMovable(item);
    }

    updateMediaState(dialog) {
        const list = dialog.querySelector('.dd-media-list-items > .row');
        const items = list.querySelectorAll('.dd-media-item');
        const hasItems = items.length > 0;

        const noFiles = dialog.querySelector('.dd-media-no-files');
        noFiles.style.display = hasItems ? 'none' : '';
        items.forEach(el => {
            this.dd.makeMovable(el);
        });
    }
}
