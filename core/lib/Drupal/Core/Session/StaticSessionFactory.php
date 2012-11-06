<?php

/**
 * @file
 * Defines Drupal\Core\Session\StaticSessionFactory.
 */

namespace Drupal\Core\Session;

use Drupal\Core\Session\Handler\DatabaseSessionHandler;
use Drupal\Core\Session\Proxy\CookieOverrideProxy;
use Drupal\Core\Session\Storage\DrupalSessionStorage;

/**
 * Static session factory that will allow us to use the session as a synthetic
 * service into the DIC and avoid dual instanciation due to bootstrap container
 * definition.
 *
 * @todo Remove this if session can be initialized after kernel.
 */
class StaticSessionFactory {

  /**
   * @var Drupal\Core\Session\Session
   */
  static private $session;

  /**
   * Get global session service.
   *
   * @return \Drupal\Core\Session\Session
   *   Session service.
   */
  static public function getSession() {
    if (null === self::$session) {

      $handler = new CookieOverrideProxy(new DatabaseSessionHandler());
      $storage = new DrupalSessionStorage(array(), $handler);

      self::$session = new Session($storage);

      /*
       * @todo Keeping this code as container registration code that should be
       * used instead when the bootstrap container disapears
       * 
      // Register the session service.
      $container->register('session.storage.backend', 'Drupal\Core\Session\Handler\DatabaseSessionHandler');
      $container->register('session.storage.proxy', 'Drupal\Core\Session\Proxy\CookieOverrideProxy')
        ->addArgument(new Reference('session.storage.backend'));
      $container->setParameter('session.storage.options', array());
      $container->register('session.storage', 'Drupal\Core\Session\Storage\DrupalSessionStorage')
        ->addArgument('%session.storage.options%')
        ->addArgument(new Reference('session.storage.proxy'));
      $container->register('session', 'Drupal\Core\Session\Session')
        ->addArgument(new Reference('session.storage'));
       */
    }

    return self::$session;
  }
}
