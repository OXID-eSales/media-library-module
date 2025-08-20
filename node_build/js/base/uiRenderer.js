/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
import '../../scss/base.scss'

export default class UIRenderer {
    constructor(fileManager, dragDropHandler) {
        this.fm = fileManager;
        this.dd = dragDropHandler;
    }

    showItemDetails(file, $dialog = $('.dd-media').first().closest('.modal')) {
        const $form = $('.dd-media-details-form', $dialog);
        if (!file) { $form.hide(); return; }

        if (file.preview) {
            $('.dd-media-details-preview-icon', $form).hide();
            $('.dd-media-details-dir-icon', $form).hide();
            $('.dd-media-details-preview', $form).attr('src', file.preview).show();
            $('.dd-media-url', $form).show();
        } else {
            if (file.filetype === 'directory') {
                $('.dd-media-details-dir-icon', $form).show();
                $('.dd-media-details-preview-icon', $form).hide();
                $('.dd-media-url', $form).hide();
            } else {
                $('.dd-media-details-dir-icon', $form).hide();
                $('.dd-media-details-preview-icon', $form).show();
                $('.dd-media-url', $form).show();
            }
            $('.dd-media-details-preview', $form).hide();
        }

        const fileInfo = file.imagesize
            ? `${file.imagesize} | ${this.fm.formatFileSize(file.filesize)}`
            : '';

        $('.dd-media-details-name', $form).text(file.file);
        $('.dd-media-details-infos', $form).text(fileInfo);
        $('.dd-media-details-id', $form).text(file.id);

        $('.dd-media-details-input-url', $form).val(file.url);
        $('.dd-media-details-link-url', $form).attr('href', file.url);

        $form.show();
    }

    addMediaItem({ id, file, filetype, filesize, thumb, imagesize }) {
        const $wrap = $('.dd-media-list-items .dd-media-dz-helper > div').clone();

        $('.dd-media-item', $wrap).data({ id, file, filetype, filesize, imagesize });

        if (!thumb) {
            $('.dd-media-thumb', $wrap).hide();
            if (filetype === 'directory') {
                $('.dd-media-icon-file', $wrap).hide();
                $('.dd-media-icon-folder', $wrap).show();
            } else {
                $('.dd-media-icon-file', $wrap).show();
                $('.dd-media-icon-folder', $wrap).hide();
            }
            $('.dd-media-item', $wrap).addClass('no-thumb');
        } else {
            $('.dd-media-thumb', $wrap).attr('src', thumb);
            $('.dd-media-item', $wrap).removeClass('no-thumb');
        }

        $('.dd-media-item-label', $wrap).show().find('span').text(file);
        $('.dd-media-list-items > .row').append($wrap);

        this.dd.makeMovable($('.dd-media-item', $wrap));
    }

    updateMediaState($dialog) {
        const $list = $('.dd-media-list-items > .row', $dialog);
        const hasItems = $('.dd-media-item', $list).length > 0;
        $('.dd-media-no-files', $dialog).toggle(!hasItems);

        $('.dd-media-item', $list).each((_, el) => {
            this.dd.makeMovable($(el));
        });
    }
}
