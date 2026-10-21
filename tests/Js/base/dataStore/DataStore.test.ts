/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {describe, expect, it} from 'vitest';
import DataStore from '../../../../node_build/js/base/dataStore/DataStore.ts';
import {DataStoreInterface} from '../../../../node_build/js/base/dataStore/DataStoreInterface.ts';

describe('DataStore', () => {
    describe('get', () => {
        it('should return undefined for an element without stored data', () => {
            const sut = getSut();

            expect(sut.get(document.createElement('div'), 'id')).toBeUndefined();
        });

        it('should return the value stored with set', () => {
            const element = document.createElement('div');
            const id = crypto.randomUUID();
            const sut = getSut();

            sut.set(element, 'id', id);

            expect(sut.get(element, 'id')).toBe(id);
        });

        it('should keep the type of the stored value', () => {
            const element = document.createElement('div');
            const sut = getSut();

            sut.set(element, 'multiple', true);
            sut.set(element, 'filesize', 1024);

            expect(sut.get(element, 'multiple')).toBe(true);
            expect(sut.get(element, 'filesize')).toBe(1024);
        });

        it('should not read values from the element dataset', () => {
            const element = document.createElement('div');
            element.dataset.id = crypto.randomUUID();
            const sut = getSut();

            expect(sut.get(element, 'id')).toBeUndefined();
        });

        it('should keep values of different elements apart', () => {
            const first = document.createElement('div');
            const second = document.createElement('div');
            const firstId = crypto.randomUUID();
            const secondId = crypto.randomUUID();
            const sut = getSut();

            sut.set(first, 'id', firstId);
            sut.set(second, 'id', secondId);

            expect(sut.get(first, 'id')).toBe(firstId);
            expect(sut.get(second, 'id')).toBe(secondId);
        });
    });

    describe('setMultiple', () => {
        it('should merge the given values into already stored values', () => {
            const element = document.createElement('div');
            const id = crypto.randomUUID();
            const file = crypto.randomUUID();
            const sut = getSut();

            sut.set(element, 'id', crypto.randomUUID());
            sut.set(element, 'filetype', 'image/png');
            sut.setMultiple(element, {id, file});

            expect(sut.getAll(element)).toEqual({id, file, filetype: 'image/png'});
        });
    });

    describe('getAll', () => {
        it('should return an empty object for an element without stored data', () => {
            const sut = getSut();

            expect(sut.getAll(document.createElement('div'))).toEqual({});
        });

        it('should return a copy that does not change the stored values', () => {
            const element = document.createElement('div');
            const id = crypto.randomUUID();
            const sut = getSut();

            sut.set(element, 'id', id);
            sut.getAll(element).id = crypto.randomUUID();

            expect(sut.get(element, 'id')).toBe(id);
        });
    });

    const getSut = (): DataStoreInterface => new DataStore();
});
