<?php

/**
 * @file
 * Contains \Drupal\datetime\Plugin\Field\FieldType\DateTimeItem.
 */

namespace Drupal\datetime\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;

/**
 * Plugin implementation of the 'datetime' field type.
 *
 * @FieldType(
 *   id = "datetime",
 *   label = @Translation("Date"),
 *   description = @Translation("Create and store date values."),
 *   default_widget = "datetime_default",
 *   default_formatter = "datetime_default",
 *   list_class = "\Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList"
 * )
 */
class DateTimeItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return array(
      'datetime_type' => 'datetime',
      'enddate_get' => FALSE,
    ) + parent::defaultStorageSettings();
  }

  /**
   * Value for the 'datetime_type' setting: store only a date.
   */
  const DATETIME_TYPE_DATE = 'date';

  /**
   * Value for the 'datetime_type' setting: store a date and time.
   */
  const DATETIME_TYPE_DATETIME = 'datetime';

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $has_end = $field_definition->getSetting('enddate_get');
    $require_end = $field_definition->getSetting('enddate_require');

    $properties['value'] = DataDefinition::create('datetime_iso8601')
      ->setLabel($has_end ? t('Date value (start)') : t('Date value'))
      ->setRequired(TRUE);

    $properties['date'] = DataDefinition::create('any')
      ->setLabel($has_end ? t('Computed date (start)') : t('Computed date'))
      ->setDescription(t('The computed start DateTime object.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\datetime\DateTimeComputed')
      ->setSetting('date source', 'value');

    $properties['value2'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('Date value (end)'))
      ->setRequired($require_end);

    $properties['date2'] = DataDefinition::create('any')
      ->setLabel(t('Computed date (end)'))
      ->setDescription(t('The computed end DateTime object.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\datetime\DateTimeComputed')
      ->setSetting('date source', 'value2');

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = array(
      'columns' => array(
        'value' => array(
          'description' => 'The start date value.',
          'type' => 'varchar',
          'length' => 20,
        ),
        'value2' => array(
          'description' => 'The end date value.',
          'type' => 'varchar',
          'length' => 20,
        ),
      ),
      'indexes' => array(
        'value' => array('value'),
        'value2' => array('value2'),
      ),
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = array();

    $element['datetime_type'] = array(
      '#type' => 'select',
      '#title' => t('Date type'),
      '#description' => t('Choose the type of date to create.'),
      '#default_value' => $this->getSetting('datetime_type'),
      '#options' => array(
        static::DATETIME_TYPE_DATE => t('Date (all day)'),
        static::DATETIME_TYPE_DATETIME => t('Date and time'),
      ),
    );

    $element['enddate_get'] = array(
      '#type' => 'checkbox',
      '#title' => t('Collect an end date'),
      '#default_value' => $this->getSetting('enddate_get'),
      '#disabled' => $has_data,
    );

    $element['enddate_require'] = array(
      '#type' => 'checkbox',
      '#title' => t('Require an end date'),
      '#default_value' => $this->getSetting('enddate_require'),
      '#disabled' => $has_data,
      '#states' => array(
        'visible' => array(
          ':input[id="edit-settings-enddate-get"]' => array('checked' => TRUE),
        ),
      ),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $type = $field_definition->getSetting('datetime_type');
    $collect_end_date = $field_definition->getSetting('enddate_get');

    // Just pick a date in the past year. No guidance is provided by this Field
    // type.
    $timestamp = REQUEST_TIME - mt_rand(0, 86400*365);
    if ($type == DateTimeItem::DATETIME_TYPE_DATE) {
      $values['value'] = gmdate(DATETIME_DATE_STORAGE_FORMAT, $timestamp);
      if ($collect_end_date) {
        $values['value2'] = gmdate(DATETIME_DATE_STORAGE_FORMAT, $timestamp + 86400);
      }
    }
    else {
      $values['value'] = gmdate(DATETIME_DATETIME_STORAGE_FORMAT, $timestamp);
      if ($collect_end_date) {
        $values['value2'] = gmdate(DATETIME_DATETIME_STORAGE_FORMAT, $timestamp + 86400);
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name, $notify = TRUE) {
    // Enforce that the computed date is recalculated.
    if ($property_name == 'value') {
      $this->date = NULL;
    }
    if ($property_name == 'value2') {
      $this->date2 = NULL;
    }
    parent::onChange($property_name, $notify);
  }

}
