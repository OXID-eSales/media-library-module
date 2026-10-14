/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import 'reflect-metadata';
import {container} from 'tsyringe';

import './settings/services.ts';
import './dialog/services.ts';
import './language/services.ts';
import './interaction/services.ts';
import './media/services.ts';
import './altText/services.ts';

export {container as DIContainer};
