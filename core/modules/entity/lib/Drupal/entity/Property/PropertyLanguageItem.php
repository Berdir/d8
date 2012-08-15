<?php
/**
 * @file
 * Definition of Drupal\entity\Property\PropertyLanguageItem.
 */

namespace Drupal\entity\Property;
use \Drupal\entity\Property\EntityPropertyItemBase;


/**
 * Defines the 'language_item' entity property item.
 */
class PropertyLanguageItem extends EntityPropertyItemBase {

  /**
   * Implements PropertyContainerInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // Statically cache the definitions to avoid creating lots of array copies.
    $definitions = drupal_static(__CLASS__);

    if (!isset($definitions)) {
      $definitions['langcode'] = array(
        'type' => 'string',
        'label' => t('Language code'),
      );
      $definitions['object'] = array(
        'type' => 'language',
        'label' => t('Language object'),
        // The language object is retrieved via the language code.
        'computed' => TRUE,
        'read-only' => FALSE,
      );
    }
    return $definitions;
  }

  /**
   * Overrides EntityPropertyItemBase::setValue().
   */
  public function setValue($values) {
    // Treat the values as property value of the object property, if no array
    // is given.
    if (!is_array($values)) {
      $values = array('object' => $values);
    }

    // Language is computed out of the langcode, so we only need to update the
    // langcode. Only set the language property if no langcode is given.
    if (!empty($values['langcode'])) {
      $this->properties['langcode']->setValue($values['langcode']);
    }
    else {
      $this->properties['object']->setValue(isset($values['object']) ? $values['object'] : NULL);
    }
    unset($values['object'], $values['langcode']);
    if ($values) {
      throw new \InvalidArgumentException('Property ' . key($values) . ' is unknown.');
    }
  }
}
