<?php

/**
 * @file
 * Definition of Drupal\Core\Config\ConfigFactory.
 */

namespace Drupal\Core\Config;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Defines the configuration object factory.
 *
 * The configuration object factory instantiates a Config object for each
 * configuration object name that is accessed and returns it to callers.
 *
 * @see Drupal\Core\Config\Config
 *
 * Each configuration object gets a storage controller object injected, which
 * is used for reading and writing the configuration data.
 *
 * @see Drupal\Core\Config\StorageInterface
 */
class ConfigFactory {

  /**
   * A storage controller instance for reading and writing configuration data.
   *
   * @var Drupal\Core\Config\StorageInterface
   */
  protected $storage;

  /**
   * An event dispatcher instance to use for configuration events.
   *
   * @var Symfony\Component\EventDispatcher\EventDispatcher
   */
  protected $eventDispatcher;

  protected $configs = array();

  /**
   * Constructs the Config factory.
   *
   * @param Drupal\Core\Config\StorageInterface $storage
   *   The storage controller object to use for reading and writing
   *   configuration data.
   * @param Symfony\Component\EventDispatcher\EventDispatcher
   *   An event dispatcher instance to use for configuration events.
   */
  public function __construct(StorageInterface $storage, EventDispatcher $event_dispatcher) {
    $this->storage = $storage;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Returns a configuration object for a given name.
   *
   * @param string $name
   *   The name of the configuration object to construct.
   *
   * @return Drupal\Core\Config\Config
   *   A configuration object with the given $name.
   */
  public function get($name) {
    global $conf;

    if (isset($this->configs[$name])) {
      return $this->configs[$name];
    }

    $this->configs[$name] = new Config($name, $this->storage, $this->eventDispatcher);
    return $this->configs[$name]->init();
  }

  function resetConfigs() {
    $this->configs = array();
  }
}
