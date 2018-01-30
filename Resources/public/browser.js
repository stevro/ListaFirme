/* 
 * This code belongs to NIMA Software SRL | nimasoftware.com
 * For details contact contact@nimasoftware.com
 */


"use strict";
var page = require('webpage').create(),
        server = 'http://mfinante.ro/infocodfiscal.html',
        data = 'cod=34150371';

page.open(server, 'post', data, function (status) {

    if (status === 'success')
    {
        page.onLoadFinished = function (status) {
            return page.content; // actual page
            phantom.exit();
        };
    }
});