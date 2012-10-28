<?php

/**
 * @file
 * Definition of Drupal\Core\ExtensionHandler.
 */

namespace Drupal\Core;

use Drupal\Component\Graph\Graph;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\KeyValueStore\KeyValueFactory;
use Drupal\Core\ExtensionHandlerInterface;
use Symfony\Component\ClassLoader\UniversalClassLoader;

class ExtensionHandler implements ExtensionHandlerInterface {

  /**
   * Database connection object.
   */
  protected $connection;

  /**
   * Cache backend that stores system info.
   */
  protected $cache;

  /**
   * Cache backend for storing enabled modules.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $bootstrapCache;

  /**
   * @var \Symfony\Component\ClassLoader\UniversalClassLoader
   */
  protected $classLoader;

  /**
   * Keeps track internally of loaded files.
   */
  protected $loadedFiles;

  /**
   * Keeps track internally of extension filenames.
   */
  protected $filenames = array();

  /**
   * Keeps track internall of extension directories.
   */
  protected $directories = array();

  /**
   * Keeps track internally of enabled modules and themes.
   */
  protected $lists;

  /**
   * Array of enabled modules.
   */
  protected $moduleList;

  /**
   * Boolean indicating whether modules have been loaded.
   */
  protected $loaded = FALSE;

  /**
   * Keeps track internally of hook implementations.
   */
  protected $implementations;

  /**
   * Keeps track internally of hook info.
   */
  protected $hookInfo;

  /**
   * Keeps track internally of alter functions.
   */
  protected $alterFunctions;

