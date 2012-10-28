<?php

/**
 * @file
 * Definition of Drupal\Core\ExtensionHandler.
 */

namespace Drupal\Core;

use Drupal\Core\ExtensionHandler;
use Symfony\Component\ClassLoader\UniversalClassLoader;

/**
 * Class representing and ExtensionHandler that the installer can use.
 *
 * Overrides all methods in the ExtensionHandler class that attempt to talk to
 * a database.
 */
class InstallerExtensionHandler extends ExtensionHandler {

  /**
   * Overrides Drupal\Core\ExtensionHandler::construct().
   */
  public function __construct(UniversalClassLoader $loader) {
    $this->classLoader = $loader;
  }

  /**
   * Overrides Drupal\Core\ExtensionHandler::getFileListFromState().
   */
  protected function getFileListFromState($type) {
    return array();
  }

  /**
   * Overrides Drupal\Core\ExtensionHandler::systemList().
   */
  public function systemList($type) {
    return array();
  }

  /**
   * Overrides Drupal\Core\ExtensionHandler::systemListReset().
   */
  public function systemListReset() {
  }

  /**
   * Overrides Drupal\Core\ExtensionHandler::moduleImplements().
   */
  public function moduleImplements($hook) {
    // Fetch implementations from cache.
    if (empty($this->implementations)) {
      $this->implementations = array();
    }
    if (!isset($this->implementations[$hook])) {
      // The hook is not cached, so ensure that whether or not it has
      // implementations, that the cache is updated at the end of the request.
      $this->implementations['#write_cache'] = TRUE;
      $hook_info = $this->moduleHookInfo();
      $this->implementations[$hook] = array();
      $enabled = $this->getEnabledModules();
      foreach ($enabled as $module) {
        $include_file = isset($hook_info[$hook]['group']) && module_load_include('inc', $module, $module . '.' . $hook_info[$hook]['group']);
        // Since module_hook() may needlessly try to load the include file again,
        // function_exists() is used directly here.
        if (function_exists($module . '_' . $hook)) {
          $this->implementations[$hook][$module] = $include_file ? $hook_info[$hook]['group'] : FALSE;
        }
      }
      // Allow modules to change the weight of specific implementations but avoid
      // an infinite loop.
      if ($hook != 'module_implements_alter') {
        $this->alter('module_implements', $this->implementations[$hook], $hook);
      }
    }
    else {
      foreach ($this->implementations[$hook] as $module => $group) {
        // If this hook implementation is stored in a lazy-loaded file, so include
        // that file first.
        if ($group) {
          module_load_include('inc', $module, "$module.$group");
        }
        // It is possible that a module removed a hook implementation without the
        // implementations cache being rebuilt yet, so we check whether the
        // function exists on each request to avoid undefined function errors.
        // Since module_hook() may needlessly try to load the include file again,
        // function_exists() is used directly here.
        if (!function_exists($module . '_' . $hook)) {
          // Clear out the stale implementation from the cache and force a cache
          // refresh to forget about no longer existing hook implementations.
          unset($this->implementations[$hook][$module]);
          $this->implementations['#write_cache'] = TRUE;
        }
      }
    }

    return array_keys($this->implementations[$hook]);
  }

  /**
   * Overrides Drupal\Core\ExtensionHandler::moduleImplementsReset().
   */
  public function moduleImplementsReset() {
    // We maintain a persistent cache of hook implementations in addition to the
    // static cache to avoid looping through every module and every hook on each
    // request. Benchmarks show that the benefit of this caching outweighs the
    // additional database hit even when using the default database caching
    // backend and only a small number of modules are enabled. The cost of the
    // cache('bootstrap')->get() is more or less constant and reduced further when
    // non-database caching backends are used, so there will be more significant
    // gains when a large number of modules are installed or hooks invoked, since
    // this can quickly lead to module_hook() being called several thousand times
    // per request.
    $this->implementations = NULL;
    $this->hookInfo = NULL;
    $this->alterFunctions = NULL;
  }

  /**
   * Overrides Drupal\Core\ExtensionHandler::moduleHookInfo().
   */
  public function moduleHookInfo() {
    // When this function is indirectly invoked from bootstrap_invoke_all() prior
    // to all modules being loaded, we do not want to cache an incomplete
    // hook_hook_info() result, so instead return an empty array. This requires
    // bootstrap hook implementations to reside in the .module file, which is
    // optimal for performance anyway.
    if (!$this->loadAll(NULL)) {
      return array();
    }

    if (!isset($this->hookInfo)) {
      $this->hookInfo = array();
      // We can't use $this->moduleInvokeAll() here or it would cause an infinite
      // loop.
      foreach ($this->getEnabledModules() as $module) {
        $function = $module . '_hook_info';
        if (function_exists($function)) {
          $result = $function();
          if (isset($result) && is_array($result)) {
            $this->hookInfo = array_merge_recursive($this->hookInfo, $result);
          }
        }
      }
      // We can't use $this->alter() for the same reason as above.
      foreach ($this->getEnabledModules() as $module) {
        $function = $module . '_hook_info_alter';
        if (function_exists($function)) {
          $function($this->hookInfo);
        }
      }
    }
    return $this->hookInfo;
  }

  /**
   * Overrides Drupal\Core\ExtensionHandler::moduleImplementsWriteCache().
   */
  public function writeModuleImplementationsCache() {
  }
}
