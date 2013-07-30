<?php

/**
 * @file
 * Contains \Drupal\book\Plugin\DataType\BookItem.
 */

namespace Drupal\book\Plugin\DataType;

use Drupal\Core\TypedData\Annotation\DataType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'book_field' entity field item.
 *
 * @DataType(
 *   id = "book_field",
 *   label = @Translation("Book field item"),
 *   description = @Translation("An entity field containing a book id and related data."),
 *   list_class = "\Drupal\Core\Entity\Field\Field"
 * )
 */
class BookItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @see BookItem::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements \Drupal\Core\TypedData\ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['bid'] = array(
        'type' => 'integer',
        'label' => t('Book id'),
      );
      static::$propertyDefinitions['menu_name'] = array(
        'type' => 'string',
        'label' => t('Menu name'),
      );
      static::$propertyDefinitions['link_path'] = array(
        'type' => 'string',
        'label' => t('Link path'),
      );
      static::$propertyDefinitions['link_title'] = array(
        'type' => 'string',
        'label' => t('Link title'),
      );
    }
    return static::$propertyDefinitions;
  }

}
