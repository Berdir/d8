<?php

/**
 * @file
 * Contains of \Drupal\Component\Yaml\Yaml.
 */

namespace Drupal\Component\Yaml;

/**
 * Factory class for Yaml.
 *
 * Determines which Yaml implementation to use, and uses that to parse
 * and encode Yaml.
 */
class Yaml implements YamlInterface {

  /**
   * Holds the Yaml implementation.
   *
   * @var Drupal\Component\Yaml\YamlInterface
   */
  protected $plugin;

  /**
   * Instantiates the correct Yaml object.
   */
  public function __construct() {
    $class = $this->determinePlugin();
    $this->plugin = new $class();
  }

  /**
   * {@inheritdoc}
   */
  public function parse($input) {
    return $this->plugin->parse($input);
  }

  /**
   * {@inheritdoc}
   */
  public function dump($value) {
    return $this->plugin->dump($value);
  }

  /**
   * Determines the optimal implementation to use for encoding and parsing Yaml.
   *
   * The selection is made based on the enabled PHP extensions with the
   * most performant available option chosen.
   *
   * @return string
   *  The class name for the optimal Yaml implementation.
   */
  protected function determinePlugin() {
    static $plugin;
    if (!empty($plugin)) {
      return $plugin;
    }

    // Fallback to the Symfony implementation.
    $plugin = 'Drupal\Component\Yaml\Symfony';

    // Is the PECL Yaml extension installed
    if (function_exists('yaml_emit')) {
      $plugin = 'Drupal\Component\Yaml\Pecl';
    }
    return $plugin;
  }
}
