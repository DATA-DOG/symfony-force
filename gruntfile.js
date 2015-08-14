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
    pkg: grunt.file.readJSON('package.json'),
    version: '<%= pkg.version %>',
    banner: '/**\n' +
      ' * <%= pkg.name %> - v<%= pkg.version %>\n' +
      ' *\n' +
      ' * <%= pkg.description %>\n' +
      ' */\n',

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

    clean: ['web/js/*', 'web/css/*', 'web/fonts/*', 'web/img/*'],

    concat: {
      app_js: {
        options: {
          banner: "<%= banner %>"
        },
        src: ['<%= src.app.js %>'],
        dest: 'web/js/app-<%= version %>.js'
      },
      admin_js: {
        options: {
          banner: "<%= banner %>"
        },
        src: ['<%= src.admin.js %>'],
        dest: 'web/js/admin-<%= version %>.js'
      },
      app_layout: {
        src: ['app/Resources/views/base.dist.html.twig'],
        dest: 'app/Resources/views/base.html.twig',
        options: { process: true }
      },
      admin_layout: {
        src: ['app/Resources/views/admin.dist.html.twig'],
        dest: 'app/Resources/views/admin.html.twig',
        options: { process: true }
      },
      app_vendor_js: {
        src: '<%= src.app.js_vendor %>',
        dest: 'web/js/app-libraries-<%= version %>.js'
      },
      admin_vendor_js: {
        src: '<%= src.admin.js_vendor %>',
        dest: 'web/js/admin-libraries-<%= version %>.js'
      }
    },

    copy: {
      fonts: {
        files: [
          { dest: 'web/fonts/', cwd: 'assets/fonts/', src: '**', expand: true},
          { dest: 'web/fonts/', cwd: 'assets/vendor/bootstrap/dist/fonts/', src: '**', expand: true},
          { dest: 'web/fonts/', cwd: 'assets/vendor/fontawesome/fonts/', src: '**', expand: true}
        ]
      },
      images: {
        files: [
          { dest: 'web/img/', cwd: 'assets/img/', src: '**', expand: true}
        ]
      },
      starwars_gif: {
        src: 'starwars.gif',
        dest: 'web/img/starwars.gif'
      },
      jq_map: {
        src: 'assets/vendor/jquery/dist/jquery.min.map',
        dest: 'web/js/jquery.min.map'
      }
    },

    less: {
      app: {
        options: {
          strictImports : true,
          compress: isRelease,
          outputSourceFiles: !isRelease,
          sourceMapURL: "app-<%= version %>.css.map"
        },
        files: {
          'web/css/app-<%= version %>.css': '<%= src.app.less %>'
        }
      },
      admin: {
        options: {
          strictImports : true,
          compress: isRelease,
          outputSourceFiles: !isRelease,
          sourceMapURL: "admin-<%= version %>.css.map"
        },
        files: {
          'web/css/admin-<%= version %>.css': '<%= src.admin.less %>'
        }
      }
    },

    uglify: {
      app: {
        options: {
          banner: "<%= banner %>"
        },
        src: ['<%= concat.app_js.dest %>'],
        dest: '<%= concat.app_js.dest %>'
      },
      admin: {
        options: {
          banner: "<%= banner %>"
        },
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
          'bin/archive ansible/frontend.tar.gz',
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
      },
      layout_app: {
        files: ['<%= concat.app_layout.src %>'],
        tasks: ['concat:app_layout']
      },
      layout_admin: {
        files: ['<%= concat.admin_layout.src %>'],
        tasks: ['concat:admin_layout']
      }
    }

  });
};
