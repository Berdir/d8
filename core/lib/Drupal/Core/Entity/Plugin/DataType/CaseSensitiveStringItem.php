<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\DataType\CaseSensitiveStringItem.
 */

namespace Drupal\Core\Entity\Plugin\DataType;

use Drupal\Core\TypedData\Annotation\DataType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'case_sensitive_string_field' entity field item.
 *
 * @DataType(
 *   id = "case_sensitive_string_field",
 *   label = @Translation("Case sensitive string field item"),
 *   description = @Translation("An entity field containing a binary string (case sensitive) value."),
 *   list_class = "\Drupal\Core\Entity\Field\Field"
 * )
 */
class CaseSensitiveStringItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @see BinaryStringItem::getPropertyDefinitions()
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
        'case sensitive' => TRUE,
      );
    }
    return static::$propertyDefinitions;
  }
}
