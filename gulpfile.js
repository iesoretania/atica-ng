var gulp = require('gulp');
var plugins = require('gulp-load-plugins')();

gulp.task('default', function() {

    // procesar SCSS
    gulp.src(['node_modules/select2/dist/css/select2.css', 'web/css/**/*.scss', 'web/css/source-sans-pro/css/fonts.css', 'node_modules/font-awesome-animation/dist/font-awesome-animation.css', 'node_modules/patternfly-bootstrap-treeview/dist/bootstrap-treeview.css', 'web/css/atica.css'])
        .pipe(plugins.sass())
        .pipe(plugins.autoprefixer({
            browsers: [
                'Android 2.3',
                'Android >= 4',
                'Chrome >= 20',
                'Firefox >= 3.6',
                'Explorer >= 8',
                'iOS >= 6',
                'Opera >= 12',
                'Safari >= 6'
            ],
            cascade: false
        }))
        .pipe(plugins.concat('pack.css'))
        .pipe(plugins.cleanCss({
            compability: 'ie8'
        }))
        .pipe(gulp.dest('web/dist/css'));

    // copiar jQuery
    gulp.src('node_modules/jquery/dist/*.min.js')
        .pipe(gulp.dest('web/dist/js/jquery'));

    // copiar Javascript de Bootstrap
    gulp.src('node_modules/bootstrap-sass/assets/javascripts/*.min.js')
        .pipe(gulp.dest('web/dist/js/bootstrap'));

    // copiar Javascript de Select2
    gulp.src('node_modules/select2/dist/js/select2.min.js')
        .pipe(gulp.dest('web/dist/js/select2'));
    gulp.src('node_modules/select2/dist/js/i18n/*')
        .pipe(gulp.dest('web/dist/js/select2/i18n'));

    // copiar Javascript de patternfly-bootstrap-treeview
    gulp.src('node_modules/patternfly-bootstrap-treeview/dist/*.min.js')
        .pipe(gulp.dest('web/dist/js/bootstrap-treeview'));

    // copiar Javascript y CSS de Dropzone.js
    gulp.src('node_modules/dropzone/dist/dropzone.js')
        .pipe(gulp.dest('web/dist/js/dropzone'));
    gulp.src('node_modules/dropzone/dist/min/dropzone.min.css')
        .pipe(gulp.dest('web/dist/css'));

    // copiar fuentes
    gulp.src(['node_modules/font-awesome/fonts/*', 'web/css/source-sans-pro/fonts/**'])
        .pipe(gulp.dest('web/dist/fonts'));
});
