<?php

/**
 * @file
 * Contains \Drupal\Core\Theme\Registry.
 */

namespace Drupal\Core\Theme;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DestructableInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Utility\ThemeRegistry;

/**
 * Defines the theme registry service.
 *
 * @todo Replace local $registry variables in methods with $this->registry.
 */
class Registry implements DestructableInterface {

  /**
   * The theme object representing the active theme for this registry.
   *
   * @var object
   */
  protected $theme;

  /**
   * An array of base theme objects.
   *
   * @var array
   */
  protected $baseThemes;

  /**
   * The name of the theme engine of $theme.
   *
   * @var string
   */
  protected $engine;

  /**
   * The complete theme registry.
   *
   * @var array
   *   An associative array keyed by theme hook names, whose values are
   *   associative arrays containing the aggregated hook definition:
   *   - type: The type of the extension the original theme hook originates
   *     from; e.g., 'module' for theme hook 'node' of Node module.
   *   - name: The name of the extension the original theme hook originates
   *     from; e.g., 'node' for theme hook 'node' of Node module.
   *   - theme path: The effective path_to_theme() during theme(), available as
   *     'directory' variable in templates.
   *     @todo Remove 'theme path', it's useless... or fix it: For theme
   *       functions, it should point to the respective theme. For templates,
   *       it should point to the directory that contains the template.
   *   - includes: (optional) An array of include files to load when the theme
   *     hook is executed by theme().
   *   - file: (optional) A filename to add to 'includes', either prefixed with
   *     the value of 'path', or the path of the extension implementing
   *     hook_theme().
   *   In case of a theme base hook, one of the following:
   *   - variables: An associative array whose keys are variable names and whose
   *     values are default values of the variables to use for this theme hook.
   *   - render element: A string denoting the name of the variable name, in
   *     which the render element for this theme hook is provided.
   *   In case of a theme template file:
   *   - path: The path to the template file to use. Defaults to the
   *     subdirectory 'templates' of the path of the extension implementing
   *     hook_theme(); e.g., 'core/modules/node/templates' for Node module.
   *   - template: The basename of the template file to use, without extension
   *     (as the extension is specific to the theme engine). The template file
   *     is in the directory defined by 'path'.
   *   - template_file: A full path and file name to a template file to use.
   *     Allows any extension to override the effective template file.
   *   - engine: The theme engine to use for the template file.
   *   In case of a theme function:
   *   - function: The function name to call to generate the output.
   *   For any registered theme hook, including theme hook suggestions:
   *   - preprocess: An array of theme variable preprocess callbacks to invoke
   *     before invoking final theme variable processors.
   *   - process: An array of theme variable process callbacks to invoke
   *     before invoking the actual theme function or template.
   */
  protected $registry;

  /**
   * The cache backend to use for the complete theme registry data.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The module handler to use to load modules.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The incomplete, runtime theme registry.
   *
   * @var \Drupal\Core\Utility\ThemeRegistry
   */
  protected $runtimeRegistry;


  /**
   * Constructs a \Drupal\Core\\Theme\Registry object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend interface to use for the complete theme registry data.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to use to load modules.
   * @param string $theme_name
   *   (optional) The name of the theme for which to construct the registry.
   */
  public function __construct(CacheBackendInterface $cache, ModuleHandlerInterface $module_handler, $theme_name = NULL) {
    $this->cache = $cache;
    $this->moduleHandler = $module_handler;
    $this->init($theme_name);
  }

