/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {describe, expect, it} from 'vitest';
import Settings from '../../../../node_build/js/base/settings/Settings.ts';
import {SettingsInterface} from '../../../../node_build/js/base/settings/SettingsInterface.ts';

describe('Settings', () => {
    describe('getApiUrl', () => {
        it('should use the shop root when no shop url is set', () => {
            const sut = getSut();

            expect(sut.getApiUrl('api/route')).toBe('/api/route');
        });

        it('should prefix the path with the shop url', () => {
            const sut = getSut();

            sut.setShopUrl('https://shop.local/');

            expect(sut.getApiUrl('api/route')).toBe('https://shop.local/api/route');
        });

        it('should add a missing slash after the shop url', () => {
            const sut = getSut();

            sut.setShopUrl('https://shop.local/sub');

            expect(sut.getApiUrl('api/route')).toBe('https://shop.local/sub/api/route');
        });

        it('should add the stoken of the action link', () => {
            const stoken = prepareStoken();
            const sut = getSut();

            sut.setActionLink(`https://shop.local/admin/index.php?stoken=${stoken}&force_admin_sid=xyz&`);

            expect(sut.getApiUrl('api/route')).toBe(`/api/route?stoken=${stoken}`);
        });

        it('should append the stoken to an existing query', () => {
            const stoken = prepareStoken();
            const sut = getSut();

            sut.setActionLink(`https://shop.local/admin/index.php?stoken=${stoken}&`);

            expect(sut.getApiUrl('api/route?id=1')).toBe(`/api/route?id=1&stoken=${stoken}`);
        });

        it('should not add a stoken when the action link has none', () => {
            const sut = getSut();

            sut.setActionLink('https://shop.local/admin/index.php?cl=media&');

            expect(sut.getApiUrl('api/route')).toBe('/api/route');
        });
    });

    describe('getActionUrl', () => {
        it('should append the parameters to the action link', () => {
            const stoken = prepareStoken();
            const sut = getSut();

            sut.setActionLink(`https://shop.local/admin/index.php?stoken=${stoken}&`);

            expect(sut.getActionUrl(new URLSearchParams({cl: 'ddoemedia_view', fnc: 'rename'})))
                .toBe(`https://shop.local/admin/index.php?stoken=${stoken}&cl=ddoemedia_view&fnc=rename`);
        });
    });

    describe('getResourceLink', () => {
        it('should be empty when no resource link is set', () => {
            const sut = getSut();

            expect(sut.getResourceLink()).toBe('');
        });

        it('should return the resource link that was set', () => {
            const resourceLink = `https://shop.local/out/pictures/ddmedia/${crypto.randomUUID()}/`;
            const sut = getSut();

            sut.setResourceLink(resourceLink);

            expect(sut.getResourceLink()).toBe(resourceLink);
        });
    });

    /** The shop generates the stoken as upper case hex. */
    const prepareStoken = (): string => crypto.randomUUID().replace(/-/g, '').slice(0, 8).toUpperCase();

    const getSut = (): SettingsInterface => new Settings();
});
