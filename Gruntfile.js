module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> */\n'
			},

			all: {
				files: grunt.file.expandMapping(['./plugins/fabrik_*/*/*.js',  "!./plugins/fabrik_*/**/*-min.js",
				'./media/com_fabrik/js/*.js', '!./media/com_fabrik/js/*-min.js', '!/media/com_fabrik/js/**',
				'./administrator/components/com_fabrik/models/fields/*.js', '!./administrator/components/com_fabrik/models/fields/*-min.js'], './plugins/fabrik_*/*/*.js', {
					rename: function(destBase, destPath) {
						console.log('making ' + destPath.replace('.js', '-min.js'));
						return destPath.replace('.js', '-min.js');
					}
				})
			}
		}
	});

	// Load the plugin that provides the "uglify" task.
	grunt.loadNpmTasks('grunt-contrib-uglify');

	// Default task(s).
	grunt.registerTask('default', ['uglify']);

};