  /**
   * Initializes a theme with a certain name.
   *
   * This function does to much magic, so it should be replaced by another
   * services which holds the current active theme information.
   *
   * @param string $theme_name
   *   (optional) The name of the theme for which to construct the registry.
   *
   * @global object $theme_info
   *   An object with (at least) the following information:
   *   - uri: The path to the theme.
   *   - owner: The name of the theme's base theme.
   *   - engine: The name of the theme engine to use.
   * @global array $base_theme_info
   *   (optional) An array of objects that represent the base themes of $theme,
   *   each having the same properties as $theme above, ordered by base theme
   *   hierarchy; i.e., the first element is the root of all themes.
   * @global string $theme_engine
   *   The name of the theme engine.
   *
   * @todo Remove global $theme_engine, it duplicates ->engine.
   * @todo Inject ThemeHandler, so as to remove all of these globals,
   *   in this way:
   *   - theme engines are dependencies of themes.
   *   - theme engines are actually treated *identically* to themes. (also:
   *     remove the /engines subdirectory)
   *   - base themes are dependencies of themes.
   *   As a result:
   *   - $theme->requires == should contain the full stack of dependencies,
   *     in the correct order.
   *
   * @todo Merge this into ::get(), so modules + tests can retrieve the registry
   *   for a particular $theme_name.
   */
  protected function init($theme_name = NULL) {
    // Unless instantiated for a specific theme, use globals.
    if (!isset($theme_name)) {
      // #1: The theme registry might get instantiated before the theme was
      // initialized. Cope with that.
      if (!isset($GLOBALS['theme_info'])) {
        unset($this->runtimeRegistry);
        unset($this->registry);
        drupal_theme_initialize();
      }
      // #2: The testing framework only cares for the global $theme variable at
      // this point. Cope with that.
      if ($GLOBALS['theme'] != $GLOBALS['theme_info']->name) {
        unset($this->runtimeRegistry);
        unset($this->registry);
        drupal_theme_initialize();
      }
      $this->theme = $GLOBALS['theme_info'];
      $this->baseThemes = $GLOBALS['base_theme_info'];
      $this->engine = $GLOBALS['theme_engine'];
    }
    // Instead of the global theme, a specific theme was requested.
    else {
      // @see drupal_theme_initialize()
      $themes = list_themes();
      $this->theme = $themes[$theme_name];

      // Find all base themes.
      $this->baseThemes = array();
      $ancestor = $theme_name;
      while ($ancestor && isset($themes[$ancestor]->base_theme)) {
        $ancestor = $themes[$ancestor]->base_theme;
        $this->baseThemes[] = $themes[$ancestor];
        if (!empty($themes[$ancestor]->owner)) {
          include_once DRUPAL_ROOT . '/' . $themes[$ancestor]->owner;
        }
      }
      $this->baseThemes = array_reverse($this->baseThemes);

      // @see _drupal_theme_initialize()
      if (isset($this->theme->engine)) {
        $this->engine = $this->theme->engine;
        include_once DRUPAL_ROOT . '/' . $this->theme->owner;
        if (function_exists($this->theme->engine . '_init')) {
          foreach ($this->baseThemes as $base) {
            call_user_func($this->theme->engine . '_init', $base);
          }
          call_user_func($this->theme->engine . '_init', $this->theme);
        }
      }
    }
  }

  /**
   * Returns the complete theme registry from cache or rebuilds it.
   *
   * @return array
   *   The complete theme registry data array.
   *
   * @see Registry::$registry
   *
   * @todo Add a lock for the (re)build operation. It's relatively quick, but
   *   not necessarily fast enough in case of many parallel requests.
   */
  public function get() {
    if (isset($this->registry)) {
      return $this->registry;
    }
    if ($cache = $this->cache->get('theme_registry:' . $this->theme->name)) {
      $this->registry = $cache->data;
    }
    else {
      $this->registry = $this->build();
      // Only persist it if all modules are loaded to ensure it is complete.
      if ($this->moduleHandler->isLoaded()) {
        $this->setCache();
      }
    }
    return $this->registry;
  }

  /**
   * Returns the incomplete, runtime theme registry.
   *
   * @return \Drupal\Core\Utility\ThemeRegistry
   *   A shared instance of the ThemeRegistry class, provides an ArrayObject
   *   that allows it to be accessed with array syntax and isset(), and is more
   *   lightweight than the full registry.
   */
  public function getRuntime() {
    if (!isset($this->runtimeRegistry)) {
      $this->runtimeRegistry = new ThemeRegistry('theme_registry:runtime:' . $this->theme->name, 'cache', array('theme_registry' => TRUE), $this->moduleHandler->isLoaded());
    }
    return $this->runtimeRegistry;
  }

