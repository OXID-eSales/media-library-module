/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {container} from 'tsyringe';
import {SettingsInterface} from './SettingsInterface.ts';
import Settings from './Settings.ts';

container.registerSingleton(SettingsInterface, Settings);
