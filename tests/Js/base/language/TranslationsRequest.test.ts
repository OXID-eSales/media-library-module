/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import TranslationsRequest from '../../../../node_build/js/base/language/TranslationsRequest.ts';
import {TranslationsRequestInterface} from '../../../../node_build/js/base/language/TranslationsRequestInterface.ts';
import {SettingsInterface} from '../../../../node_build/js/base/settings/SettingsInterface.ts';
import {InterfaceMock} from '../InterfaceMock.ts';

describe('TranslationsRequest', () => {
    beforeEach(() => {
        vi.stubGlobal('fetch', vi.fn());
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    describe('getTranslations', () => {
        it('should request the translations route with the session cookie', async () => {
            vi.mocked(fetch).mockResolvedValue(new Response('{}'));
            const sut = getSut();

            await sut.getTranslations();

            expect(fetch).toHaveBeenCalledWith('[api/oeml-admin-translations]', {credentials: 'same-origin'});
        });

        it('should return the translations from the response', async () => {
            const translations = {[crypto.randomUUID()]: crypto.randomUUID()};
            vi.mocked(fetch).mockResolvedValue(new Response(JSON.stringify(translations)));
            const sut = getSut();

            expect(await sut.getTranslations()).toEqual(translations);
        });

        it('should return null for a non-2xx response', async () => {
            vi.mocked(fetch).mockResolvedValue(new Response('', {status: 500}));
            const sut = getSut();

            expect(await sut.getTranslations()).toBeNull();
        });

        it('should return null when the request fails', async () => {
            vi.mocked(fetch).mockRejectedValue(new TypeError('Failed to fetch'));
            const sut = getSut();

            expect(await sut.getTranslations()).toBeNull();
        });
    });

    const getSut = ({
        settings = InterfaceMock<SettingsInterface>({getApiUrl: (path: string) => `[${path}]`}),
    }: {
        settings?: SettingsInterface;
    } = {}): TranslationsRequestInterface => new TranslationsRequest(settings);
});