  /**
   * Persists the theme registry in the cache backend.
   */
  protected function setCache() {
    $this->cache->set('theme_registry:' . $this->theme->name, $this->registry, CacheBackendInterface::CACHE_PERMANENT, array('theme_registry' => TRUE));
  }

  /**
   * Builds the theme registry from scratch.
   *
   * Theme hook definitions are collected in the following order:
   * - Modules
   * - Base theme engines
   * - Base themes
   * - Theme engine
   * - Theme
   *
   * All theme hook definitions are essentially just collated and merged in the
   * above order. However, various extension-specific default values and
   * customizations are required; e.g., to record the effective file path for
   * theme template. Therefore, this method first collects all extensions per
   * type, and then dispatches the processing for each extension to
   * processExtension().
   *
   * @see hook_theme()
   *
   * After completing the collection, modules are allowed to alter it. Lastly,
   * any derived and incomplete theme hook definitions that are hook suggestions
   * for base hooks (e.g., 'block__node' for the base hook 'block') need to be
   * determined based on the full registry and classified as 'base hook'.
   *
   * @see theme()
   * @see hook_theme_registry_alter()
   */
  protected function build() {
    // @todo Replace local $registry variables in methods with $this->registry.
    $registry = array();

    // hook_theme() implementations of modules are always the same.
    if ($cache = $this->cache->get('theme_registry:build:modules')) {
      $registry = $cache->data;
    }
    else {
      foreach ($this->moduleHandler->getImplementations('theme') as $module) {
        $this->processExtension($registry, 'module', $module, $module, drupal_get_path('module', $module));
      }
      // Only persist it if all modules are loaded to ensure it is complete.
      // @todo Get rid of these checks.
      if ($this->moduleHandler->isLoaded()) {
        $this->cache->set("theme_registry:build:modules", $registry, CacheBackendInterface::CACHE_PERMANENT, array('theme_registry' => TRUE), TRUE);
      }
    }

    // Process each base theme.
    foreach ($this->baseThemes as $base) {
      // If the base theme uses a theme engine, process its hooks.
      $base_path = dirname($base->uri);
      if ($this->engine) {
        $this->processExtension($registry, 'base_theme_engine', $this->engine, $base->name, $base_path);
      }
      $this->processExtension($registry, 'base_theme', $base->name, $base->name, $base_path);
    }

    // Process the theme itself.
    if ($this->engine) {
      $this->processExtension($registry, 'theme_engine', $this->engine, $this->theme->name, dirname($this->theme->uri));
    }
    if ($this->engine == 'phptemplate') {
      // Check for Twig templates if this is a PHPTemplate theme.
      // @todo Remove this once all core themes are converted to Twig.
      $this->processExtension($registry, 'theme_engine', 'twig', $this->theme->name, dirname($this->theme->uri));
    }
    $this->processExtension($registry, 'theme', $this->theme->name, $this->theme->name, dirname($this->theme->uri));

    // Allow modules to alter the complete registry.
    $this->moduleHandler->alter('theme_registry', $registry);

    $this->registry = $registry;
    $this->compile();
    return $this->registry;
  }

