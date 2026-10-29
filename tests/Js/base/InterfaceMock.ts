/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {vi} from 'vitest';

export const InterfaceMock = <T extends object>(overrides: Partial<T> = {}): T => {
    return new Proxy({} as unknown as T, {
        get(target, prop) {
            if (prop in overrides) {
                return overrides[prop as keyof T];
            }
            if (!(prop in target)) {
                (target as any)[prop] = vi.fn();
            }
            return (target as any)[prop];
        }
    });
};
