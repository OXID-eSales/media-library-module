/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Mirrors the MediaController::remove() and ::movefile() responses.
 */
export type MediaActionResult = {
    success: boolean;
    /** Translation key of the error, empty on success. */
    msg: string;
};
