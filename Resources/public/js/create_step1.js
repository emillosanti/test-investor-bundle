jQuery(function ($) {
    'use strict';

    $('.autocomplete-investor-import').on('select2:select', function (e) {
        $('#investor_import_companyOrContact').val(e.params.data.id).trigger('change');
        $('#investor_import_type').val(e.params.data.type).trigger('change');
    });
});