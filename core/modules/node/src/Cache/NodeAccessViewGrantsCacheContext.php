<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\NodeAccessViewGrantsCacheContext.
 */

namespace Drupal\node\Cache;

use Drupal\Core\Cache\CacheContextInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the node access view grants cache context service.
 *
 * This allows for node access grants sensitive caching.
 *
 * node_query_node_access_alter().
 */
class NodeAccessViewGrantsCacheContext implements CacheContextInterface {

  /**
   * Const
   * @param \Drupal\Core\Session\AccountInterface $account
   */
  public function __construct(AccountInterface $account) {
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t("Content access view grants");
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    // If $account can bypass node access, or there are no node access modules,
    // or the operation is 'view' and the $account has a global view grant
    // (such as a view grant for node ID 0), we don't need to alter the query.
    if ($this->account->hasPermission('bypass node access')) {
      return '';
    }
    if (!count(\Drupal::moduleHandler()->getImplementations('node_grants'))) {
      return '';
    }
    if (node_access_view_all_nodes($this->account)) {
      return '';
    }

    $grants = node_access_grants('view', $this->account);
    $grants_context = '';
    foreach ($grants as $realm => $gids) {
      $grants_context = ';' . $realm . ':' . implode(',', $gids);
    }
    return $grants_context;
  }

}
