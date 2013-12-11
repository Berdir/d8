<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\ProcessPluginBase.
 */

namespace Drupal\migrate;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\Plugin\MigrateProcessInterface;

/**
 * The base class for all process plugins.
 */
abstract class ProcessPluginBase extends PluginBase implements MigrateProcessInterface {

  public function multiple() {
    return FALSE;
  }
}
