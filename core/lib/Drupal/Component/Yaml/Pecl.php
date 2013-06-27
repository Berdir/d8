<?php

/**
 * @file
 * Contains \Drupal\Component\Yaml\Pecl.
 */

namespace Drupal\Component\Yaml;

/**
 * Yaml implementation using the PECL extension.
 */
class Pecl implements YamlInterface {

  /**
   * Sets defaults according to Drupal coding standards.
   */
  public function __construct() {
    ini_set('yaml.output_indent', 2);
    // Don't break Yaml files at 80 characters.
    ini_set('yaml.output_width', -1);
  }

  /**
   * {@inheritdoc}
   */
  public function parse($input) {
    // Prevent PHP warning if $input is empty.
    if (empty($input)) {
      return NULL;
    }
    return yaml_parse($input);
  }

  /**
   * {@inheritdoc}
   */
  public function dump($value) {
    return yaml_emit($value);
  }

}
