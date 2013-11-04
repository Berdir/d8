<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\DefaultValue.
 */


namespace Drupal\migrate\Plugin\migrate\process;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\ProcessInterface;
use Drupal\migrate\Row;


/**
 * This plugin sets missing values on the destination.
 *
 * @PluginId("default_value")
 */
class DefaultValue extends PluginBase implements ProcessInterface {

 /**
   * {@inheritdoc}
   */
  public function apply(Row $row, MigrateExecutable $migrate_executable) {
    foreach ($this->configuration as $key_string => $default_value) {
      $keys = explode(':', $key_string);
      if (!$row->hasDestinationPropertyDeep($keys)) {
        $row->setDestinationPropertyDeep($keys, $default_value);
      }
    }
  }

}
