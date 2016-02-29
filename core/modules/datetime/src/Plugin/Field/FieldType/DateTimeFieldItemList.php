<?php

/**
 * @file
 * Contains \Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList.
 */

namespace Drupal\datetime\Plugin\Field\FieldType;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Form\FormStateInterface;

/**
 * Represents a configurable entity datetime field.
 */
class DateTimeFieldItemList extends FieldItemList {

  /**
   * Defines the default value as now.
   */
  const DEFAULT_VALUE_NOW = 'now';

  /**
   * Defines the default value as relative.
   */
  const DEFAULT_VALUE_CUSTOM = 'relative';

  /**
   * Defines the default value as relative.
   */
  const DEFAULT_VALUE_SAME = 'same';

  /**
   * {@inheritdoc}
   */
  public function defaultValuesForm(array &$form, FormStateInterface $form_state) {
    if (empty($this->getFieldDefinition()->getDefaultValueCallback())) {
      $default_value = $this->getFieldDefinition()->getDefaultValueLiteral();

      $element = array(
        '#parents' => array('default_value_input'),
        'default_date_type' => array(
          '#type' => 'select',
          '#title' => t('Default date'),
          '#description' => t('Set a default value for this date.'),
          '#default_value' => isset($default_value[0]['default_date_type']) ? $default_value[0]['default_date_type'] : '',
          '#options' => array(
            static::DEFAULT_VALUE_NOW => t('Current date'),
            static::DEFAULT_VALUE_CUSTOM => t('Relative date'),
          ),
          '#empty_value' => '',
        ),
        'default_date' => array(
          '#type' => 'textfield',
          '#title' => t('Relative default value'),
          '#description' => t("Describe a time by reference to the current day, like '+90 days' (90 days from the day the field is created) or '+1 Saturday' (the next Saturday). See <a href=\"http://php.net/manual/function.strtotime.php\">strtotime</a> for more details."),
          '#default_value' => (isset($default_value[0]['default_date_type']) && $default_value[0]['default_date_type'] == static::DEFAULT_VALUE_CUSTOM) ? $default_value[0]['default_date'] : '',
          '#states' => array(
            'visible' => array(
              ':input[id="edit-default-value-input-default-date-type"]' => array('value' => static::DEFAULT_VALUE_CUSTOM),
            )
          )
        )
      );

      if ($this->getFieldDefinition()->getSetting('enddate_get')) {
        $element += array(
          'default_date_type2' => array(
            '#type' => 'select',
            '#title' => t('Default end date'),
            '#description' => t('Set a default value for this end date.'),
            '#default_value' => isset($default_value[0]['default_date_type2']) ? $default_value[0]['default_date_type2'] : '',
            '#options' => array(
              static::DEFAULT_VALUE_SAME => t('Same as Default date'),
              static::DEFAULT_VALUE_NOW => t('Current date'),
              static::DEFAULT_VALUE_CUSTOM => t('Relative date'),
            ),
            '#empty_value' => '',
            '#states' => array(
              'visible' => array(
                ':input[id="edit-default-value-input-default-date-type"]' => array('filled' => TRUE),
              ),
            ),
          ),
          'default_date2' => array(
            '#type' => 'textfield',
            '#title' => t('Relative default end value'),
            '#description' => t("Describe a time by reference to the current day, like '+90 days' (90 days from the day the field is created) or '+1 Saturday' (the next Saturday). See <a href=\"@url\">@strtotime</a> for more details.", array('@strtotime' => 'strtotime', '@url' => 'http://www.php.net/manual/en/function.strtotime.php')),
            '#default_value' => (isset($default_value[0]['default_date_type2']) && $default_value[0]['default_date_type2'] == static::DEFAULT_VALUE_CUSTOM) ? $default_value[0]['default_date2'] : '',
            '#states' => array(
              'visible' => array(
                ':input[id="edit-default-value-input-default-date-type2"]' => array('value' => static::DEFAULT_VALUE_CUSTOM),
              ),
            ),
          ),
        );
      }

      return $element;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultValuesFormValidate(array $element, array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue(['default_value_input', 'default_date_type']) == static::DEFAULT_VALUE_CUSTOM) {
      $is_strtotime = @strtotime($form_state->getValue(array('default_value_input', 'default_date')));
      if (!$is_strtotime) {
        $form_state->setErrorByName('default_value_input][default_date', t('The relative date value entered is invalid.'));
      }
    }

    if ($form_state->getValue(['default_value_input', 'default_date_type2']) == static::DEFAULT_VALUE_CUSTOM) {
      $is_strtotime = @strtotime($form_state->getValue(array('default_value_input', 'default_date2')));
      if (!$is_strtotime) {
        $form_state->setErrorByName('default_value_input][default_date2', t('The relative date value entered is invalid.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultValuesFormSubmit(array $element, array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue(array('default_value_input', 'default_date_type'))) {
      // Set the start date default to now.
      if ($form_state->getValue(array('default_value_input', 'default_date_type')) == static::DEFAULT_VALUE_NOW) {
        $form_state->setValueForElement($element['default_date'], static::DEFAULT_VALUE_NOW);
      }
      // Set the end date default to now.
      if ($form_state->getValue(array('default_value_input', 'default_date_type2')) == static::DEFAULT_VALUE_NOW) {
        $form_state->setValueForElement($element['default_date2'], static::DEFAULT_VALUE_NOW);
      }
      // Set the end date default to same default as start date.
      if ($form_state->getValue(array('default_value_input', 'default_date_type2')) == static::DEFAULT_VALUE_SAME) {
        $form_state->setValueForElement($element['default_date2'], $form_state->getValue(array('default_value_input', 'default_date')));
      }
      return array($form_state->getValue('default_value_input'));
    }
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public static function processDefaultValue($default_value, FieldableEntityInterface $entity, FieldDefinitionInterface $definition) {
    $default_value = parent::processDefaultValue($default_value, $entity, $definition);

    $return = array();

    if (isset($default_value[0]['default_date_type'])) {
      // A default value should be in the format and timezone used for date
      // storage.
      $date = new DrupalDateTime($default_value[0]['default_date'], DATETIME_STORAGE_TIMEZONE);
      $storage_format = $definition->getSetting('datetime_type') == DateTimeItem::DATETIME_TYPE_DATE ? DATETIME_DATE_STORAGE_FORMAT: DATETIME_DATETIME_STORAGE_FORMAT;
      $value = $date->format($storage_format);
      // We only provide a default value for the first item, as do all fields.
      // Otherwise, there is no way to clear out unwanted values on multiple value
      // fields.
      $return = array(
        array(
          'value' => $value,
          'date' => $date,
        )
      );
    }

    // Repeat for end date.
    if ($definition->getSetting('enddate_get') && !empty($default_value[0]['default_date_type2'])) {
      // A default value should be in the format and timezone used for date
      // storage.
      $date = new DrupalDateTime($default_value[0]['default_date2'], DATETIME_STORAGE_TIMEZONE);
      $storage_format = $definition->getSetting('datetime_type2') == DateTimeItem::DATETIME_TYPE_DATE ? DATETIME_DATE_STORAGE_FORMAT : DATETIME_DATETIME_STORAGE_FORMAT;
      $value = $date->format($storage_format);
      // We only provide a default value for the first item, as do all fields.
      // Otherwise, there is no way to clear out unwanted values on multiple value
      // fields.
      $return[0] += array(
        'value2' => $value,
        'date2' => $date,
      );
    }
    return empty($return) ? $default_value : $return;
  }

}
