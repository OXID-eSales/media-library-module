/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import type {MediaUrlStore} from './MediaUrlStore.ts';

declare global {
    interface Window {
        /** Translations, set by the MediaLangJs controller. */
        i18n?: Record<string, string>;
        /** The admin content frame, reached through `top.basefrm`. */
        basefrm?: Window;
        mediaUrls?: MediaUrlStore;
        /** Overlay of the wysiwyg editor module. */
        OverlayInstance?: {
            $overlay: JQuery;
            overlayContext?: {
                invoke(action: string, url: string, callback: ($image: JQuery) => void): void;
            } | null;
            onContentLoad(callback: (this: NonNullable<Window['OverlayInstance']>) => void): void;
            hideOverlay(): void;
        };
    }
}

export {};
