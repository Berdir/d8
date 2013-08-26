<?php

/**
 * @file
 * Contains \Drupal\config_test\ConfigTestAccessController.
 */

namespace Drupal\config_test;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the access controller for the config_test entity type.
 */
class ConfigTestAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, AccountInterface $account = NULL) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return TRUE;
  }

}
