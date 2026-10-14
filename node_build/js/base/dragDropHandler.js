/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {ddh} from './Ddh.ts';
import {DIContainer} from './DIContainer.ts';
import {MediaRequestInterface} from './media/MediaRequestInterface.ts';

export default class DragDropHandler {
    constructor(dataStore) {
        this.store = dataStore;
    }

    get mediaRequest() {
        return DIContainer.resolve(MediaRequestInterface);
    }

    makeMovable(item) {
        const fileType = this.store.get(item, 'filetype');
        let $item = $(item);
        //TODO: moving file to parent folder
        if (fileType !== 'directory') {
            $($item).draggable({
                revert: 'invalid',
                helper: function (e) {
                    let original = e.target.classList.contains('ui-draggable')
                        ? e.target
                        : e.target.closest('.ui-draggable');

                    let clone = original.cloneNode(true);

                    let rect = original.getBoundingClientRect();
                    clone.style.width = rect.width + 'px';
                    clone.style.height = rect.height + 'px';

                    return clone;
                },
                zIndex: 100,
                opacity: 0.70,
                start: function (_e, ui) {
                    ui.helper.get(0).classList.add('ui-draggable-helper');
                }
            });
        } else {
            $item.droppable({
                hoverClass: 'ui-state-hover',
                drop: (event, ui) => {
                    if (ui.draggable.length) {
                        const $drag = ui.draggable;
                        const fileId = this.store.get($drag[0], 'id');
                        const folderId = this.store.get(item, 'id');

                        if (fileId && folderId) {
                            this.mediaRequest.moveFile(fileId, folderId)
                                .then((res) => {
                                    if (res.success) {
                                        $drag.get(0).parentElement.remove();
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
