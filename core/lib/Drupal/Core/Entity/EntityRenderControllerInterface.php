<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\EntityRenderControllerInterface.
 */

namespace Drupal\Core\Entity;

/**
 * Defines a common interface for entity view controller classes.
 */
interface EntityRenderControllerInterface {
  /**
   * Build the structured $content property on the entity.
   *
   * @param EntityInterface $entity
   *   The entities, implementing EntityInterface, whose content is being built.
   * @param string $view_mode
   *   The view mode to use when building that entity. All core entities include
   *   at least a default "full" view mode.
   * @param string $langcode
   *   The language for which to built the content of the entity.
   *
   * @return array
   *   The content array.
   */
  public function buildContent(array &$entities = array(), $view_mode = 'full', $langcode = NULL);

  /**
   * Main Entity view method.
   *
   * @param EntityInterface $entity
   *   The entity to view.
   * @param string $view_mode
   *   The view mode to use when building that entity. All core entities include
   *   at least a default "full" view mode.
   * @param string $langcode
   *   The language for which to view the entity.
   *
   * @return array
   *   A render array for the entity.
   *
   * @throws \InvalidArgumentException
   *   Can be thrown when the set of parameters is inconsistent, like when
   *   trying to view a Comment and passing a Node which is not the one the
   *   comment belongs to, or not passing one, and having the comment node not
   *   be available for loading.
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL);

  /**
   * Multiple Entity view method.
   *
   * @param array $entities
   *   An array of entities implementing EntityInterface to view.
   * @param string $view_mode
   *   The view mode to use when building that entity. All core entities include
   *   at least a default "full" view mode.
   * @param string $langcode
   *   The language for which to view the entity.
   *
   * @return
   *   A render array for the entities, indexed by the same keys as the
   *   entities array passed in $entities.
   *
   * @throws \InvalidArgumentException
   *   Can be thrown when the set of parameters is inconsistent, like when
   *   trying to view Comments and passing a Node which is not the one the
   *   comments belongs to, or not passing one, and having the comments node not
   *   be available for loading.
   */
  public function viewMultiple(array $entities = array(), $view_mode = 'full', $langcode = NULL);
}
