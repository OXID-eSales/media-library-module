/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

$.noConflict();

+function( $ )
{
    'use strict';

    var tooltipPlugin = $.fn.tooltip;

    $.fn.tooltip = function( options )
    {
        if (Object.prototype.toString.call(options) === "[object Object]") {
            options.container = '#ddoew';
        }

        return tooltipPlugin.call( this, options );
    };

}( jQuery );
