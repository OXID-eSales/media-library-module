/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {inject, injectable} from 'tsyringe';
import {LanguageInterface} from './LanguageInterface.ts';
import {TranslationsRequestInterface} from './TranslationsRequestInterface.ts';
import type {Translations} from './types/Translations.ts';

@injectable()
export default class Language implements LanguageInterface {
    #translations: Translations | null = null;
    #loading: Promise<void> | null = null;

    constructor(
        @inject(TranslationsRequestInterface) private readonly translationsRequest: TranslationsRequestInterface,
    ) {}

    load(): Promise<void> {
        this.#loading ??= this.translationsRequest.getTranslations().then((translations) => {
            this.#translations = translations;
        });

        return this.#loading;
    }

    /**
     * Until the translations are loaded, or when loading failed, the ones the deprecated
     * MediaLangJs controller puts on window.i18n are used.
     */
    translate(key: string): string {
        const translations = this.#translations ?? window.i18n ?? {};

        return translations[key] || key;
    }
}
