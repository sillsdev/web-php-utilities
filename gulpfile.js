var gulp = require('gulp');
var gutil = require('gulp-util');
var exec = require('child_process').exec;
var async = require('async');

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
  execute('php test/php/languageforge/lexicon/AllTests.php', function(err) {
    cb(err);
  });
});

gulp.task('test-watch', function() {
  gulp.watch('**/*.php', ['test-php']);
});

var coverageFolder = 'src/vendor/simpletest/simpletest/extensions/coverage/';
gulp.task('coverage-open', function(cb) {
  var options = {
      includes: [
        'src/libraries/.*\.php$',
        'src/models/.*\.php$'
      ],
      excludes: [
        'src/vendor/.*',
        'src/config/.*',
        'src/errors/.*',
        'src/helpers/.*',
        'lib/.*'
      ]
  };
  var command = function(commandName, args) {
    return 'php ' + coverageFolder + commandName + ' ' + args;
  };
  var args = '--';
  options.includes.forEach(function(regEx) {
    args = args + ' \\ \'--include=' + regEx + '\'';
  });
  options.excludes.forEach(function(regEx) {
    args = args + ' \\ \'--exclude=' + regEx + '\'';
  });

  execute(command('bin/php-coverage-open.php', args), function(err) {
    cb(err);
  });

});

gulp.task('coverage-close', function(cb) {
  var command = function(commandName, args) {
    return 'php ' + coverageFolder + commandName + ' ' + args;
  };
  async.series([
    function(callback) {
      execute(command('bin/php-coverage-close.php', ''), function(err) {
        callback(err);
      });
    },
    function(callback) {
      execute(command('bin/php-coverage-report.php', ''), function(err) {
        callback(err);
      });
    }
  ], function(err, results) {
    cb(err)
  });

});
