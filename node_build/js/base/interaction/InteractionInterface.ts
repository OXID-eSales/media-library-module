/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export const InteractionInterface = Symbol('InteractionInterface');

export interface InteractionInterface {
    confirm(message: string, callback: () => void, title?: string, warn?: boolean): void;
    prompt(message: string, callback: (value: string) => void, title?: string, value?: string): void;
    alert(message: string, title?: string): void;
}
