'use strict';

// development or production
const devBuild = ((process.env.NODE_ENV || 'production').trim().toLowerCase() === 'development');
console.log('Node env: ' + process.env.NODE_ENV);
console.log('devBuld: ' + devBuild);

const
    composerJsonFile    = 'composer.json',
    entryPointFile      = 'entryPoint.php'
    ;

const
    packageJson     = require('./package.json'),
    gulp            = require('gulp'),
    composer        = require('gulp-composer'),
    fs              = require('fs'),
    path            = require('path'),
    rename          = require('gulp-rename'),
    replace         = require('gulp-replace'),
    zip             = require('gulp-zip')
    ;

console.log('Gulp', devBuild ? 'development' : 'production', 'build');

// Using a for...of loop with async/await
async function deleteFiles(folder, files) {
    for (const file of files) {
        const filePath = path.join(folder, file);
        
        try {
            await fs.promises.unlink(filePath);
        } catch (err) {
            console.error(`Error deleting file: ${filePath}`, err);
        }
    }
}

function setupEmptyFolder(folder, cb) {
    fs.mkdir(
        folder,
        { recursive: true },
        (err) => {
            if (err) {
                console.error(`Error creating directory: ${filePath}`, err);
                cb(false);
            } else {
                fs.readdir(folder, (err, files) => {
                    if (err) {
                        console.error("Error reading directory:", err);
                        cb(false);
                    } else {
                        deleteFiles(folder, files);
                        cb();
                    }
                });
            }
        }
    );
}

function cleanTask(cb) {
    cb();
}

function updateComposerJsonVersion() {
    return gulp
        .src([composerJsonFile])
        .pipe(replace(/"version" *: *"\d+\.\d+\.\d+",/, '"version" : "'+packageJson.version+'",'))
        .pipe(gulp.dest('.'))
        ;
}

function updateEntryPointVersion() {
    return gulp
        .src([entryPointFile])
        .pipe(replace(/Version:(.*)\d+\.\d+\.\d+/, 'Version:$1'+packageJson.version))
        .pipe(gulp.dest('.'))
        ;
}

function runComposerTask() {
    return composer({ "async": false, "self-install": false, "no-dev": true });
}

let zipContents = [
    entryPointFile,
    'index.php',
    'src/**',
    'vendor/**'
];

function zipReleaseTask() {
    return gulp
        .src(zipContents, {base: '.', encoding: false})
        .pipe(rename(function(file) {
            file.dirname = packageJson.name + '/' + file.dirname; // put everything in the project name folder within the zip
        }))
        .pipe(zip.default(packageJson.name+'.zip'))
        .pipe(gulp.dest('.'))
        ;
}


exports.scripts = gulp.series(cleanTask);
exports.package = gulp.series(zipReleaseTask);

exports.default = gulp.series(
    exports.scripts,
    gulp.parallel(updateComposerJsonVersion, updateEntryPointVersion),
    runComposerTask,
    exports.package
);
