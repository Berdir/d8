<?php

/**
 * @file
 * Contains \Drupal\config_test\ConfigTestAccess.
 */

namespace Drupal\config_test;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityAccessHandler;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the access class for the config_test entity type.
 *
 * @see \Drupal\config_test\Entity\ConfigTest
 */
class ConfigTestAccess extends EntityAccessHandler {

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
