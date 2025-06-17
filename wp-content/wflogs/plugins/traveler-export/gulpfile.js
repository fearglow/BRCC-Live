var gulp = require('gulp');
var bower = require('gulp-bower');
var sass = require('gulp-sass');
const autoprefixer = require('gulp-autoprefixer');

var config = {
    bowerDir: './bower_components'
}

var vendors = {
    'bower_components/chart.js/dist/**/*':'chart.js',
    'bower_components/select2/dist/**/*':'select2',
};

// Install ow Bower Components
gulp.task('bower', function() {
    return bower()
        .pipe(gulp.dest(config.bowerDir))
});


gulp.task('vendor',function(){

    for(k in vendors)
    {
        gulp.src(k)
            .pipe(gulp.dest('assets/vendors/'+vendors[k]));
    }
    return gulp;
});

gulp.task('scss', function() {
    gulp.src('./assets/sass/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer({
            browsers: ['last 2 versions'],
            cascade: false
        }))
        .pipe(gulp.dest('./assets/css/'))
});

gulp.task('default', ['scss'] , function () {
    gulp.watch('./assets/sass/**/*.scss', ['scss']);
});
//gulp.task('default', ['bower', 'vendor' ]);