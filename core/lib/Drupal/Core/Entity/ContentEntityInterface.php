<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\ContentEntityInterface.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * Defines a common interface for all content entity objects.
 */
interface ContentEntityInterface extends EntityInterface, RevisionableInterface, TranslatableInterface, ComplexDataInterface {

  /**
   * Marks the translation identified by the given language code as existing.
   *
   * @param string $langcode
   *   The language code identifying the translation to be initialized.
   *
   * @todo Remove this as soon as translation metadata have been converted to
   *    regular fields.
   */
  public function initTranslation($langcode);

  /**
   * Defines the base fields of the entity type.
   *
   * @param string $entity_type
   *   The entity type to return properties for. Useful when a single class is
   *   used for multiple, possibly dynamic entity types.
   *
   * @return array
   *   An array of entity field definitions as specified by
   *   \Drupal\Core\Entity\EntityManager::getFieldDefinitions(), keyed by field
   *   name.
   *
   * @see \Drupal\Core\Entity\EntityManager::getFieldDefinitions()
   */
  public static function baseFieldDefinitions($entity_type);

}
