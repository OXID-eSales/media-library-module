/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {container} from 'tsyringe';
import {AltTextManagerInterface} from './AltTextManagerInterface.ts';
import AltTextManager from './AltTextManager.ts';

container.registerSingleton(AltTextManagerInterface, AltTextManager);
