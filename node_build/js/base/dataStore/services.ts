/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {container} from 'tsyringe';
import {DataStoreInterface} from './DataStoreInterface.ts';
import DataStore from './DataStore.ts';

container.registerSingleton(DataStoreInterface, DataStore);
