<?php

/**
 * @file
 * Contains \Drupal\Component\Yaml\Symfony.
 */

namespace Drupal\Component\Yaml;

use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

/**
 * Yaml implementation using the Symfony classes.
 */
class Symfony implements YamlInterface {

  /*
   * Number of spaces to indent sections.
   *
   * @var int
   */
  protected $indentation = 2;

  /**
   * A shared YAML dumper instance.
   *
   * @var \Symfony\Component\Yaml\Dumper
   */
  protected $dumper;

  /**
   * A shared YAML parser instance.
   *
   * @var \Symfony\Component\Yaml\Parser
   */
  protected $parser;

  /**
   * {@inheritdoc}
   */
  public function parse($input) {
    return $this->getParser()->parse($input);
  }

  /**
   * {@inheritdoc}
   */
  public function dump($value) {
    // The level where you switch to inline YAML is set to PHP_INT_MAX to ensure
    // this does not occur.
    return $this->getDumper()->dump($value, PHP_INT_MAX);
  }

  /**
   * Gets the YAML dumper instance.
   *
   * @return \Symfony\Component\Yaml\Dumper
   */
  protected function getDumper() {
    if (!isset($this->dumper)) {
      $this->dumper = new Dumper();
      // Set Yaml\Dumper's default indentation for nested nodes/collections to
      // 2 spaces for consistency with Drupal coding standards.
      $this->dumper->setIndentation($this->indentation);
    }
    return $this->dumper;
  }

  /**
   * Gets the YAML parser instance.
   *
   * @return \Symfony\Component\Yaml\Parser
   */
  protected function getParser() {
    if (!isset($this->parser)) {
      $this->parser = new Parser();
    }
    return $this->parser;
  }

}
