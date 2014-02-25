<?php

/**
 * @file
 * Contains \Drupal\Component\Plugin\Discovery\DiscoveryCachedBase.
 */

namespace Drupal\Component\Plugin\Discovery;

/**
 * Contains a base class for statically cached discovery.
 *
 * @todo Replace with a trait.
 */
abstract class DiscoveryCachedBase extends DiscoveryBase {

  /**
   * Cached definitions array.
   *
   * @var array
   */
  protected $definitions;

  /**
   * {@inheritdoc}
   */
  public function getDefinition($plugin_id, $exception_on_invalid = TRUE) {
    // Fetch definitions if they're not loaded yet.
    if (!isset($this->definitions)) {
      $this->getDefinitions();
    }

    return $this->doGetDefinition($this->definitions, $plugin_id, $exception_on_invalid);
  }

}
