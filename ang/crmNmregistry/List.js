(function(angular, $, _) {

  // Store these here because I only want to write them once.
  var nmRemindersApiParams = {
    "options": {
      "limit":0,
      "sort":"days_after DESC"
    },
    "sequential": 1,
  };

  angular.module('crmNmregistry').config(function($routeProvider) {
      $routeProvider.when('/nmregistry/reminders', {
        controller: 'CrmNmregistryList',
        controllerAs: '$ctrl',
        templateUrl: '~/crmNmregistry/List.html',

        // If you need to look up data when opening the page, list it out
        // under "resolve".
        resolve: {
          nmReminders: function(crmApi) {
            return crmApi('NmregistryReminder', 'get', nmRemindersApiParams);
          },
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

//    this.nmReminders = nmReminders;

    // DRY. Returns a Promise.
    this.reloadReminders = function() {
      return crmApi('nmregistryReminder', 'get', nmRemindersApiParams)
        .then(function(result) {
          $scope.nmReminders = result.values || [];
        });
    };

    this.editReminder = function(nmReminder) {
      // Take a copy; we might not want to save it.
      $scope.nmReminder = Object.assign({
        // No defaults, but could put them here.
        is_final: 0,
        name: ''
      }, nmReminder);
      $scope.screen = 'reminder-edit';
    };
    this.saveReminderEdits = function(nmReminder, editReminderForm) {
      if (!editReminderForm.$valid) {
        // If the form values don't pass invalidation, don't bother trying to save anything.
        return;
      }
      var params = _.pick(nmReminder, ['id', 'name', 'days_after', 'msg_template_id', 'is_final']);
      return crmApi('nmregistryReminder', 'create', params)
        .then(this.reloadReminders)
        .then(function() { $scope.screen = 'reminders'; });
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

