<?php

/**
 * @file
 * Contains \Drupal\field_test\Plugin\field\field_type\TestItem.
 */

namespace Drupal\field_test\Plugin\field\field_type;

use Drupal\Core\Entity\Annotation\FieldType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Field\PrepareCacheInterface;
use Drupal\field\FieldInterface;
use Drupal\field\Plugin\Type\FieldType\ConfigFieldItemBase;

/**
 * Defines the 'test_field' entity field item.
 *
 * @FieldType(
 *   id = "test_field",
 *   label = @Translation("Test field"),
 *   description = @Translation("Dummy field type used for tests."),
 *   settings = {
 *     "test_field_setting" = "dummy test string",
 *     "changeable" =  "a changeable field setting",
 *     "unchangeable" = "an unchangeable field setting"
 *   },
 *   instance_settings = {
 *     "test_instance_setting" = "dummy test string",
 *     "test_hook_field_load" = FALSE
 *   },
 *   default_widget = "test_field_widget",
 *   default_formatter = "field_test_default"
 * )
 */
class TestItem extends ConfigFieldItemBase implements PrepareCacheInterface {

  /**
   * Property definitions of the contained properties.
   *
   * @see TestItem::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {

    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['value'] = array(
        'type' => 'integer',
        'label' => t('Test integer value'),
      );
    }
    return static::$propertyDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldInterface $field) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'int',
          'size' => 'medium',
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'value' => array('value'),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    $this->setValue(array('value' => 99), $notify);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state, $has_data) {
    $form['test_field_setting'] = array(
      '#type' => 'textfield',
      '#title' => t('Field test field setting'),
      '#default_value' => $this->getFieldSetting('test_field_setting'),
      '#required' => FALSE,
      '#description' => t('A dummy form element to simulate field setting.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function instanceSettingsForm(array $form, array &$form_state) {
    $form['test_instance_setting'] = array(
      '#type' => 'textfield',
      '#title' => t('Field test field instance setting'),
      '#default_value' => $this->getFieldSetting('test_instance_setting'),
      '#required' => FALSE,
      '#description' => t('A dummy form element to simulate field instance setting.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareCache() {
    if ($this->getFieldSetting('test_hook_field_load')) {
      $this->additional_key = 'additional_value';
    }
  }

}
