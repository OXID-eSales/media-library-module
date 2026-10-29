/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {describe, expect, it} from 'vitest';
import {DIContainer} from '../../../node_build/js/base/DIContainer.ts';
import {DialogInterface} from '../../../node_build/js/base/dialog/DialogInterface.ts';
import Dialog from '../../../node_build/js/base/dialog/Dialog.ts';
import {InteractionInterface} from '../../../node_build/js/base/interaction/InteractionInterface.ts';
import Interaction from '../../../node_build/js/base/interaction/Interaction.ts';
import {LanguageInterface} from '../../../node_build/js/base/language/LanguageInterface.ts';
import Language from '../../../node_build/js/base/language/Language.ts';
import {TranslationsRequestInterface} from '../../../node_build/js/base/language/TranslationsRequestInterface.ts';
import TranslationsRequest from '../../../node_build/js/base/language/TranslationsRequest.ts';
import {SettingsInterface} from '../../../node_build/js/base/settings/SettingsInterface.ts';
import Settings from '../../../node_build/js/base/settings/Settings.ts';
import {MediaRequestInterface} from '../../../node_build/js/base/media/MediaRequestInterface.ts';
import MediaRequest from '../../../node_build/js/base/media/MediaRequest.ts';
import {AltTextManagerInterface} from '../../../node_build/js/base/altText/AltTextManagerInterface.ts';
import AltTextManager from '../../../node_build/js/base/altText/AltTextManager.ts';
import {DataStoreInterface} from '../../../node_build/js/base/dataStore/DataStoreInterface.ts';
import DataStore from '../../../node_build/js/base/dataStore/DataStore.ts';
import {DragDropHandlerInterface} from '../../../node_build/js/base/dragDrop/DragDropHandlerInterface.ts';
import DragDropHandler from '../../../node_build/js/base/dragDrop/DragDropHandler.ts';

/**
 * Catches missing registrations and missing @inject decorators, which only fail at runtime.
 */
describe('DIContainer', () => {
    it.each([
        {token: DialogInterface, implementation: Dialog},
        {token: InteractionInterface, implementation: Interaction},
        {token: LanguageInterface, implementation: Language},
        {token: TranslationsRequestInterface, implementation: TranslationsRequest},
        {token: SettingsInterface, implementation: Settings},
        {token: MediaRequestInterface, implementation: MediaRequest},
        {token: AltTextManagerInterface, implementation: AltTextManager},
        {token: DataStoreInterface, implementation: DataStore},
        {token: DragDropHandlerInterface, implementation: DragDropHandler},
    ])('should resolve $token.description as a singleton', ({token, implementation}) => {
        const first = DIContainer.resolve(token);

        expect(first).toBeInstanceOf(implementation);
        expect(DIContainer.resolve(token)).toBe(first);
    });
});
