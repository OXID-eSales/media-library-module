/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {inject, injectable} from 'tsyringe';
import {DragDropHandlerInterface} from './DragDropHandlerInterface.ts';
import {DataStoreInterface} from '../dataStore/DataStoreInterface.ts';
import {MediaRequestInterface} from '../media/MediaRequestInterface.ts';
import {InteractionInterface} from '../interaction/InteractionInterface.ts';
import {LanguageInterface} from '../language/LanguageInterface.ts';

/**
 * Uses the jQuery UI draggable and droppable widgets, jQuery and jQuery UI are loaded as globals.
 */
@injectable()
export default class DragDropHandler implements DragDropHandlerInterface {
    constructor(
        @inject(DataStoreInterface) private readonly store: DataStoreInterface,
        @inject(MediaRequestInterface) private readonly mediaRequest: MediaRequestInterface,
        @inject(InteractionInterface) private readonly interaction: InteractionInterface,
        @inject(LanguageInterface) private readonly language: LanguageInterface,
    ) {}

    makeMovable(item: HTMLElement): void {
        // todo: moving a file to the parent folder
        if (this.store.get(item, 'filetype') === 'directory') {
            this.#makeDropTarget(item);
        } else {
            this.#makeDraggable(item);
        }
    }

    async moveFile(fileItem: HTMLElement, folderItem: HTMLElement): Promise<void> {
        const fileId = this.store.get(fileItem, 'id');
        const folderId = this.store.get(folderItem, 'id');
        if (!fileId || !folderId) {
            return;
        }

        try {
            const result = await this.mediaRequest.moveFile(fileId, folderId);
            if (result.success) {
                fileItem.parentElement?.remove();
            } else if (result.msg) {
                this.interaction.alert(this.language.translate(result.msg));
            }
        } catch {
            // MediaRequest logs the error.
        }
    }

    #makeDraggable(item: HTMLElement): void {
        $(item).draggable({
            revert: 'invalid',
            helper: (event: JQueryEventObject): HTMLElement => this.#createDragHelper(event),
            zIndex: 100,
            opacity: 0.70,
            start: (_event, ui) => {
                ui.helper[0].classList.add('ui-draggable-helper');
            },
        });
    }

    #makeDropTarget(folderItem: HTMLElement): void {
        $(folderItem).droppable({
            hoverClass: 'ui-state-hover',
            drop: (_event, ui) => {
                const fileItem = ui.draggable[0];
                if (fileItem) {
                    void this.moveFile(fileItem, folderItem);
                }
            },
        });
    }

    /**
     * Drags a copy of the item with the same size, so the item stays in the list until it is moved.
     */
    #createDragHelper(event: JQueryEventObject): HTMLElement {
        const target = event.target as HTMLElement;
        const original = target.closest<HTMLElement>('.ui-draggable') ?? target;
        const clone = original.cloneNode(true) as HTMLElement;

        const rect = original.getBoundingClientRect();
        clone.style.width = rect.width + 'px';
        clone.style.height = rect.height + 'px';

        return clone;
    }
}