  /**
   * Process a single implementation of hook_theme().
   *
   * @param array $registry
   *   The theme registry that will eventually be cached.
   * @param string $type
   *   One of 'module', 'base_theme_engine', 'base_theme', 'theme_engine', or
   *   'theme'.
   * @param string $name
   *   The name of the extension implementing hook_theme().
   * @param string $theme_name
   *   The name of the extension for which theme hooks are currently processed.
   *   This equals $name for all extension types, except when $type is a theme
   *   engine, in which case $theme_name and $theme_path are pointing to the
   *   respective [base] theme the theme engine is associated with.
   * @param string $theme_path
   *   The directory of $theme_name; e.g., 'core/modules/node' or
   *   'themes/bartik'.
   *
   * @see theme()
   * @see hook_theme()
   */
  protected function processExtension(&$registry, $type, $name, $theme_name, $theme_path) {
    $function = $name . '_theme';
    // Extensions do not necessarily have to implement hook_theme().
    if (!function_exists($function)) {
      return;
    }
    foreach ($function($registry, $type, $theme_name, $theme_path) as $hook => $info) {
      // Ensure this hook key exists; it will be set either way.
      $registry += array($hook => array(
        // @todo It's not really clear what these are pointing to, whether they
        //   are used anywhere, and how, and whether they are needed at all.
        'type' => $type,
        'name' => $name,
      ));

      if (isset($info['file'])) {
        $include_path = isset($info['path']) ? $info['path'] : $theme_path;
        $info['includes'][] = $include_path . '/' . $info['file'];
        unset($info['file']);
      }

      // An actual/original theme hook must define either 'variables' or a
      // 'render element', in which case we need to assign default values for
      // 'template' or 'function'.
      if (isset($info['variables']) || isset($info['render element'])) {
        // Add an internal build process marker to track that this an actual
        // theme hook and not a suggestion.
        $info['exists'] = TRUE;

        // The effective path_to_theme() during theme().
        $info['theme path'] = $theme_path;

        if (isset($info['template'])) {
          // Default the template path to the 'templates' directory of the
          // extension, unless overridden.
          if (!isset($info['path'])) {
            $info['path'] = $theme_path . '/templates';
          }
          // Find the preferred theme engine for this module template.
          // @todo Remove this. Simply support multiple theme engines;
          //   which will simplify the entire processing in the first place.
          if ($type == 'module' || $type == 'theme_engine') {
            // Add two render engines for modules and theme engines.
            $render_engines = array(
              '.html.twig' => 'twig',
              '.tpl.php' => 'phptemplate',
            );
            // Find the best engine for this template.
            foreach ($render_engines as $extension => $engine) {
              // Render the output using the template file.
              $template_file = $info['path'] . '/' . $info['template'] . $extension;
              if (file_exists($template_file)) {
                $info['template_file'] = $template_file;
                $info['engine'] = $engine;
                break;
              }
            }
          }
        }
        // Otherwise, the implementation must be a function. However, functions
        // do not need to be specified manually; the array key of the hook is
        // expected to be taken over as function, unless overridden.
        elseif (!isset($info['function'])) {
          if ($type == 'module') {
            $info['function'] = 'theme_' . $hook;
          }
          else {
            $info['function'] = $name . '_' . $hook;
          }
        }
      }
      // If no 'variables' or 'render element' was defined, then this hook
      // definition extends an existing, or defines data for a hook suggestion.
      else {
        // Data for hook suggestions requires a full registry in order to check
        // for base hooks, since suggestions are extending hooks horizontally
        // (instead of overriding vertically); therefore it happens after
        // per-extension processing.
        // @see Registry::compile()

        // Revert the above theme engine hack for Twig, if the actual theme
        // engine returns a template.
        // @todo Remove the above hack. Simply support multiple theme engines;
        //   which will simplify the entire processing in the first place.
        if ($type == 'theme_engine' && isset($info['template'])) {
          // If the theme engine found a template set it as used theme engine.
          $info['engine'] = $name;

          $render_engines = array(
            'twig' => '.html.twig',
            'phptemplate' => '.tpl.php',
          );
          $extension = $render_engines[$name];
          // Render the output using the template file.
          $template_file = $info['path'] . '/' . $info['template'] . $extension;
          if (file_exists($template_file)) {
            $info['template_file'] = $template_file;
          }
        }

        if (isset($info['template'])) {
          // A template implementation always takes precedence over functions.
          // A potentially existing function pointer is obsolete.
          unset($registry[$hook]['function']);
          // Adjust the effective path_to_theme() during theme().
          $info['theme path'] = $theme_path;
          // Default the template path to the 'templates' directory of the
          // extension, unless overridden.
          if (!isset($info['path'])) {
            $info['path'] = $theme_path . '/templates';
          }
        }
      }

      // Record (pre)process functions by extension type.
      // The override logic here is essential:
      // - The first time a 'template' is defined by any extension, default
      //   template (pre)processor functions need to be injected.
      // - Some of the template (pre)processor functions have to run first;
      //   e.g., template_*().
      // - A special variant of template (pre)processor functions,
      //   template_preprocess_HOOK(), needs to run second, right after the base
      //   template_(pre)process() functions.
      // - Followed by the global hook_(pre)process() functions that apply to
      //   all templates, which need to be collated from all modules, all
      //   engines, and all themes (in this order).
      // - And lastly, any other (pre)process functions that have been declared
      //   in hook_theme().
      // Furthermore:
      // - template_preprocess_HOOK() and hook_(pre)process_HOOK() also need to
      //   run for theme *functions*, not only templates. All other template/
      //   default processors are omitted, unless explicitly declared in
      //   hook_theme(). (performance).
      // - If a later extension type in the build process replaces a theme
      //   function with a theme template by declaring 'template' (e.g., a theme
      //   wants to use a template instead of a function), then all of the
      //   default processors need to be injected (in the order described above).
      // - All recorded data of previous processing steps is expected to be
      //   available to hook_theme() implementations, which means that these
      //   operations cannot happen in Registry::compile().
      // To achieve the required ordering, the build process records all
      // registered and automatically determined (pre)process functions keyed by
      // extension type. This allows the final compile pass in
      // Registry::compile() to sort the final list of functions in their
      // required order.
      // Additionally, all functions are added with a string key, so they do not
      // get duplicated when merging the info of the current extension into the
      // existing registry info.
      // @see theme()
      $has_template = isset($registry[$hook]['template']) || isset($info['template']);
      foreach (array('preprocess', 'process') as $phase) {
        if (isset($info[$phase]) || $has_template) {
          if (isset($info[$phase])) {
            $info[$phase] = array_combine($info[$phase], $info[$phase]);
          }
          else {
            $info[$phase] = array();
          }
          $functions = array();
          // 1) The base template_(pre)process(). Only for templates.
          if ($has_template) {
            $template_function = "template_{$phase}";
            if (function_exists($template_function)) {
              $functions['template'][$template_function] = $template_function;
            }
          }
          // 2) template_(pre)process_HOOK(), if registered in $info.
          $template_function = "template_{$phase}_{$hook}";
          if (isset($info[$phase][$template_function])) {
            $functions['template_hook'][$template_function] = $template_function;
            unset($info[$phase][$template_function]);
          }
          // 3) hook_(pre)process() of all modules. Only for templates.
          // Since modules are processed before themes, but themes can declare
          // templates, module hook implementations need to be added whenever a
          // template is added.
          if ($has_template) {
            $functions['module'] = $this->getHookImplementations($phase);
          }
          // 4) hook_(pre)process() of theme engines and themes.
          // Template hooks of modules are processed in later steps, so we need
          // to add hook_(pre)process() functions.
          if ($type != 'module' && $has_template) {
            $function = $name . '_' . $phase;
            if (function_exists($function)) {
              $functions[$type][$function] = $function;
            }
          }
          // 5) hook_(pre)process_HOOK(), as declared in $info.
          // Since template_(pre)process_HOOK() was removed above, check whether
          // any functions are left first.
          if (!empty($info[$phase])) {
            $key = $type . '_hook';
            $functions[$key] = $info[$phase];
          }

          // Replace the list functions (they are keyed by extension type now).
          $info[$phase] = $functions;
        }
      }

      // Themes and theme engines can force-remove all preprocess functions.
      // If so, they need to provide their own. Therefore, unset existing before
      // merging.
      // @see hook_theme()
      if (!empty($info['no preprocess'])) {
        unset($registry[$hook]['preprocess']);
        unset($registry[$hook]['no preprocess']);
      }

      // Merge this extension's theme hook definition into the existing.
      $registry[$hook] = NestedArray::mergeDeep($registry[$hook], $info);
    }
  }

