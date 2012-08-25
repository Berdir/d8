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
   * Implements DataStructureInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    // Statically cache the definitions to avoid creating lots of array copies.
    $definitions = &drupal_static(__CLASS__);

    if (!isset($definitions)) {
      $definitions['value'] = array(
        'type' => 'string',
        'label' => t('Language code'),
      );
      $definitions['language'] = array(
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
      $values = array('language' => $values);
    }

    // Language is computed out of the langcode, so we only need to update the
    // langcode. Only set the language property if no langcode is given.
    if (!empty($values['value'])) {
      $this->properties['value']->setValue($values['value']);
    }
    else {
      $this->properties['language']->setValue(isset($values['language']) ? $values['language'] : NULL);
    }
    unset($values['language'], $values['value']);
    if ($values) {
      throw new \InvalidArgumentException('Property ' . key($values) . ' is unknown.');
    }
  }
}
