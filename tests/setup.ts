import 'reflect-metadata';
import jQuery from 'jquery';

// The admin page loads jQuery as a global before the media library, jQuery UI extends that global.
Object.assign(globalThis, {$: jQuery, jQuery});
