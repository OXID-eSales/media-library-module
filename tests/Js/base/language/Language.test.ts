/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {afterEach, describe, expect, it, vi} from 'vitest';
import Language from '../../../../node_build/js/base/language/Language.ts';
import {LanguageInterface} from '../../../../node_build/js/base/language/LanguageInterface.ts';
import {TranslationsRequestInterface} from '../../../../node_build/js/base/language/TranslationsRequestInterface.ts';
import type {Translations} from '../../../../node_build/js/base/language/types/Translations.ts';
import {InterfaceMock} from '../InterfaceMock.ts';

describe('Language', () => {
    afterEach(() => {
        delete window.i18n;
    });

    describe('load', () => {
        it('should request the translations only once', async () => {
            const translationsRequestSpy = InterfaceMock<TranslationsRequestInterface>({
                getTranslations: vi.fn().mockResolvedValue({}),
            });
            const sut = getSut({translationsRequest: translationsRequestSpy});

            await Promise.all([sut.load(), sut.load()]);
            await sut.load();

            expect(translationsRequestSpy.getTranslations).toHaveBeenCalledOnce();
        });
    });

    describe('translate', () => {
        it('should return the loaded translation', async () => {
            const key = crypto.randomUUID();
            const translation = crypto.randomUUID();
            const sut = getSut({translationsRequest: prepareTranslationsRequestMock({[key]: translation})});

            await sut.load();

            expect(sut.translate(key)).toBe(translation);
        });

        it('should return the key when the loaded translations do not have it', async () => {
            const key = crypto.randomUUID();
            window.i18n = {[key]: crypto.randomUUID()};
            const sut = getSut({translationsRequest: prepareTranslationsRequestMock({})});

            await sut.load();

            expect(sut.translate(key)).toBe(key);
        });

        it('should use window.i18n before the translations are loaded', () => {
            const key = crypto.randomUUID();
            const translation = crypto.randomUUID();
            window.i18n = {[key]: translation};
            const sut = getSut({translationsRequest: prepareTranslationsRequestMock({})});

            expect(sut.translate(key)).toBe(translation);
        });

        it('should use window.i18n when loading the translations failed', async () => {
            const key = crypto.randomUUID();
            const translation = crypto.randomUUID();
            window.i18n = {[key]: translation};
            const sut = getSut({translationsRequest: prepareTranslationsRequestMock(null)});

            await sut.load();

            expect(sut.translate(key)).toBe(translation);
        });

        it('should return the key when nothing is loaded and window.i18n is not set', () => {
            const key = crypto.randomUUID();
            const sut = getSut({translationsRequest: prepareTranslationsRequestMock({})});

            expect(sut.translate(key)).toBe(key);
        });
    });

    const prepareTranslationsRequestMock = (translations: Translations | null): TranslationsRequestInterface =>
        InterfaceMock<TranslationsRequestInterface>({getTranslations: () => Promise.resolve(translations)});

    const getSut = ({
        translationsRequest = prepareTranslationsRequestMock({}),
    }: {
        translationsRequest?: TranslationsRequestInterface;
    } = {}): LanguageInterface => new Language(translationsRequest);
});