  /**
   * Constructor.
   */
  public function __construct(KeyValueFactory $key_value, CacheBackendInterface $cache, CacheBackendInterface $bootstrapCache, UniversalClassLoader $loader) {
    $this->keyValue = $key_value;
    $this->cache = $cache;
    $this->bootstrapCache = $bootstrapCache;
    $this->classLoader = $loader;
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::getFilename().
   */
  public function getFilename($type, $name, $filename = NULL) {

    // Profiles are converted into modules in system_rebuild_module_data().
    // @todo Remove false-exposure of profiles as modules.
    $original_type = $type;
    if ($type == 'profile') {
      $type = 'module';
    }
    if (!isset($this->filenames[$type])) {
      $this->filenames[$type] = array();
    }

    if (!empty($filename) && file_exists($filename)) {
      $this->filenames[$type][$name] = $filename;
    }
    elseif (!isset($this->filenames[$type][$name])) {
      $file_list = $this->getFileListFromState($type);
      if ($file_list && isset($file_list[$name]) && file_exists(DRUPAL_ROOT . '/' . $file_list[$name])) {
        $this->filenames[$type][$name] = $file_list[$name];
      }
      // Fallback to searching the filesystem if the database could not find the
      // file or the file returned by the database is not found.
      if (!isset($this->filenames[$type][$name])) {
        // We have consistent directory naming: modules, themes...
        $dir = $type . 's';
        if ($type == 'theme_engine') {
          $dir = 'themes/engines';
          $extension = 'engine';
        }
        elseif ($type == 'theme') {
          $extension = 'info';
        }
        // Profiles are converted into modules in system_rebuild_module_data().
        // @todo Remove false-exposure of profiles as modules.
        elseif ($original_type == 'profile') {
          $dir = 'profiles';
          $extension = 'profile';
        }
        else {
          $extension = $type;
        }

        if (!isset($this->directories[$dir][$extension])) {
          $this->directories[$dir][$extension] = TRUE;
          if (!function_exists('drupal_system_listing')) {
            require_once DRUPAL_ROOT . '/core/includes/common.inc';
          }
          // Scan the appropriate directories for all files with the requested
          // extension, not just the file we are currently looking for. This
          // prevents unnecessary scans from being repeated when this function is
          // called more than once in the same page request.
          $matches = drupal_system_listing("/^" . DRUPAL_PHP_FUNCTION_PATTERN . "\.$extension$/", $dir, 'name', 0);
          foreach ($matches as $matched_name => $file) {
            $this->filenames[$type][$matched_name] = $file->uri;
          }
        }
      }
    }

    if (isset($this->filenames[$type][$name])) {
      return $this->filenames[$type][$name];
    }
  }

  /**
   * Retrieves the file list from the key/value store's 'state' collection.
   */
  protected function getFileListFromState($type) {
    $file_list = array();
    try {
      $file_list = $this->keyValue->get('state')->get('system.' . $type . '.files');
    }
    catch (Exception $e) {
      // The keyvalue service raised an exception because the backend might
      // be down. We have a fallback for this case so we hide the error
      // completely.
    }
    return $file_list;
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::load().
   */
  public function load($type, $name) {

    if (isset($this->loadedFiles[$type][$name])) {
      return TRUE;
    }

    $filename = $this->getFilename($type, $name);

    if ($filename) {
      include_once DRUPAL_ROOT . '/' . $filename;
      $this->loadedFiles[$type][$name] = TRUE;

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::loadAll().
   */
  public function loadAll($bootstrap = FALSE, $reset = FALSE, $loaded = FALSE) {
    if ($reset) {
      $this->loaded = $loaded;
    }
    if (isset($bootstrap) && !$this->loaded) {
      $type = $bootstrap ? 'bootstrap' : 'module_enabled';
      foreach ($this->getEnabledModules($type) as $module) {
        $this->load('module', $module);
      }
      // $has_run will be TRUE if $bootstrap is FALSE.
      $this->loaded = !$bootstrap;
    }
    return $this->loaded;
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::moduelList().
   */
  public function getEnabledModules($type = 'module_enabled', array $fixed_list = NULL, $reset = FALSE) {
    if ($reset) {
      $this->moduleList = NULL;
      // Do nothing if no $type and no $fixed_list have been passed.
      if (!isset($type) && !isset($fixed_list)) {
        return;
      }
    }

    // The list that will be be returned. Separate from $moduleList in order
    // to not duplicate the static cache of drupal_extension_handler()->systemList().
    $list = $this->moduleList;

    if (isset($fixed_list)) {
      $this->moduleList = array();
      foreach ($fixed_list as $name => $module) {
        $this->getFilename('module', $name, $module['filename']);
        $this->moduleList[$name] = $name;
      }
      $list = $this->moduleList;
    }
    elseif (!isset($this->moduleList)) {
      $list = $this->systemList($type);
    }
    return $list;
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::moduleListReset().
   */
  public function moduleListReset() {
    $this->moduleList = NULL;
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::systemList().
   */
  public function systemList($type) {
    // For bootstrap modules, attempt to fetch the list from cache if possible.
    // if not fetch only the required information to fire bootstrap hooks
    // in case we are going to serve the page from cache.
    if ($type == 'bootstrap') {
      if (isset($this->lists['bootstrap'])) {
        return $lists['bootstrap'];
      }
      if ($cached = $this->bootstrapCache->get('bootstrap_modules')) {
        $bootstrap_list = $cached->data;
      }
      else {
        $bootstrap_list = $this->keyValue->get('state')->get('system.module.bootstrap') ?: array();
        $this->bootstrapCache->set('bootstrap_modules', $bootstrap_list);
      }
      // To avoid a separate database lookup for the filepath, prime the
      // $filenames internal cache for bootstrap modules only.
      // The rest is stored separately to keep the bootstrap module cache small.
      $this->systemListWarm($bootstrap_list);
      // We only return the module names here since getEnabledModules() doesn't need
      // the filename itself.
      $lists['bootstrap'] = array_keys($bootstrap_list);
    }
    // Otherwise build the list for enabled modules and themes.
    elseif (!isset($lists['module_enabled'])) {
      if ($cached = $this->bootstrapCache->get('system_list')) {
        $lists = $cached->data;
      }
      else {
        $lists = array(
          'module_enabled' => array(),
          'theme' => array(),
          'filepaths' => array(),
        );
        // The module name (rather than the filename) is used as the fallback
        // weighting in order to guarantee consistent behavior across different
        // Drupal installations, which might have modules installed in different
        // locations in the file system. The ordering here must also be
        // consistent with the one used in module_implements().
        $enabled_modules = config('system.module')->get('enabled');
        $module_files = $this->keyValue->get('state')->get('system.module.files');
        foreach ($enabled_modules as $name => $weight) {
          // Build a list of all enabled modules.
          $lists['module_enabled'][$name] = $name;
          // Build a list of filenames so getFilename can use it.
          $lists['filepaths'][$name] = $module_files[$name];
        }

        // Build a list of themes.
        $enabled_themes = config('system.theme')->get('enabled');
        // @todo Themes include all themes, including disabled/uninstalled. This
        //   system.theme.data state will go away entirely as soon as themes have
        //   a proper installation status.
        // @see http://drupal.org/node/1067408
        $theme_data = $this->keyValue->get('state')->get('system.theme.data');
        if (empty($theme_data)) {
          // @todo: system_list() may be called from _drupal_bootstrap_code() and
          // module_load_all(), in which case system.module is not loaded yet.
          // Prevent a filesystem scan in self::load() and include it directly.
          // @see http://drupal.org/node/1067408
          if (!function_exists('drupal_system_listing')) {
            require_once DRUPAL_ROOT . '/core/includes/common.inc';
          }
          require_once DRUPAL_ROOT . '/core/modules/system/system.module';
          $theme_data = system_rebuild_theme_data();
        }
        foreach ($theme_data as $name => $theme) {
          $theme->status = (int) isset($enabled_themes[$name]);
          $lists['theme'][$name] = $theme;
          // Build a list of filenames so getFilename can use it.
          if (isset($enabled_themes[$name])) {
            $lists['filepaths'][$name] = $theme->filename;
          }
        }
        // @todo Move into list_themes(). Read info for a particular requested
        //   theme from state instead.
        foreach ($lists['theme'] as $key => $theme) {
          if (!empty($theme->info['base theme'])) {
            // Make a list of the theme's base themes.
            require_once DRUPAL_ROOT . '/core/includes/theme.inc';
            $lists['theme'][$key]->base_themes = drupal_find_base_themes($lists['theme'], $key);
            // Don't proceed if there was a problem with the root base theme.
            if (!current($lists['theme'][$key]->base_themes)) {
              continue;
            }
            // Determine the root base theme.
            $base_key = key($lists['theme'][$key]->base_themes);
            // Add to the list of sub-themes for each of the theme's base themes.
            foreach (array_keys($lists['theme'][$key]->base_themes) as $base_theme) {
              $lists['theme'][$base_theme]->sub_themes[$key] = $lists['theme'][$key]->info['name'];
            }
            // Add the base theme's theme engine info.
            $lists['theme'][$key]->info['engine'] = $lists['theme'][$base_key]->info['engine'];
          }
          else {
            // A plain theme is its own base theme.
            $base_key = $key;
          }
          // Set the theme engine prefix.
          $lists['theme'][$key]->prefix = ($lists['theme'][$key]->info['engine'] == 'theme') ? $base_key : $lists['theme'][$key]->info['engine'];
        }
        $this->bootstrapCache->set('system_list', $lists);
      }
      // To avoid a separate database lookup for the filepath, prime the
      // $filenames internal cache with all enabled modules and themes.
      $this->systemListWarm($lists['filepaths']);
    }

    return $lists[$type];
  }

  public function systemListWarm($list) {
    foreach ($list as $name => $filename) {
      $this->classLoader->registerNamespace('Drupal\\' . $name, DRUPAL_ROOT . '/' . dirname($filename) . '/lib');
      $this->getFilename('module', $name, $filename);
    }
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::systemListReset().
   */
  public function systemListReset() {
    $this->lists = NULL;
    drupal_static_reset('system_rebuild_module_data');
    drupal_static_reset('list_themes');
    $this->bootstrapCache->deleteMultiple(array('bootstrap_modules', 'system_list'));
    $this->cache->delete('system_info');
    // Remove last known theme data state.
    // This causes system_list() to call system_rebuild_theme_data() on its next
    // invocation. When enabling a module that implements hook_system_info_alter()
    // to inject a new (testing) theme or manipulate an existing theme, then that
    // will cause system_list_reset() to be called, but theme data is not
    // necessarily rebuilt afterwards.
    // @todo Obsolete with proper installation status for themes.
    $this->keyValue->get('state')->delete('system.theme.data');
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::buildModuleDependencies().
   */
  public function buildModuleDependencies($files) {
    foreach ($files as $filename => $file) {
      $graph[$file->name]['edges'] = array();
      if (isset($file->info['dependencies']) && is_array($file->info['dependencies'])) {
        foreach ($file->info['dependencies'] as $dependency) {
          $dependency_data = $this->parseDependency($dependency);
          $graph[$file->name]['edges'][$dependency_data['name']] = $dependency_data;
        }
      }
    }
    $graph_object = new Graph($graph);
    $graph = $graph_object->searchAndSort();
    foreach ($graph as $module => $data) {
      $files[$module]->required_by = isset($data['reverse_paths']) ? $data['reverse_paths'] : array();
      $files[$module]->requires = isset($data['paths']) ? $data['paths'] : array();
      $files[$module]->sort = $data['weight'];
    }
    return $files;
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::moduleExists().
   */
  public function moduleExists($module) {
    $list = $this->getEnabledModules();
    return isset($list[$module]);
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::loadAllIncludes().
   */
  public function loadAllIncludes($type, $name = NULL) {
    $modules = $this->getEnabledModules();
    foreach ($modules as $module) {
      module_load_include($type, $module, $name);
    }
  }


  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::moduleImplements().
   */
  public function moduleImplements($hook) {
    // Fetch implementations from cache.
    if (empty($this->implementations)) {
      $implementations = $this->bootstrapCache->get('module_implements');
      if ($implementations === FALSE) {
        $this->implementations = array();
      }
      else {
        $this->implementations = $implementations->data;
      }
    }

    if (!isset($this->implementations[$hook])) {
      // The hook is not cached, so ensure that whether or not it has
      // implementations, that the cache is updated at the end of the request.
      $this->implementations['#write_cache'] = TRUE;
      $hookInfo = $this->moduleHookInfo();
      $this->implementations[$hook] = array();
      foreach ($this->getEnabledModules() as $module) {
        $include_file = isset($hookInfo[$hook]['group']) && module_load_include('inc', $module, $module . '.' . $hookInfo[$hook]['group']);
        // Since module_hook() may needlessly try to load the include file again,
        // function_exists() is used directly here.
        if (function_exists($module . '_' . $hook)) {
          $this->implementations[$hook][$module] = $include_file ? $hookInfo[$hook]['group'] : FALSE;
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
   * Implements Drupal\Core\ExtensionHandlerInterface::cachedHookImplementations().
   */
  public function cachedHookImplementations() {
    if (empty($this->implementations)) {
      return array();
    }
    return $this->implementations;
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::moduleImplementsReset().
   */
  public function moduleImplementsReset() {
    // We maintain a persistent cache of hook implementations in addition to the
    // static cache to avoid looping through every module and every hook on each
    // request. Benchmarks show that the benefit of this caching outweighs the
    // additional database hit even when using the default database caching
    // backend and only a small number of modules are enabled. The cost of the
    // $this->bootstrapCache->get() is more or less constant and reduced further when
    // non-database caching backends are used, so there will be more significant
    // gains when a large number of modules are installed or hooks invoked, since
    // this can quickly lead to module_hook() being called several thousand times
    // per request.
    $this->implementations = NULL;
    $this->bootstrapCache->set('module_implements', array());
    $this->hookInfo = NULL;
    $this->alterFunctions = NULL;
    $this->bootstrapCache->delete('hook_info');
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::moduleHookInfo().
   */
  public function moduleHookInfo() {
    // When this function is indirectly invoked from bootstrap_invoke_all() prior
    // to all modules being loaded, we do not want to cache an incomplete
    // hook_hookInfo() result, so instead return an empty array. This requires
    // bootstrap hook implementations to reside in the .module file, which is
    // optimal for performance anyway.
    if (!$this->loadAll(NULL)) {
      return array();
    }

    if (!isset($this->hookInfo)) {
      $this->hookInfo = array();
      $cache = $this->bootstrapCache->get('hook_info');
      if ($cache === FALSE) {
        // Rebuild the cache and save it.
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
        $this->bootstrapCache->set('hook_info', $this->hookInfo);
      }
      else {
        $this->hookInfo = $cache->data;
      }
    }

    return $this->hookInfo;
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::moduleImplementsWriteCache().
   */
  public function writeModuleImplementationsCache() {
    if (isset($this->implementations['#write_cache'])) {
      unset($this->implementations['#write_cache']);
      $this->bootstrapCache->set('module_implements', $this->implementations);
    }
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::moduleInvokeAll().
   */
  public function moduleInvokeAll($hook, $args) {
    $return = array();
    foreach ($this->moduleImplements($hook) as $module) {
      $function = $module . '_' . $hook;
      if (function_exists($function)) {
        $result = call_user_func_array($function, $args);
        if (isset($result) && is_array($result)) {
          $return = array_merge_recursive($return, $result);
        }
        elseif (isset($result)) {
          $return[] = $result;
        }
      }
    }

    return $return;
  }

  /**
   * Implements Drupal\Core\ExtensionHandlerInterface::alter().
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL) {
    // Most of the time, $type is passed as a string, so for performance,
    // normalize it to that. When passed as an array, usually the first item in
    // the array is a generic type, and additional items in the array are more
    // specific variants of it, as in the case of array('form', 'form_FORM_ID').
    if (is_array($type)) {
      $cid = implode(',', $type);
      $extra_types = $type;
      $type = array_shift($extra_types);
      // Allow if statements in this function to use the faster isset() rather
      // than !empty() both when $type is passed as a string, or as an array with
      // one item.
      if (empty($extra_types)) {
        unset($extra_types);
      }
    }
    else {
      $cid = $type;
    }

    // Some alter hooks are invoked many times per page request, so statically
    // cache the list of functions to call, and on subsequent calls, iterate
    // through them quickly.
    if (!isset($this->alterFunctions[$cid])) {
      $this->alterFunctions[$cid] = array();
      $hook = $type . '_alter';
      $modules = $this->moduleImplements($hook);
      if (!isset($extra_types)) {
        // For the more common case of a single hook, we do not need to call
        // function_exists(), since $this->moduleImplements() returns only modules with
        // implementations.
        foreach ($modules as $module) {
          $this->alterFunctions[$cid][] = $module . '_' . $hook;
        }
      }
      else {
        // For multiple hooks, we need $modules to contain every module that
        // implements at least one of them.
        $extra_modules = array();
        foreach ($extra_types as $extra_type) {
          $extra_modules = array_merge($extra_modules, $this->moduleImplements($extra_type . '_alter'));
        }
        // If any modules implement one of the extra hooks that do not implement
        // the primary hook, we need to add them to the $modules array in their
        // appropriate order. $this->moduleImplements() can only return ordered
        // implementations of a single hook. To get the ordered implementations
        // of multiple hooks, we mimic the $this->moduleImplements() logic of first
        // ordering by $this->getEnabledModules(), and then calling
        // $this->alter('module_implements').
        if (array_diff($extra_modules, $modules)) {
          // Merge the arrays and order by getEnabledModules().
          $modules = array_intersect($this->getEnabledModules(), array_merge($modules, $extra_modules));
          // Since $this->moduleImplements() already took care of loading the necessary
          // include files, we can safely pass FALSE for the array values.
          $implementations = array_fill_keys($modules, FALSE);
          // Let modules adjust the order solely based on the primary hook. This
          // ensures the same module order regardless of whether this if block
          // runs. Calling $this->alter() recursively in this way does not result
          // in an infinite loop, because this call is for a single $type, so we
          // won't end up in this code block again.
          $this->alter('module_implements', $implementations, $hook);
          $modules = array_keys($implementations);
        }
        foreach ($modules as $module) {
          // Since $modules is a merged array, for any given module, we do not
          // know whether it has any particular implementation, so we need a
          // function_exists().
          $function = $module . '_' . $hook;
          if (function_exists($function)) {
            $this->alterFunctions[$cid][] = $function;
          }
          foreach ($extra_types as $extra_type) {
            $function = $module . '_' . $extra_type . '_alter';
            if (function_exists($function)) {
              $this->alterFunctions[$cid][] = $function;
            }
          }
        }
      }
      // Allow the theme to alter variables after the theme system has been
      // initialized.
      global $theme, $base_theme_info;
      if (isset($theme)) {
        $theme_keys = array();
        foreach ($base_theme_info as $base) {
          $theme_keys[] = $base->name;
        }
        $theme_keys[] = $theme;
        foreach ($theme_keys as $theme_key) {
          $function = $theme_key . '_' . $hook;
          if (function_exists($function)) {
            $this->alterFunctions[$cid][] = $function;
          }
          if (isset($extra_types)) {
            foreach ($extra_types as $extra_type) {
              $function = $theme_key . '_' . $extra_type . '_alter';
              if (function_exists($function)) {
                $this->alterFunctions[$cid][] = $function;
              }
            }
          }
        }
      }
    }

    foreach ($this->alterFunctions[$cid] as $function) {
      $function($data, $context1, $context2);
    }
  }

  /**
   * Parses a dependency for comparison by drupal_check_incompatibility().
   *
   * @param $dependency
   *   A dependency string, for example 'foo (>=8.x-4.5-beta5, 3.x)'.
   *
   * @return
   *   An associative array with three keys:
   *   - 'name' includes the name of the thing to depend on (e.g. 'foo').
   *   - 'original_version' contains the original version string (which can be
   *     used in the UI for reporting incompatibilities).
   *   - 'versions' is a list of associative arrays, each containing the keys
   *     'op' and 'version'. 'op' can be one of: '=', '==', '!=', '<>', '<',
   *     '<=', '>', or '>='. 'version' is one piece like '4.5-beta3'.
   *   Callers should pass this structure to drupal_check_incompatibility().
   *
   * @see drupal_check_incompatibility()
   */
  protected function parseDependency($dependency) {
    // We use named subpatterns and support every op that version_compare
    // supports. Also, op is optional and defaults to equals.
    $p_op = '(?P<operation>!=|==|=|<|<=|>|>=|<>)?';
    // Core version is always optional: 8.x-2.x and 2.x is treated the same.
    $p_core = '(?:' . preg_quote(DRUPAL_CORE_COMPATIBILITY) . '-)?';
    $p_major = '(?P<major>\d+)';
    // By setting the minor version to x, branches can be matched.
    $p_minor = '(?P<minor>(?:\d+|x)(?:-[A-Za-z]+\d+)?)';
    $value = array();
    $parts = explode('(', $dependency, 2);
    $value['name'] = trim($parts[0]);
    if (isset($parts[1])) {
      $value['original_version'] = ' (' . $parts[1];
      foreach (explode(',', $parts[1]) as $version) {
        if (preg_match("/^\s*$p_op\s*$p_core$p_major\.$p_minor/", $version, $matches)) {
          $op = !empty($matches['operation']) ? $matches['operation'] : '=';
          if ($matches['minor'] == 'x') {
            // Drupal considers "2.x" to mean any version that begins with
            // "2" (e.g. 2.0, 2.9 are all "2.x"). PHP's version_compare(),
            // on the other hand, treats "x" as a string; so to
            // version_compare(), "2.x" is considered less than 2.0. This
            // means that >=2.x and <2.x are handled by version_compare()
            // as we need, but > and <= are not.
            if ($op == '>' || $op == '<=') {
              $matches['major']++;
            }
            // Equivalence can be checked by adding two restrictions.
            if ($op == '=' || $op == '==') {
              $value['versions'][] = array('op' => '<', 'version' => ($matches['major'] + 1) . '.x');
              $op = '>=';
            }
          }
          $value['versions'][] = array('op' => $op, 'version' => $matches['major'] . '.' . $matches['minor']);
        }
      }
    }
    return $value;
  }
}
