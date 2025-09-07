/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export default class DragDropHandler {
    constructor(fileManager, dataStore) {
        this.fm = fileManager;
        this.store = dataStore;
        this.dragMoved = false;
    }

    makeMovable(item) {
        const fileType = this.store.get(item, 'filetype');
        let $item = $(item);
        //TODO: moving file to parent folder
        if (fileType !== 'directory') {
            $($item).draggable({
                revert: 'invalid',
                helper: function (e) {
                    const original = $(e.target).hasClass('ui-draggable')
                        ? $(e.target)
                        : $(e.target).closest('.ui-draggable');
                    return original.clone().css({
                        width: original.width(),
                        height: original.height()
                    });
                },
                zIndex: 100,
                opacity: 0.70,
                start: function (_e, ui) { $(ui.helper).addClass('ui-draggable-helper'); }
            });
        } else {
            $item.droppable({
                hoverClass: 'ui-state-hover',
                drop: (event, ui) => {
                    if (ui.draggable.length) {
                        const $drag = ui.draggable;
                        const fileId = this.store.get($drag[0], 'id');
                        const file = this.store.get($drag[0], 'file');
                        const folderId = this.store.get(item, 'id');
                        const folder = this.store.get(item, 'file');
                        const thumb = this.store.get($drag[0], 'thumb');

                        if (fileId && folderId) {
                            this.fm.moveFile(fileId, folderId, file, folder, thumb)
                                .then((res) => {
                                    if (res.success) {
                                        $drag.parent().remove();
                                    } else if (res.msg) {
                                        ddh.alert(ddh.translate(res.msg));
                                    }
                                });
                        }
                    }
                }
            });
        }
    }
}
