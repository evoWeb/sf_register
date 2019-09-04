'use strict';

import gulp from 'gulp';
import log from 'gulplog';
import path from 'path';

import browserify from 'browserify';
import buffer from 'vinyl-buffer';
import rename from 'gulp-rename';
import source from 'vinyl-source-stream';
import sourcemaps from 'gulp-sourcemaps';
import tsify from 'tsify';
import ts from 'gulp-typescript';
import uglify from 'gulp-uglify';

import postcss from 'gulp-postcss';
import autoprefixer from 'autoprefixer';
import sass from 'gulp-sass';

const paths = {
	src: './Sources/',
	dest: '../Resources/Public/'
};

const tasks = {
	typescript: {
		src: 'TypeScript',
		dest: 'JavaScript'
	},
	scss: {
		src: 'Scss/*.scss',
		dest: 'Stylesheets'
	}
};

gulp.task('typescript', () => {
	let b = browserify({
		entries: [path.join(paths.src, tasks.typescript.src, 'sf_register.ts')],
		debug: true
	});

	return b
		.plugin(tsify)
		.bundle()
		.pipe(source('sf_register.js'))
		.pipe(buffer())
		.pipe(sourcemaps.init({loadMaps: true}))
		// This will output the non-minified version
		.pipe(gulp.dest(path.join(paths.dest, tasks.typescript.dest)))
		// Add transformation tasks to the pipeline here.
		.pipe(uglify())
		.on('error', log.error)
		.pipe(rename({ extname: '.min.js' }))
		.pipe(sourcemaps.write('./', {
			mapFile: function(mapFilePath) {
				// source map files are named *.map instead of *.js.map
				return mapFilePath.replace('.min.js.map', '.js.map');
			}
		}))
		.pipe(gulp.dest(path.join(paths.dest, tasks.typescript.dest)));
});

gulp.task('scss', () => {
	return gulp.src(path.join(paths.src, tasks.scss.src))
		.pipe(sourcemaps.init())
		.pipe(
			sass({
				includePaths: require('node-normalize-scss').includePaths
			}).on('error', sass.logError)
		)
		.pipe(postcss([autoprefixer()]))
		.pipe(sourcemaps.write('./'))
		.pipe(gulp.dest(path.join(paths.dest, tasks.scss.dest)));
});

gulp.task('build', gulp.series('typescript', 'scss'));
