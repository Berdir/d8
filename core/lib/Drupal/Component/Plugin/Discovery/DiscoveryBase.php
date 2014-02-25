<?php

/**
 * @file
 * Contains \Drupal\Component\Plugin\Discovery\DiscoveryBase.
 */

namespace Drupal\Component\Plugin\Discovery;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;

/**
 * Contains a base class for discovery.
 *
 * @todo Replace with a trait.
 */
abstract class DiscoveryBase implements DiscoveryInterface {

  /**
   * {@inheritdoc}
   */
  public function getDefinition($plugin_id, $exception_on_invalid = TRUE) {
    $definitions = $this->getDefinitions();
    return $this->doGetDefinition($definitions, $plugin_id, $exception_on_invalid);
  }

  /**
   * Gets a specific plugin definition.
   *
   * @param array $definitions
   *   An array of the available plugin definitions.
   * @param string $plugin_id
   *   A plugin id.
   * @param bool $exception_on_invalid
   *   (optional) If TRUE, an invalid plugin ID will throw an exception.
   *   Defaults to FALSE.
   *
   * @return array|null
   *   A plugin definition, or NULL if the plugin ID is invalid and
   *   $exception_on_invalid is TRUE.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if $plugin_id is invalid and $exception_on_invalid is TRUE.
   */
  protected function doGetDefinition(array $definitions, $plugin_id, $exception_on_invalid) {
    // Avoid using a ternary that would create a copy of the array.
    if (isset($definitions[$plugin_id])) {
      return $definitions[$plugin_id];
    }
    elseif (!$exception_on_invalid) {
      return NULL;
    }

    throw new PluginNotFoundException($plugin_id, sprintf('The "%s" plugin does not exist.', $plugin_id));
  }

  /**
   * {@inheritdoc}
   */
  public function hasDefinition($plugin_id) {
    return (bool) $this->getDefinition($plugin_id, FALSE);
  }

}
