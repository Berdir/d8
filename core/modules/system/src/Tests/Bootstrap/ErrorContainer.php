<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Bootstrap\ErrorContainer.
 */

namespace Drupal\system\Tests\Bootstrap;

use Drupal\Core\DependencyInjection\Container;

/**
 * Container base class which triggers an error.
 */
class ErrorContainer extends Container {

  /**
   * {@inheritdoc}
   */
  public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE) {
    trigger_error('Fatal error', E_USER_ERROR);
  }

}
