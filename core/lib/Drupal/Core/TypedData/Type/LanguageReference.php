<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\Type\LanguageReference.
 */

namespace Drupal\Core\TypedData\Type;

use Drupal\Core\TypedData\DataReferenceBase;

/**
 * Defines the 'language_reference' data type.
 *
 * The plain value is the language object, i.e. an instance of
 * \Drupal\Core\Language\Language. For setting the value the language object or
 * the language code as string may be passed.
 *
 * Required settings (below the definition's 'settings' key) are:
 *  - source: The langcode property used to load the language object.
 */
class LanguageReference extends DataReferenceBase {

  /**
   * Implements \Drupal\Core\TypedData\DataReferenceInterface::getTargetDefinition().
   */
  public function getTargetDefinition() {
    return array(
      'type' => 'language',
    );
  }

  /**
   * Implements \Drupal\Core\TypedData\DataReferenceInterface::getTargetIdentifier().
   */
  public function getTargetIdentifier() {
    if ($target_value = $this->getTarget()->getValue()) {
      return $target_value->langcode;
    }
  }
}
