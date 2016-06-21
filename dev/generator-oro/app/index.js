'use strict';
var yeoman = require('yeoman-generator');
var _ = require('lodash');
var path = require('path');
var fs = require('fs');
var dateFormat = require('dateformat');
var now = new Date();
var defaults = require('./defaults.json');
var execFile = require('child_process').execFile;
var Q = require('q');
var yaml = require('js-yaml');

//I got some code from here: https://github.com/yeoman/generator-chrome-extension/blob/master/app/index.js
module.exports = yeoman.generators.Base.extend({
  constructor: function (args, options, config) {
    yeoman.generators.Base.apply(this, arguments);
    this.sourceRoot(path.join(__dirname, 'templates'));
  },
  askFor: function (argument) {
    var cb = this.async();

    var escapeQuotes = function (value) {
      if (value) {
        return value.replace(/\"/g, '\\"');
      }
      return '';
    };

    var appendProps = function (props) {
      this.props = _.extend(this.props || {}, props);
    }.bind(this);
    var appendExtraProps = function (props) {
      this.extraProps = _.extend(this.extraProps || {}, props);
    }.bind(this);
    var setBundlePath = function (path) {
      this.bundlePath = path;
    }.bind(this);

    var promptHandler = function (prompts) {
      var localDeferred = Q.defer();
      this.prompt(prompts, function (answers) {
        localDeferred.resolve(answers);
      });
      return localDeferred.promise;
    }.bind(this);

    var extractChoiceFromFilename = function (filename) {
      return {
        value: filename,
        name: filename
      };
    };

    var extractBundleDataFromPath = function (path) {
      var bundleData, matches = path.match(/.*src\/(\w+)\/Bundle\/(\w+)Bundle$/);
      if (_.isArray(matches)) {
        return {
          bundleBase: matches[1],
          bundleShortName: matches[2]
        };
      }
      return false;
    };

    var deferred = Q.defer();
    execFile('sh', ['-c', 'find . | grep "^\./src.*Bundle$"'], function (err, stdout, stderr) {
      if (stdout.length > 0) {
        deferred.resolve(stdout.split('\n'));
      } else {
        deferred.resolve(false);
      }
    });
    deferred.promise.then(function (filesList) {
      var prompts = [], choices;
      if (filesList) {
        choices = filesList.map(extractChoiceFromFilename);
        prompts.push({
          type: 'list',
          name: 'bundle',
          message: 'Choose a bundle to use or enter your own',
          choices: choices
        });
        return promptHandler(prompts);
      }
      return false;
    }).then(function (answers) {
      var bundleData;
      if (answers) {
        bundleData = extractBundleDataFromPath(answers.bundle) || defaults;
        setBundlePath('/' + answers.bundle + '/');
      } else {
        bundleData = defaults;
      }
      var prompts = [
        {
          name: 'entityName',
          message: 'What would you like to call the entity name (enter it in CamelCase)?',
          default: 'DefaultEntity'
        },
        {
          name: 'entityNamePlural',
          message: 'What would you like to call the entity name plural (enter it in CamelCase, leave empty if you want to append "s" to the entity name)?',
          default: ''
        },
        {
          name: 'bundleBase',
          message: 'What is the bundle base?',
          default: bundleData.bundleBase
        },
        {
          name: 'bundleShortName',
          message: 'What is the bundle short/effective name?',
          default: bundleData.bundleShortName
        }
      ];
      return promptHandler(prompts);
    }).then(function (answers) {

      this.user = answers.user;
      this.entityName = escapeQuotes(answers.entityName);
      this.entityNamePlural = escapeQuotes(answers.entityNamePlural);
      if (this.entityNamePlural.length === 0) {
        this.entityNamePlural = this.entityName + 's';
      }
      this.bundleShortName = escapeQuotes(answers.bundleShortName);
      this.bundleBase = escapeQuotes(answers.bundleBase);

      appendProps({
        EntityName: this.entityName,
        entityName: _.camelCase(this.entityName),
        entity_name: _.snakeCase(this.entityName),
        entityname: _.camelCase(this.entityName).toLowerCase(),
        EntityNames: this.entityNamePlural,
        entityNames: _.camelCase(this.entityNamePlural),
        enity_names: _.snakeCase(this.entityNamePlural),
        enitynames: _.camelCase(this.entityNamePlural).toLowerCase(),
        BundleBase: this.bundleBase,
        BundleShortName: this.bundleShortName
      });
      appendExtraProps({
        created: false,
        date: dateFormat(now, 'dd/mmm/yy'),
        time: dateFormat(now, 'HH:MM'),
        user: process.env.USER
      });
      this.appendable = /(Resources\/(config|translations|config\/oro)\/[^\/]*\.yml$)/;
      this.notAppendable = /oro\/(routing|bundles)\.yml$/;
      var extendedPrompts = [{
        name: 'bundleName',
        message: 'What is the bundle full name?',
        default: this.bundleBase + this.bundleShortName + 'Bundle'
      }, {
        name: 'bundle_name',
        message: 'What is the bundle full name?',
        default: _.snakeCase(this.bundleBase) + '_' + _.snakeCase(this.bundleShortName)
      }, {
        name: 'bundleDotName',
        message: 'What is the bundle dot notation?',
        default: _.snakeCase(this.bundleBase) + '.' + _.camelCase(this.bundleShortName).toLowerCase()
      }, {
        name: 'bundleNamespace',
        message: 'What is the bundle namespace?',
        default: answers.bundleBase + '\\Bundle\\' + answers.bundleShortName + 'Bundle'
      }
      ];
      return promptHandler(extendedPrompts);
    }.bind(this)).then(function (extendedAnswers) {
      this.bundleName = escapeQuotes(extendedAnswers.bundleName);
      this.bundle_name = escapeQuotes(extendedAnswers.bundle_name);
      this.bundleDotName = escapeQuotes(extendedAnswers.bundleDotName);
      this.bundleNamespace = escapeQuotes(extendedAnswers.bundleNamespace);
      appendProps({
        bundle: {
          _name: this.bundleDotName
        },
        BundleName: this.bundleName,
        bundleName: _.camelCase(this.bundleName),
        bundle_name: _.snakeCase(this.bundle_name),
        BundleNamespace: this.bundleNamespace
      });
      cb();
    }.bind(this));
  },
  generateFiles: function () {
    var self = this,
      source = this.sourceRoot(),
      baseLength = source.length,
      dest = this.options.env.cwd + (this.bundlePath || ''),
      extraProps = _.assign(this.extraProps, this.props), escapeYaml;

    escapeYaml = function (str) {
      return str.replace(/([^"'])(%[^%\s\n]*%)([^"'])/g, "$1'$2'$3").replace(/([^"'])(@[\w\.]+)/g, "$1'$2'");
    };
    execFile('find', [source], function (err, stdout, stderr) {
      var suffix,
        target,
        fileList = stdout.split('\n');
      fileList.forEach(function (file) {
        var k, fileContent, fileContentYml, newYml, newContent;
        if (_.trim(file).length == 0) {
          return;
        }
        if (!fs.lstatSync(file).isFile()) {
          return;
        }
        suffix = file.substring(baseLength);
        if (suffix.search(/(EntityName|entity_name)/i) != -1) {
          for (k in self.props) {
            if (self.props.hasOwnProperty(k)) {
              suffix = suffix.replace(new RegExp(k, 'g'), self.props[k]);
            }
          }
        }
        target = dest + suffix;
        if (suffix.match(self.appendable) && !suffix.match(self.notAppendable) && fs.existsSync(target)) {
          //append
          fileContent = self.readFileAsString(target)
          newContent = _.template(self.readFileAsString(file))(extraProps);

          if (file.match(/(yml|yaml)$/)) {
            fileContentYml = yaml.load(escapeYaml(fileContent));
            newYml = yaml.load(escapeYaml(newContent));
            fileContent = yaml.dump(_.merge({}, fileContentYml, newYml), {
              indent: 4,
              //flowLevel: 5,
              styles: {
                '!!null' : 'canonical'
              }
            });
          } else {
            fileContent += newContent;
          }
          self.write(target, fileContent);
        } else {
          self.fs.copyTpl(file, target, _.extend(self.extraProps, {created: true}));
        }
      })
    });
  }
});
