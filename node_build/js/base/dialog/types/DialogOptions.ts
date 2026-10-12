/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import type {DialogButton} from './DialogButton.ts';

export type DialogOptions = {
    /** HTML string or element shown in the modal body. */
    message: string | HTMLElement;
    title?: string;
    buttons?: DialogButton[];
    size?: 'sm' | 'md' | 'lg' | 'xl';
    /** Classes added to the .modal element. */
    css?: string;
    backdrop?: boolean;
    keyboard?: boolean;
};
