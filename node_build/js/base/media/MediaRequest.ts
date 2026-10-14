/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {inject, injectable} from 'tsyringe';
import {MediaRequestInterface} from './MediaRequestInterface.ts';
import {SettingsInterface} from '../settings/SettingsInterface.ts';
import Logger from '../Logger.ts';
import type {MediaItem} from '../types/MediaItem.ts';
import type {MediaListPage} from './types/MediaListPage.ts';
import type {MediaActionResult} from './types/MediaActionResult.ts';
import type {RenamedMedia} from './types/RenamedMedia.ts';
import type {CreatedFolder} from './types/CreatedFolder.ts';
import type {AltTexts, AltTextSaveResult} from './types/AltTexts.ts';

const PAGE_SIZE = 18;

@injectable()
export default class MediaRequest implements MediaRequestInterface {
    constructor(
        @inject(SettingsInterface) private readonly settings: SettingsInterface,
    ) {}

    async loadFiles(folderId: string, tab: string): Promise<string> {
        const params = this.#mediaParams();
        if (tab) {
            params.append('tab', tab);
        }
        if (folderId) {
            params.append('folderid', folderId);
        }

        const response = await this.#fetch('loadFiles', this.settings.getActionUrl(params));
        return response.text();
    }

    async fetchMoreFiles(page: number, folderId: string): Promise<MediaListPage> {
        const params = this.#mediaParams('moreFiles');
        params.append('start', String(page * PAGE_SIZE));
        params.append('folderid', folderId);

        const response = await this.#fetch('fetchMoreFiles', this.settings.getActionUrl(params));
        const data: {
            files: (Omit<MediaItem, 'imagesize'> & {imageSize: string})[];
            more: boolean;
        } = await response.json();

        return {
            files: data.files.map(({imageSize, ...file}) => ({...file, imagesize: imageSize})),
            more: data.more,
        };
    }

    async moveFile(fileId: string, targetFolderId: string): Promise<MediaActionResult> {
        const body = new URLSearchParams({sourceid: fileId, targetid: targetFolderId});

        return this.#post('moveFile', this.#mediaParams('movefile'), body);
    }

    async renameFile(id: string, newName: string): Promise<RenamedMedia> {
        const body = new URLSearchParams({id, newname: newName});

        return this.#post('renameFile', this.#mediaParams('rename'), body);
    }

    async removeFiles(ids: string[]): Promise<MediaActionResult> {
        const params = this.#mediaParams('remove');
        ids.forEach(id => params.append('ids[]', id));

        const response = await this.#fetch('removeFiles', this.settings.getActionUrl(params));
        return response.json();
    }

    async addFolder(name: string): Promise<CreatedFolder> {
        return this.#post('addFolder', this.#mediaParams('addFolder'), new URLSearchParams({name}));
    }

    getUploadUrl(folderId: string): string {
        const params = this.#mediaParams('upload');
        params.append('folderid', folderId);

        return this.settings.getActionUrl(params);
    }

    async getAltTexts(objectId: string): Promise<AltTexts> {
        const params = new URLSearchParams({cl: 'ddoemedialibrary_media_alt_text', fnc: 'getAltTexts', objectId});

        const response = await this.#fetch('getAltTexts', this.settings.getActionUrl(params));
        const data: {altTexts: AltTexts | string[]} = await response.json();

        return Object.fromEntries(Object.entries(data.altTexts));
    }

    async saveAltTexts(objectId: string, altTexts: AltTexts): Promise<AltTextSaveResult> {
        const params = new URLSearchParams({cl: 'ddoemedialibrary_media_alt_text', fnc: 'saveAltText'});
        const body = new URLSearchParams({objectId});
        Object.entries(altTexts).forEach(([languageId, text]) => body.append(`altTexts[${languageId}]`, text));

        return this.#post('saveAltTexts', params, body);
    }

    #mediaParams(fnc = ''): URLSearchParams {
        const params = new URLSearchParams({cl: 'ddoemedia_view'});
        if (fnc) {
            params.append('fnc', fnc);
        }

        return params;
    }

    async #post<T>(method: string, params: URLSearchParams, body: URLSearchParams): Promise<T> {
        const response = await this.#fetch(method, this.settings.getActionUrl(params), {method: 'POST', body});
        return response.json();
    }

    async #fetch(method: string, url: string, init: RequestInit = {}): Promise<Response> {
        const response = await fetch(url, {...init, credentials: 'same-origin'});
        if (!response.ok) {
            const error = await this.#readError(response);
            Logger.log(`MediaRequest.${method}: ${response.status} ${error}`, 'error');
            throw new Error(error);
        }

        return response;
    }

    /**
     * The shop sends validation errors as {"error": "<message>"}.
     */
    async #readError(response: Response): Promise<string> {
        try {
            const body: unknown = await response.json();
            if (typeof body === 'object' && body !== null && 'error' in body && typeof body.error === 'string') {
                return body.error;
            }
        } catch {
            // not json, use the status below
        }

        return `${response.status} ${response.statusText}`;
    }
}
