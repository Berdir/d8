<?php

/**
 * @file
 * Contains \Drupal\migrate\Annotation\ProcessPlugin.
 */

namespace Drupal\migrate\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation
 */
class MigrateProcessPlugin extends Plugin {

  /**
   * A unique identifier for the process plugin.
   *
   * @var string
   */
  public $id;

  /**
   * Whether the plugin handles multiples itself
   *
   * @var bool (optional)
   */
  public $handle_multiples = FALSE;
}
