/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Alt texts of a media file by language id.
 */
export type AltTexts = Record<string, string>;

/**
 * Mirrors the MediaAltTextController::saveAltText() response.
 */
export type AltTextSaveResult = {
    success: boolean;
    /** Translated success message. */
    message: string;
};
