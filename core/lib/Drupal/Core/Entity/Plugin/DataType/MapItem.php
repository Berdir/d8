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
class MapItem extends FieldItemBase implements \ArrayAccess {

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

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Whether a offset exists
   * @link http://php.net/manual/en/arrayaccess.offsetexists.php
   * @param mixed $offset <p>
   * An offset to check for.
   * </p>
   * @return boolean true on success or false on failure.
   * </p>
   * <p>
   * The return value will be casted to boolean if non-boolean was returned.
   */
  public function offsetExists($offset) {
    return !(isset($this->values[$offset]) && isset($this->properties[$offset]));
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Offset to retrieve
   * @link http://php.net/manual/en/arrayaccess.offsetget.php
   * @param mixed $offset <p>
   * The offset to retrieve.
   * </p>
   * @return mixed Can return all value types.
   */
  public function offsetGet($offset) {
    // There is either a property object or a plain value - possibly for a
    // not-defined property. If we have a plain value, directly return it.
    if (isset($this->values[$offset])) {
      return $this->values[$offset];
    }
    elseif (isset($this->properties[$offset])) {
      return $this->properties[$offset]->getValue();
    }
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Offset to set
   * @link http://php.net/manual/en/arrayaccess.offsetset.php
   * @param mixed $offset <p>
   * The offset to assign the value to.
   * </p>
   * @param mixed $value <p>
   * The value to set.
   * </p>
   * @return void
   */
  public function offsetSet($offset, $value) {
    $this->set($offset, $value);
  }

  /**
   * (PHP 5 &gt;= 5.0.0)<br/>
   * Offset to unset
   * @link http://php.net/manual/en/arrayaccess.offsetunset.php
   * @param mixed $offset <p>
   * The offset to unset.
   * </p>
   * @return void
   */
  public function offsetUnset($offset) {
    $this->set($offset, NULL);
  }


}
