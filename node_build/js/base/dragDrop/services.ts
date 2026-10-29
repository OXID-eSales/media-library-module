/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {container} from 'tsyringe';
import {DragDropHandlerInterface} from './DragDropHandlerInterface.ts';
import DragDropHandler from './DragDropHandler.ts';

container.registerSingleton(DragDropHandlerInterface, DragDropHandler);
