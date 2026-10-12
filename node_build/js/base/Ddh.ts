/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {DIContainer} from './DIContainer.ts';
import {DialogInterface} from './dialog/DialogInterface.ts';
import {InteractionInterface} from './interaction/InteractionInterface.ts';
import {LanguageInterface} from './language/LanguageInterface.ts';
import type {DialogButton} from './dialog/types/DialogButton.ts';
import type {DialogOptions} from './dialog/types/DialogOptions.ts';

const dialog = (): DialogInterface => DIContainer.resolve<DialogInterface>(DialogInterface);
const interaction = (): InteractionInterface => DIContainer.resolve<InteractionInterface>(InteractionInterface);
const language = (): LanguageInterface => DIContainer.resolve<LanguageInterface>(LanguageInterface);

/**
 * @deprecated will be removed in next major. Use DialogInterface, InteractionInterface and LanguageInterface instead.
 */
export const ddh = {
    /**
     * Accepts either a DialogOptions object, or message, title, buttons, size and css as separate arguments.
     */
    _dialog(
        message: string | HTMLElement | DialogOptions,
        title?: string,
        buttons: DialogButton[] = [],
        size: DialogOptions['size'] = 'sm',
        css = 'dd-dialog',
    ): HTMLElement {
        if (typeof message === 'object' && !(message instanceof HTMLElement)) {
            return dialog().open(message);
        }

        return dialog().open({message, title, buttons, size, css});
    },

    confirm(message: string, callback: () => void, title?: string, warn?: boolean): void {
        interaction().confirm(message, callback, title, warn);
    },

    prompt(message: string, callback: (value: string) => void, title?: string, value?: string): void {
        interaction().prompt(message, callback, title, value);
    },

    alert(message: string, title?: string): void {
        interaction().alert(message, title);
    },

    translate(key: string): string {
        return language().translate(key);
    },
};
