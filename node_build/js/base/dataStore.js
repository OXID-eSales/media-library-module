/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export default class DataStore {
    constructor() {
        this._map = new WeakMap();
    }

    set(el, key, value, persist = false) {
        if (persist) {
            el.dataset[key] = value;
        } else {
            let store = this._map.get(el);
            if (!store) {
                store = {};
                this._map.set(el, store);
            }
            store[key] = value;
        }
    }

    setMultiple(el, data, persist = false) {
        if (persist) {
            for (const key in data) {
                el.dataset[key] = data[key];
            }
        } else {
            let store = this._map.get(el);
            if (!store) {
                store = {};
                this._map.set(el, store);
            }
            Object.assign(store, data);
        }
    }

    get(el, key) {
        if (!el) {
            return undefined;
        }
        if (el.dataset && key in el.dataset) {
            return el.dataset[key];
        }
        const store = this._map.get(el);
        return store ? store[key] : undefined;
    }

    getAll(el) {
        return { ...el.dataset, ...(this._map.get(el) || {}) };
    }
}
