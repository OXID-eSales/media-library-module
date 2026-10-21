/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import type {ElementData} from '../types/ElementData.ts';

export const DataStoreInterface = Symbol('DataStoreInterface');

export interface DataStoreInterface {
    set<K extends keyof ElementData>(el: Element, key: K, value: ElementData[K]): void;
    setMultiple(el: Element, data: Partial<ElementData>): void;
    get<K extends keyof ElementData>(el: Element, key: K): ElementData[K] | undefined;
    getAll(el: Element): Partial<ElementData>;
}
