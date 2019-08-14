// @TODO vik rename
jQuery(function ($) {
    'use strict';

    let $form = $('.investor-main-form'),
        select = $('.contact-merged-autocomplete-select'),
        contacts = $('ul.company-contacts'),
        $tabs = $('.tab-pane'),
        $ileAdd = $('#ile-add'),
        $ileRemove = $('.ile-remove'),
        $ileAddSelect = $('#ile-add').find('[name=legal-entity]'),
        $legalEntities = $('#legal-entities-tabs'),
        ileNew = {},
        ileNavNew = {},
        tabActive = '.tab-pane.active',
        navActive = '#legal-entities-tabs li.active';

    $tabs.each(function(i, tab) {
        if (!$(tab).find('.ile_contact_primary:checked').length) {
            $(tab).find('.ile_contact_primary').first().attr('checked', true);
        }

        $(tab).find('.investment-amount').each(function(i, investmentAmount) {
            var investmentAmountOverridden = $(investmentAmount).parent().parent().find('.investment-amount-overridden');
            investmentAmountOverridden.on('change', function () {
                $(investmentAmount).attr('readonly', $(this).is(':checked') !== true);
                if ($(this).is(':checked')) {
                    $(investmentAmount).focus().select();
                }
            });
            investmentAmountOverridden.trigger('change');
        });
    });

    select.on('select2:select', function() {
        if (!$(tabActive).find('.ile_contact_primary:checked').length) {
            $(tabActive).find('.ile_contact_primary').first().attr('checked', true);
        }
    });

    contacts.on('click', '.remove-item', function() {
        if ($(this).closest('li').find('.ile_contact_primary').is(':checked') && contacts.length > 1) {
            $(this).closest('li').next().find('.ile_contact_primary').attr('checked', true);
        }
    });

    $form.on('select2:select', '.contact-autocomplete select', function(e) {
        var contact = $(this).select2('data');
        if (contact[0] && contact[0].company) {
            var companySelect = $(e.currentTarget).closest('.panel-body').find('.company-autocomplete select'),
                company = companySelect.select2('data');
            if (!company.length) {
                company = {
                    id: contact[0].company.id,
                    text: contact[0].company.name,
                }
                var option = new Option(company.text, company.id, true, true);
                companySelect.append(option).trigger('change');
                companySelect.trigger({
                    type: 'select2:select',
                    params: {
                        data: company
                    }
                });
            }
        }
    });

    // investor legal entities
    $('.tab-pane.hidden').each(function(i, legalEntity) {
        ileNew[$(legalEntity).data('id')] = $(legalEntity).removeClass('hidden').detach();
    });
    $legalEntities.find('li.hidden').each(function(i, nav) {
        ileNavNew[$(nav).data('id')] = $(nav).removeClass('hidden').detach();
    });

    $legalEntities.find('[data-id]:not(.hidden)').each(function(i, id) {
        $ileAddSelect.find(`option[value=${$(id).data('id')}]`).attr('disabled', 'disabled');
    });

    $ileAdd.on('submit', function(e) {
        if (!$ileAddSelect.val()) {
            return;
        }

        e.preventDefault();
        $(this).parent().modal('toggle');
        ileAdd();
    });

    $ileRemove.on('submit', function(e) {
        e.preventDefault();
        $(this).parent().modal('toggle');
        ileRemove($(this));
    });

    function ileRemove($removeForm) {
        if ($legalEntities.find('[data-id]').length === 1 && !confirm($removeForm.data('confirm'))) {
            return
        }

        if ($legalEntities.find('[data-id]').length === 1) {
            $form.submit();
        } else {
            if ($removeForm.attr('action')) {
                $removeForm.unbind('submit').submit();
            } else {
                onIleRemove();
            }
        }
    }

    function onIleRemove() {
        // enable ile for adding
        $ileAddSelect.find('option[value="'+ $(tabActive).data('id') +'"]').prop('disabled', false);
        $(tabActive).remove();
        $(navActive).remove();
        $('#investor-tab a').tab('show');
    }

    function ileAdd() {
        // update select
        let $selected = $ileAddSelect.find('option:selected');
        $selected.prop('disabled', true);
        setTimeout(function() {
            $ileAddSelect[0].selectedIndex = 0;
        }, 500);

        // remove
        $(tabActive).removeClass('active');
        $(navActive).removeClass('active');

        // add
        let a = ileNavNew[$selected.val()];
        $('.tab-content').append(ileNew[$selected.val()]);
        $legalEntities.append(a);
        a.find('a').tab('show');
    }
});