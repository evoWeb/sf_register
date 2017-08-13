/* global module */

/**
 * @param {{initConfig, file, loadNpmTasks, registerTask}} grunt
 */
module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		compass: {
			dist: {
				options: {
					environment: 'development',
					httpPath: 'typo3conf/ext/sf_register/Resources/',

					basePath: 'Private/',
					sassDir: 'Sass/',
					load: [
						'node_modules/modularscale-sass'
					],

					cssDir: '../Public/Stylesheets/',
					imagesDir: '../Public/Images/',
					javascriptsDir: '../Public/JavaScript/'
				}
			}
		},
		cssmin: {
			dist: {
				files: [
					{ src: ['Public/Stylesheets/styles.css'], dest: 'Public/Stylesheets/styles.min.css' }
				]
			}
		},

		concat: {
			options: {
				separator: ';'
			},
			dist: {
				files: [
					{ src: ['Private/Script/*.js'], dest: 'Public/JavaScript/sf_register.js' }
				]
			}
		},
		uglify: {
			dist: {
				files: [
					{ src: 'Public/JavaScript/sf_register.js', dest: 'Public/JavaScript/sf_register.min.js' }
				]
			}
		},

		jshint: {
			options: {
				curly: true,
				eqeqeq: true,
				immed: true,
				latedef: true,
				newcap: true,
				noarg: true,
				sub: true,
				undef: true,
				boss: true,
				eqnull: true,
				node: true,
				globals: {
					window: true,
					document: true,
					$: true,
					ga: true
				}
			},
			globals: {
				exports: true,
				module: false,
				files: [
					{src: [
						'Private/Script/*.js'
					]}
				]
			}
		},

		watch: {
			javascript: {
				files: ['Private/Script/*.js'],
				tasks: ['jshint', 'concat']
			},
			compass: {
				files: ['Private/Sass/*.scss'],
				tasks: ['compass']
			}
		}
	});

	// load modules
	grunt.loadNpmTasks('grunt-contrib-compass');
	grunt.loadNpmTasks('grunt-contrib-cssmin');

	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');

	grunt.loadNpmTasks('grunt-contrib-jshint');

	grunt.loadNpmTasks('grunt-contrib-watch');

	// add tasks
	grunt.registerTask('test', ['jshint']);
	grunt.registerTask('development', ['concat', 'compass']);
	grunt.registerTask('production', ['concat', 'compass', 'uglify', 'cssmin']);
	grunt.registerTask('default', ['watch']);
};
