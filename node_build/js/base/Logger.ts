/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export default class Logger {
    static #timestamp(): string {
        return new Date().toISOString().slice(0, 19).replace('T', ' ');
    }

    static log(msg: unknown, type: 'log' | 'warn' | 'error' = 'log'): void {
        if (window.debug !== true) {
            return;
        }

        console[type]?.(`[Media Library][${this.#timestamp()}]`, msg);
    }
}
