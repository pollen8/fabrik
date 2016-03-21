/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, $H:true, FbForm:true , define:true */

/**
 * Console.log wrapper
 */
function fconsole() {
    if (typeof (window.console) !== 'undefined') {
        var str = '', i;
        for (i = 0; i < arguments.length; i++) {
            str += arguments[i] + ' ';
        }
        console.log(str);
    }
}

/**
 * This class is temporarily required until this patch makes it into the CMS
 * code: https://github.com/joomla/joomla-platform/pull/1209/files Its purpose
 * is to queue ajax requests so they are not all fired at the same time - which
 * result in db session errors.
 *
 * Currently this is called from: fabriktables.js
 *
 */

var RequestQueue = new Class({

    queue: {}, // object of xhr objects

    initialize: function () {
        this.periodical = this.processQueue.periodical(500, this);
    },

    add: function (xhr) {
        var k = xhr.options.url + Object.toQueryString(xhr.options.data) + Math.random();
        if (!this.queue[k]) {
            this.queue[k] = xhr;
        }
    },

    processQueue: function () {
        if (Object.keys(this.queue).length === 0) {
            return;
        }
        var running = false;

        // Remove successfully completed xhr
        $H(this.queue).each(function (xhr, k) {
            if (xhr.isSuccess()) {
                delete (this.queue[k]);
                running = false;
            } else {
                if (xhr.status === 500) {
                    console.log('Fabrik Request Queue: 500 ' + xhr.xhr.statusText);
                    delete (this.queue[k]);
                    running = false;
                }
            }
        }.bind(this));

        // Find first xhr not run and completed to run
        $H(this.queue).each(function (xhr, k) {
            if (!xhr.isRunning() && !xhr.isSuccess() && !running) {
                xhr.send();
                running = true;
            }
        });
    },

    empty: function () {
        return Object.keys(this.queue).length === 0;
    }
});

Request.HTML = new Class({

    Extends: Request,

    options: {
        update     : false,
        append     : false,
        evalScripts: true,
        filter     : false,
        headers    : {
            Accept: 'text/html, application/xml, text/xml, */*'
        }
    },

    success: function (text) {
        var options = this.options, response = this.response;

        response.html = text.stripScripts(function (script) {
            response.javascript = script;
        });

        var match = response.html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
        if (match) {
            response.html = match[1];
        }
        var temp = new Element('div').set('html', response.html);

        response.tree = temp.childNodes;
        response.elements = temp.getElements(options.filter || '*');

        if (options.filter) {
            response.tree = response.elements;
        }
        if (options.update) {
            var update = document.id(options.update).empty();
            if (options.filter) {
                update.adopt(response.elements);
            } else {

                update.set('html', response.html);
            }
        } else if (options.append) {
            var append = document.id(options.append);
            if (options.filter) {
                response.elements.reverse().inject(append);
            } else {
                append.adopt(temp.getChildren());
            }
        }
        if (options.evalScripts) {
            Browser.exec(response.javascript);
        }

        this.onSuccess(response.tree, response.elements, response.html, response.javascript);
    }
});

/**
 * Keeps the element position in the centre even when scroll/resizing
 */

Element.implement({
    keepCenter: function () {
        this.makeCenter();
        window.addEvent('scroll', function () {
            this.makeCenter();
        }.bind(this));
        window.addEvent('resize', function () {
            this.makeCenter();
        }.bind(this));
    },
    makeCenter: function () {
        var l = jQuery(window).width() / 2 - this.getWidth() / 2;
        var t = window.getScrollTop() + (jQuery(window).height() / 2 - this.getHeight() / 2);
        this.setStyles({
            left: l,
            top : t
        });
    }
});

/**
 * Extend the Array object
 *
 * @param candid
 *            The string to search for
 * @returns Returns the index of the first match or -1 if not found
 */
Array.prototype.searchFor = function (candid) {
    var i;
    for (i = 0; i < this.length; i++) {
        if (this[i].indexOf(candid) === 0) {
            return i;
        }
    }
    return -1;
};

/**
 * Object.keys polyfill for IE8
 */
if (!Object.keys) {
    Object.keys = function (obj) {
        return jQuery.map(obj, function (v, k) {
            return k;
        });
    };
}

/**
 * Loading animation class, either inline next to an element or full screen
 * Paul 20130809 Adding functionality to handle multiple simultaneous spinners
 * on same field.
 */
var Loader = new Class({

    initialize: function () {
        this.spinners = {};
        this.spinnerCount = {};
        this.watchResize();
    },

    sanitizeInline: function (inline) {

        inline = inline ? inline : document.body;

        if (inline instanceof jQuery) {
            if (inline.length === 0) {
                inline = false;
            } else {
                inline = inline[0];
            }
        } else {
            if (typeOf(document.id(inline)) === 'null') {
                inline = false;
            }
        }
        return inline;
    },

    start: function (inline, msg) {
        inline = this.sanitizeInline(inline);

        msg = msg ? msg : Joomla.JText._('COM_FABRIK_LOADING');
        if (!this.spinners[inline]) {
            this.spinners[inline] = new Spinner(inline, {
                'message': msg
            });
        }
        if (!this.spinnerCount[inline]) {
            this.spinnerCount[inline] = 1;
        } else {
            this.spinnerCount[inline]++;
        }
        // If field is hidden we will get a TypeError
        if (this.spinnerCount[inline] === 1) {
            try {
                this.spinners[inline].position().show();
            } catch (err) {
                // Do nothing
            }
        }
    },

    stop: function (inline) {
        inline = this.sanitizeInline(inline);
        if (!this.spinners[inline] || !this.spinnerCount[inline]) {
            return;
        }
        if (this.spinnerCount[inline] > 1) {
            this.spinnerCount[inline]--;
            return;
        }

        var s = this.spinners[inline];

        // Don't keep the spinner once stop is called - causes issue when loading
        // ajax form for 2nd time
        if (Browser.ie && Browser.version < 9) {

            // Well ok we have to in ie8 ;( otherwise it give a js error
            // somewhere in FX
            s.hide();
        } else {
            s.destroy();
            delete this.spinnerCount[inline];
            delete this.spinners[inline];
        }
    },

    watchResize: function () {
        var self = this;
        setInterval(function () {
            jQuery.each(self.spinners, function (index, spinner) {
                try {

                    var h = Math.max(40, jQuery(spinner.target).height()),
                        w = jQuery(spinner.target).width();
                    jQuery(spinner.element).height(h);
                    if (w !== 0) {
                        jQuery(spinner.element).width(w);
                        jQuery(spinner.element).find('.spinner-content').css('left', w / 2);
                    }

                    spinner.position();
                } catch (err) {
                    // Do nothing
                }
            });
        }, 300);
    }
});