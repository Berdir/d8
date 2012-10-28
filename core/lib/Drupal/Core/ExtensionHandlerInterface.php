<?php

/**
 * @file
 * Contains Drupal\Core\ExtensionHandlerInterface.
 */

namespace Drupal\Core;

interface ExtensionHandlerInterface {

  /**
   * Returns and optionally sets the filename for a system resource.
   *
   * The filename, whether provided, cached, or retrieved from the database, is
   * only returned if the file exists.
   *
   * This function plays a key role in allowing Drupal's resources (modules
   * and themes) to be located in different places depending on a site's
   * configuration. For example, a module 'foo' may legally be be located
   * in any of these three places:
   *
   * core/modules/foo/foo.module
   * modules/foo/foo.module
   * sites/example.com/modules/foo/foo.module
   *
   * Calling getFilename('module', 'foo') will give you one of the above,
   * depending on where the module is located.
   *
   * @param $type
   *   The type of the item (i.e. theme, theme_engine, module, profile).
   * @param $name
   *   The name of the item for which the filename is requested.
   * @param $filename
   *   The filename of the item if it is to be set explicitly rather
   *   than by consulting the database.
   *
   * @return
   *   The filename of the requested item.
   */
  public function getFilename($type, $name, $filename = NULL);

  /**
   * Includes a file with the provided type and name.
   *
   * This prevents including a theme, engine, module, etc., more than once.
   *
   * @param $type
   *   The type of item to load (i.e. theme, theme_engine, module).
   * @param $name
   *   The name of the item to load.
   *
   * @return
   *   TRUE if the item is loaded or has already been loaded.
   */
  public function load($type, $name);

  /**
   * Loads all the modules that have been enabled.
   *
   * @param $bootstrap
   *   Whether to load only the reduced set of modules loaded in "bootstrap mode"
   *   for cached pages. See bootstrap.inc.
   *
   * @param bool $reset
   *   (optional) Internal use only. Whether to reset the internal flag of
   *   whether modules have been loaded. If TRUE, all modules are (re)loaded in
   *   the same call. Used by the testing framework to override and persist a
   *   limited module list for the duration of a unit test (in which no module
   *   system exists).
   *
   * @return
   *   If $bootstrap is NULL, return a boolean indicating whether all modules
   *   have been loaded.
   */
  public function loadAll($bootstrap = FALSE, $reset = FALSE);

  /**
   * Returns a list of currently active modules.
   *
   * Acts as a wrapper around systemList(), returning either a list of all
   * enabled modules, or just modules needed for bootstrap.
   *
   * The returned module list is always based on systemList(). The only exception
   * to that is when a fixed list of modules has been passed in previously, in
   * which case systemList() is omitted and the fixed list is always returned in
   * subsequent calls until manually reverted via moduleListReset().
   *
   * @param string $type
   *   The type of list to return:
   *   - module_enabled: All enabled modules.
   *   - bootstrap: All enabled modules required for bootstrap.
   * @param array $fixed_list
   *   (optional) An array of module names to override the list of modules. This
   *   list will persist until the next call with a new $fixed_list passed in.
   *   Primarily intended for internal use (e.g., in install.php and update.php).
   *   Use moduleListReset() to undo the $fixed_list override.
   * @param bool $reset
   *   (optional) Whether to reset/remove the $fixed_list.
   *
   * @return array
   *   An associative array whose keys and values are the names of the modules in
   *   the list.
   *
   * @see self::moduleListReset()
   */
  public function getEnabledModules($type = 'module_enabled', array $fixed_list = NULL, $reset = FALSE);

  /**
   * Reverts an enforced fixed list of self::getEnabledModules().
   *
   * Subsequent calls to getEnabledModules() will no longer use a fixed list.
   */
  public function moduleListReset();

  /**
   * Builds a list of bootstrap modules and enabled modules and themes.
   *
   * @param $type
   *   The type of list to return:
   *   - module_enabled: All enabled modules.
   *   - bootstrap: All enabled modules required for bootstrap.
   *   - theme: All themes.
   *
   * @return
   *   An associative array of modules or themes, keyed by name. For $type
   *   'bootstrap' and 'module_enabled', the array values equal the keys.
   *   For $type 'theme', the array values are objects representing the
   *   respective database row, with the 'info' property already unserialized.
   *
   * @see self::getEnabledModules()
   * @see list_themes()
   */
  public function systemList($type);

