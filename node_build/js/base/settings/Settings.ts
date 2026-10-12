/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {injectable} from 'tsyringe';
import {SettingsInterface} from './SettingsInterface.ts';

@injectable()
export default class Settings implements SettingsInterface {
    #shopUrl = '/';
    #stoken = '';

    setShopUrl(url: string): void {
        this.#shopUrl = url;
    }

    setActionLink(url: string): void {
        this.#stoken = new URL(url, window.location.href).searchParams.get('stoken') ?? '';
    }

    getApiUrl(path: string): string {
        const url = this.#shopUrl.replace(/\/?$/, '/') + path;
        if (!this.#stoken) {
            return url;
        }

        const separator = url.includes('?') ? '&' : '?';
        return url + separator + new URLSearchParams({stoken: this.#stoken});
    }
}
