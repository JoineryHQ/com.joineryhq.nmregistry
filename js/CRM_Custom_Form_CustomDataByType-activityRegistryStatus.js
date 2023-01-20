CRM.$(function($) {
  function statusChange() {
    var statusLabel = $('label[for="' + this.id + '"]').html();
    var subject = ts('Update registry status: %1', {1: statusLabel});
    $('input#subject').val(subject);
    $('div#nmregistry-subject').html(subject);
  }
  
  var statusCustomFieldId = 22; // TODO: GET THIS FROM A SETTING.
  var statusCustomFieldRow = $('input[data-api-field="custom_' + statusCustomFieldId + '"]').closest('tr.custom_field-row');

  // Add a class to the custom field group container so we can handle it later.
  $(statusCustomFieldRow).closest('tr.crm-activity-form-block-custom_data').addClass('x-nmregistry-statusField-container');
  // Move the status field to below the Subject field.
  $('input#subject').closest('tr').after(statusCustomFieldRow);
  // Un-check all status values.
  $(statusCustomFieldRow).find('input').prop('checked', 0);
  // Add change handler to status radios.
  $(statusCustomFieldRow).find('input').change(statusChange);
  // Hide subject field and replace it with plain text
  $('input#subject').hide().after('<div style="background-color: rgba(255,255,255,0.5); padding: .5em;" id="nmregistry-subject">...</div>');

  // Set activity status = completed and trigger change so as to propagate to select2
  $('select#status_id').val(2).change();
  // Hide custom field group container if it's now empty
  if (!$('.x-nmregistry-statusField-container tr.custom_field-row').length) {
    $('.x-nmregistry-statusField-container').hide();
  }
  
});