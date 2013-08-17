<?php

/**
 * @file
 * Contains \Drupal\Component\Discovery\YamlDiscovery.
 */

namespace Drupal\Component\Discovery;

use Symfony\Component\Yaml\Parser;

/**
 * Provides discovery for YAML files within a given set of directories.
 */
class YamlDiscovery implements DiscoverableInterface {

  /**
   * The base filename to look for in each directory.
   *
   * @var string
   */
  protected $name;

  /**
   * An array of directories to scan, keyed by the provider.
   *
   * @var array
   */
  protected $directories = array();

  /**
   * The symfony YAML parser.
   *
   * @var \Symfony\Component\Yaml\Parser
   */
  protected $parser;

  /**
   * Constructs a YamlDiscovery object.
   *
   * @param string $name
   *   The
   * @param array $directories
   *   An array of directories to scan, keyed by the provider.
   */
  public function __construct($name, array $directories) {
    $this->name = $name;
    $this->directories = $directories;
  }

  /**
   * {@inheritdoc}
   */
  public function findAll() {
    $all = array();
    $parser = $this->parser();

    foreach ($this->findFiles() as $key => $file) {
      $all[$key] = $parser->parse(file_get_contents($file));
    }

    return $all;
  }

  /**
   * Returns the YAML parse.
   *
   * @return \Symfony\Component\Yaml\Parser
   *   The symfony YAML parser.
   */
  protected function parser() {
    if (!isset($this->parser)) {
      $this->parser = new Parser();
    }
    return $this->parser;
  }

  /**
   * Returns an array of file paths.
   *
   * @return array
   */
  protected function findFiles() {
    $files = array();
    foreach ($this->directories as $provider => $directory) {
      $file = $directory . '/' . $provider . '.' . $this->name . '.yml';
      if (file_exists($file)) {
        $files[$provider] = $file;
      }
    }
    return $files;
  }

}

