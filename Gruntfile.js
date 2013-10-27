module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-contrib-compass');
	
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

	grunt.registerTask('default', ['compass']);
};