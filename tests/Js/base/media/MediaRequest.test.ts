/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import MediaRequest from '../../../../node_build/js/base/media/MediaRequest.ts';
import {MediaRequestInterface} from '../../../../node_build/js/base/media/MediaRequestInterface.ts';
import {SettingsInterface} from '../../../../node_build/js/base/settings/SettingsInterface.ts';
import {InterfaceMock} from '../InterfaceMock.ts';

describe('MediaRequest', () => {
    beforeEach(() => {
        vi.stubGlobal('fetch', vi.fn());
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    describe('loadFiles', () => {
        it('should request the folder and tab and return the html', async () => {
            const folderId = crypto.randomUUID();
            const html = `<div>${crypto.randomUUID()}</div>`;
            prepareFetchMock(new Response(html));
            const sut = getSut();

            const result = await sut.loadFiles(folderId, 'upload');

            expect(fetch).toHaveBeenCalledWith(
                `[cl=ddoemedia_view&tab=upload&folderid=${folderId}]`,
                {credentials: 'same-origin'},
            );
            expect(result).toBe(html);
        });

        it('should leave out an empty folder and tab', async () => {
            prepareFetchMock(new Response(''));
            const sut = getSut();

            await sut.loadFiles('', '');

            expect(fetch).toHaveBeenCalledWith('[cl=ddoemedia_view]', {credentials: 'same-origin'});
        });
    });

    describe('fetchMoreFiles', () => {
        it('should request the page start of the folder', async () => {
            const folderId = crypto.randomUUID();
            prepareFetchMock(prepareJsonResponse({files: [], more: false}));
            const sut = getSut();

            await sut.fetchMoreFiles(2, folderId);

            expect(fetch).toHaveBeenCalledWith(
                `[cl=ddoemedia_view&fnc=moreFiles&start=36&folderid=${folderId}]`,
                {credentials: 'same-origin'},
            );
        });

        it('should rename imageSize of the shop response to imagesize', async () => {
            const file = {
                id: crypto.randomUUID(),
                file: `${crypto.randomUUID()}.jpg`,
                filetype: 'image/jpeg',
                filesize: 2048,
                thumb: `${crypto.randomUUID()}.jpg`,
            };
            prepareFetchMock(prepareJsonResponse({files: [{...file, imageSize: '800x600'}], more: true}));
            const sut = getSut();

            const result = await sut.fetchMoreFiles(0, '');

            expect(result).toEqual({files: [{...file, imagesize: '800x600'}], more: true});
        });
    });

    describe('moveFile', () => {
        it('should post only the file and the target folder', async () => {
            const fileId = crypto.randomUUID();
            const targetFolderId = crypto.randomUUID();
            const result = {success: true, msg: ''};
            prepareFetchMock(prepareJsonResponse(result));
            const sut = getSut();

            expect(await sut.moveFile(fileId, targetFolderId)).toEqual(result);
            expect(fetch).toHaveBeenCalledWith('[cl=ddoemedia_view&fnc=movefile]', {
                method: 'POST',
                body: new URLSearchParams({sourceid: fileId, targetid: targetFolderId}),
                credentials: 'same-origin',
            });
        });
    });

    describe('renameFile', () => {
        it('should post the new name and return the renamed media', async () => {
            const id = crypto.randomUUID();
            const newName = crypto.randomUUID();
            const result = {id, name: `${newName}_1`};
            prepareFetchMock(prepareJsonResponse(result));
            const sut = getSut();

            expect(await sut.renameFile(id, newName)).toEqual(result);
            expect(fetch).toHaveBeenCalledWith('[cl=ddoemedia_view&fnc=rename]', {
                method: 'POST',
                body: new URLSearchParams({id, newname: newName}),
                credentials: 'same-origin',
            });
        });

        it('should reject with the error the shop sent', async () => {
            const error = crypto.randomUUID();
            prepareFetchMock(prepareJsonResponse({error}, 400));
            const sut = getSut();

            await expect(sut.renameFile(crypto.randomUUID(), crypto.randomUUID())).rejects.toThrow(error);
        });
    });

    describe('removeFiles', () => {
        it('should request the removal of all ids', async () => {
            const ids = [crypto.randomUUID(), crypto.randomUUID()];
            const result = {success: true, msg: ''};
            prepareFetchMock(prepareJsonResponse(result));
            const sut = getSut();

            expect(await sut.removeFiles(ids)).toEqual(result);
            expect(fetch).toHaveBeenCalledWith(
                `[cl=ddoemedia_view&fnc=remove&ids%5B%5D=${ids[0]}&ids%5B%5D=${ids[1]}]`,
                {credentials: 'same-origin'},
            );
        });
    });

    describe('addFolder', () => {
        it('should post the folder name and return the created folder', async () => {
            const name = crypto.randomUUID();
            const result = {id: crypto.randomUUID(), name};
            prepareFetchMock(prepareJsonResponse(result));
            const sut = getSut();

            expect(await sut.addFolder(name)).toEqual(result);
            expect(fetch).toHaveBeenCalledWith('[cl=ddoemedia_view&fnc=addFolder]', {
                method: 'POST',
                body: new URLSearchParams({name}),
                credentials: 'same-origin',
            });
        });

        it('should reject with the status when the error is not json', async () => {
            prepareFetchMock(new Response('<html></html>', {status: 500, statusText: 'Internal Server Error'}));
            const sut = getSut();

            await expect(sut.addFolder(crypto.randomUUID())).rejects.toThrow('500 Internal Server Error');
        });
    });

    describe('getUploadUrl', () => {
        it('should return the upload url of the folder', () => {
            const folderId = crypto.randomUUID();
            const sut = getSut();

            expect(sut.getUploadUrl(folderId)).toBe(`[cl=ddoemedia_view&fnc=upload&folderid=${folderId}]`);
        });
    });

    describe('getAltTexts', () => {
        it('should request the alt texts of the object', async () => {
            const objectId = crypto.randomUUID();
            const altTexts = {1: crypto.randomUUID(), 2: crypto.randomUUID()};
            prepareFetchMock(prepareJsonResponse({success: true, altTexts}));
            const sut = getSut();

            expect(await sut.getAltTexts(objectId)).toEqual(altTexts);
            expect(fetch).toHaveBeenCalledWith(
                `[cl=ddoemedialibrary_media_alt_text&fnc=getAltTexts&objectId=${objectId}]`,
                {credentials: 'same-origin'},
            );
        });

        it('should turn the json list the shop sends for language ids 0, 1… into an object', async () => {
            const altTexts = [crypto.randomUUID(), crypto.randomUUID()];
            prepareFetchMock(prepareJsonResponse({success: true, altTexts}));
            const sut = getSut();

            expect(await sut.getAltTexts(crypto.randomUUID())).toEqual({0: altTexts[0], 1: altTexts[1]});
        });

        it('should return an empty object when there are no alt texts', async () => {
            prepareFetchMock(prepareJsonResponse({success: true, altTexts: []}));
            const sut = getSut();

            expect(await sut.getAltTexts(crypto.randomUUID())).toEqual({});
        });
    });

    describe('saveAltTexts', () => {
        it('should post the alt texts by language id', async () => {
            const objectId = crypto.randomUUID();
            const altTexts = {0: crypto.randomUUID(), 1: crypto.randomUUID()};
            const result = {success: true, message: crypto.randomUUID()};
            prepareFetchMock(prepareJsonResponse(result));
            const sut = getSut();

            expect(await sut.saveAltTexts(objectId, altTexts)).toEqual(result);
            expect(fetch).toHaveBeenCalledWith('[cl=ddoemedialibrary_media_alt_text&fnc=saveAltText]', {
                method: 'POST',
                body: new URLSearchParams({objectId, 'altTexts[0]': altTexts[0], 'altTexts[1]': altTexts[1]}),
                credentials: 'same-origin',
            });
        });
    });

    const prepareFetchMock = (response: Response): void => {
        vi.mocked(fetch).mockResolvedValue(response);
    };

    const prepareJsonResponse = (data: unknown, status = 200): Response =>
        new Response(JSON.stringify(data), {status});

    const getSut = ({
        settings = InterfaceMock<SettingsInterface>({getActionUrl: (params: URLSearchParams) => `[${params}]`}),
    }: {
        settings?: SettingsInterface;
    } = {}): MediaRequestInterface => new MediaRequest(settings);
});
