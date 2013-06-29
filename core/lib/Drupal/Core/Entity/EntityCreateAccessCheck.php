<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityCreateAccessCheck.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\Access\AccessCheckInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Defines an access checker for entity creation.
 */
class EntityCreateAccessCheck implements AccessCheckInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManager $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return array_key_exists('_entity_create_access', $route->getRequirements());
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request) {
    list($entity_type, $bundle) = explode(':', $route->getRequirement('_entity_create_access') . ':');

    $definition = $this->entityManager->getDefinition($entity_type);

    $values = $this->prepareEntityValues($definition, $request, $bundle);
    $entity = $this->entityManager->getStorageController($entity_type)
      ->create($values);

    return $this->entityManager->getAccessController($entity_type)->access($entity, 'create');
  }

  /**
   * Prepare the values passed into the storage controller.
   *
   * @param array $definition
   *   The entity type definition.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param string $bundle
   *   (optional) The bundle to check access for.
   *
   * @return array
   *   An array of values to be used when creating the entity.
   */
  protected function prepareEntityValues(array $definition, Request $request, $bundle = NULL) {
    $values = array();
    if ($bundle && isset($definition['entity_keys']['bundle'])) {
      $values[$definition['entity_keys']['bundle']] = $bundle;
    }
    return $values;
  }

}
