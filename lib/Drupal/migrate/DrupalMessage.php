<?php

/**
 * @file
 * Contains \Drupal\migrate\DrupalMessage.
 */

namespace Drupal\migrate;

class DrupalMessage implements MigrateMessageInterface {

  function display($message, $type = 'status') {
    drupal_set_message($message, $type);
  }
}
