/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export const SettingsInterface = Symbol('SettingsInterface');

export interface SettingsInterface {
    setShopUrl(url: string): void;

    /**
     * The admin self link; it carries the stoken of the admin session.
     */
    setActionLink(url: string): void;

    /**
     * Returns the url of a shop api route with the admin stoken added,
     * e.g. getApiUrl('api/oeml-admin-translations').
     */
    getApiUrl(path: string): string;
}
