<?php

/**
 * @file
 * Contains \Drupal\text\Plugin\field\field_type\CTextItem.
 */

namespace Drupal\text\Plugin\field\field_type;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\field\Plugin\Core\Entity\Field;

/**
 * Plugin implementation of the 'text' field type.
 *
 * @Plugin(
 *   id = "text",
 *   module = "text",
 *   label = @Translation("Text"),
 *   description = @Translation("This field stores varchar text in the database."),
 *   settings = {
 *     "max_length" = "255"
 *   },
 *   instance_settings = {
 *     "text_processing" = "0"
 *   },
 *   default_widget = "text_textfield",
 *   default_formatter = "text_default"
 * )
 */
class CTextItem extends CTextItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(Field $field) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'varchar',
          'length' => $field->settings['max_length'],
          'not null' => FALSE,
        ),
        'format' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'format' => array('format'),
      ),
      'foreign keys' => array(
        'format' => array(
          'table' => 'filter_format',
          'columns' => array('format' => 'format'),
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state, $has_data) {
    $element = array();

    $element['max_length'] = array(
      '#type' => 'number',
      '#title' => t('Maximum length'),
      '#default_value' => $this->instance->getField()->settings['max_length'],
      '#required' => TRUE,
      '#description' => t('The maximum length of the field in characters.'),
      '#min' => 1,
      // @todo: If $has_data, add a validate handler that only allows
      // max_length to increase.
      '#disabled' => $has_data,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function instanceSettingsForm(array $form, array &$form_state) {
    $element = array();

    $element['text_processing'] = array(
      '#type' => 'radios',
      '#title' => t('Text processing'),
      '#default_value' => $this->instance->settings['text_processing'],
      '#options' => array(
        t('Plain text'),
        t('Filtered text (user selects text format)'),
      ),
    );

    return $element;
  }

}
