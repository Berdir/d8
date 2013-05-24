<?php

/**
 * @file
 * Contains \Drupal\Core\Condition\ConditionManager.
 */

namespace Drupal\Core\Condition;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Executable\ExecutableInterface;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\CacheDecorator;

/**
 * A plugin manager for condition plugins.
 */
class ConditionManager extends DefaultPluginManager implements ExecutableManagerInterface {

  /**
   * Constructs a ConditionManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths keyed by the corresponding namespace to look
   *   for plugin implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct('Condition', $namespaces);
    $this->alterHook = 'condition_info';
    $this->moduleHandler = $module_handler;
    $this->setCache($cache, $language_manager, 'condition');
  }

  /**
   * Override of Drupal\Component\Plugin\PluginManagerBase::createInstance().
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    $plugin = $this->factory->createInstance($plugin_id, $configuration);
    return $plugin->setExecutableManager($this);
  }

  /**
   * Implements Drupal\Core\Executable\ExecutableManagerInterface::execute().
   */
  public function execute(ExecutableInterface $condition) {
    $result = $condition->evaluate();
    return $condition->isNegated() ? !$result : $result;
  }

}
