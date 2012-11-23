<?php

/**
 * @file
 * Contains Drupal\user\UserBundle.
 */

namespace Drupal\user;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * User dependency injection container.
 */
class UserBundle extends Bundle {

  /**
   * Overrides Symfony\Component\HttpKernel\Bundle\Bundle::build().
   */
  public function build(ContainerBuilder $container) {
    $container
      ->register('user.data', 'Drupal\user\UserData')
      ->addArgument(new Reference('database'));
  }

}
