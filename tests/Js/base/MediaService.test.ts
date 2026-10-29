/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {beforeEach, describe, expect, it} from 'vitest';
import {
    getMediaUrl,
    preloadMediaUrls,
    setMediaUrl,
    setMediaUrlStore,
} from '../../../node_build/js/base/MediaService.ts';
import type {MediaUrlStore} from '../../../node_build/js/base/types/MediaUrlStore.ts';

describe('MediaService', () => {
    beforeEach(() => {
        setMediaUrlStore({});
    });

    describe('getMediaUrl', () => {
        it('should return null for an unknown id', () => {
            expect(getMediaUrl(crypto.randomUUID())).toBeNull();
        });

        it('should return the url set with setMediaUrl', () => {
            const id = crypto.randomUUID();
            const url = crypto.randomUUID();

            setMediaUrl(id, url);

            expect(getMediaUrl(id)).toBe(url);
        });
    });

    describe('setMediaUrlStore', () => {
        it('should write into the given store object', () => {
            const store: MediaUrlStore = {};
            const id = crypto.randomUUID();
            const url = crypto.randomUUID();

            setMediaUrlStore(store);
            setMediaUrl(id, url);

            expect(store[id]).toBe(url);
        });

        it('should read urls another realm wrote into the given store object', () => {
            const id = crypto.randomUUID();
            const url = crypto.randomUUID();
            const store: MediaUrlStore = {};

            setMediaUrlStore(store);
            store[id] = url;

            expect(getMediaUrl(id)).toBe(url);
        });

        it('should fall back to the module cache when no store is given', () => {
            const store: MediaUrlStore = {};
            const id = crypto.randomUUID();
            const url = crypto.randomUUID();

            setMediaUrlStore(store);
            setMediaUrlStore(null);
            setMediaUrl(id, url);

            expect(store).toEqual({});
            expect(getMediaUrl(id)).toBe(url);
        });
    });

    describe('preloadMediaUrls', () => {
        it('should use the given object as the store', () => {
            const id = crypto.randomUUID();
            const url = crypto.randomUUID();
            const store: MediaUrlStore = {[id]: url};

            preloadMediaUrls(store);

            expect(getMediaUrl(id)).toBe(url);
        });
    });
});
