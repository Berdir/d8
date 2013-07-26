<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\DataType\MapItem.
 */

namespace Drupal\Core\Entity\Plugin\DataType;

use Drupal\Core\TypedData\Annotation\DataType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'string_field' entity field item.
 *
 * @DataType(
 *   id = "map_field",
 *   label = @Translation("Map field item"),
 *   description = @Translation("An entity field containing a map value."),
 *   list_class = "\Drupal\Core\Entity\Field\Field"
 * )
 */
class MapItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @see MapItem::getPropertyDefinitions()
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
        'type' => 'map',
        'label' => t('Array values'),
      );
    }
    return static::$propertyDefinitions;
  }

}
