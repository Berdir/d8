<?php

use Drupal\migrate\MigrateMessageInterface;

class DrushMigrateMessage implements MigrateMessageInterface {
  public function display($message, $type = 'status') {
    drush_log($message, $type);
  }
}
