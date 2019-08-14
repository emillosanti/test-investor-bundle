jQuery(function ($) {
    'use strict';

    var form = $('.investors-search'),
        body = $('body'),
        loader = $('.loader'),
        results = $('.results-wrapper'),
        listWrapper = $('.investor_search_results'),
        stepChoices = form.find('.investor-step-wrapper'),
        dateRangeWidget = form.find('.daterange-widget'),
        timeout = null,
        selectQuery = form.find('.query_autocomplete_wrapper select');

    body.on('loader.start', function() {
        if (form && form.length > 0) {
            results.hide();
            loader.html('<div class="loading"><section class="loader"><svg class="spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg"><circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle></svg></section></div>');
        }
    });

    body.on('legalentity.updated', function(event, data) {
        if (form && form.length > 0) {
            process(form);
        }
    });

    var process = function() {
        $.ajax({
            url: form.attr('action'),
            method: 'GET',
            data: form.serialize(),
            beforeSend: function() {
                results.hide();
                loader.html('<div class="loading"><section class="loader"><svg class="spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg"><circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle></svg></section></div>');
            },
        }).done(function (response) {
            if ('html' in response) {
                results.html(response.html);
            }
            if ('count' in response) {
                let $counter = $('#investor-results-count');

                if ($counter.length) {
                    $counter.find('span').html(response.count);

                    $counter.show();
                }
            }
            if ('exportButton' in response ) {
                let $button = $('a.export-investors');

                if ($button.length) {
                    if (response.exportButton === true) {
                        $button.removeClass('disabled');
                    } else {
                        $button.addClass('disabled');
                    }
                }
            }

            window.history.pushState(null, null, form.attr('action') + '?' + form.serialize());
        }).always(function() {
            loader.html('');
            results.show();
        });
    };

    form.on('click', '.export-investors', function(event) {
        event.preventDefault();
        $('#export-enabled').val('1');
        form.submit();
        $('#export-enabled').val('0');
    });

    stepChoices.find('label').each(function(index, element) {
        var jqElement = $(element),
            input = jqElement.parent().find('input'),
            color = input.data('color');
        if (color) {
            jqElement.hover(
                function () {
                    jqElement.css('color', '#fff');
                    jqElement.css('background', color);
                },
                function () {
                    if (!input.is(':checked')) {
                        jqElement.css('color', color);
                        jqElement.css('background', '#fff');
                    }
                }
            )
        }
    });

    var updateStepColor = function(){
        stepChoices.find('input[type="radio"]').each(function(index, element) {
            var radio = $(element),
                label = radio.parent().find('label'),
                dataColor = radio.data('color'),
                color = dataColor,
                background = '#fff';

            if (radio.is(':checked')) {
                color = '#fff';
                background = dataColor;
            }

            if (dataColor) {
                label.css('color', color);
                label.css('background', background);
            }
        });
    };

    stepChoices.find('input[type="radio"]').on('change', updateStepColor);

    function getDateRanges(){
        var ranges = {};

        for (var i = 0; i < 4; i++) {
            var m = moment().subtract(i * 3, 'months');
            ranges['T' + m.quarter() + ' ' + m.year()] = [
                moment().subtract(i * 3, 'months').startOf('quarter'),
                moment().subtract(i * 3, 'months').endOf('quarter')
            ];
        }

        if (moment().month() < 6) {
            ranges['S1 ' + moment().year()] = [
                moment().startOf('year'),
                moment().endOf('year').subtract(6, 'months')
            ];
            ranges['S2 ' + (moment().year() - 1)] = [
                moment().subtract(1, 'year').startOf('year').add(6, 'months'),
                moment().subtract(1, 'year').endOf('year'),
            ];
        } else {
            ranges['S2 ' + moment().year()] = [
                moment().startOf('year').add(6, 'months'),
                moment().endOf('year'),
            ];
            ranges['S1 ' + moment().year()] = [
                moment().startOf('year'),
                moment().endOf('year').subtract(6, 'months')
            ];
        }

        ranges[moment().year()] = [moment().startOf('year'), moment().endOf('year')];
        ranges[moment().year() - 1] = [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')];

        return ranges;
    }

    form.find('input[name="daterange"]').daterangepicker({
        "showDropdowns": true,
        "locale": {
            "format": "DD/MM/YYYY",
            "separator": " - ",
            "applyLabel": "Appliquer",
            "cancelLabel": "Annuler",
            "fromLabel": "De",
            "toLabel": "A",
            "customRangeLabel": "Personnalisé",
            "weekLabel": "S",
            "daysOfWeek": [
                "Di",
                "Lu",
                "Ma",
                "Me",
                "Je",
                "Ve",
                "Sa"
            ],
            "monthNames": [
                "Janvier",
                "Février",
                "Mars",
                "Avril",
                "Mai",
                "Juin",
                "Juillet",
                "Août",
                "Septembre",
                "Octobre",
                "Novembre",
                "Décembre"
            ],
            "firstDay": 1
        },
        "showISOWeekNumbers": true,
        ranges: getDateRanges(),
        "alwaysShowCalendars": true,
        "startDate": form.find('#investor_search_dateRange_start').val() ? form.find('#investor_search_dateRange_start').val() : moment().subtract(1, 'year').startOf('day'),
        "endDate": form.find('#investor_search_dateRange_end').val() ? form.find('#investor_search_dateRange_end').val() : moment().endOf('day')
    }, function(start, end, label) {
        form.find('#investor_search_dateRange_start').val(start.format('DD/MM/YYYY'));
        form.find('#investor_search_dateRange_end').val(end.format('DD/MM/YYYY'));
    });

    form.on('change', function () {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            process(form);
        }, 500);
    });

    if (!form.find('#investor_search_dateRange_start').val()) {
        form.find('#investor_search_dateRange_start').val(moment().subtract(1, 'year').startOf('day').format('DD/MM/YYYY'));
    }
    if (!form.find('#investor_search_dateRange_end').val()) {
        form.find('#investor_search_dateRange_end').val(moment().endOf('day').format('DD/MM/YYYY'));
    }

    selectQuery.select2({
        placeholder: "",
        allowClear: true,
        language: {
            errorLoading: function () {
                return 'Les résultats ne peuvent pas être chargés.';
            },
            inputTooLong: function (args) {
                var overChars = args.input.length - args.maximum;
                return 'Supprimez ' + overChars + ' caractère' +
                    ((overChars > 1) ? 's' : '');
            },
            inputTooShort: function (args) {
                var remainingChars = args.minimum - args.input.length;
                return 'Saisissez au moins ' + remainingChars + ' caractère' + ((remainingChars > 1) ? 's' : '');
            },
            loadingMore: function () {
                return 'Chargement de résultats supplémentaires…';
            },
            maximumSelected: function (args) {
                return 'Vous pouvez seulement sélectionner ' + args.maximum + ' élément' + ((args.maximum > 1) ? 's' : '');
            },
            searching: function () {
                return 'Recherche en cours…';
            },
            removeAllItems: function () {
                return 'Supprimer tous les articles';
            },
            noResults: function () {
                return "Aucun résultat.";
            }
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        ajax: {
            url: selectQuery.data('url'),
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    search: {
                        query: encodeURIComponent(params.term)
                    }
                };
            },
            processResults: function (data) {
                return {
                    results: typeof selectMultiTransformer === 'function' ? selectMultiTransformer(data) : data
                };
            }
        }
    });

    selectQuery.on('select2:selecting', function(e) {
        if (e.params && e.params.args.originalEvent && e.params.args.originalEvent.currentTarget) {
            let link = $(e.params.args.originalEvent.currentTarget).find('a');
            if (link) {
                window.location = link.attr('href');
                e.preventDefault();
            }
        }
    });
});
