<?php

/**
 * @file
 * Contains \Drupal\text\Plugin\Field\FieldType\TextItem.
 */

namespace Drupal\text\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'text' field type.
 *
 * @FieldType(
 *   id = "text",
 *   label = @Translation("Text (filtered)"),
 *   description = @Translation("This field stores varchar text in the database."),
 *   default_widget = "text_textfield",
 *   default_formatter = "text_default"
 * )
 */
class TextItem extends TextItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'max_length' => 255,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'varchar',
          'length' => $field_definition->getSetting('max_length'),
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
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    if ($max_length = $this->getSetting('max_length')) {
      $constraints[] = $constraint_manager->create('ComplexData', array(
        'value' => array(
          'Length' => array(
            'max' => $max_length,
            'maxMessage' => t('%name: the text may not be longer than @max characters.', array('%name' => $this->getFieldDefinition()->getLabel(), '@max' => $max_length)),
          )
        ),
      ));
    }

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = array();

    $element['max_length'] = array(
      '#type' => 'number',
      '#title' => t('Maximum length'),
      '#default_value' => $this->getSetting('max_length'),
      '#required' => TRUE,
      '#description' => t('The maximum length of the field in characters.'),
      '#min' => 1,
      '#disabled' => $has_data,
    );

    return $element;
  }

}
