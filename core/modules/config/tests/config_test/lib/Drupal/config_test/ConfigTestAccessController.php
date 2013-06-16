<?php

/**
 * @file
 * Contains \Drupal\config_test\ConfigTestAccessController.
 */

namespace Drupal\config_test;

use Drupal\user\Plugin\Core\Entity\User;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the access controller for the config_test entity type.
 */
class ConfigTestAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, $langcode = LANGUAGE_DEFAULT, User $account = NULL) {
    return TRUE;
  }

}
