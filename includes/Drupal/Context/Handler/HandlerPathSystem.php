<?php

namespace Drupal\Context\Handler;

use \Drupal\Context\ContextInterface;
use \Drupal\Context\Handler;

/**
 * System path Context Handler implementation.
 */
class HandlerPathSystem extends HandlerAbstract {

  public function getValue(array $args = array(), ContextInterface $context = null) {
    $system_path = '';

    $path = $context->getValue('path:raw');
    if (!empty($path)) {
      $system_path = drupal_get_normal_path($path);
    }
    else {
      $system_path = drupal_get_normal_path(variable_get('site_frontpage', 'node'));
    }

    return $system_path;
  }

}
