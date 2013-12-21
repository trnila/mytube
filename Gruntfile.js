module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-bower-install');

	grunt.initConfig({
		'bower-install': {
			target: {
				html: 'app/templates/@layout.latte',
				ignorePath: 'www',
				cssPattern: '<link href="{$basePath}{{filePath}}" rel="stylesheet">',
				jsPattern: '<script src="{$basePath}{{filePath}}"></script>'
			}
		}

	});


/*	grunt.loadNpmTasks('grunt-contrib-compass');

	grunt.initConfig({
		compass: {
			dist: {
				options: {
					config: 'www/assets/config.rb',
					basePath: 'www/assets/',
					environment: 'production',
					force: true
				}
			}
		}
	});

	grunt.registerTask('default', ['compass']);*/
};