  /**
   * Compiles the theme registry.
   *
   * Compilation involves these steps:
   * - Theme hook suggestions are mapped and resolved to base hooks.
   * - (Pre)process functions are sorted.
   * - Unnecessary data is removed.
   */
  protected function compile() {
    // Merge base hooks into suggestions and remove unnecessary hooks.
    foreach ($this->registry as $hook => &$info) {
      if (empty($info['exists'])) {
        if (!$base_hook = $this->getBaseHook($hook)) {
          // If no base hook was found, then this is a suggestion for a theme
          // hook of another extension that is not enabled.
          unset($this->registry[$hook]);
          continue;
        }
        // If a base hook is found, use it as base. (Pre)processor functions
        // of the hook suggestion are appended, since the hook suggestion is
        // more specific, by design. Any other info of this hook overrides the
        // base hook.
        $info = NestedArray::mergeDeep($this->registry[$base_hook], $info);
        $info['base hook'] = $base_hook;
      }
    }

    // Compile (pre)process functions and clean up unnecessary data.
    $preprocessor_phases = array(
      'template',
      'template_hook',
      'module',
      'module_hook',
      'base_theme_engine',
      'base_theme_engine_hook',
      'theme_engine',
      'theme_engine_hook',
      'base_theme',
      'base_theme_hook',
      'theme',
      'theme_hook',
    );
    foreach ($this->registry as $hook => &$info) {
      if (isset($info['exists'])) {
        unset($info['exists']);
      }
      // (Pre)process functions have been collected separately by extension type
      // during the build process. Due to the required final merging of base
      // hooks and hook suggestions, as well as the possibility of functions
      // getting replaced with templates by themes, the final set of functions
      // needs to be determined and compiled now.
      foreach (array('preprocess', 'process') as $phase) {
        if (isset($info[$phase])) {
          $functions = array();
          foreach ($preprocessor_phases as $type) {
            if (isset($info[$phase][$type])) {
              $functions += $info[$phase][$type];
            }
          }
          // Remove the unnecessary array keys to decrease the array size.
          $info[$phase] = array_values($functions);
        }
      }
    }

    return $this->registry;
  }

