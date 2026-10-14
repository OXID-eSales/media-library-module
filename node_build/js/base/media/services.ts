/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {container} from 'tsyringe';
import {MediaRequestInterface} from './MediaRequestInterface.ts';
import MediaRequest from './MediaRequest.ts';

container.registerSingleton(MediaRequestInterface, MediaRequest);
