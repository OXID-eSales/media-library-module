/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import type {Translations} from './types/Translations.ts';

export const TranslationsRequestInterface = Symbol('TranslationsRequestInterface');

export interface TranslationsRequestInterface {
    /**
     * Returns null when the translations could not be loaded.
     */
    getTranslations(): Promise<Translations | null>;
}
