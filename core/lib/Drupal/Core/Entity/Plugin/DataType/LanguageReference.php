<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Field\Type\LanguageReference.
 */

namespace Drupal\Core\Entity\Field\Type;

use Drupal\Core\TypedData\DataReferenceBase;

/**
 * Defines the 'language_reference' data type.
 *
 * This serves as 'language' property of entity reference field items and gets
 * its value set from the parent, i.e. LanguageItem.
 *
 * The plain value is the language object, i.e. an instance of
 * \Drupal\Core\Language\Language. For setting the value the language object or
 * the language code as string may be passed.
 *
 * @DataType(
 *   id = "language_reference",
 *   label = @Translation("Language reference"),
 * )
 */
class LanguageReference extends DataReferenceBase {

  /**
   * {@inheritdoc}
   */
  public function getTargetDefinition() {
    return array(
      'type' => 'language',
    );
  }
}
