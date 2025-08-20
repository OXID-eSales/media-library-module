/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export default class DragDropHandler {
    constructor(fileManager) {
        this.fm = fileManager;
    }

    makeMovable($item) {
        //TODO: moving file to parent folder
        if ($item.data('filetype') !== 'directory') {
            $item.draggable({
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
                    if (!ui.draggable.length) return;
                    const $drag = ui.draggable;
                    const fileId = $drag.data('id');
                    const file = $drag.data('file');
                    const folderId = $item.data('id');
                    const folder = $item.data('file');
                    const thumb = $drag.data('thumb');

                    if (fileId && folderId) {
                        this.fm.moveFile(fileId, folderId, file, folder, thumb)
                            .done((res) => {
                                if (res.success) {
                                    $drag.parent().remove();
                                } else if (res.msg) {
                                    ddh.alert(ddh.translate(res.msg));
                                }
                            });
                    }
                }
            });
        }
    }
}
