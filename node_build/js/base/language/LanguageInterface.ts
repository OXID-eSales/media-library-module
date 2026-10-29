/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export const LanguageInterface = Symbol('LanguageInterface');

export interface LanguageInterface {
    /**
     * Loads the translations once; later calls return the same promise.
     */
    load(): Promise<void>;

    /**
     * Returns the translation of the key, or the key itself when there is none.
     */
    translate(key: string): string;
}
