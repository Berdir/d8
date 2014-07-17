<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\ContainerFactoryPluginInterface.
 */

namespace Drupal\Core\Plugin;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines an interface for pulling plugin dependencies from the container.
 */
interface ContainerFactoryPluginInterface {

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param mixed $configuration
   *   Configuration for the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, $configuration, $plugin_id, $plugin_definition);

}
