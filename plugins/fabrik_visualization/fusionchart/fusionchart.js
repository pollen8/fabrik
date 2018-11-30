/**
 * Fusion Charts Visualization
 *
 * @copyright: Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

define(['jquery', 'fab/fabrik', 'fusionchart'], function (jQuery, Fabrik, fc) {

    var Fusionchart = new Class({
        Implements: [Options],
        options: {
            chartJSON: '{}',
            chartType: 'pie2d',
            chartWidth: '100%',
            chartHeight: '100%',
            chartID: 'FusionChart',
            chartContainer: 'chart-container'
        },
        chart: null,

        initialize: function (ref, options) {
            this.setOptions(options);

            this.updater = new Request.JSON({
                url      : '',
                data     : {
                    'option'         : 'com_fabrik',
                    'format'         : 'raw',
                    'task'           : 'ajax_getFusionchart',
                    'view'           : 'visualization',
                    'controller'     : 'visualization.fusionchart',
                    'visualizationid': this.options.id
                },
                onSuccess: function (json) {
                    Fabrik.fireEvent('fabrik.viz.fusionchart.ajax.refresh', [this]);
                    Fabrik.loader.stop(this.options.chartContainer);
                    this.chart.setJSONData(json);
                }.bind(this)
            });

            this.render();
        },

        render: function () {
            this.chart = new FusionCharts({
                'type': this.options.chartType,
                'id': this.options.chartID,
                'width': this.options.chartWidth,
                'height': this.options.chartHeight,
                'renderAt': this.options.chartContainer,
                'dataFormat': 'json',
                'dataSource': this.options.chartJSON
            });

            FusionCharts(this.options.chartID).render();
        },

        update: function () {
            Fabrik.loader.start(this.options.chartContainer);
            //this.updater.send();
            var self = this;
            jQuery.ajax({
                url     : '',
                method  : 'post',
                dataType: 'json',
                data  : {
                    'option'         : 'com_fabrik',
                    'format'         : 'raw',
                    'task'           : 'ajax_getFusionchart',
                    'view'           : 'visualization',
                    'controller'     : 'visualization.fusionchart',
                    'visualizationid': self.options.id
                }

            }).always(function () {
                Fabrik.loader.stop(self.options.chartContainer);
            }).fail(function (jqXHR, textStatus, errorThrown) {
                window.alert(textStatus);
            }).done(function (json) {
                Fabrik.fireEvent('fabrik.viz.fusionchart.ajax.refresh', [this]);
                self.chart.setJSONData(json);
            });
        }
    });

    return Fusionchart;
});