/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export type DialogButton = {
    /** Complete button markup; when set, label, type and css are ignored. */
    html?: string;
    label?: string;
    type?: string;
    css?: string[];
    attributes?: Record<string, string>;
    action?: (modalEl: HTMLElement) => void;
};
