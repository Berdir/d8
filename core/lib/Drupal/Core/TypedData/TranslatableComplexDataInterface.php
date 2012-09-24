<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\TranslatableComplexDataInterface.
 */

namespace Drupal\Core\TypedData;

/**
 * Interface for translatable complex data.
 */
interface TranslatableComplexDataInterface extends ComplexDataInterface {

  /**
   * Returns the default language.
   *
   * @return
   *   The language object.
   */
  public function language();

  /**
   * Returns the languages the data is translated to.
   *
   * @param bool $include_default
   *   Whether the default language should be included.
   *
   * @return
   *   An array of language objects, keyed by language codes.
   */
  public function getTranslationLanguages($include_default = TRUE);

  /**
   * Gets a translation of contained properties.
   *
   * @param $langcode
   *   The language code of the translation to get or LANGUAGE_DEFAULT to get
   *   the data in default language.
   *
   * @return \Drupal\Core\TypedData\ComplexDataInterface
   *   A complex data object containing the translated properties.
   */
  public function getTranslation($langcode);
}
