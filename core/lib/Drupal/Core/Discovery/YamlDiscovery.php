<?php

/**
 * @file
 * Contains \Drupal\Core\Discovery\YamlDiscovery.
 */

namespace Drupal\Core\Discovery;

use Symfony\Component\Yaml\Parser;

/**
 */
class YamlDiscovery implements DiscoverableInterface {

  /**
   * @param string $name
   * @param array $directories
   */
  public function __construct($name, array $directories) {
    $this->name = $name;
    $this->directories = $directories;
  }

  /**
   * {@inheritdoc}
   */
  public function findAll() {
    $parser = $this->parser();
    $all = array_map(function($file) use ($parser) {
      return $parser->parse(file_get_contents($file));
    }, $this->findFiles());
    return $all;
  }

  /**
   * @return \Symfony\Component\Yaml\Parser
   */
  protected function parser() {
    if (!isset($this->parser)) {
      $this->parser = new Parser();
    }
    return $this->parser;
  }

  /**
   * @return array
   */
  protected function findFiles() {
    $files = array();
    foreach ($this->directories as $directory) {
      $file = $directory . '/' . basename($directory) . '.' . $this->name . '.yml';
      if (file_exists($file)) {
        $files[] = $file;
      }
    }
    return $files;
  }

}
