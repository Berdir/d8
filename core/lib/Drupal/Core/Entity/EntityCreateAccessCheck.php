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
   * The key used by the routing requirement.
   *
   * @var string
   */
  protected $requirementsKey = '_entity_create_access';

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
    return array_key_exists($this->requirementsKey, $route->getRequirements());
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request) {
    list($entity_type, $bundle) = explode(':', $route->getRequirement($this->requirementsKey) . ':');
    return $this->entityManager->getAccessController($entity_type)->createAccess($bundle);
  }

}
