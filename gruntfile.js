module.exports = function(grunt) {

  'use strict';

  require('time-grunt')(grunt);
  require('load-grunt-tasks')(grunt);
  grunt.option('force', true);

  grunt.registerTask('build', ['clean', 'concat', 'less', 'copy']);
  grunt.registerTask('release', ['build', 'removelogging', 'uglify', 'shell:release']);
  grunt.registerTask('test', ['shell:test']);
  grunt.registerTask('default', ['build', 'watch']);

  var isRelease = false;
  grunt.cli.tasks.forEach(function (ts) {
    if (ts === 'release') {
      isRelease = true;
    }
  });

  grunt.initConfig({
    src: {
      app: {
        js: ['assets/js/app/**/*.js'],
        js_vendor: [
          'assets/vendor/jquery/dist/jquery.js',
          'assets/vendor/bootstrap/dist/js/bootstrap.js'
        ],
        less: ['assets/less/app.less']
      },
      admin: {
        js: ['assets/js/admin/**/*.js'],
        js_vendor: [
          'assets/vendor/jquery/dist/jquery.js',
          'assets/vendor/bootstrap/dist/js/bootstrap.js'
        ],
        less: ['assets/less/admin.less']
      }
    },

    clean: ['web/build/*'],

    concat: {
      app_js: {
        src: ['<%= src.app.js %>'],
        dest: 'web/build/app.js'
      },
      admin_js: {
        src: ['<%= src.admin.js %>'],
        dest: 'web/build/admin.js'
      },
      app_vendor_js: {
        src: '<%= src.app.js_vendor %>',
        dest: 'web/build/app-vendor.js'
      },
      admin_vendor_js: {
        src: '<%= src.admin.js_vendor %>',
        dest: 'web/build/admin-vendor.js'
      }
    },

    copy: {
      fonts: {
        files: [
          { dest: 'web/build/fonts/', cwd: 'assets/fonts/', src: '**', expand: true},
          { dest: 'web/build/fonts/', cwd: 'assets/vendor/bootstrap/dist/fonts/', src: '**', expand: true},
          { dest: 'web/build/fonts/', cwd: 'assets/vendor/fontawesome/fonts/', src: '**', expand: true}
        ]
      },
      images: {
        files: [
          { dest: 'web/build/img/', cwd: 'assets/img/', src: '**', expand: true}
        ]
      },
      starwars_gif: {
        src: 'starwars.gif',
        dest: 'web/build/img/starwars.gif'
      },
      jq_map: {
        src: 'assets/vendor/jquery/dist/jquery.min.map',
        dest: 'web/build/jquery.min.map'
      }
    },

    less: {
      app: {
        options: {
          strictImports : true,
          compress: isRelease,
          sourceMap: true,
          outputSourceFiles: true,
          sourceMapURL: "app.css.map"
        },
        files: {
          'web/build/app.css': '<%= src.app.less %>'
        }
      },
      admin: {
        options: {
          strictImports : true,
          compress: isRelease,
          sourceMap: true,
          outputSourceFiles: true,
          sourceMapURL: "admin.css.map"
        },
        files: {
          'web/build/admin.css': '<%= src.admin.less %>'
        }
      }
    },

    uglify: {
      app: {
        src: ['<%= concat.app_js.dest %>'],
        dest: '<%= concat.app_js.dest %>'
      },
      admin: {
        src: ['<%= concat.admin_js.dest %>'],
        dest: '<%= concat.admin_js.dest %>'
      },
      vendor: {
        src: ['<%= concat.app_vendor_js.dest %>'],
        dest: '<%= concat.admin_vendor_js.dest %>'
      }
    },

    removelogging: {
      app: {
        src: "<%= concat.app_js.dest %>",
        dest: "<%= concat.app_js.dest %>"
      },
      admin: {
        src: "<%= concat.admin_js.dest %>",
        dest: "<%= concat.admin_js.dest %>"
      }
    },

    shell: {
      options: {
        callback: function(err, stdout, stderr, cb) {
          grunt.log.write(stdout);
          grunt.log.write(stderr);
          cb();
        }
      },
      release: {
        command: [
          'composer install --no-scripts --no-dev --ansi',
          'composer dump-autoload --optimize --ansi',
          'tar --exclude-from=.tarignore --exclude-vcs -czf ansible/frontend.tar.gz .',
          'composer install --ansi'
        ].join('&&')
      },

      test: {
        command: [
          'bin/reload test',
          'bin/phpunit -c app --colors=always',
          'bin/behat -fprogress --colors'
        ].join('&&')
      }
    },

    watch: {
      js_app: {
        files: ['<%= src.app.js %>'],
        tasks: ['concat:app_js']
      },
      js_admin: {
        files: ['<%= src.admin.js %>'],
        tasks: ['concat:admin_js']
      },
      less_app: {
        files: ['<%= src.app.less %>', 'assets/less/app/**/*.less'],
        tasks: ['less:app']
      },
      less_admin: {
        files: ['<%= src.admin.less %>', 'assets/less/admin/**/*.less'],
        tasks: ['less:admin']
      },
      images: {
        files: ['assets/img/**/*'],
        tasks: ['copy:images']
      }
    }

  });
};
