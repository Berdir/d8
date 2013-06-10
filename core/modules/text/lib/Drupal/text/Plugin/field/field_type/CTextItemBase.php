<?php

/**
 * @file
 * Contains \Drupal\text\Plugin\field\field_type\CTextItemBase.
 */

namespace Drupal\text\Plugin\field\field_type;

use Drupal\field\Plugin\Core\Entity\Field;
use Drupal\field\Plugin\Core\Entity\FieldInstance;
use Drupal\field\Plugin\Type\FieldType\CFieldItemBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Plugin\Type\FieldType\PrepareCacheInterface;

/**
 * Base class for 'text' configurable field types.
 */
abstract class CTextItemBase extends CFieldItemBase implements PrepareCacheInterface {

  /**
   * Definitions of the contained properties.
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
        'type' => 'string',
        'label' => t('Text value'),
      );
      static::$propertyDefinitions['format'] = array(
        'type' => 'string',
        'label' => t('Text format'),
      );
      static::$propertyDefinitions['processed'] = array(
        'type' => 'string',
        'label' => t('Processed text'),
        'description' => t('The text value with the text format applied.'),
        'computed' => TRUE,
        'class' => '\Drupal\text\TextProcessed',
        'settings' => array(
          'text source' => 'value',
        ),
      );
    }
    return static::$propertyDefinitions;
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
  public function getConstraints() {
    $constraint_manager = \Drupal::typedData()->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    // @todo Remove - Just for testing.
//    $constraints[] = $constraint_manager->create('ComplexData', array(
//      'value' => array(
//        'Length' => array(
//          'max' => 3,
//          'maxMessage' => t('%name: testing - max is @max.', array('%name' => $this->instance->label, '@max' => 3)),
//        ),
//      ),
//    ));

    $max_length = $this->instance->getField()->settings['max_length'];
    if (!empty($max_length)) {
      $constraints[] = $constraint_manager->create('ComplexData', array(
        'value' => array(
          'Length' => array(
            'max' => $max_length,
            'maxMessage' => t('%name: the text may not be longer than @max characters.', array('%name' => $this->instance->label, '@max' => $max_length)),
          )
        ),
      ));
    }

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareCache() {
    // Where possible, generate the sanitized version of each field early so
    // that it is cached in the field cache. This avoids the need to look up the
    // field in the filter cache separately.
    if (!$this->instance->settings['text_processing'] || filter_format_allowcache($this->get('format')->getValue())) {
      $itemBC = $this->getValue();
      $langcode = $this->getParent()->getParent()->language()->langcode;
      $this->set('safe_value', text_sanitize($this->instance->settings['text_processing'], $langcode, $itemBC, 'value'));
      if ($this->getPluginId() == 'field_type:text_with_summary') {
        $this->set('safe_summary', text_sanitize($this->instance->settings['text_processing'], $langcode, $itemBC, 'summary'));
      }
    }
  }


  // @todo

  /**
   * {@inheritdoc}
   */
  public function prepareTranslation(EntityInterface $source_entity, $source_langcode) {
    parent::prepareTranslation($entity, $instance, $langcode, $items, $source_entity, $source_langcode);

    // If the translating user is not permitted to use the assigned text format,
    // we must not expose the source values.
    if (!empty($source_entity->{$this->field->id}[$source_langcode])) {
      $formats = filter_formats();
      foreach ($source_entity->{$this->field->id}[$source_langcode] as $delta => $item) {
        $format_id = $item['format'];
        if (!empty($format_id) && !filter_access($formats[$format_id])) {
          unset($items[$delta]);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @todo Just for testing - remove...
   */
  public function prepareView(array $entities, array $instances, $langcode, array &$items) {
    foreach ($entities as $id => $entity) {
      foreach ($items[$id] as $delta => $item) {
//        $items[$id][$delta]['safe_value'] = $delta . $items[$id][$delta]['safe_value'];
      }
    }
  }

}
