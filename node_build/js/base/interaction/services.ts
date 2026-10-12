/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {container} from 'tsyringe';
import {InteractionInterface} from './InteractionInterface.ts';
import Interaction from './Interaction.ts';

container.registerSingleton(InteractionInterface, Interaction);
