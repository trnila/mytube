# Require any additional compass plugins here.

# Set this to the root of your project when deployed:
#http_path = "/assets"

sass_dir = "sass"
javascripts_dir = "javascripts"
images_dir = "images"
fonts_dir = "fonts"
generated_images_dir = "sprites"

# You can select your preferred output style here (can be overridden via the command line):
# output_style = :expanded or :nested or :compact or :compressed
output_style = (environment == :production) ? :compressed : :expanded

# To enable relative paths to assets via compass helper functions. Uncomment:
relative_assets = true

# To disable debugging comments that display the original location of your selectors. Uncomment:
# line_comments = false


preferred_syntax = :sass

def hashFile(file)
	Digest::MD5.hexdigest(File.read(file))
end

def assetPath(pathname, hash)
	"%s/%s-%s%s" % [pathname.dirname, pathname.basename(pathname.extname), hash, pathname.extname]
end

asset_cache_buster do |pathname, real_path|
	if File.exists?(real_path)
		pathname = Pathname.new(pathname)
		new_path = assetPath(pathname, hashFile(real_path))

		{:path => new_path, :query => nil}
	end
end
