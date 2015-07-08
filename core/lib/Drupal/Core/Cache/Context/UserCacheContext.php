<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\Context\UserCacheContext.
 */

namespace Drupal\Core\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the UserCacheContext service, for "per user" caching.
 *
 * Cache context ID: 'user'.
 */
class UserCacheContext implements CacheContextInterface {

  /**
   * Constructs a new UserCacheContext service.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  public function __construct(AccountInterface $user) {
    $this->user = $user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('User');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return "u." . $this->user->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
