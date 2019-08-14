jQuery(function ($) {
    'use strict';

    let form = $('.investor-add-form'),
        ileNav = '#legal-entities-tabs li[data-id]';

    form.on('submit', function(e) {
        if ($(ileNav).length) {
            return;
        }

        e.preventDefault();
        $('#ile-add-modal').modal('toggle');
    });
});