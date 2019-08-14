jQuery(function ($) {
    'use strict';

    var body = $('body'),
        wrapper = $('.investor-form'),
        selects = wrapper.find('.autocomplete-select'),
        timeEntryModal = $('#timeline-entry-modal'),
        documentsWrapper = $('.investor-documents'),
        documentModal = $('#document-modal'),
        cancelModal = $('#cancel-modal'),
        datepickerOptions = {
            weekStart: 1,
            language: "fr",
        },
        updateMainFormButton = $('.update-investor'),
        mainForm = wrapper.find('.investor-main-form'),
        legalEntitiesTabs = $('#legal-entities-tabs'),
        CA = 0,
        EBITDA = 1,
        EBIT = 2;

    function getNewIndex(list) {
        var maxIndex = 0;
        list.children('li').each(function(index, elt) {
            var key = parseInt($(elt).data('index'));
            if (key > maxIndex) {
                maxIndex = key;
            }
        });

        return maxIndex + 1;
    }

    function getDocumentIndex(){
        var maxIndex = 0;
        documentsWrapper.find('.tab-pane li').each(function(index, elt) {
            var key = parseInt($(elt).data('index'));
            if (key > maxIndex) {
                maxIndex = key;
            }
        });

        return maxIndex + 1;
    }

    function getDocumentSteps() {
        var maxStep = 0;
        documentsWrapper.find('.tab-pane ul').each(function(index, elt) {
            var key = parseInt($(elt).data('step'));
            if (key > maxStep) {
                maxStep = key;
            }
        });

        return maxStep;
    }

    function getDocumentStepIndexes(step){
        var maxIndex = -1;
        documentsWrapper.find('.tab-pane ul[data-step=' + step + '] li').each(function (index, elt) {
            var key = parseInt($(elt).data('index'));
            if (key > maxIndex) {
                maxIndex = key;
            }
        });

        return maxIndex;
    }

    legalEntitiesTabs.on('click', 'a', function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

    updateMainFormButton.on('click', function(event) {
        event.preventDefault();
        mainForm.submit();
    });

    wrapper.on('click', '.add-metric-item', function(event) {
        event.preventDefault();
        event.stopPropagation();
        var list = wrapper.find('#' + $(this).data('collection')),
            index = getNewIndex(list),
            prototype = list.data('prototype'),
            year = parseInt(list.find('li:first-child').data('year')) - 1;

        var template = prototype.replace(/__name__/g, index);
        var jqElement = $(template),
            option = jqElement.find('.year-wrapper select option[value="' + year + '"]');
        if (option.length > 0) {
            option.attr('selected', 'selected');
        }
        jqElement.attr('data-year', year);
        list.prepend(jqElement);
    });

    wrapper.on('click', '.company-metrics .remove-item', function(event) {
        event.preventDefault();
        $(this).closest('li').remove();
    });

    selects.each(function (index, element) {
        var select = $(element);
        select.cardCollection();
    });

    timeEntryModal.on('submit', 'form', function (event) {
        event.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize()
        }).done(function(response) {
            wrapper.find('.timeline-entries-wrapper').html(response);
            timeEntryModal.modal('hide');
        }).fail(function(xhr) {
            timeEntryModal.html($(xhr.responseText).html());
        });
    });

    timeEntryModal.on('shown.bs.modal', function () {
        timeEntryModal.find('.autocomplete-select').cardCollection();
        timeEntryModal.find('form .datepicker').datepicker(datepickerOptions)
    });

    timeEntryModal.on('hidden.bs.modal', function () {
        var form = timeEntryModal.find('form');
        $.ajax({
            url: form.attr('action')
        }).done(function(response) {
            form.replaceWith($(response).find('form'));
        });
    });

    documentModal.on('click', '#add-document-submit', function(event) {
        event.preventDefault();
        var nameInput = documentModal.find('#add-document-name'),
            urlInput = documentModal.find('#add-document-url'),
            isValid = nameInput[0].checkValidity() && urlInput[0].checkValidity();

        if (isValid) {
            var document = {
                name: nameInput.val(),
                url: urlInput.val(),
                user: documentsWrapper.data("user")
            };
            documentModal.modal('hide');
            addDocument(document);
        }
    });

    documentModal.on('hidden.bs.modal', function () {
        documentModal.find('#add-document-name').val("");
        documentModal.find('#add-document-url').val("");
    });

    body.on('click', '.add-dropbox-document-btn', function(event) {
        event.preventDefault();
        Dropbox.choose({
            linkType: "preview",
            folderselect: false,
            multiselect: true,
            success: function(files) {
                files.map(function(file) {
                    addDocument({
                        name: file.name,
                        url: file.link,
                        user: documentsWrapper.data("user")
                    });
                });
                saveDocuments();
            },
        });
    });

    documentsWrapper.on('click', '.remove-item, .remove-document', function(event) {
        event.preventDefault();
        $(this).closest('li').remove();
        saveDocuments();
    });

    var addDocument = function(document){
        var index = getDocumentIndex(),
            currentTab = documentsWrapper.find('.tab-pane.active'),
            tabList = currentTab.find('ul'),
            prototype = documentsWrapper.data('prototype'),
            currentStep = tabList.data('step'),
            formName = documentsWrapper.data('form-name');

        var template = prototype.replace(/__name__/g, index);
            template = template.replace(/__document_url__/g, document.url);
            template = template.replace(/__document_name__/g, document.name);
            template = template.replace(/__document_created_at__/g, moment().format('DD/MM/Y'));
            template = template.replace(/__document_user__/g, document.user);
            template = template.replace(/__document_user_picture__/g, document.user.picture);

        var jqElement = $(template);
        jqElement.find('#' + formName + '_documents_' + index + '_step').val(currentStep);
        jqElement.find('#' + formName + '_documents_' + index + '_name').val(document.name);
        jqElement.find('#' + formName + '_documents_' + index + '_url').val(document.url);

        tabList.append(jqElement);
    };

    var saveDocuments = function() {
        var steps = getDocumentSteps();
        var form = $(this).closest('form');
        var formName = documentsWrapper.data('form-name');
        var data = {}, dataKey = formName + '[documents]';
        data[dataKey] = null;

        for (var step = 1; step <= steps; step++) {
            var indexes = getDocumentStepIndexes(step);
            if (indexes === null) {
                continue;
            }
            for (var index = 0; index <= indexes; index++) {
                if (!$('#step_' + step + '_documents li[data-index=' + index + ']').length) {
                    continue;
                }
                data[formName + '[documents][' + index + '][step]'] = step;
                data[formName + '[documents][' + index + '][name]'] = $('#' + formName + '_documents_' + index + '_name').val();
                data[formName + '[documents][' + index + '][url]'] = $('#' + formName + '_documents_' + index + '_url').val();
            }
        }

        $.ajax({
            url : form.attr('action'),
            type: form.attr('method'),
            data : data,
            method: 'PATCH',
            success: function(html) {
            }
        });
    };

    cancelModal.on('submit', 'form', function(event) {
        event.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize()
        }).done(function(response) {
            if (response.redirectUrl) {
                document.location = response.redirectUrl;
            }
        }).fail(function(xhr) {
            cancelModal.html($(xhr.responseText).html());
        });
    });

    function addFloatingData() {
        var chartInstance = window.companyMetrics,
            ctx = chartInstance.ctx,
            allDatasets = this.data.datasets;

        ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, 'bold', Chart.defaults.global.defaultFontFamily);
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';

        this.data.datasets.forEach(function(dataset, i) {
            var meta = chartInstance.controller.getDatasetMeta(i);
            meta.data.forEach(function(bar, index) {
                var data = dataset.data[index],
                    ca = allDatasets[CA].data[index],
                    yPosition = ((bar._model.base + bar._model.y) / 2) + (Chart.defaults.global.defaultFontSize / 2);

                if (index > 0 && i === CA) {
                    var previous = dataset.data[index - 1];
                    var result = Math.round((data - previous) / previous * 100);
                    var label = '+' + result + '%';
                    if (result < 0) {
                        label = result + '%';
                    }

                    if (previous > 0) {
                        ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                        ctx.fillText(label, bar._model.x, yPosition);
                    }
                } else if (-1 !== [EBITDA, EBIT].indexOf(i) && ca > 0) {
                    var ratio = Math.round((data / ca) * 100);
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                    ctx.fillText(ratio + '%', bar._model.x, yPosition);
                }

                if (null !== data) {
                    ctx.fillStyle = dataset.backgroundColor;
                    if (data > 0) {
                        ctx.fillText(data + 'M€', bar._model.x, bar._model.y - 10);
                    } else {
                        ctx.fillText('(' + Math.abs(data) + 'M€)', bar._model.x, bar._model.base - 10);
                    }
                }
            });
        });
    }

    var loadCompanyMetricsChart = function() {
        if ($('#company-metrics-chart').length === 0) {
            return;
        }
        var rawData = $('#company-metrics-chart').data('chart');
        var datasets = [];
        if (rawData.datasets.ca) {
            datasets.push({
                        label: 'Chiffres d\'affaires',
                        backgroundColor: '#2196F3',
                        borderColor: '#2196F3',
                        borderWidth: 1,
                        data: rawData.datasets.ca
                    });
        }
        if (rawData.datasets.ebitda) {
            datasets.push({
                        label: 'EBITDA',
                        backgroundColor: '#13588e',
                        borderColor: '#13588e',
                        borderWidth: 1,
                        data: rawData.datasets.ebitda
                    });
        }
        if (rawData.datasets.ebit) {
            datasets.push({
                        label: 'EBIT',
                        backgroundColor: '#101821',
                        borderColor: '#101821',
                        borderWidth: 1,
                        data: rawData.datasets.ebit,
                    });
        }
        if (rawData.datasets.dfn) {
            datasets.push({
                        label: 'DFN',
                        backgroundColor: '#5E7CE2',
                        borderColor: '#5E7CE2',
                        borderWidth: 1,
                        data: rawData.datasets.dfn,
                    });
        }
        
        var chartData = {
            labels: rawData.labels,
            datasets: datasets
        };

        if ((rawData.datasets.ca && rawData.datasets.ca.length > 0) || (rawData.datasets.ebitda && rawData.datasets.ebitda.length > 0) || (rawData.datasets.ebit && rawData.datasets.ebit.length > 0)) {
            var ctx = document.getElementById('company-metrics-chart').getContext('2d');
            window.companyMetrics = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    legend: false,
                    title: false,
                    scales: {
                        yAxes: [{
                            display: false,
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    tooltips: {
                        enabled: true
                    },
                    animation: {
                        onComplete: addFloatingData
                    },
                    layout: {
                        padding: {
                            top: 40
                        }
                    },
                    events: []
                }
            });
        } else {
            $('#company-metrics-chart').remove();
            $('.metrics-chart-legend').remove();
        }
    };

    loadCompanyMetricsChart();
    $('.edit-company-metrics').on('click', function(event) {
        event.preventDefault();
        $(this).hide();
        $('.company-metrics-chart').hide();
        $('.show-company-metrics').show();
        $('.company-metrics-form').show();
    });
    $('.show-company-metrics').on('click', function(event) {
        event.preventDefault();
        $(this).hide();
        $('.company-metrics-form').hide();
        $('.edit-company-metrics').show();
        $('.company-metrics-chart').show();
    });

    wrapper.on('select2:select', '.contact-autocomplete select', function() {
        var contact = $(this).select2('data');
        if (contact[0] && contact[0].company) {
            var companySelect = wrapper.find('.company-autocomplete select'),
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
});
