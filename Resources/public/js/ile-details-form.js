jQuery(function ($) {
    'use strict';

    let tabActive = '.tab-pane.active',
        $form = $('.ile-details-form'),
        $formAdd = $form.find('.ile-details-add'),
        $list = $form.find('table'),
        $amount = $form.find('.amount'),
        $category = $formAdd.find('select.category');

    // init
    $('.tab-pane').each(function(i, tab) {
        updateSelectableTypes($(tab));
        $(tab).find($category).val($(tab).find($category).find('option:enabled').eq(1).val());
    });

    // submit by enter
    $formAdd.on('keypress', 'input.amount', function(event) {
        if (event.which === 13) {
            event.preventDefault();
            addDetails();
        }
    });

    // submit
    $formAdd.on('click', '[data-add]', function(event) {
        addDetails();
    });

    $form.on('keyup', '.shares-table input[type="number"]', function(e) {
        try {
            let totalAmount = 0;
            $('.shares-table tbody tr').each(function() {
                let unitPrice = parseInt($(this).data('unit-price'));
                let amount = parseInt($(this).find('input[type="number"]').val());
                totalAmount += unitPrice * amount / 1000;
            });
            let $amountField = $('.investment-amount');
            if ($amountField) {
                $amountField.val(totalAmount);
            }
        } catch(error) {}
    });

    // remove
    $form.on('click', '.remove-details', function(e) {
        try {
            let tr = $(this).closest('tr');
            if (tr) {
                let unitPrice = parseInt(tr.data('unit-price'));
                let amount = parseInt(tr.data('amount'));
                updateInvestmentAmount(amount, unitPrice, false);
            }
        } catch(error) {}

        $(this).closest('tr').remove();
        $(this).closest('form').change();
        updateSelectableTypes($(tabActive));
    });

    function updateSelectableTypes($wrapper) {
        let existingTypes = [];
        $wrapper.find($list).find('tbody tr').each(function(index, row) {
            existingTypes.push($(row).data('type'));
        });

        $wrapper.find($category).find('option').each(function(index, item) {
            let option = $(item),
                optionVal = parseInt(option.attr('value'));
            if (-1 !== existingTypes.indexOf(optionVal)) {
                option.attr('disabled', 'disabled');
            } else {
                option.removeAttr('disabled');
            }
        });
    }

    function addDetails() {
        // get active tab objects
        let $aCategory = $(tabActive).find($category),
            $aAmount = $(tabActive).find($amount),
            $aList = $(tabActive).find($list);

        $aCategory[0].reportValidity();
        $aAmount[0].reportValidity();
        if ('' === $aCategory.val() || '' === $aAmount.val()) {
            return;
        }

        let html = $aList.data('prototype');
        html = html.replace(/__name__/g, $aList.find('tbody tr[data-key]').length ? parseInt($aList.find('tbody tr:last').data('key')) + 1 : 0);
        html = html.replace(/__amount__/g, $aAmount.val());
        html = html.replace(/__category__/g, $aCategory.val());
        html = html.replace(/__category_label__/g, $aCategory.find(':selected').data('name'));
        html = html.replace(/__unit_price__/g, $aCategory.find(':selected').data('unit-price'));

        updateInvestmentAmount($aAmount.val(), parseInt($aCategory.find(':selected').data('unit-price')), true);

        $aList.find('tbody').append(html);
        let $emptyRow = $aList.find('.empty-row');
        if ($emptyRow.length > 0) {
            $emptyRow.remove();
        }
        $aList.find('input').change();
        updateSelectableTypes($(tabActive));
        $aCategory.val($aCategory.find('option:enabled').eq(1).val());
        $aAmount.val('');
        $aAmount.focus();
    }

    function updateInvestmentAmount(amount, unitPrice, increase)
    {
        if (unitPrice > 0) {
            let $amountField = $('.investment-amount');
            if ($amountField) {
                let currentAmount = parseInt($amountField.val());
                currentAmount = currentAmount ? currentAmount : 0;
                if (increase) {
                    currentAmount += amount * unitPrice / 1000;
                } else {
                    currentAmount -= amount * unitPrice / 1000;
                }
                $amountField.val(currentAmount);
            }
        }
    }
});