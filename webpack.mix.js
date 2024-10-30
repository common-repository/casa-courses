const mix = require("laravel-mix");

require("laravel-mix-eslint");
require("laravel-mix-stylelint");
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */
mix
  .options({
    // Don't perform any css url rewriting by default
    processCssUrls: false
  })
  .stylelint({
    configFile: ".stylelintrc",
    context: ".",
    files: "**/*.scss",
    failOnWarning: true,
    failOnError: true
  })
  .eslint({
    extensions: ["js"],
    failOnWarning: true,
    failOnError: true
  });

mix
  .js("resources/js/casa-courses-public.js", "public/js")
  .sass("resources/sass/casa-courses-public.scss", "public/css")
  .sass("resources/sass/casa-courses-variables.scss", "public/css")
  .sass("resources/admin/sass/casa-courses-admin.scss", "admin/css")
  .js("resources/admin/js/casa-courses-admin.js", "admin/js");
