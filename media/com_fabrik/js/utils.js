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
