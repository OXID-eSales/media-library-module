/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Formats a size in bytes for display, e.g. 2048 → "2 kb".
 */
export function formatFileSize(bytes: number): string {
    const names = ['tb', 'gb', 'mb', 'kb', 'b'];
    let size = bytes;
    while (size > 1024 && names.length > 1) {
        size = Math.round((size / 1024) * 100) / 100;
        names.pop();
    }

    return size + ' ' + names.pop();
}
