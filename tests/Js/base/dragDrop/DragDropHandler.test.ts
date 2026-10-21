/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import 'jquery-ui/dist/jquery-ui.js';
import {afterEach, describe, expect, it, vi} from 'vitest';
import DragDropHandler from '../../../../node_build/js/base/dragDrop/DragDropHandler.ts';
import {DragDropHandlerInterface} from '../../../../node_build/js/base/dragDrop/DragDropHandlerInterface.ts';
import DataStore from '../../../../node_build/js/base/dataStore/DataStore.ts';
import {DataStoreInterface} from '../../../../node_build/js/base/dataStore/DataStoreInterface.ts';
import {MediaRequestInterface} from '../../../../node_build/js/base/media/MediaRequestInterface.ts';
import {InteractionInterface} from '../../../../node_build/js/base/interaction/InteractionInterface.ts';
import {LanguageInterface} from '../../../../node_build/js/base/language/LanguageInterface.ts';
import type {MediaActionResult} from '../../../../node_build/js/base/media/types/MediaActionResult.ts';
import {InterfaceMock} from '../InterfaceMock.ts';

describe('DragDropHandler', () => {
    afterEach(() => {
        document.body.innerHTML = '';
    });

    describe('makeMovable', () => {
        it('should make a file draggable', () => {
            const store = new DataStore();
            const fileItem = prepareItem(store, {id: crypto.randomUUID(), filetype: 'image/jpeg'});
            const sut = getSut({store});

            sut.makeMovable(fileItem);

            expect(fileItem.classList.contains('ui-draggable')).toBe(true);
            expect(fileItem.classList.contains('ui-droppable')).toBe(false);
        });

        it('should make a folder a drop target', () => {
            const store = new DataStore();
            const folderItem = prepareItem(store, {id: crypto.randomUUID(), filetype: 'directory'});
            const sut = getSut({store});

            sut.makeMovable(folderItem);

            expect(folderItem.classList.contains('ui-droppable')).toBe(true);
            expect(folderItem.classList.contains('ui-draggable')).toBe(false);
        });
    });

    describe('moveFile', () => {
        it('should move the file into the folder', async () => {
            const fileId = crypto.randomUUID();
            const folderId = crypto.randomUUID();
            const store = new DataStore();
            const mediaRequestSpy = prepareMediaRequestMock();
            const sut = getSut({store, mediaRequest: mediaRequestSpy});

            await sut.moveFile(
                prepareItem(store, {id: fileId, filetype: 'image/jpeg'}),
                prepareItem(store, {id: folderId, filetype: 'directory'}),
            );

            expect(mediaRequestSpy.moveFile).toHaveBeenCalledWith(fileId, folderId);
        });

        it('should remove the file from the list when it was moved', async () => {
            const store = new DataStore();
            const fileItem = prepareItem(store, {id: crypto.randomUUID(), filetype: 'image/jpeg'});
            const sut = getSut({store});

            await sut.moveFile(fileItem, prepareItem(store, {id: crypto.randomUUID(), filetype: 'directory'}));

            expect(fileItem.isConnected).toBe(false);
            expect(document.querySelectorAll('.dd-media-col')).toHaveLength(1);
        });

        it('should show the error the shop sent when the file was not moved', async () => {
            const msg = crypto.randomUUID();
            const store = new DataStore();
            const fileItem = prepareItem(store, {id: crypto.randomUUID(), filetype: 'image/jpeg'});
            const interactionSpy = InterfaceMock<InteractionInterface>();
            const sut = getSut({
                store,
                mediaRequest: prepareMediaRequestMock({result: {success: false, msg}}),
                interaction: interactionSpy,
            });

            await sut.moveFile(fileItem, prepareItem(store, {id: crypto.randomUUID(), filetype: 'directory'}));

            expect(interactionSpy.alert).toHaveBeenCalledWith(`[${msg}]`);
            expect(fileItem.isConnected).toBe(true);
        });

        it('should keep the file in the list when the request fails', async () => {
            const store = new DataStore();
            const fileItem = prepareItem(store, {id: crypto.randomUUID(), filetype: 'image/jpeg'});
            const mediaRequest = prepareMediaRequestMock();
            vi.mocked(mediaRequest.moveFile).mockRejectedValue(new Error(crypto.randomUUID()));
            const sut = getSut({store, mediaRequest});

            await sut.moveFile(fileItem, prepareItem(store, {id: crypto.randomUUID(), filetype: 'directory'}));

            expect(fileItem.isConnected).toBe(true);
        });

        it('should not send a request for an item without id', async () => {
            const store = new DataStore();
            const mediaRequestSpy = prepareMediaRequestMock();
            const sut = getSut({store, mediaRequest: mediaRequestSpy});

            await sut.moveFile(
                prepareItem(store, {filetype: 'image/jpeg'}),
                prepareItem(store, {id: crypto.randomUUID(), filetype: 'directory'}),
            );

            expect(mediaRequestSpy.moveFile).not.toHaveBeenCalled();
        });
    });

    /**
     * A media item in its list column, like uiRenderer.addMediaItem() creates it.
     */
    const prepareItem = (store: DataStoreInterface, data: {id?: string; filetype: string}): HTMLElement => {
        const column = document.createElement('div');
        column.className = 'dd-media-col';
        const item = document.createElement('a');
        item.className = 'dd-media-item';
        column.append(item);
        document.body.append(column);

        store.setMultiple(item, data);

        return item;
    };

    const prepareMediaRequestMock = ({
        result = {success: true, msg: ''},
    }: {
        result?: MediaActionResult;
    } = {}): MediaRequestInterface => InterfaceMock<MediaRequestInterface>({
        moveFile: vi.fn().mockResolvedValue(result),
    });

    const getSut = ({
        store = new DataStore(),
        mediaRequest = prepareMediaRequestMock(),
        interaction = InterfaceMock<InteractionInterface>(),
        language = InterfaceMock<LanguageInterface>({translate: (key: string) => `[${key}]`}),
    }: {
        store?: DataStoreInterface;
        mediaRequest?: MediaRequestInterface;
        interaction?: InteractionInterface;
        language?: LanguageInterface;
    } = {}): DragDropHandlerInterface => new DragDropHandler(store, mediaRequest, interaction, language);
});
