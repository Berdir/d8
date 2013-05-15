<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\DefaultPluginManager
 */

namespace Drupal\Core\Plugin;

use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;

/**
 * Base class for plugin managers.
 */
class DefaultPluginManager extends PluginManagerBase implements PluginManagerInterface {

  /**
   * The object that discovers plugins managed by this manager.
   *
   * @var \Drupal\Component\Plugin\Discovery\DiscoveryInterface
   */
  protected $discovery;

  /**
   * The object that instantiates plugins managed by this manager.
   *
   * @var \Drupal\Component\Plugin\Factory\FactoryInterface
   */
  protected $factory;

  /**
   * The object that returns the preconfigured plugin instance appropriate for
   * a particular runtime condition.
   *
   * @var \Drupal\Component\Plugin\Mapper\MapperInterface
   */
  protected $mapper;

  /**
   * A set of defaults to be referenced by $this->processDefinition() if
   * additional processing of plugins is necessary or helpful for development
   * purposes.
   *
   * @var array
   */
  protected $defaults = array();

  /**
   * Cached definitions array.
   *
   * @var array
   */
  protected $definitions;

  /**
   * Cache backend instance.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface $cache
   */
  protected $cache;

  /**
   * Provided cache key prefix.
   *
   * @var string
   */
  protected $cacheKeyPrefix;

  /**
   * Actually used cache key with the language code appended.
   *
   * @var string
   */
  protected $cacheKey;

  /**
   * Name of the alter hook if one should be invoked.
   *
   * @var string
   */
  protected $alterHook;

  /**
   * The plugin's subdirectory, for example views/filter.
   *
   * @var string
   */
  protected $subdir;

  /**
   * The module handler to invoke the alter hook.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\Core\Plugin\DrupalPluginManagerBase object.
   *
   * Provides a Drupal ready plugin manager base class that implements caching
   * appropriately and simplifies developer experience for creating new plugin
   * managers.
   *
   * @param string $subdir
   *   The plugin's subdirectory, for example views/filter.
   * @param array $namespaces
   *   An array of paths keyed by it's corresponding namespaces.
   */
  public function __construct($subdir, array $namespaces) {
    $this->subdir = $subdir;
    $this->discovery = new AnnotatedClassDiscovery($subdir, $namespaces);
    $this->discovery = new DerivativeDiscoveryDecorator($this->discovery);

    $this->factory = new DefaultFactory($this);
  }

  /**
   * Sets the cache backend that should be used.
   *
   * Plugin definitions are cached used the cache backend if one is provided.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend instance to use.
   * @param type $cache_key_prefix
   *   Cache key prefix to use, the language code will be appended automatically.
   */
  public function setCache(CacheBackendInterface $cache, $cache_key_prefix) {
    $this->cache = $cache;
    $this->cacheKeyPrefix = $cache_key_prefix;
    $this->cacheKey = $cache_key_prefix . ':' . language(LANGUAGE_TYPE_INTERFACE)->langcode;
  }

  /**
   * Set the alter hook name that should be used if needed.
   *
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler to invoke the alter hook with.
   * @param string $alter_hook
   *   (optional) Name of the alter hook. Defaults to $owner_$type if not given.
   */
  public function setAlterHook(ModuleHandler $module_handler, $alter_hook = NULL) {
    $this->moduleHandler = $module_handler;
    $this->alterHook = $alter_hook ? $alter_hook : strtolower($this->subdir);
  }

  /**
   * Implements Drupal\Component\Plugin\PluginManagerInterface::createInstance().
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    return $this->factory->createInstance($plugin_id, $configuration);
  }

  /**
   * Implements Drupal\Component\Plugin\PluginManagerInterface::getInstance().
   */
  public function getInstance(array $options) {
    if (!empty($this->mapper)) {
      return $this->mapper->getInstance($options);
    }
  }

  /**
   * Performs extra processing on plugin definitions.
   *
   * By default we add defaults for the type to the definition. If a type has
   * additional processing logic they can do that by replacing or extending the
   * method.
   */
  public function processDefinition(&$definition, $plugin_id) {
    if ($this->defaults) {
      $definition = NestedArray::mergeDeep($this->defaults, $definition);
    }
  }

  /**
   * Implements Drupal\Component\Plugin\Discovery\DicoveryInterface::getDefinition().
   */
  public function getDefinition($plugin_id) {
    // Optimize for fast access to definitions if they are already in memory.
    if (isset($this->definitions)) {
      // Avoid using a ternary that would create a copy of the array.
      if (isset($this->definitions[$plugin_id])) {
        return $this->definitions[$plugin_id];
      }
      else {
        return;
      }
    }

    $definitions = $this->getDefinitions();
    // Avoid using a ternary that would create a copy of the array.
    if (isset($definitions[$plugin_id])) {
      return $definitions[$plugin_id];
    }
  }

  /**
   * Implements \Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface::getDefinitions().
   */
  public function getDefinitions() {
    $definitions = $this->getCachedDefinitions();
    if (!isset($definitions)) {
      $definitions = $this->discovery->getDefinitions();
      foreach ($definitions as $plugin_id => &$definition) {
        $this->processDefinition($definition, $plugin_id);
      }
      if ($this->alterHook) {
        $this->moduleHandler->alter($this->alterHook, $definitions);
      }
      $this->setCachedDefinitions($definitions);
    }
    return $definitions;
  }

  /**
   * Implements \Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface::clearCachedDefinitions().
   */
  public function clearCachedDefinitions() {
    if ($this->cache) {
      $cache_keys = array();
      foreach (language_list() as $langcode => $language) {
        $cache_keys[] = $this->cacheKeyPrefix . ':' .$langcode;
      }
      $this->cache->deleteMultiple($cache_keys);
    }
    $this->definitions = NULL;
  }

  /**
   * Returns the cached plugin definitions of the decorated discovery class.
   *
   * @return mixed
   *   On success this will return an array of plugin definitions. On failure
   *   this should return NULL, indicating to other methods that this has not
   *   yet been defined. Success with no values should return as an empty array
   *   and would actually be returned by the getDefinitions() method.
   */
  protected function getCachedDefinitions() {
    if (!isset($this->definitions) && $this->cache && $cache = $this->cache->get($this->cacheKey)) {
      $this->definitions = $cache->data;
    }
    return $this->definitions;
  }

  /**
   * Sets a cache of plugin definitions for the decorated discovery class.
   *
   * @param array $definitions
   *   List of definitions to store in cache.
   */
  protected function setCachedDefinitions($definitions) {
    if ($this->cache) {
      $this->cache->set($this->cacheKey, $definitions);
    }
    $this->definitions = $definitions;
  }

}
