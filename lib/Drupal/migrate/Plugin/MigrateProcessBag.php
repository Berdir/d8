<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\FieldMappingBag.
 */

namespace Drupal\migrate\Plugin;

use Drupal\Component\Plugin\DefaultPluginBag;
use Drupal\Component\Plugin\PluginManagerInterface;

class MigrateProcessBag extends DefaultPluginBag {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\migrate\Plugin\MigrateProcessInterface
   */
  public function &get($instance_id) {
    return parent::get($instance_id);
  }

  public function initializePlugin($instance_id) {
    $this->configurations[$instance_id] += array('id' => 'column_map');
    parent::initializePlugin($instance_id);
  }

}
