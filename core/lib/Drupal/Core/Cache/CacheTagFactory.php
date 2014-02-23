<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\CacheTagFactory.
 */

namespace Drupal\Core\Cache;

use Drupal\Component\Utility\Settings;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Defines a cache tag factory.
 */
class CacheTagFactory extends ContainerAware {

  /**
   * The settings array.
   *
   * @var \Drupal\Component\Utility\Settings
   */
  protected $settings;

  /**
   * Constructs CacheTagFactory object.
   *
   * @param \Drupal\Component\Utility\Settings $settings
   *   The settings array.
   */
  function __construct(Settings $settings) {
    $this->settings = $settings;
  }

  /**
   * Instantiates a cache tag class.
   *
   * By default, this returns an instance of the
   * Drupal\Core\Cache\DatabaseTag class.
   *
   * @return \Drupal\Core\Cache\CacheTagInterface
   *   The cache tag instance.
   */
  public function get() {
    $cache_tag_service = $this->settings->get('cache_tag_service');

    if (isset($cache_tag_service)) {
      $service_name = $cache_tag_service;
    }
    else {
      $service_name = 'cache.tag.database';
    }

    return $this->container->get($service_name);
  }

}
