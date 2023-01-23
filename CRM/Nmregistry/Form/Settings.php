<?php

require_once 'CRM/Core/Form.php';
use CRM_Nmregistry_ExtensionUtil as E;

/**
 * Form controller class for extension Settings form.
 * Borrowed heavily from
 * https://github.com/eileenmcnaughton/nz.co.fuzion.civixero/blob/master/CRM/Civixero/Form/XeroSettings.php
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Nmregistry_Form_Settings extends CRM_Core_Form {

  // Group must be the same as the group in your Extension.setting.php
  public static $settingFilter = array('group' => 'nmregistry');
  public static $extensionName = 'com.joineryhq.nmregistry';
  private $_submittedValues = array();
  private $_settings = array();

  public function __construct(
    $state = NULL,
    $action = CRM_Core_Action::NONE,
    $method = 'post',
    $name = NULL
  ) {

    $this->setSettings();

    parent::__construct(
      $state = NULL,
      $action = CRM_Core_Action::NONE,
      $method = 'post',
      $name = NULL
    );
  }

  public function buildQuickForm() {
    $settings = $this->_settings;
    foreach ($settings as $name => $setting) {
      $element = NULL;
      if (isset($setting['quick_form_type'])) {
        switch ($setting['html_type']) {
          case 'Select':
            $element = $this->add(
              // field type
              $setting['html_type'],
              // field name
              $setting['name'],
              // field label
              $setting['title'],
              $this->getSettingOptions($setting),
              NULL,
              $setting['html_attributes']
            );
            break;

          case 'CheckBox':
            $element = $this->addCheckBox(
              // field name
              $setting['name'],
              // field label
              $setting['title'],
              array_flip($this->getSettingOptions($setting))
            );
            break;

          case 'Radio':
            $element = $this->addRadio(
              // field name
              $setting['name'],
              // field label
              $setting['title'],
              $this->getSettingOptions($setting)
            );
            break;

          default:
            $add = 'add' . $setting['quick_form_type'];
            if ($add == 'addElement') {
              $element = $this->$add($setting['html_type'], $name, E::ts($setting['title']), CRM_Utils_Array::value('html_attributes', $setting, array()));
            }
            else {
              $element = $this->$add($name, E::ts($setting['title']));
            }
            break;
        }
        if ($element && ($setting['html_attributes']['readonly'] ?? FALSE)) {
          $element->freeze();
        }
      }
      $descriptions[$setting['name']] = E::ts($setting['description']);

      if (!empty($setting['X_form_rules_args'])) {
        $rules_args = (array) $setting['X_form_rules_args'];
        foreach ($rules_args as $rule_args) {
          array_unshift($rule_args, $setting['name']);
          call_user_func_array(array($this, 'addRule'), $rule_args);
        }
      }
    }
    $this->assign("descriptions", $descriptions);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ),
    ));

    // Add styles path if you have custom styles for the form in your extension
    /*
    $style_path = CRM_Core_Resources::singleton()->getPath(self::$extensionName, 'css/extension.css');
    if ($style_path) {
      CRM_Core_Resources::singleton()->addStyleFile(self::$extensionName, 'css/extension.css');
    }
    */

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());

    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/admin/nmregistry/settings', 'reset=1', TRUE));
    parent::buildQuickForm();
  }

  /**
   * You need to write custom code for this function to validate the data in your settings fields
   */
  public function validate() {
    $error = parent::validate();
    return (0 == count($this->_errors));
  }

  /*
   * You need to write custom code for this function to save the data in your settings fields
   *
   */
  public function postProcess() {
    $this->_submittedValues = $this->exportValues();
    $this->saveSettings();
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons". These
    // items don't have labels. We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  /**
   * Define the list of settings we are going to allow to be set on this form.
   *
   */
  public function setSettings() {
    if (empty($this->_settings)) {
      $this->_settings = self::getSettings();
    }
  }

  public static function getSettings() {
    $settings = _nmregistry_civicrmapi('setting', 'getfields', array('filters' => self::$settingFilter));
    return $settings['values'];
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   */
  public function saveSettings() {
    $settings = $this->_settings;
    $values = array_intersect_key($this->_submittedValues, $settings);
    _nmregistry_civicrmapi('setting', 'create', $values);

    // Save any that are not submitted, as well (e.g., checkboxes that aren't checked).
    $settingsEditable = $this->filterEditableSettings();
    $unsettings = array_fill_keys(array_keys(array_diff_key($settingsEditable, $this->_submittedValues)), NULL);
    _nmregistry_civicrmapi('setting', 'create', $unsettings);

    CRM_Core_Session::setStatus(" ", E::ts('Settings saved.'), "success");
  }

  /**
   * From all settings, get only the ones that are editable in the form.
   * (E.g. settings are not shown in the form if 'quick_form_type' is NULL;
   * settings are not editable in the form if ['html_attributes']['readonly'] is set.)
   */
  private function filterEditableSettings() {
    $ret = [];
    foreach ($this->_settings as $name => $setting) {
      if (
        !isset($setting['quick_form_type'])
        || ($setting['html_attributes']['readonly'] ?? NULL)
      ) {
        continue;
      }
      $ret[$name] = $setting;
    }
    return $ret;
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  public function setDefaultValues() {
    $result = _nmregistry_civicrmapi('setting', 'get', array('return' => array_keys($this->_settings)));
    $domainID = CRM_Core_Config::domainID();
    $ret = CRM_Utils_Array::value($domainID, $result['values']);
    return $ret;
  }

  public function getSettingOptions($setting) {
    if (!empty($setting['X_options_callback']) && is_callable($setting['X_options_callback'])) {
      return call_user_func($setting['X_options_callback']);
    }
    else {
      return CRM_Utils_Array::value('X_options', $setting, array());
    }
  }

}
