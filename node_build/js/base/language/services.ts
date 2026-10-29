/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {container} from 'tsyringe';
import {LanguageInterface} from './LanguageInterface.ts';
import Language from './Language.ts';
import {TranslationsRequestInterface} from './TranslationsRequestInterface.ts';
import TranslationsRequest from './TranslationsRequest.ts';

container.registerSingleton(TranslationsRequestInterface, TranslationsRequest);
container.registerSingleton(LanguageInterface, Language);
