/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {inject, injectable} from 'tsyringe';
import {TranslationsRequestInterface} from './TranslationsRequestInterface.ts';
import {SettingsInterface} from '../settings/SettingsInterface.ts';
import Logger from '../Logger.ts';
import type {Translations} from './types/Translations.ts';

@injectable()
export default class TranslationsRequest implements TranslationsRequestInterface {
    constructor(
        @inject(SettingsInterface) private readonly settings: SettingsInterface,
    ) {}

    async getTranslations(): Promise<Translations | null> {
        try {
            const response = await fetch(
                this.settings.getApiUrl('api/oeml-admin-translations'),
                {credentials: 'same-origin'},
            );

            if (!response.ok) {
                Logger.log(`TranslationsRequest.getTranslations: ${response.status} ${response.statusText}`, 'error');
                return null;
            }

            return await response.json();
        } catch (error) {
            Logger.log(`TranslationsRequest.getTranslations: ${error}`, 'error');
            return null;
        }
    }
}
