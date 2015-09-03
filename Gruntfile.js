var fs = require('fs-extra');
var Promise = require('bluebird');
var archiver = require('archiver');
var updateServer = require('./fabrik_build/update-server.js'),
	filesPrep = require('./fabrik_build/files-prep.js');
var mkdirp = require('mkdirp');
Promise.promisifyAll(fs);


module.exports = function (grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg   : grunt.file.readJSON('package.json'),
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> */\n'
			},

			all: {
				files: grunt.file.expandMapping(['./plugins/fabrik_*/*/*.js', "!./plugins/fabrik_*/**/*-min.js",
						'./media/com_fabrik/js/*.js', '!./media/com_fabrik/js/*-min.js', '!/media/com_fabrik/js/**',
						'./administrator/components/com_fabrik/models/fields/*.js', '!./administrator/components/com_fabrik/models/fields/*-min.js',
						'./administrator/components/com_fabrik/views/**/*.js', '!./administrator/components/com_fabrik/views/**/*-min.js']

					, './plugins/fabrik_*/*/*.js', {
						rename: function (destBase, destPath) {
							console.log('making ' + destPath.replace('.js', '-min.js'));
							return destPath.replace('.js', '-min.js');
						}
					})
			}
		},

		compress: {
			main: {
				options: {
					archive: grunt.config('config.version') + '.zip'
				},
				files: [
					{src: ['path/*'], dest: 'internal_folder/', filter: 'isFile'}, // includes files in path
					{src: ['path/**'], dest: 'internal_folder2/'}, // includes files in path and its subdirs
				]
			}
		},

		prompt: {
			target: {
				options: {
					questions: [
						{
							config: 'pkg.version', // arbitrary name or config for any other grunt task
							type: 'input', // list, checkbox, confirm, input, password
							message: 'Fabrik version:', // Question to ask the user, function needs to return a string,
							default: grunt.config('pkg.version') // default value if nothing is entered
						},
						{
							config: 'jversion',
							type: 'input',
							message: 'Joomla target version #',
							default: '3.4'
						},
						{
							config: 'live',
							type: 'confirm',
							message: 'Deployment to live server',
							default: false
						},
						{
							config: 'changelog',
							type: 'confirm',
							message: 'Create Change log?'
						},
						{
							config: 'phpdocs.create',
							type: 'confirm',
							message: 'Build PHP Docs?'
						},
						{
							config: 'phpdocs.upload',
							type: 'confirm',
							message: 'Upload PHP docs to fabrikar.com'
						},
					]
				}
			},
		}
	});

	// Load the plugin that provides the "uglify" task.
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-prompt');

	// Default task(s).
	grunt.registerTask('default', ['prompt', 'fabrik']);

	grunt.registerTask('fabrik', 'testing build', function () {

		var v  = grunt.config('pkg.version');
		grunt.log.writeln('Building fabrik......' + v);
		filesPrep();
		return;
		updateServer(grunt);

		var pluginTypes = ['fabrik_cron', 'fabrik_element', 'fabrik_form', 'fabrik_list', 'fabrik_validationrule', 'fabrik_visualization'];

		var done = this.async();
		mkdirp('fabrik_build/output/plugins');
		mkdirp('fabrik_build/pkg_fabrik/packages');
		mkdirp('fabrik_build/pkg_fabrik_sink/packages');

		for (var p = 0; p < pluginTypes.length; p ++) {
			mkdirp('fabrik_build/output/plugins/' + pluginTypes[p]);
			fs.copySync('plugins/' + pluginTypes[p], 'fabrik_build/output/plugins/' + pluginTypes[p]);

			var plugins = fs.readdirSync('fabrik_build/output/plugins/' + pluginTypes[p]);
			grunt.log.writeln(plugins);

			for (var i = 0; i < plugins.length; i ++) {
				zipPlugin('fabrik_build/output/plugins/' + pluginTypes[p] + '/' + plugins[i], 'fabrik_build/pkg_fabrik_sink/packages/plg_' + pluginTypes[p] + '_' + plugins[i] + '_' + v + '.zip')
			}
		}


		zipPlugin('plugins/content/fabrik', 'fabrik_build/pkg_fabrik_sink/packages/plg_fabrik_' + v + '.zip');
		zipPlugin('plugins/search/fabrik', 'fabrik_build/pkg_fabrik_sink/packages/plg_fabrik_search_' + v + '.zip');
		zipPlugin('plugins/system/fabrikcron', 'fabrik_build/pkg_fabrik_sink/packages/plg_fabrik_schedule_' + v + '.zip');
		zipPlugin('plugins/system/fabrik', 'fabrik_build/pkg_fabrik_sink/packages/plg_system_fabrik_' + v + '.zip');
		zipPlugin('plugins/system/fabrik', 'fabrik_build/pkg_fabrik/packages/plg_system_fabrik_' + v + '.zip');
		zipPlugin('components/com_comprofiler/plugin/user/plug_fabrik', 'fabrik_build/pkg_fabrik_sink/packages/plug_cb_fabrik_' + v + '.zip');
		zipPlugin('components/com_comprofiler/plugin/user/plug_fabrik', 'fabrik_build/pkg_fabrik_sink/packages/plug_cb_fabrik_' + v + '.zip');


		buildPHPDocs();
		uploadPHPDocs();
		changelog();
	})

};

/**
 * Build a zip
 * @param source
 * @param dest
 */
var zipPlugin = function (source, dest) {
	var archive = archiver.create('zip', {});
	var output = fs.createWriteStream(dest);

	output.on('close', function() {
		console.log(dest + ': ' + archive.pointer() + ' total bytes');
	});

	archive.on('error', function(err) {
		console.log(err);
	});

	archive.pipe(output);
	archive.directory(source, false);
	archive.finalize();
}

var buildPHPDocs = function () {
	console.log('todo: build php docs' + grunt.config('phpdocs.create'));
}

var uploadPHPDocs = function () {
	console.log('todo: uploadPHPDocs: ' + grunt.config('phpdocs.upload'));
	/**
	 * <ftp passive ="yes" server="${ftp.server}" userid="${ftp.user}" password="${ftp.password}"
	 depends="false"
	 ignoreNoncriticalErrors="true"
	 retriesAllowed="3"
	 verbose="yes" remotedir="${ftp.docdir}"
	 skipFailedTransfers="true">
	 <fileset dir="${phpdoc.localdir}">
	 </fileset>
	 </ftp>
	 */
}

var changelog = function () {
	console.log('todo: changelog: ' + grunt.config('changelog'));
}


