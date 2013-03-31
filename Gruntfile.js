module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-contrib-compass');
	
	grunt.initConfig({
		compass: {
			dist: {
				options: {
					config: 'www/assets/config.rb',
					sassDir: 'www/assets/sass',
					cssDir: 'www/assets/stylesheets',
					environment: 'production',
					force: true
				}
			}
		}
	});

	grunt.registerTask('default', ['compass']);
};