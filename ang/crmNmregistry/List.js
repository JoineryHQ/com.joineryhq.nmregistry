(function(angular, $, _) {

  var nmRemindersLoadAll = function nmRemindersLoadAll(crmApi) {
    return crmApi('NmregistryReminder', 'get', {
      "options": {
        "limit":0,
      },
      "sequential": 1,
    })
    .then(function(result) {
      for (var i in result.values) {
        result.values[i].criteria = JSON.parse(result.values[i].criteria);
        result.values[i].nmregistryOrder = parseInt(result.values[i].criteria.days);
      }
      result.values = _.sortBy(result.values, function(value) {
        return value.nmregistryOrder;
      });
      return result;
    });
  };

  angular.module('crmNmregistry').config(function($routeProvider) {
      $routeProvider.when('/nmregistry/reminders', {
        controller: 'CrmNmregistryList',
        controllerAs: '$ctrl',
        templateUrl: '~/crmNmregistry/List.html',

        // If you need to look up data when opening the page, list it out
        // under "resolve".
        resolve: {
          nmReminders: nmRemindersLoadAll,
          msgTemplates: function(crmApi) {
            return crmApi('messageTemplate', 'get', {
              "workflow_name": {"IS NULL":1},
              "options": {
                "limit":0
              }
            });
          }
        }
      });
    }
  );

  // The controller uses *injection*. This default injects a few things:
  //   $scope -- This is the set of variables shared between JS and HTML.
  //   crmApi, crmStatus, crmUiHelp -- These are services provided by civicrm-core.
  //   nmReminders -- All nmregistry_reminders
  //   msgTemplates -- All non-workflow message templates
  angular.module('crmNmregistry').controller('CrmNmregistryList', function($scope, crmApi, crmStatus, crmUiHelp, nmReminders, msgTemplates) {
    // The ts() and hs() functions help load strings for this module.
    var ts = $scope.ts = CRM.ts('nmregistry');
    var hs = $scope.hs = crmUiHelp({file: 'CRM/crmNmregistry/List'}); // See: templates/CRM/crmNmregistry/List.hlp
    // all reminders:

    $scope.msgTemplates = msgTemplates.values || [];
    $scope.nmReminders  = nmReminders.values || [];
    $scope.messageTemplateUrl = CRM.url('civicrm/admin/messageTemplates', 'reset=1');

    // UI mode:
    $scope.screen = 'reminders';

    // Local variable for this controller (needed when inside a callback fn where `this` is not available).
    var ctrl = this;

    // DRY. Returns a Promise.
    this.reloadReminders = function() {
      return nmRemindersLoadAll(crmApi).then(function(result) {
        $scope.nmReminders = result.values || [];
      });
    };

    this.editReminder = function(nmReminder) {
      // Take a copy; we might not want to save it.
      $scope.nmReminder = Object.assign({
        // No defaults, but could put them here.
        is_archive: 0,
        name: '',
        criteria: {
          'days': '',
        }
      }, nmReminder);
      $scope.screen = 'reminder-edit';
    };
    this.saveReminderEdits = function(nmReminder) {
      if (!$scope.editReminderForm.$valid) {
        // If the form values don't pass invalidation, don't bother trying to save anything.
        return;
      }
      var params = _.pick(nmReminder, ['id', 'name', 'msg_template_id', 'is_archive']);
      params.criteria = JSON.stringify(nmReminder.criteria);
      return crmApi('nmregistryReminder', 'create', params)
        .then(this.reloadReminders)
        .then(function() {
          $scope.screen = 'reminders';
          $scope.editReminderForm.$setPristine();
      });
    };
    this.deleteReminder = function(nmReminder) {
      if (confirm(ts('Delete reminder "%1"? This cannot be un-done.', { 1: nmReminder.name }))) {
        return crmApi('nmregistryReminder', 'delete', { id: nmReminder.id })
          .then(this.reloadReminders);
      }
    };
  })

  // stringToNumber directive, per https://code.angularjs.org/1.8.2/docs/error/ngModel/numfmt
  .directive('stringToNumber', function() {
    return {
      require: 'ngModel',
      link: function(scope, element, attrs, ngModel) {
        ngModel.$parsers.push(function(value) {
          return '' + value;
        });
        ngModel.$formatters.push(function(value) {
          return parseFloat(value);
        });
      }
    };
  });

})(angular, CRM.$, CRM._);

