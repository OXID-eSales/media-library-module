/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export const DragDropHandlerInterface = Symbol('DragDropHandlerInterface');

export interface DragDropHandlerInterface {
    /**
     * Makes a file draggable, or a folder a drop target for files.
     */
    makeMovable(item: HTMLElement): void;

    /**
     * Moves the file into the folder and removes it from the list on success.
     */
    moveFile(fileItem: HTMLElement, folderItem: HTMLElement): Promise<void>;
}
