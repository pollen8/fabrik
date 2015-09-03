var fs = require('fs-extra');
var Promise = require('bluebird');
Promise.promisifyAll(fs);
var buildConfig = require('./build-config.js');
var libxmljs = require('libxmljs'),
    mkdirp = require('mkdirp'),
    updateDir = './fabrik_build/output/admin/update/fabrik31/',
    packageList = updateDir + 'package_list.xml',
    extensions = [];


module.exports = function (grunt) {
    mkdirp.sync(updateDir);
    fs.copySync('administrator/components/com_fabrik/update/fabrik31', updateDir);
    jPlugins(grunt);
    fabrikPlugins(grunt);
    fabrikModules(grunt);
    makePackageList(extensions);
}
/**
 * Build the update server's XML file that describes where each individual plugin etc xml
 * manifest files are located
 */
var makePackageList = function (extensions) {
    //console.log(extensions);
    var xmlDoc = libxmljs.Document(),
    root = xmlDoc.node('extensionset');
    root.attr({'description': 'Fabrik', 'name': 'Fabrik'});

    for (var i = 0; i < extensions.length; i ++) {
        var node = libxmljs.Element(xmlDoc, 'extension');
        node.attr(extensions[i].$);
        root.addChild(node);
    }
    console.log(xmlDoc.toString());
    try {
        fs.writeFileSync(packageList, xmlDoc.toString());
    } catch (err) {
        console.log(err);
    }

    return xmlDoc;
}

var writeXml = function (xmlFile, props) {
    var xmlDoc = buildXml(xmlFile, props);
    console.log('write....', xmlFile);
    try {
        fs.writeFileSync(xmlFile, xmlDoc.toString());
    } catch (err) {
        console.log(err);
    }

    console.log('done');
}

var buildXml = function (xmlFile, props) {
    var xml, xmlDoc;

    try {
        xml = fs.readFileSync(xmlFile);
        xmlDoc = libxmljs.parseXmlString(xml.toString());
    } catch (err) {
        console.log('no xml file found for ', xmlFile);
        xmlDoc = libxmljs.Document();
        xmlDoc.node('updates');
    }

    var elem = xmlDoc.root();
    var update = libxmljs.Element(xmlDoc, 'update');
    update = map(xmlDoc, props, update)
    elem.addChild(update);
    return xmlDoc;
}

var map = function (xmlDoc, props, update) {

    if (Object.prototype.toString.call(props) === '[object Array]') {
        // todo...
    } else {
        for (key in props) {
            if (typeof(props[key]) !== 'object') {
                var name = libxmljs.Element(xmlDoc, key, props[key]);
                update.addChild(name);
            } else {
                if (props[key].$) {
                    var txt = props[key]['_'] ? props[key]['_'] : '';
                    var name = libxmljs.Element(xmlDoc, key, txt);
                    name.attr(props[key].$);
                    update.addChild(name);
                } else {
                    var parent = libxmljs.Element(xmlDoc, key);
                    update.addChild(parent);
                    update = map(xmlDoc, props[key], update);
                }
            }
        }
    }


    return update;
}

var fabrikModules = function (grunt) {
    var version = grunt.config.get('pkg.version'),
        updateFolder = grunt.config.get('pkg.config.live.downloadFolder');
    for (var i = 0; i < buildConfig.modules.length; i ++) {
        var mod = buildConfig.modules[i];

        var props = {
            'name'          : mod.name,
            'description'   : mod.name,
            'element'       : mod.element,
            'type'          : 'module',
            'version'       : version,
            'downloads'     : {
                'downloadurl': {
                    '$': {'type': 'full', 'format': 'zip'},
                    '_': updateFolder + mod.fileName.replace('{version}', version)
                }
            },
            'maintainer'    : 'Fabrikar.com',
            'maintainerurl' : 'http://fabrikar.com',
            'targetplatform': {
                '$': {
                    'name'   : 'joomla',
                    'version': version
                }
            }
        };
        extensions.push({
            '$': {'name': mod.name, 'element': mod.element, 'type': 'module', 'folder': '', 'version': version,
                detailsurl: 'http://fabrikar.com/update/fabrik31/' + mod.xmlFile }
        });

        var xmlFile = updateDir + mod.xmlFile;
        writeXml(xmlFile, props);
    }
}

var jPlugins = function (grunt) {
    var version = grunt.config.get('pkg.version');
    for (var p in buildConfig.plugins) {
        for (var i = 0; i < buildConfig.plugins[p].length; i ++) {
            var plg = buildConfig.plugins[p][i];

            var props = {
                'name'          : plg.name,
                'description'   : plg.name,
                'element'       : plg.element,
                'type'          : 'plugin',
                'folder'        : p,
                'version'       : version,
                'downloads'     : {
                    'downloadurl': {
                        '$': {'type': 'full', 'format': 'zip'},
                        '_': grunt.config.get('pkg.config.live.downloadFolder') + plg.fileName.replace('{version}', version)
                    }
                },
                'maintainer'    : 'Fabrikar.com',
                'maintainerurl' : 'http://fabrikar.com',
                'targetplatform': {
                    '$': {
                        'name'   : 'joomla',
                        'version': version
                    }
                }
            };

            extensions.push({
                '$': {'name': plg.name, 'element': plg.element, 'type': 'plugin', 'folder': p, 'version': version,
                    detailsurl: 'http://fabrikar.com/update/fabrik31/' + plg.xmlFile }
            });
            
            var xmlFile = updateDir + plg.xmlFile;
            writeXml(xmlFile, props);
        }
    }
}

var fabrikPlugins = function (grunt) {
    var productName = grunt.config.get('pkg.name'),
        version = grunt.config.get('pkg.version');
    var folders = buildConfig.pluginFolders;
    for (var i = 0; i < folders.length; i++) {
        var pluginPath = 'fabrik_build/output/plugins/fabrik_' + folders[i];
        if (fs.lstatSync(pluginPath).isDirectory()) {
            var plugins = fs.readdirSync(pluginPath);
            for (var j = 0; j < plugins.length; j++) {
                if (fs.lstatSync(pluginPath + '/' + plugins[j]).isDirectory()) {
                    var xmlFile = updateDir + '/plg_' + folders[i] + '_' + plugins[j] + '.xml';
                    var name = productName + ' ' + folders[i] + ': ' + plugins[j],
                        element = 'plg_' + folders[i] + '_' + plugins[j]
                        folder = productName + '_' + folders[i];
                    var props = {
                        'name'          : name,
                        'description'   : name,
                        'element'       : element,
                        'type'          : 'plugin',
                        'folder'        : folder,
                        'version'       : version,
                        'downloads'     : {
                            'downloadurl': {
                                '$': {'type': 'full', 'format': 'zip'},
                                '_': grunt.config.get('pkg.config.live.downloadFolder') + 'plg_' + productName + '_' + folders[i] + '_' + plugins[j] + '_' + version + '.zip'
                            }
                        },
                        'maintainer'    : 'Fabrikar.com',
                        'maintainerurl' : 'http://fabrikar.com',
                        'targetplatform': {
                            '$': {
                                'name'   : 'joomla',
                                'version': version
                            }
                        }
                    };

                    extensions.push({
                        '$': {'name': name, 'element': element, 'type': 'plugin', 'folder': folder, 'version': version,
                            detailsurl: 'http://fabrikar.com/update/fabrik31/plg_' + folders[i] + '_' + plugins[j] + '.xml' }
                    });

                    writeXml(xmlFile, props);
                }
            }
        }
    }
}