module.exports = function(grunt) {

  'use strict';

  require('time-grunt')(grunt);
  require('load-grunt-tasks')(grunt);
  grunt.option('force', true);

  grunt.registerTask('default', ['jshint', 'build']);
  grunt.registerTask('build', ['clean', 'concat', 'recess:build', 'copy']);
  grunt.registerTask('dev', ['build', 'watch']);
  grunt.registerTask('release', ['clean', 'concat', 'uglify', 'jshint', 'recess:min', 'copy', 'shell']);

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    version: '<%= pkg.name %>-<%= pkg.version %>',
    banner: '/**\n' +
      ' * <%= pkg.name %> - v<%= pkg.version %>\n' +
      ' *\n' +
      ' * <%= pkg.description %>\n' +
      ' */\n',

    src: {
      js: ['assets/js/**/*.js'],
      less: ['assets/less/style.less']
    },

    clean: ['web/js/*', 'web/css/*', 'web/fonts/*', 'web/img/*'],

    concat: {
      js: {
        options: {
          banner: "<%= banner %>"
        },
        src: ['<%= src.js %>'],
        dest: 'web/js/<%= version %>.js'
      },
      layout: {
        src: ['app/Resources/views/layout.dist.html.twig'],
        dest: 'app/Resources/views/layout.html.twig',
        options: {
          process: true
        }
      },
      vendor_js: {
        src: [
          'assets/vendor/jquery/dist/jquery.js',
          'assets/vendor/bootstrap/dist/js/bootstrap.js'
        ],
        dest: 'web/js/libraries.js'
      },
      vendor_css: {
        src: [
          'assets/vendor/bootstrap/dist/css/bootstrap.css'
        ],
        dest: 'web/css/libraries.css'
      }
    },

    copy: {
      fonts: {
        files: [
          { dest: 'web/fonts/', cwd: 'assets/fonts/', src: '**', expand: true},
          { dest: 'web/fonts/', cwd: 'assets/vendor/bootstrap/dist/fonts/', src: '**', expand: true}
        ]
      },
      images: {
        files: [
          { dest: 'web/img/', cwd: 'assets/img/', src: '**', expand: true}
        ]
      },
      bs_map: {
        src: 'assets/vendor/bootstrap/dist/css/bootstrap.css.map',
        dest: 'web/css/bootstrap.css.map'
      },
      jq_map: {
        src: 'assets/vendor/jquery/dist/jquery.min.map',
        dest: 'web/js/jquery.min.map'
      }
    },

    uglify: {
      app: {
        options: {
          banner: "<%= banner %>"
        },
        src: ['<%= concat.js.dest %>'],
        dest: '<%= concat.js.dest %>'
      },
      vendor: {
        src: ['<%= concat.vendor_js.dest %>'],
        dest: '<%= concat.vendor_js.dest %>'
      }
    },

    recess: {
      build: {
        files: {'web/css/<%= version %>.css': ['<%= src.less %>']},
        options: {
          compile: true
        }
      },
      min: {
        files: {'web/css/<%= version %>.css': ['<%= src.less %>']},
        options: {
          compress: true
        }
      }
    },

    jshint: {
      files: ['gruntfile.js', '<%= src.js %>'],
      options: {
        jshintrc: '.jshintrc',
        reporter: require('jshint-stylish')
      }
    },

    shell: {
      composer_prod: {
        command: 'composer install --no-scripts --no-dev'
      },
      composer_dump: {
        command: 'composer dump-autoload --optimize'
      },
      archive: {
        command: 'bin/archive ansible/frontend.tar.gz'
      },
      composer_back: {
        command: 'composer install'
      }
    },

    watch: {
      js: {
        files: ['<%= src.js %>'],
        tasks: ['concat:js']
      },
      less: {
        files: ['assets/less/**/*.less'],
        tasks: ['recess:build']
      },
      images: {
        files: ['assets/img/**/*'],
        tasks: ['copy:images']
      },
      layout: {
        files: ['<%= concat.layout.src %>'],
        tasks: ['concat:layout']
      }
    }

  });
};
