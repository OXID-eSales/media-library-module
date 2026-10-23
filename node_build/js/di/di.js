/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Shared DI runtime for the admin modules.
 * Resolved via import map as "tsyringe", so all modules share one container instance.
 */
import 'reflect-metadata';

export * from 'tsyringe';
