/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

+function ($) {
    'use strict';

    // MEDIA LIBRARY CLASS DEFINITION
    // ==============================

    var MediaLibrary = function () {
        var self = this;

        $(document).on('keydown', function (e) {
            if (e.ctrlKey || e.keyCode === 224 || e.keyCode === 91 || e.keyCode === 93) {
                self.ctrlKeyPressed = true;
            }
        }).on('keyup', function (e) {
            if (e.ctrlKey || e.keyCode === 224 || e.keyCode === 91 || e.keyCode === 93) {
                self.ctrlKeyPressed = false;
            }
        });

        // Create pseudo helper if not exists
        if (typeof ddh == 'undefined') {
            window.ddh = {
                translate: function (string) {
                    if (string && typeof i18n === 'object') {
                        if (i18n[string]) {
                            return i18n[string];
                        }
                    }

                    return string;
                }
            }
        }
    };


    // MEDIA LIBRARY MAIN PROPERTIES
    // =============================

    MediaLibrary.VERSION = '1.0.0';

    MediaLibrary.prototype.ctrlKeyPressed = false;
    MediaLibrary.prototype.currentPath = '';
    MediaLibrary.prototype.currentFolderId = '';

    MediaLibrary.prototype._actionLink = '';
    MediaLibrary.prototype._resourceLink = '';


    // MEDIA LIBRARY METHODS
    // =====================

    MediaLibrary.prototype.setActionLink = function (url) {
        this._actionLink = decodeURI(url);
    };

    MediaLibrary.prototype.setResourceLink = function (url) {
        this._resourceLink = decodeURI(url);
    };

    MediaLibrary.prototype._loadItemDetails = function (file, $dialog) {
        var ui = this;

        if (typeof file === 'undefined') {
            file = false;
        }

        if (typeof $dialog === 'undefined') {
            $dialog = $('.dd-media').first().closest('.modal');
        }

        var $detailForm = $('.dd-media-details-form', $dialog);

        if (!file) {
            $detailForm.hide();
        } else {
            if (file.preview) {
                $('.dd-media-details-preview-icon', $detailForm).hide();
                $('.dd-media-details-dir-icon', $detailForm).hide();
                $('.dd-media-details-preview', $detailForm).attr('src', file.preview).show();
                $('.dd-media-url', $detailForm).show();
            } else {
                if (file.filetype == "directory") {
                    $('.dd-media-details-dir-icon', $detailForm).show();
                    $('.dd-media-details-preview-icon', $detailForm).hide();
                    $('.dd-media-url', $detailForm).hide();
                } else {
                    $('.dd-media-details-dir-icon', $detailForm).hide();
                    $('.dd-media-details-preview-icon', $detailForm).show();
                    $('.dd-media-url', $detailForm).show();
                }
                $('.dd-media-details-preview', $detailForm).hide();
            }

            var fileInfo = file.imagesize ? (file.imagesize ? file.imagesize + ' | ' : '') + ui._formatFileSize(file.filesize) : '';

            $('.dd-media-details-name', $detailForm).text(file.file);
            $('.dd-media-details-infos', $detailForm).text(fileInfo);

            $('.dd-media-details-input-url', $detailForm).val(file.url);
            $('.dd-media-details-link-url', $detailForm).attr('href', file.url);

            $detailForm.show();
        }
    };


    MediaLibrary.prototype._formatFileSize = function (size) {
        size = parseInt(size);

        var names = ['tb', 'gb', 'mb', 'kb', 'b'];

        while (size > 1024 && names.length) {
            size = Math.round((size / 1024) * 100) / 100;
            names.pop();
        }

        return size + ' ' + names.pop();
    };

    MediaLibrary.prototype._makeItemMovable = function ($item) {
        var actionLink = this._actionLink;

        //TODO: moving file to parent folder
        if ($item.data('filetype') != 'directory') {
            $($item).draggable({
                revert: "invalid", helper: function (e) {
                    var original = $(e.target).hasClass("ui-draggable") ? $(e.target) : $(e.target).closest(".ui-draggable");
                    return original.clone().css({
                        width: original.width(), // or outerWidth*
                        height: original.height() // or outerHeight*
                    });
                }, zIndex: 100, opacity: 0.70, start: function (e, ui) {
                    $(ui.helper).addClass("ui-draggable-helper");
                }
            });
        } else if ($item.data('filetype') == 'directory') {
            $($item).droppable({
                hoverClass: "ui-state-hover", drop: function (event, ui) {
                    if (ui.draggable.length) {
                        var $item = $(ui.draggable);
                        var fileId = $item.data('id');
                        var file = $item.data('file');

                        var folderId = $(this).data('id');
                        var folder = $(this).data('file');
                        var thumb = $item.data('thumb');

                        if (fileId && folderId) {
                            $.post(actionLink + 'cl=ddoemedia_view&fnc=movefile', {
                                sourceid: fileId, targetid: folderId, file: file, folder: folder, thumb: thumb
                            }, function (_res) {
                                if (_res.success) {
                                    $item.parent().remove();
                                } else {
                                    if (_res.msg) {
                                        ddh.alert(ddh.translate(_res.msg));
                                    }
                                }
                            });
                        }

                    }
                }
            });
        }
    };


    /**
     * Usage:
     * MediaLibrary.open( [ filter ], [ multiple ], callback );
     *
     * @param callback
     */
    MediaLibrary.prototype.open = function (callback) {
        var actionLink = this._actionLink;
        var filter = null, multiple = false;
        var ui = this;

        if (arguments.length === 2) {
            if (typeof arguments[0] === 'string' || arguments[0] instanceof RegExp) {
                filter = arguments[0];
            } else {
                if (typeof arguments[0] === 'boolean') {
                    multiple = arguments[0];
                }
            }

            callback = arguments[1];
        } else {
            if (arguments.length === 3) {
                if (typeof arguments[0] === 'string' || arguments[0] instanceof RegExp) {
                    filter = arguments[0];
                    multiple = arguments[1];
                } else {
                    if (typeof arguments[0] === 'boolean') {
                        multiple = arguments[0];
                        filter = arguments[1];
                    }
                }

                callback = arguments[2];
            }
        }

        var actions = [{
            label: ddh.translate('DD_CANCEL'), attributes: {
                'data-dismiss': 'modal'
            }
        }, {
            label: ddh.translate('DD_APPLY'), css: ['btn btn-primary dd-media-submit'], action: function ($dialog) {
                var $item = $('.dd-media-item.active', $dialog);
                var foldername = $('.dd-media', $dialog).data('foldername');

                if (!$item.length) {
                    return;
                }

                if (typeof callback === 'function') {
                    var blTypeNotAllowed = false;
                    var files = [];

                    $item.each(function () {
                        var filetype = $(this).data('filetype');

                        if (filter !== null && ((typeof filter === 'string' && filter !== filetype) || (filter instanceof RegExp && !filetype.match(filter)))) {
                            blTypeNotAllowed = true;
                        } else {
                            files.push({
                                id: $(this).data('id'),
                                file: (foldername ? foldername + '/' : '') + $(this).data('file'),
                                url: ui._resourceLink + $(this).data('file'),
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

                $dialog.modal('hide');
            }
        }];

        if (multiple) {
            actions.unshift({
                html: '<span class="text-muted" style="font-style: italic; margin-right: 15px;">' + ddh.translate('DD_MEDIA_MULTIPLE_INFO') + '</span>',
                css: ['dd-media-multiple-info']
            });
        }

        var $dialog = ddh._dialog({
            title: ddh.translate('DD_MEDIA_DIALOG'),
            message: '<div class="dd-dialog-loader"></div>',
            buttons: actions,
            size: 'lg',
            backdrop: true
        });

        $dialog.data('media-options', {
            multiple: multiple, filter: filter
        });

        this._loadMediaContent($dialog);
    };

    /**
     * Usage:
     * MediaLibrary.init( [ filter ], [ multiple ], callback );
     *
     * @param callback
     */
    MediaLibrary.prototype.init = function (callback) {
        var actionLink = this._actionLink;
        var resourceLink = this._resourceLink;
        var filter = null, multiple = false;
        var ui = this;

        if (arguments.length === 2) {
            if (typeof arguments[0] === 'string' || arguments[0] instanceof RegExp) {
                filter = arguments[0];
            } else {
                if (typeof arguments[0] === 'boolean') {
                    multiple = arguments[0];
                }
            }

            callback = arguments[1];
        } else {
            if (arguments.length === 3) {
                if (typeof arguments[0] === 'string' || arguments[0] instanceof RegExp) {
                    filter = arguments[0];
                    multiple = arguments[1];
                } else {
                    if (typeof arguments[0] === 'boolean') {
                        multiple = arguments[0];
                        filter = arguments[1];
                    }
                }

                callback = arguments[2];
            }
        }

        var $dialog = $('.dd-media-wrapper');

        $dialog.data('media-options', {
            multiple: multiple, filter: filter
        });

        // Communicate with Overlay
        if (top.basefrm && top.basefrm.OverlayInstance) {
            top.basefrm.OverlayInstance.onContentLoad(function () {
                var self = this;

                if ($('.dd-overlay-dialog-footer .dd-overlay-dialog-apply', self.$overlay).length) {
                    $('.dd-overlay-dialog-footer .dd-overlay-dialog-apply', self.$overlay).remove();
                }

                if (typeof callback !== 'function' && self.overlayContext) {
                    callback = function (id, file, fullpath) {
                        self.overlayContext.invoke('editor.insertImage', fullpath, function ($image) {
                            $image.css('max-width', '100%');
                            $image.attr('data-filename', file);
                            $image.attr('data-filepath', fullpath);
                            $image.attr('data-source', 'media');
                            $image.addClass('dd-wysiwyg-media-image');
                        });
                    };
                }

                var $applyAction = $('<button type="button" class="dd-overlay-dialog-button dd-overlay-dialog-apply">' + ddh.translate('DD_APPLY') + '</button>');

                $applyAction.on('click', function (e) {
                    e.preventDefault();

                    var $item = $('.dd-media-item.active', $dialog);

                    if (!$item.length) {
                        return;
                    }

                    if (typeof callback === 'function') {
                        var blTypeNotAllowed = false;
                        var files = [];
                        var foldername = $('.dd-media', $dialog).data('foldername');

                        $item.each(function () {
                            var filetype = $(this).data('filetype');

                            if (filter !== null && ((typeof filter === 'string' && filter !== filetype) || (filter instanceof RegExp && !filetype.match(filter)))) {
                                blTypeNotAllowed = true;
                            } else {
                                files.push({
                                    id: $(this).data('id'),
                                    file: (foldername ? foldername + '/' : '') + $(this).data('file'),
                                    url: ui._resourceLink + $(this).data('file'),
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

                    self.hideOverlay();
                });

                $('.dd-overlay-dialog-footer', self.$overlay).prepend($applyAction);
            });
        }

        this._loadMediaContent($dialog);

    };

    MediaLibrary.prototype.refreshMedia = function (id) {
        var $media = $('.dd-media');

        if ($media.length) {
            var $dialog = $media.closest('.modal');

            var tab;

            if ($('.dd-media-tabs .tab-pane.active').length) {
                tab = $('.dd-media-tabs .tab-pane.active').attr('id');
            }

            if ($dialog.length) {
                $('.modal-body', $dialog).html('<div class="dd-dialog-loader"></div>');
            } else {
                $dialog = $('.dd-media-wrapper');

                $('.dd-content', $dialog).html('<div class="dd-dialog-loader"></div>');
            }

            this._loadMediaContent($dialog, id, tab);
        }
    };

    MediaLibrary.prototype.addMediaItem = function (id, file, filetype, filesize, thumb, imagesize) {
        var resourceLink = this._resourceLink;
        var ui = this;
        var $item = $('.dd-media-list-items .dd-media-dz-helper > div').clone();

        $('.dd-media-item', $item).data({
            'id': id, 'file': file, 'filetype': filetype, 'filesize': filesize, 'imagesize': imagesize
        });

        if (!thumb || thumb === undefined) {
            $('.dd-media-thumb', $item).hide();
            if (filetype == 'directory') {
                $('.dd-media-icon-file', $item).hide();
                $('.dd-media-icon-folder', $item).show();
            } else {
                $('.dd-media-icon-file', $item).show();
                $('.dd-media-icon-folder', $item).hide();
            }
            $('.dd-media-item', $item).addClass('no-thumb');
        } else {
            $('.dd-media-thumb', $item).attr('src', thumb);
            $('.dd-media-item', $item).removeClass('no-thumb');
        }

        $('.dd-media-item-label', $item).show()
            .find('span').text(file);

        $('.dd-media-list-items > .row').append($item);

        //make added item draggable or droppable for moving images to folder by drag & drop
        ui._makeItemMovable($('.dd-media-item', $item));

    };

    MediaLibrary.prototype._loadMediaContent = function ($dialog, folderId, tab) {
        var actionLink = this._actionLink;
        var resourceLink = this._resourceLink;
        var ui = this;
        var mediaOptions = $dialog.data('media-options');
        var actionLinkParam = '';

        // if( $( '.dd-media-tabs .tab-pane.active' ).length )
        if (tab !== undefined && tab) {
            actionLinkParam += '&tab=' + tab;
        }

        if (folderId !== undefined && folderId) {
            actionLinkParam += '&folderid=' + folderId;
        }

        $.get(actionLink + 'cl=ddoemedia_view' + actionLinkParam, function (html) {
            if ($dialog.is('.dd-media-wrapper')) {
                $('.dd-content', $dialog).html(html);
            } else {
                $('.modal-body', $dialog).html(html);
            }

            ui.currentFolderId = $('.dd-media', $dialog).data('folderid');
            resourceLink = $('.dd-media', $dialog).data('medialink');
            ui.setResourceLink(resourceLink);

            ui.currentPath = resourceLink.substr(resourceLink.indexOf('out/pictures/') + 'out/pictures/'.length);

            $('.dd-media-item[data-id]', $dialog).each(function () {
                ui._makeItemMovable($(this));
            });

            $('.dd-media-remove-action, .dd-media-move-action, .dd-media-rename-action', $dialog).prop('disabled', true);

            $('.dd-media-folder-action', $dialog).on('click', function () {
                ddh.prompt(ddh.translate('DD_MEDIA_ADD_FOLDER'), function (val) {
                    $.ajax({
                        type: "POST",
                        url: actionLink + 'cl=ddoemedia_view&fnc=addFolder',
                        data: {name: val},
                        success: function (addFolderResult) {
                            if (addFolderResult.id) {
                                ui.addMediaItem(addFolderResult.id, addFolderResult.name, 'directory', 0, null, '');
                                $('.dd-media-list', $dialog).removeClass('empty');
                                $('.dd-media-file-count', $dialog).text(parseInt($('.dd-media-file-count', $dialog).text()) + 1);
                            }
                        },
                        error: function (result) {
                            ddh.alert(ddh.translate(result.responseJSON.error));
                        }
                    });
                });
            });

            // if folder id is given then we are in a folder
            if (folderId) {
                $('.dd-media-folder-action').hide();
                $('.dd-media-folder-up-action', $dialog).on('click', function () {
                    ui.refreshMedia();
                });
            } else // otherwise we are in root folder
            {
                $('.dd-media-folder-up-action', $dialog).prop('disabled', true);
            }

            $('.dd-media-remove-action', $dialog).on('click', function () {
                var $item = $('.dd-media-item.active', $dialog);
                var $btn = this;

                if (!$($btn).prop('disabled') && $item.length) {
                    var sConfirmMsg = 'DD_MEDIA_REMOVE_CONFIRM';

                    if ($item.length > 1) {
                        sConfirmMsg = 'DD_MEDIA_REMOVE_MANY_CONFIRM';
                    } else if ($item.data('filetype') == 'directory') {
                        sConfirmMsg = 'DD_MEDIA_REMOVE_FOLDER_CONFIRM';
                    }

                    ddh.confirm(ddh.translate(sConfirmMsg), function () {
                        $item.addClass('dd-media-item-removing');

                        var deleteIDs = [];

                        $item.each(function () {
                            deleteIDs.push($(this).data('id'));
                        });

                        var folderId = $('.dd-media', $dialog).data('folderid');

                        $.get(actionLink + 'cl=ddoemedia_view&fnc=remove&ids[]=' + deleteIDs.join('&ids[]=') + '&folderid=' + folderId, function (response) {
                            if (response.success) {
                                $('.dd-media-file-count', $dialog).text(parseInt($('.dd-media-file-count', $dialog).text(), 10) - $item.length);

                                $item.each(function () {
                                    $(this).parent().remove();
                                });

                                $($btn).prop('disabled', true);

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

            $('.dd-media-rename-action', $dialog).on('click', function () {
                var $item = $('.dd-media-item.active', $dialog);
                var $btn = this;

                if (!$($btn).prop('disabled') && $item.length == 1) {
                    ddh.prompt(ddh.translate('DD_MEDIA_RENAME_FILE_FOLDER'), function (val) {
                        var activeItem = $('.dd-media-item.active', $dialog);

                        if (val != activeItem.data('file')) {
                            $.ajax({
                                type: "POST",
                                url: actionLink + 'cl=ddoemedia_view&fnc=rename',
                                data: {
                                    newname: val,
                                    id: activeItem.data('id'),
                                },
                                success: function (resultJson) {
                                    activeItem.data('file', resultJson.name);
                                    activeItem.data('id', resultJson.id);
                                    activeItem.data('url', resourceLink + resultJson.name);

                                    $('.dd-media-item-label span', activeItem).text(resultJson.name);
                                    ui._loadItemDetails(activeItem.data(), $dialog);
                                },
                                error: function (result) {
                                    ddh.alert(ddh.translate(result.responseJSON.error));
                                }
                            });
                        }

                    }, undefined, $item.data('file'));

                }

            });

            $('.dd-media', $dialog).on('click', '.dd-media-item', function (e) {
                e.preventDefault();

                if ($(this).parent('.dz-error').length) {
                    return;
                }

                if (mediaOptions && mediaOptions.multiple && ui.ctrlKeyPressed) {
                    $(this).toggleClass('active');
                } else {
                    $('.dd-media-item', $dialog).removeClass('active');
                    $(this).addClass('active');
                }

                var $detailsItem = null;

                if ($(this).hasClass('active')) {
                    $detailsItem = $(this);
                } else {
                    $detailsItem = $(this).parent().siblings().find('.dd-media-item.active').first();
                }

                if ($detailsItem && $detailsItem.length) {
                    var itemData = $detailsItem.data();

                    itemData.url = resourceLink + $detailsItem.data('file');
                    itemData.type = itemData.filetype;
                    itemData.size = itemData.filesize;

                    if ($('.dd-media-thumb', $detailsItem).length) {
                        itemData.preview = $('.dd-media-thumb', $detailsItem).attr('src');
                    }

                    ui._loadItemDetails(itemData, $dialog);
                }

                var $activeItems = $('.dd-media-list-items > .row > .dd-media-col > .active', $dialog);

                if (!$activeItems.length) {
                    ui._loadItemDetails(false, $dialog);

                    $('.dd-media-remove-action, .dd-media-move-action, .dd-media-rename-action').prop('disabled', true);
                } else {
                    if ($activeItems.length > 1) {
                        $('.dd-media-remove-action, .dd-media-move-action').prop('disabled', false);
                        $('.dd-media-rename-action').prop('disabled', true);
                    } else {
                        $('.dd-media-remove-action, .dd-media-move-action, .dd-media-rename-action').prop('disabled', false);
                    }
                }
            }).on('dblclick', '.dd-media-item', function (e) {
                e.preventDefault();

                if ($(this).data('filetype') == 'directory') {
                    //open folder
                    ui.refreshMedia($(this).data('id'));
                } else {
                    $('.dd-media-submit', $dialog).trigger('click');
                }
            });

            $('.dd-media', $dialog).dropzone({
                url: actionLink + 'cl=ddoemedia_view&fnc=upload&folderid=' + $('.dd-media', $dialog).data('folderid'),
                parallelUploads: 10,

                previewsContainer: $('.dd-media-list-items > .row', $dialog)[0],
                previewTemplate: $('.dd-media-list-items .dd-media-dz-helper', $dialog).html(),

                clickable: $('.dd-media-upload', $dialog)[0],

                init: function () {
                    this.on('addedfile', function () {
                        $('.dd-media-list', $dialog).removeClass('empty');
                        $('.dd-media-tabs .nav-tabs a[href="#mediaList"]', $dialog).tab('show');

                        $('.dd-media-list-items', $dialog).scrollTop($('.dd-media-list-items > .row', $dialog).height());
                    });

                    this.on('success', function (file, response) {
                        $('.dd-media-item', file.previewElement).find('.dd-media-thumb').attr('src', response.thumb);
                        $('.dd-media-item', file.previewElement).data({
                            'id': response.id,
                            'file': response.file,
                            'filetype': response.filetype,
                            'filesize': response.filesize,
                            'imagesize': (response.imagesize || null),
                            'thumb': response.thumb
                        }).trigger('click');

                        ui._makeItemMovable($('.dd-media-item', file.previewElement));

                        $('.dd-media-file-count', $dialog).text(parseInt($('.dd-media-file-count', $dialog).text()) + 1);
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

                error: function (file, responseJson, xhr) {
                    $(file.previewElement).addClass("dz-error")
                        .find('.dd-media-item-error').show().text(ddh.translate(responseJson.error));
                }
            });

            $('.dd-media-search-form').on('submit', function (e) {
                e.preventDefault();
                return false;
            });

            $('.dd-media-search-form input').on('keyup', function (e) {
                e.preventDefault();

                var val = $(this).val();

                if (val === '') {
                    $('.dd-media-list-items > .row > .dd-media-col', $dialog).show();
                } else {
                    $('.dd-media-list-items > .row > .dd-media-col', $dialog).each(function () {
                        var $item = $('.dd-media-item', this);

                        if ($item.data('file').search(val) > -1) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            });

            ui._loadMoreMediaContent(0);
        });
    };

    MediaLibrary.prototype._loadMoreMediaContent = function (page) {
        var actionLink = this._actionLink;
        var start = page * 18;
        var ui = this;

        $.get(actionLink + 'cl=ddoemedia_view&fnc=moreFiles&start=' + start + '&folderid=' + $('.dd-media').data('folderid'), function (data) {
            if (data.files && data.files.length) {
                $.each(data.files, function () {
                    ui.addMediaItem(this.id, this.file, this.filetype, this.filesize, (this.thumb || false), (this.imageSize || null));
                });
            }

            if (data.more) {
                ui._loadMoreMediaContent(page + 1);
            }
        });
    };

    // Make MediaLibrary public
    window.MediaLibrary = new MediaLibrary();

}(jQuery);
