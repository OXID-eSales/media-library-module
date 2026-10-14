/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {describe, expect, it} from 'vitest';
import {formatFileSize} from '../../../node_build/js/base/FileSize.ts';

describe('formatFileSize', () => {
    it.each([
        {bytes: 0, expected: '0 b'},
        {bytes: 1024, expected: '1024 b'},
        {bytes: 2048, expected: '2 kb'},
        {bytes: 1536000, expected: '1.46 mb'},
        {bytes: 3 * 1024 ** 3, expected: '3 gb'},
        {bytes: 5 * 1024 ** 4 + 1, expected: '5 tb'},
    ])('should format $bytes bytes as "$expected"', ({bytes, expected}) => {
        expect(formatFileSize(bytes)).toBe(expected);
    });

    it('should not go past tb', () => {
        expect(formatFileSize(2048 * 1024 ** 4)).toBe('2048 tb');
    });
});
