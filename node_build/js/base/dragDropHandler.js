/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import interact from 'interactjs';

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

    makeMovableNew(itemEl) {
        const fileType = this.store.get(itemEl, 'filetype');

        if (fileType !== 'directory') {
            // Draggable FILE
            interact(itemEl).draggable({
                inertia: false,
                modifiers: [],
                listeners: {
                    start: (event) => {
                        this.dragMoved = false;

                        // Create ghost
                        const ghost = event.target.cloneNode(true);
                        ghost.classList.add('drag-ghost');
                        ghost.style.position = 'absolute';
                        ghost.style.pointerEvents = 'none';
                        ghost.style.width = `${event.target.offsetWidth}px`;
                        ghost.style.zIndex = '10000';
                        document.body.appendChild(ghost);

                        // Store references
                        event.interaction.ghost = ghost;
                        event.interaction.sourceEl = event.target;

                        event.target.classList.add('ui-draggable-helper');
                    },
                    move: (event) => {
                        this.dragMoved = true;

                        const ghost = event.interaction.ghost;
                        if (ghost) {
                            ghost.style.left = `${event.clientX - ghost.offsetWidth / 2}px`;
                            ghost.style.top = `${event.clientY - ghost.offsetHeight / 2}px`;
                        }
                    },
                    end: (event) => {
                        event.target.classList.remove('ui-draggable-helper');

                        const ghost = event.interaction.ghost;
                        if (ghost && ghost.parentNode) {
                            ghost.parentNode.removeChild(ghost);
                        }

                        requestAnimationFrame(() => {
                            this.dragMoved = false;
                        });
                    }
                }
            });

        } else {
            // Droppable FOLDER
            interact(itemEl).dropzone({
                accept: '[data-filetype="image/png"]',   // <-- only allow files, not directories
                overlap: 0.5,
                ondropactivate: (event) => {
                    console.log('ondropactivate', event.target);
                    event.target.classList.add('drop-active');
                },
                ondragenter: (event) => {
                    console.log('ondragenter', event.target);
                    event.target.classList.add('ui-state-hover');
                },
                ondragleave: (event) => {
                    console.log('ondragleave', event.target);
                    event.target.classList.remove('ui-state-hover');
                },
                ondrop: (event) => {
                    console.log('ondrop', event.target);

                    event.target.classList.remove('ui-state-hover');

                    const dragEl = event.relatedTarget;   // <--- InteractJS gives you the real dragged element

                    const fileId = dragEl.dataset.id;
                    const file   = dragEl.dataset.file;
                    const thumb  = dragEl.dataset.thumb;

                    const folderId = itemEl.dataset.id;
                    const folder   = itemEl.dataset.file;

                    if (fileId && folderId) {
                        this.fm.moveFile(fileId, folderId, file, folder, thumb)
                            .then(res => {
                                if (res.success) {
                                    dragEl.parentElement.remove();
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
