'use strict';

const gulp = require('gulp');
const sass = require('gulp-dart-sass');
const sassGlob = require('gulp-sass-glob');
const plumber = require('gulp-plumber');
const eslint = require('gulp-eslint');
const imagemin = require('gulp-imagemin');
const pngquant = require('imagemin-pngquant');
const mozjpeg = require('imagemin-mozjpeg');
const named = require('vinyl-named');
const notify = require('gulp-notify');
const postcss = require('gulp-postcss');
const sourcemaps = require('gulp-sourcemaps');
const rename = require('gulp-rename');
const autoprefixer = require('autoprefixer');
const webpack = require('webpack');
const webpackStream = require('webpack-stream');
const webpackConfig = require('./webpack.config.js');

sass.compiler = require('sass');

/*
 * CSS tasks
 */
gulp.task('sass', () => gulp
    .src('./assets/scss/*.scss')
    .pipe(sassGlob())
    .pipe(sourcemaps.init())
    .pipe(sass({
        outputStyle: 'compressed',
    }))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('./public/assets/css'))
);

gulp.task('css:autoprefix', () => gulp
    .src('./public/assets/css/*.css')
    .pipe(sourcemaps.init())
    .pipe(postcss([autoprefixer()]))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./public/assets/css'))
);

// CSS Bundle task.
gulp.task('css', gulp.series(
    'sass',
    'css:autoprefix',
));

/*
 * Bundle JS
 */
gulp.task('js:bundle', function () {
    var tmp = {};
    return gulp.src(['./assets/js/*.js', '!./assets/js/_*.js'])
        .pipe(plumber({
            errorHandler: notify.onError('<%= error.message %>')
        }))
        .pipe(named())
        .pipe(rename(function (path) {
            tmp[path.basename] = path.dirname;
        }))
        .pipe(webpackStream(webpackConfig, webpack))
        .pipe(rename(function (path) {
            if (tmp[path.basename]) {
                path.dirname = tmp[path.basename];
            } else if ('.map' === path.extname && tmp[path.basename.replace(/\.js$/, '')]) {
                path.dirname = tmp[path.basename.replace(/\.js$/, '')];
            }
            return path;
        }))
        .pipe(gulp.dest('./public/assets/js/'));
});

gulp.task('js:lint', () => gulp
    .src(['./assets/js/**/*.js'])
    .pipe(eslint({useEslintrc: true}))
    .pipe(eslint.format())
);

gulp.task('js', gulp.parallel(
    'js:bundle',
    'js:lint'
));


// Image min
gulp.task('imagemin', function () {
    return gulp.src('./assets/img/**/*')
        .pipe(gulp.dest('./public/assets/img'));
});


/**
 * Default task
 */
gulp.task('default', gulp.parallel('css', 'js', 'imagemin'));

/*
 * Watch tasks
 */
gulp.task('watch', function () {
    gulp.watch('assets/scss/**/*.scss', gulp.task('css'));
    gulp.watch('assets/js/**/*.js', gulp.task('js'));
    gulp.watch('assets/img/**/*', gulp.task('imagemin'));
});
