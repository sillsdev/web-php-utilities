var gulp = require('gulp');
var gutil = require('gulp-util');
var exec = require('child_process').exec;

var execute = function(command, callback) {
  gutil.log(gutil.colors.green(command));
  exec(command, function(err, stdout, stderr) {
    gutil.log(stdout);
    gutil.log(gutil.colors.yellow(stderr));
    callback(err);
  });
};

gulp.task('default', function() {
  // place code for your default task here
});

gulp.task('test-php', function(cb) {
  execute('/usr/bin/env php -d xdebug.show_exception_trace=0 vendor/phpunit/phpunit/phpunit test/*_Test.php', function(err) {
    cb(null);
  });
});

gulp.task('test-watch', function() {
  gulp.watch(['src/**/*.php', 'test/**/*.php'], ['test-php']);
});
