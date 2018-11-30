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
        }
    });

    return Fusionchart;
});