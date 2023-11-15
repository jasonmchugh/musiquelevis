// GULPFILE BUILT USING THIS GUIDE: https://goede.site/setting-up-gulp-4-for-automatic-sass-compilation-and-css-injection

const { src, dest, parallel } = require('gulp');
var gulp = require("gulp");
var sass = require("gulp-sass");
var concat = require('gulp-concat');
var postcss = require("gulp-postcss");
var autoprefixer = require("autoprefixer");
var cssnano = require("cssnano");
var sourcemaps = require("gulp-sourcemaps");
var browserSync = require("browser-sync");
var uglify = require("gulp-uglify");


function style() {
    return (
        gulp
            .src("wp-content/themes/oasis/assets/scss/*.scss")
 
            // Use sass with the files found, and log any errors
            .pipe(sourcemaps.init())
            .pipe(sass())
            .on("error", sass.logError)
            .pipe(postcss([autoprefixer(), cssnano()]))

            // Write Destination folder for the compiled CSS
            .pipe(sourcemaps.write())
            .pipe(gulp.dest("wp-content/themes/oasis/assets/css/"))

            .pipe(browserSync.stream())
    );
}

function reload() {
    browserSync.reload();
}

function js(){
    return gulp.src('wp-content/themes/oasis/assets/js/modules/*.js')
        .pipe(concat('app.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('wp-content/themes/oasis/assets/js/'));
}
    
function watch() {
    browserSync.init({
        proxy: "oasis"
    });
    gulp.watch("wp-content/themes/oasis/assets/scss/*.scss", style);
    gulp.watch("wp-content/themes/oasis/assets/scss/**/*.scss", style);
    
    gulp.watch("wp-content/themes/oasis/assets/js/modules/*.js", js);

    gulp.watch("wp-content/themes/oasis/assets/scss/*.scss", reload);
}

exports.style = style;
exports.reload = reload
exports.js = js;
exports.watch = watch





