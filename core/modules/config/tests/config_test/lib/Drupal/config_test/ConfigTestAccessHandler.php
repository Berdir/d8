<?php

/**
 * @file
 * Contains \Drupal\config_test\ConfigTestAccessHandler.
 */

namespace Drupal\config_test;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityAccessHandler;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the access handler for the config_test entity type.
 *
 * @see \Drupal\config_test\Entity\ConfigTest
 */
class ConfigTestAccessHandler extends EntityAccessHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return TRUE;
  }

}
