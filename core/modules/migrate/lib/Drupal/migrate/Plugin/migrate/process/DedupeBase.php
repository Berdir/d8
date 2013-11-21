<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\DedupeBase.
 */


namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\Row;

/**
 * This abstract base contains the dedupe logic.
 */
abstract class DedupeBase extends PluginBase implements MigrateProcessInterface {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    $i = 1;
    $postfix = isset($this->configuration['postfix']) ? $this->configuration['postfix'] : '';
    $new_value = $value;
    while ($this->exists($new_value)) {
      $new_value = $value . $postfix . $i++;
    }
    return $new_value;
  }

  /**
   * This is a query checking the existence of some value.
   *
   * @return bool
   */
  abstract protected function exists($value);

}
