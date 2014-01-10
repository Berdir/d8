<?php

/**
 * @file
 * Contains \Drupal\migrate\MigrateMessage.
 */

namespace Drupal\migrate;

/**
 * Defines a migrate message class.
 */
class MigrateMessage implements MigrateMessageInterface {

  /**
   * {@inheritdoc}
   */
  public function display($message, $type = 'status') {
    drupal_set_message($message, $type);
  }

}
