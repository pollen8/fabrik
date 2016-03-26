/**
 * Created by rob on 21/03/2016.
 */
define (['jquery', 'fab/tipsBootStrapMock'], function (jQuery, FloatingTips) {

    /**
     * set up and show/hide list actions for each row
     * Deprecated in 3.1
     */
    var FbListActions = new Class({

        Implements: [Options],
        options   : {
            'selector': 'ul.fabrik_action, .btn-group.fabrik_action',
            'method'  : 'floating',
            'floatPos': 'bottom'
        },

        initialize: function (list, options) {
            this.setOptions(options);
            this.list = list; // main list js object
            this.actions = [];
            this.setUpSubMenus();
            Fabrik.addEvent('fabrik.list.update', function (list, json) {
                this.observe();
            }.bind(this));
            this.observe();
        },

        observe: function () {
            if (this.options.method === 'floating') {
                this.setUpFloating();
            } else {
                this.setUpDefault();
            }
        },

        setUpSubMenus: function () {
            if (!this.list.form) {
                return;
            }
            this.actions = this.list.form.getElements(this.options.selector);
            this.actions.each(function (ul) {
                // Sub menus ie group by options
                if (ul.getElement('ul')) {
                    var el = ul.getElement('ul');
                    var c = new Element('div').adopt(el.clone());
                    var trigger = el.getPrevious();
                    if (trigger.getElement('.fabrikTip')) {
                        trigger = trigger.getElement('.fabrikTip');
                    }
                    var t = Fabrik.tips ? Fabrik.tips.options : {};
                    var tipOpts = Object.merge(Object.clone(t), {
                        showOn  : 'click',
                        hideOn  : 'click',
                        position: 'bottom',
                        content : c
                    });
                    var tip = new FloatingTips(trigger, tipOpts);
                    el.dispose();
                }
            });
        },

        setUpDefault: function () {
            this.actions = this.list.form.getElements(this.options.selector);
            this.actions.each(function (ul) {
                if (ul.getParent().hasClass('fabrik_buttons')) {
                    return;
                }
                ul.fade(0.6);
                var r = ul.getParent('.fabrik_row') ? ul.getParent('.fabrik_row') : ul.getParent('.fabrik___heading');
                if (r) {
                    // $$$ hugh - for some strange reason, if we use 1 the object disappears
                    // in Chrome and Safari!
                    r.addEvents({
                        'mouseenter': function (e) {
                            ul.fade(0.99);
                        },
                        'mouseleave': function (e) {
                            ul.fade(0.6);
                        }
                    });
                }
            });
        },

        setUpFloating: function () {
            var chxFound = false, i;
            this.list.form.getElements(this.options.selector).each(function (ul) {
                if (ul.getParent('.fabrik_row')) {
                    if (i = ul.getParent('.fabrik_row').getElement('input[type=checkbox]')) {
                        chxFound = true;
                        var hideFn = function (e, elem, leaving) {
                            if (!e.target.checked) {
                                this.hide(e, elem);
                            }
                        };

                        var c = function (el, o) {
                            var r = ul.getParent();
                            r.store('activeRow', ul.getParent('.fabrik_row'));
                            return r;
                        }.bind(this.list);

                        var opts = {
                            position : this.options.floatPos,
                            showOn   : 'change',
                            hideOn   : 'click',
                            content  : c,
                            'heading': 'Edit: ',
                            hideFn   : function (e) {
                                return !e.target.checked;
                            },
                            showFn   : function (e, trigger) {
                                Fabrik.activeRow = ul.getParent().retrieve('activeRow');
                                trigger.store('list', this.list);
                                return e.target.checked;
                            }.bind(this.list)
                        };

                        var tipOpts = Fabrik.tips ? Object.merge(Object.clone(Fabrik.tips.options), opts) : opts;
                        var tip = new FloatingTips(i, tipOpts);
                    }
                }
            }.bind(this));

            this.list.form.getElements('.fabrik_select input[type=checkbox]').addEvent('click', function (e) {
                Fabrik.activeRow = e.target.getParent('.fabrik_row');
            });
            // watch the top/master chxbox
            var chxall = this.list.form.getElement('input[name=checkAll]');
            if (typeOf(chxall) !== 'null') {
                chxall.store('listid', this.list.id);
            }

            var c = function (el) {
                var p = el.getParent('.fabrik___heading');
                return typeOf(p) !== 'null' ? p.getElement(this.options.selector) : '';
            }.bind(this);

            var t = Fabrik.tips ? Object.clone(Fabrik.tips.options) : {};
            var tipChxAllOpts = Object.merge(t, {
                position : this.options.floatPos,
                html     : true,
                showOn   : 'click',
                hideOn   : 'click',
                content  : c,
                'heading': 'Edit all: ',
                hideFn   : function (e) {
                    return !e.target.checked;
                },
                showFn   : function (e, trigger) {
                    trigger.retrieve('tip').click.store('list', this.list);
                    return e.target.checked;
                }.bind(this.list)
            });
            var tip = new FloatingTips(chxall, tipChxAllOpts);

            // hide markup that contained the actions
            if (this.list.form.getElements('.fabrik_actions') && chxFound) {
                this.list.form.getElements('.fabrik_actions').hide();
            }
            if (this.list.form.getElements('.fabrik_calculation')) {
                var calc = this.list.form.getElements('.fabrik_calculation').getLast();
                if (typeOf(calc) !== 'null') {
                    calc.hide();
                }
            }
        }
    });

    return FbListActions;
});