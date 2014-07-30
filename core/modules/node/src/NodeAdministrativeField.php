<?php
/**
 * @file
 * Contains \Drupal\node\NodeAdministrativeField.
 */


namespace Drupal\node;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;


/**
 * Status field of node.
 */
class NodeAdministrativeField extends FieldItemList {

  /**
   * {@inheritdoc}
   */
  public function defaultAccess($operation = 'view', AccountInterface $account = NULL) {

    if (!isset($account)) {
      $account = \Drupal::currentUser();
    }

    if ($operation == 'view') {
      // If access to the node itself is granted the fields value may be viewed.
      return TRUE;
    }
    // Edit operation: only grant access to administrative users.
    if ($account->hasPermission('bypass node access') || $account->hasPermission('administer nodes')) {
      return TRUE;
    }
    return FALSE;
  }
}
