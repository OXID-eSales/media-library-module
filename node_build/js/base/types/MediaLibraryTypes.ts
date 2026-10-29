/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export type MediaLibraryOptions = {
    /** Matched against the mime type, e.g. new RegExp('image/.*', 'i'). */
    filter?: RegExp;
    multiple?: boolean;
};

export type MediaFile = {
    id: string;
    file: string;
    url: string;
    type: string;
};

/**
 * Called with the dialog element as `this` when multiple=false.
 */
export type SingleMediaCallback = (this: HTMLElement, id: string, file: string, url: string, type: string) => void;

/**
 * Called with the dialog element as `this` when multiple=true.
 */
export type MultipleMediaCallback = (this: HTMLElement, files: MediaFile[]) => void;
