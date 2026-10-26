/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Shared DI runtime for the admin modules.
 * Resolved via import map as "tsyringe", so all modules share one container instance.
 */
import 'reflect-metadata';
import {container} from 'tsyringe';

export * from 'tsyringe';

const originalAfterResolution = container.afterResolution.bind(container);

// tsyringe silently ignores decorations of not registered services (e.g. a mistyped key), so report them
container.afterResolution = (token, callback, options) => {
    originalAfterResolution(token, callback, options);

    document.addEventListener('DOMContentLoaded', () => {
        if (!container.isRegistered(token, true)) {
            console.warn(`${String(token)} is not registered when the page is loaded, its afterResolution callback may never be called`);
        }
    });
};