  /**
   * Returns the base hook for a given hook suggestion.
   *
   * @param string $hook
   *   The name of a theme hook whose base hook to find.
   *
   * @return string|false
   *   The name of the base hook or FALSE.
   */
  public function getBaseHook($hook) {
    $base_hook = $hook;
    // Iteratively strip everything after the last '__' delimiter, until a
    // base hook definition is found. Recursive base hooks of base hooks are
    // not supported, so the base hook must be an original implementation that
    // points to a theme function or template.
    while ($pos = strrpos($base_hook, '__')) {
      $base_hook = substr($base_hook, 0, $pos);
      if (isset($this->registry[$base_hook]['exists'])) {
        break;
      }
    }
    if ($pos !== FALSE && $base_hook !== $hook) {
      return $base_hook;
    }
    return FALSE;
  }

  /**
   * Retrieves module hook implementations for a given theme hook name.
   *
   * @param string $hook
   *   The hook name to discover.
   *
   * @return array
   *   An array of module hook implementations; i.e., the actual function names.
   */
  protected function getHookImplementations($hook) {
    $implementations = array();
    foreach ($this->moduleHandler->getImplementations($hook) as $module) {
      $function = $module . '_' . $hook;
      $implementations[$function] = $function;
    };
    return $implementations;
  }

  /**
   * Invalidates theme registry caches.
   *
   * To be called when the list of enabled extensions is changed.
   */
  public function reset() {

    // Reset the runtime registry.
    if (isset($this->runtimeRegistry) && $this->runtimeRegistry instanceof ThemeRegistry) {
      $this->runtimeRegistry->clear();
    }
    $this->runtimeRegistry = NULL;

    $this->registry = NULL;
    $this->cache->invalidateTags(array('theme_registry' => TRUE));
    return $this;
  }

  /**
   * Implements Drupal\Core\DestructableInterface::destruct().
   */
  public function destruct() {
    if (isset($this->runtimeRegistry)) {
      $this->runtimeRegistry->destruct();
    }
  }

}
