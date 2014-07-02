<?php

/**
 * @file
 * Contains Drupal\entity_test\EntityTestAccessHandler.
 */

namespace Drupal\entity_test;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessHandler;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access handler for the test entity type.
 *
 * @see \Drupal\entity_test\Entity\EntityTest
 * @see \Drupal\entity_test\Entity\EntityTestBaseFieldDisplay
 * @see \Drupal\entity_test\Entity\EntityTestCache
 * @see \Drupal\entity_test\Entity\EntityTestMul
 * @see \Drupal\entity_test\Entity\EntityTestMulRev
 * @see \Drupal\entity_test\Entity\EntityTestRev
 * @see \Drupal\entity_test\Entity\EntityTestStringId
 */
class EntityTestAccessHandler extends EntityAccessHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation === 'view') {
      if ($langcode != LanguageInterface::LANGCODE_DEFAULT) {
        return user_access('view test entity translations', $account);
      }
      return user_access('view test entity', $account);
    }
    elseif (in_array($operation, array('update', 'delete'))) {
      return user_access('administer entity_test content', $account);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return user_access('administer entity_test content', $account);
  }

}
