/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import type {DialogOptions} from './types/DialogOptions.ts';

export const DialogInterface = Symbol('DialogInterface');

export interface DialogInterface {
    /**
     * Shows a modal and returns its .modal element. The element is removed again once the modal is hidden.
     */
    open(options: DialogOptions): HTMLElement;
    close(modalEl: HTMLElement): void;
}