  /**
   * Resets all systemList() caches.
   */
  public function systemListReset();

  /**
   * Determines which modules require and are required by each module.
   *
   * @param $files
   *   The array of filesystem objects used to rebuild the cache.
   *
   * @return
   *   The same array with the new keys for each module:
   *   - requires: An array with the keys being the modules that this module
   *     requires.
   *   - required_by: An array with the keys being the modules that will not work
   *     without this module.
   */
  public function buildModuleDependencies($files);

  /**
   * Determines whether a given module exists.
   *
   * @param $module
   *   The name of the module (without the .module extension).
   *
   * @return
   *   TRUE if the module is both installed and enabled.
   */
  public function moduleExists($module);

  /**
   * Loads an include file for each module enabled in the {system} table.
   */
  public function loadAllIncludes($type, $name = NULL);

  /**
   * Determines which modules are implementing a hook.
   *
   * @param $hook
   *   The name of the hook (e.g. "help" or "menu").
   *
   * @return
   *   An array with the names of the modules which are implementing this hook.
   *
   * @see module_implements_write_cache()
   */
  public function moduleImplements($hook);

  /**
   * Regenerates the stored list of hook implementations.
   */
  public function moduleImplementsReset();

  /**
   * Returns the hook implementation cache.
   */
  public function cachedHookImplementations();

  /**
   * Retrieves a list of hooks that are declared through hook_hook_info().
   *
   * @return
   *   An associative array whose keys are hook names and whose values are an
   *   associative array containing a group name. The structure of the array
   *   is the same as the return value of hook_hook_info().
   *
   * @see hook_hook_info()
   */
  public function moduleHookInfo();

  /**
   * Writes the hook implementation cache.
   *
   * @see $this->moduleImplements()
   */
  public function writeModuleImplementationsCache();

  /**
   * Invokes a hook in all enabled modules that implement it.
   *
   * @param $hook
   *   The name of the hook to invoke.
   * @param ...
   *   Arguments to pass to the hook.
   *
   * @return
   *   An array of return values of the hook implementations. If modules return
   *   arrays from their implementations, those are merged into one array.
   */
  public function moduleInvokeAll($hook, $args);

  /**
   * Passes alterable variables to specific hook_TYPE_alter() implementations.
   *
   * This dispatch function hands off the passed-in variables to type-specific
   * hook_TYPE_alter() implementations in modules. It ensures a consistent
   * interface for all altering operations.
   *
   * A maximum of 2 alterable arguments is supported. In case more arguments need
   * to be passed and alterable, modules provide additional variables assigned by
   * reference in the last $context argument:
   * @code
   *   $context = array(
   *     'alterable' => &$alterable,
   *     'unalterable' => $unalterable,
   *     'foo' => 'bar',
   *   );
   *   $this->alter('mymodule_data', $alterable1, $alterable2, $context);
   * @endcode
   *
   * Note that objects are always passed by reference in PHP5. If it is absolutely
   * required that no implementation alters a passed object in $context, then an
   * object needs to be cloned:
   * @code
   *   $context = array(
   *     'unalterable_object' => clone $object,
   *   );
   *   $this->alter('mymodule_data', $data, $context);
   * @endcode
   *
   * @param $type
   *   A string describing the type of the alterable $data. 'form', 'links',
   *   'node_content', and so on are several examples. Alternatively can be an
   *   array, in which case hook_TYPE_alter() is invoked for each value in the
   *   array, ordered first by module, and then for each module, in the order of
   *   values in $type. For example, when Form API is using $this->alter() to
   *   execute both hook_form_alter() and hook_form_FORM_ID_alter()
   *   implementations, it passes array('form', 'form_' . $form_id) for $type.
   * @param $data
   *   The variable that will be passed to hook_TYPE_alter() implementations to be
   *   altered. The type of this variable depends on the value of the $type
   *   argument. For example, when altering a 'form', $data will be a structured
   *   array. When altering a 'profile', $data will be an object.
   * @param $context1
   *   (optional) An additional variable that is passed by reference.
   * @param $context2
   *   (optional) An additional variable that is passed by reference. If more
   *   context needs to be provided to implementations, then this should be an
   *   associative array as described above.
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL);
}
