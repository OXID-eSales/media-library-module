/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export const AltTextManagerInterface = Symbol('AltTextManagerInterface');

export interface AltTextManagerInterface {
    /**
     * Loads the alt texts of the media file into the details form and binds its
     * language select and save button. Resolves when the alt texts are shown.
     */
    show(objectId: string, detailForm: HTMLElement): Promise<void>;
}
