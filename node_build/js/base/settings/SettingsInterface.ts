/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export const SettingsInterface = Symbol('SettingsInterface');

export interface SettingsInterface {
    setShopUrl(url: string): void;

    /**
     * The admin self link, e.g. "https://shop.local/admin/index.php?stoken=1A2B3C4D&".
     * It ends with "?" or "&" and carries the stoken of the admin session.
     */
    setActionLink(url: string): void;

    /**
     * Returns the admin action link with the given parameters added,
     * e.g. getActionUrl(new URLSearchParams({cl: 'ddoemedia_view', fnc: 'rename'})).
     */
    getActionUrl(params: URLSearchParams): string;

    /**
     * The url of the media folder that is shown, the file name is appended to it.
     */
    setResourceLink(url: string): void;

    getResourceLink(): string;

    /**
     * Returns the url of a shop api route with the admin stoken added,
     * e.g. getApiUrl('api/oeml-admin-translations').
     */
    getApiUrl(path: string): string;
}
