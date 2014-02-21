<?php

/**
 * @file
 * Contains \Drupal\migrate\Annotation\MigrateDestination.
 */


namespace Drupal\migrate\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a migration destination plugin annotation object.
 *
 * @Annotation
 */
class MigrateDestination extends Plugin {

  /**
   * A unique identifier for the process plugin.
   *
   * @var string
   */
  public $id;

  /**
   * A class to make the plugin derivative aware.
   *
   * @var string
   *
   * @see \Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator
   */
  public $derivative;

}
