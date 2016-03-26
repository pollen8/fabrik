/**
 * Created by rob on 18/03/2016.
 */
define(['jquery'], function (jQuery) {
    /**
     * @author Rob
     * contains methods that are used by any element which manipulates files/folders
     */
    window.FbFileElement = new Class({

        Extends   : FbElement,
        ajaxFolder: function () {
            this.folderlist = [];
            if (typeOf(this.element) === 'null') {
                return;
            }
            var el = this.element.getParent('.fabrikElement');
            this.breadcrumbs = el.getElement('.breadcrumbs');
            this.folderdiv = el.getElement('.folderselect');
            this.slider = new Fx.Slide(this.folderdiv, {duration: 500});
            this.slider.hide();
            this.hiddenField = el.getElement('.folderpath');
            el.getElement('.toggle').addEvent('click', function (e) {
                e.stop();
                this.slider.toggle();
            }.bind(this));
            this.watchAjaxFolderLinks();
        },


        watchAjaxFolderLinks: function () {
            this.folderdiv.getElements('a').addEvent('click', function (e) {
                this.browseFolders(e);
            }.bind(this));
            this.breadcrumbs.getElements('a').addEvent('click', function (e) {
                this.useBreadcrumbs(e);
            }.bind(this));
        },


        browseFolders: function (e) {
            e.stop();
            this.folderlist.push(e.target.get('text'));
            var dir = this.options.dir + this.folderlist.join(this.options.ds);
            this.addCrumb(e.target.get('text'));
            this.doAjaxBrowse(dir);
        },

        useBreadcrumbs: function (e) {
            e.stop();
            var found = false;
            var c = e.target.className;
            this.folderlist = [];
            var res = this.breadcrumbs.getElements('a').every(function (link) {
                if (link.className === c) {
                    return false;
                }
                this.folderlist.push(e.target.innerHTML);
                return true;
            }, this);

            var home = [this.breadcrumbs.getElements('a').shift().clone(),
                this.breadcrumbs.getElements('span').shift().clone()];
            this.breadcrumbs.empty();
            this.breadcrumbs.adopt(home);
            this.folderlist.each(function (txt) {
                this.addCrumb(txt);
            }, this);
            var dir = this.options.dir + this.folderlist.join(this.options.ds);
            this.doAjaxBrowse(dir);
        },

        doAjaxBrowse: function (dir) {

            var data = {
                'dir'       : dir,
                'option'    : 'com_fabrik',
                'format'    : 'raw',
                'task'      : 'plugin.pluginAjax',
                'plugin'    : 'fileupload',
                'method'    : 'ajax_getFolders',
                'element_id': this.options.id
            };
            new Request({
                url       : '',
                data      : data,
                onComplete: function (r) {
                    r = JSON.decode(r);
                    this.folderdiv.empty();

                    r.each(function (folder) {
                        new Element('li', {'class': 'fileupload_folder'}).adopt(
                            new Element('a', {'href': '#'}).set('text', folder)).inject(this.folderdiv);
                    }.bind(this));
                    if (r.length === 0) {
                        this.slider.hide();
                    } else {
                        this.slider.slideIn();
                    }
                    this.watchAjaxFolderLinks();
                    this.hiddenField.value = '/' + this.folderlist.join('/') + '/';
                    this.fireEvent('onBrowse');
                }.bind(this)
            }).send();
        },


        addCrumb: function (txt) {
            this.breadcrumbs.adopt(
                new Element('a', {'href': '#', 'class': 'crumb' + this.folderlist.length}).set('text', txt),
                new Element('span').set('text', ' / ')
            );
        }
    });

    return  window.FbFileElement;
});