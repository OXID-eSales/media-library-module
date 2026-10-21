/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {injectable} from 'tsyringe';
import {DataStoreInterface} from './DataStoreInterface.ts';
import type {ElementData} from '../types/ElementData.ts';

@injectable()
export default class DataStore implements DataStoreInterface {
    readonly #map = new WeakMap<Element, Partial<ElementData>>();

    set<K extends keyof ElementData>(el: Element, key: K, value: ElementData[K]): void {
        this.#getOrCreate(el)[key] = value;
    }

    setMultiple(el: Element, data: Partial<ElementData>): void {
        Object.assign(this.#getOrCreate(el), data);
    }

    get<K extends keyof ElementData>(el: Element, key: K): ElementData[K] | undefined {
        return this.#map.get(el)?.[key];
    }

    getAll(el: Element): Partial<ElementData> {
        return {...this.#map.get(el)};
    }

    #getOrCreate(el: Element): Partial<ElementData> {
        let store = this.#map.get(el);
        if (!store) {
            store = {};
            this.#map.set(el, store);
        }

        return store;
    }
}
