/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {container} from 'tsyringe';
import {DialogInterface} from './DialogInterface.ts';
import Dialog from './Dialog.ts';

container.registerSingleton(DialogInterface, Dialog);
