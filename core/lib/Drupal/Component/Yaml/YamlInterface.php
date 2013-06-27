<?php

/**
 * @file
 * Contains \Drupal\Component\Yaml\YamlInterface.
 */

namespace Drupal\Component\Yaml;

/**
 * Interface that defines a Yaml backend.
 */
interface YamlInterface {

  /**
   * Parses a Yaml string to PHP value.
   *
   * If $input is empty this should return NULL and not produce a PHP warning.
   *
   * @param string $input
   *   Yaml string to parse.
   *
   * @return mixed
   *   A PHP value.
   */
  public function parse($input);

  /**
   * Dumps a PHP value to Yaml string.
   *
   * @param mixed $input
   *   The PHP value.
   *
   * @return string
   *   Yaml string.
   */
  public function dump($input);

}
