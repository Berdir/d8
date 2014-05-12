<?php

/**
* @file
* Contains \Drupal\Core\Menu\MenuLinkTree.
*/

namespace Drupal\Core\Menu;

use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Access\AccessManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
* Manages discovery, instantiation, and tree building of menu link plugins.
*
* This manager finds plugins that are rendered as menu links.
*/
class MenuLinkTree implements MenuLinkTreeInterface {

  /**
   * {@inheritdoc}
   */
  protected $defaults = array(
    // (required) The name of the menu for this link.
    'menu_name' => 'tools',
    // (required) The name of the route this links to, unless it's external.
    'route_name' => '',
    // Parameters for route variables when generating a link.
    'route_parameters' => array(),
    // The external URL if this link references one (required if route_name is empty).
    'url' => '',
    // The static title for the menu link.
    'title' => '',
    'title_arguments' => array(),
    'title_context' => '',
    // The description.
    'description' => '',
    // The plugin ID of the parent link (or NULL for a top-level link).
    'parent' => '',
    // The weight of the link.
    'weight' => 0,
    // The default link options.
    'options' => array(),
    'expanded' => 0,
    'hidden' => 0,
    // Flag for whether this plugin was discovered. Should be set to 0 or NULL
    // for definitions that are added via a direct save.
    'discovered' => 0,
    'provider' => '',
    'metadata' => array(),
    // Default class for local task implementations.
    'class' => 'Drupal\Core\Menu\MenuLinkDefault',
    'form_class' => 'Drupal\Core\Menu\Form\MenuLinkDefaultForm',
    // The plugin id. Set by the plugin system based on the top-level YAML key.
    'id' => '',
  );

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
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Cache backend instance for the extracted tree data.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $treeCacheBackend;

  /**
   * The menu link tree storage.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeStorageInterface
   */
  protected $treeStorage;

  /**
   * Service providing overrides for static links
   *
   * @var \Drupal\Core\Menu\StaticMenuLinkOverridesInterface
   */
  protected $overrides;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The plugin instances.
   *
   * @var array
   */
  protected $instances = array();

  /**
   * The statically cached definitions.
   *
   * @var array
   */
  protected $definitions = array();

  /**
   * The route provider to load routes by name.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The access manager.
   *
   * @var \Drupal\Core\Access\AccessManager
   */
  protected $accessManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Stores the menu tree used by the doBuildTree method, keyed by a cache ID.
   *
   * This cache ID is built using the $menu_name, the current language and
   * some parameters passed into an entity query.
   */
  protected $menuTree;

  /**
   * Stores the menu tree data on the current page keyed by a cache ID.
   *
   * This contains less information than a tree built with buildAllData.
   *
   * @var array
   */
  protected $menuPageTrees;

  protected $preferredLinks = array();
  protected $buildAllDataParameters = array();

  /**
   * Constructs a \Drupal\Core\Menu\MenuLinkTree object.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeStorageInterface $tree_storage
   *   The menu link tree storage.
   * @param \Drupal\Core\Menu\StaticMenuLinkOverridesInterface $overrides
   *   Service providing overrides for static links
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request object to use for building titles and paths for plugin instances.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider to load routes by name.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $tree_cache_backend
   *   Cache backend instance for the extracted tree data.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Access\AccessManager $access_manager
   *   The access manager.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(MenuLinkTreeStorageInterface $tree_storage, StaticMenuLinkOverridesInterface $overrides, RequestStack $request_stack, RouteProviderInterface $route_provider, ModuleHandlerInterface $module_handler, CacheBackendInterface $tree_cache_backend, LanguageManagerInterface $language_manager, AccessManager $access_manager, AccountInterface $account, EntityManagerInterface $entity_manager) {
    $this->treeStorage = $tree_storage;
    $this->overrides = $overrides;
    $this->factory = new ContainerFactory($this);
    $this->requestStack = $request_stack;
    $this->routeProvider = $route_provider;
    $this->accessManager = $access_manager;
    $this->account = $account;
    $this->moduleHandler = $module_handler;
    $this->treeCacheBackend = $tree_cache_backend;
    $this->languageManager = $language_manager;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    $definition = NestedArray::mergeDeep($this->defaults, $definition);
    $definition['parent'] = (string) $definition['parent'];
    $definition['id'] = $plugin_id;
  }

  /**
   * Instanciates the discovery.
   */
  protected function getDiscovery() {
    if (empty($this->discovery)) {
      $yaml = new YamlDiscovery('menu_links', $this->moduleHandler->getModuleDirectories());
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($yaml);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    // Since this function is called rarely, instantiate the discovery here.
    $definitions = $this->getDiscovery()->getDefinitions();

    $this->moduleHandler->alter('menu_links', $definitions);

    foreach ($definitions as $plugin_id => &$definition) {
      $definition['id'] = $plugin_id;
      $this->processDefinition($definition, $plugin_id);
    }

    // If this plugin was provided by a module that does not exist, remove the
    // plugin definition.
    foreach ($definitions as $plugin_id => $plugin_definition) {
      if (!empty($plugin_definition['provider']) && !$this->moduleHandler->moduleExists($plugin_definition['provider'])) {
        unset($definitions[$plugin_id]);
      }
      else {
        // Any link found here is flagged as discovered, so it can be purged
        // if it does exit in the future.
        $definitions[$plugin_id]['discovered'] = 1;
      }
    }
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function rebuild() {
    // Fetch the list of existing menus, in case some are not longer populated
    // after the rebuild.
    $before_menus = $this->treeStorage->getMenuNames();
    $definitions = $this->getDefinitions();
    // Apply overrides from config.
    $overrides = $this->overrides->loadMultipleOverrides(array_keys($definitions));
    foreach ($overrides as $id => $changes) {
      if (!empty($definitions[$id])) {
        $definitions[$id] = $changes + $definitions[$id];
      }
    }
    $this->treeStorage->rebuild($definitions);
    $this->treeCacheBackend->deleteAll();
    $affected_menus = $this->treeStorage->getMenuNames() + $before_menus;
    Cache::invalidateTags(array('menu' => $affected_menus));
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition($plugin_id) {
    // When building tress, we will usually have the definitions already loaded.
    // This makes the call to $this->factory->createInstance() faster.
    if (!isset($this->definitions[$plugin_id])) {
      $this->definitions[$plugin_id] = $this->treeStorage->load($plugin_id);
    }
    return $this->definitions[$plugin_id];
  }

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    return $this->factory->createInstance($plugin_id, $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function getInstance(array $options) {
    if (isset($options['id'])) {
      return $this->createInstance($options['id']);
    }
  }

  /**
   * Returns an array containing all links for a menu.
   *
   * @param string $menu_name
   *   The name of the menu whose links should be returned.
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface[]
   *   An array of menu link plugin instances keyed by ID.
   */
  public function loadLinks($menu_name) {
    $instances = array();
    $loaded = $this->treeStorage->loadByProperties(array('menu_name' => $menu_name));
    foreach ($loaded as $plugin_id => $definition) {
      // Setting the definition here means it will be used by getDefinition()
      // which is called by createInstance() from the factory.
      $this->definitions[$plugin_id] = $definition;
      $instances[$plugin_id] = $this->createInstance($plugin_id);
    }
    return $instances;
  }

  /**
   * Deletes all links for a menu.
   *
   * @todo - this should really only be called as part of the flow of
   * deleting a menu entity, so maybe we should load it and make sure it's
   * not locked?
   *
   * @param $menu_name
   *   The name of the menu whose links will be deleted.
   */
  public function deleteLinksInMenu($menu_name) {
    $affected_menus = array($menu_name => $menu_name);
    foreach ($this->treeStorage->loadByProperties(array('menu_name' => $menu_name)) as $plugin_id => $definition) {
      // Setting the definition here means it will be used by getDefinition()
      // which is called by createInstance() from the factory.
      $this->definitions[$plugin_id] = $definition;
      $instance = $this->createInstance($plugin_id);
      if ($instance->isResetable()) {
        $new_instance = $this->resetInstance($instance);
        $affected_menus[$new_instance->getMenuName()] = $new_instance->getMenuName();
      }
      elseif ($instance->isDeletable()) {
        $this->deleteInstance($instance, TRUE);
      }
    }
    Cache::invalidateTags(array('menu' => $affected_menus));
  }

  protected function deleteInstance(MenuLinkInterface $instance, $persist) {
    $id = $instance->getPluginId();
    if ($instance->isDeletable()) {
      if ($persist) {
        $instance->deleteLink();
      }
    }
    else {
      throw new PluginException(sprintf("Menu link plugin with ID %s does not support deletion", $id));
    }
    $this->treeStorage->delete($id);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLink($id, $persist = TRUE) {
    $definition = $this->treeStorage->load($id);
    // It's possible the definition has already been deleted, or doesn't exist.
    if ($definition) {
      // Setting the definition here means it will be used by getDefinition()
      // which is called by createInstance() from the factory.
      $this->definitions[$id] = $definition;
      $instance = $this->createInstance($id);
      $this->deleteInstance($instance, $persist);
      // Many children may have moved.
      $this->resetDefinitions();
      Cache::invalidateTags(array('menu' => array($definition['menu_name'])));
    }
    $this->resetDefinition($id);
  }

  /**
   * {@inheritdoc}
   */
  public function countMenuLinks($menu_name = NULL) {
    return $this->treeStorage->countMenuLinks($menu_name);
  }

  public function loadLinksByRoute($route_name, array $route_parameters = array(), $include_hidden = FALSE) {
    $instances = array();
    $loaded = $this->treeStorage->loadByRoute($route_name, $route_parameters, $include_hidden);
    foreach ($loaded as $plugin_id => $definition) {
      // Setting the definition here means it will be used by getDefinition()
      // which is called by createInstance() from the factory.
      $this->definitions[$plugin_id] = $definition;
      $instances[$plugin_id] = $this->createInstance($plugin_id);
    }
    return $instances;
  }

  /**
   * {@inheritdoc}
   */
  public function maxDepth() {
    return $this->treeStorage->maxDepth();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRenderTree($tree) {
    $build = array();

    foreach ($tree as $data) {
      $class = array();
      /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
      $link = $data['link'];
      // Generally we only deal with visible links, but just in case.
      if ($link->isHidden()) {
        continue;
      }
      // Set a class for the <li>-tag. Only set 'expanded' class if the link
      // also has visible children within the current tree.
      if ($data['has_children'] && $data['below']) {
        $class[] = 'expanded';
      }
      elseif ($data['has_children']) {
        $class[] = 'collapsed';
      }
      else {
        $class[] = 'leaf';
      }
      // Set a class if the link is in the active trail.
      if ($data['in_active_trail']) {
        $class[] = 'active-trail';
        //$data['link']['localized_options']['attributes']['class'][] = 'active-trail';
      }

      // Allow menu-specific theme overrides.
      $element['#theme'] = 'menu_link__' . strtr($link->getMenuName(), '-', '_');
      $element['#attributes']['class'] = $class;
      $element['#title'] = $link->getTitle();
      // @todo Use route name and parameters to generate the link path, unless
      //    it is external.
      $element['#url'] = $link->getUrlObject();
      $element['#below'] = $data['below'] ? $this->buildRenderTree($data['below']) : array();
      $element['#original_link'] = $link;
      // Index using the link's unique ID.
      $build[$link->getPluginId()] = $element;
    }
    if ($build) {
      // Make sure drupal_render() does not re-order the links.
      $build['#sorted'] = TRUE;
      // Get the menu name from the last link.
      $menu_name = $link->getMenuName();
      // Add the theme wrapper for outer markup.
      // Allow menu-specific theme overrides.
      $build['#theme_wrappers'][] = 'menu_tree__' . strtr($menu_name, '-', '_');
      // Set cache tag.
      $build['#cache']['tags']['menu'][$menu_name] = $menu_name;
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveTrailIds($menu_name) {
    // Parent ids; used both as key and value to ensure uniqueness.
    // We always want all the top-level links with parent == ''.
    $active_trail = array('' => '');

    $request = $this->requestStack->getCurrentRequest();

    if ($route_name = $request->attributes->get(RouteObjectInterface::ROUTE_NAME)) {
      $route_parameters = $request->attributes->get('_raw_variables')->all();
      // @todo https://drupal.org/node/2068471 is adding support so we can tell
      // if this is called on a 404/403 page.
      // Find a menu link corresponding to the current path. If
      // $active_path is NULL, let $this->menuLinkGetPreferred() determine the
      // path.
      if ($active_link = $this->menuLinkGetPreferred($route_name, $route_parameters, $menu_name)) {
        if ($active_link->getMenuName() == $menu_name) {
          $active_trail += $this->treeStorage->getMaterializedPathIds($active_link->getPluginId());
        }
      }
    }
    return $active_trail;
  }

  /**
   * {@inheritdoc}
   */
  public function menuLinkGetPreferred($route_name = NULL, array $route_parameters = array(), $selected_menu = NULL) {
    if (!isset($route_name)) {
      $request = $this->requestStack->getCurrentRequest();

      $route_name = $request->attributes->get(RouteObjectInterface::ROUTE_NAME);
      $route_parameters = $request->attributes->get('_raw_variables')->all();
    }

    $access = $this->accessManager->checkNamedRoute($route_name, $route_parameters, $this->account);
    if (!$access) {
      return NULL;
    }
    asort($route_parameters);
    $route_key = hash('sha256', $route_name . serialize($route_parameters));

    if (empty($selected_menu)) {
      // Use an illegal menu name as the key for the preferred menu link.
      $selected_menu = '%';
    }

    if (!isset($this->preferredLinks[$route_key])) {
      // Retrieve a list of menu names, ordered by preference.
      $menu_names = $this->menuGetActiveMenuNames();
      // Put the selected menu at the front of the list.
      array_unshift($menu_names, $selected_menu);
      // If this menu name is not fond later, we want to just get NULL.
      $this->preferredLinks[$route_key][$selected_menu] = NULL;

      // Only load non-hidden links.
      $definitions = $this->treeStorage->loadByRoute($route_name, $route_parameters);
      // Sort candidates by menu name.
      $candidates = array();
      foreach ($definitions as $candidate) {
        $candidates[$candidate['menu_name']] = $candidate;
        $menu_names[] = $candidate['menu_name'];
      }
      foreach ($menu_names as $menu_name) {
        if (isset($candidates[$menu_name]) && !isset($this->preferredLinks[$route_key][$menu_name])) {
          $candidate = $candidates[$menu_name];
          $this->definitions[$candidate['id']] = $candidate;
          $instance = $this->createInstance($candidate['id']);
          $this->preferredLinks[$route_key][$menu_name] = $instance;
          if (!isset($this->preferredLinks[$route_key]['%'])) {
            $this->preferredLinks[$route_key]['%'] = $instance;
          }
        }
      }

    }
    return isset($this->preferredLinks[$route_key][$selected_menu]) ? $this->preferredLinks[$route_key][$selected_menu] : NULL;
  }

  protected function menuGetActiveMenuNames() {
    return menu_get_active_menu_names();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPageData($menu_name, $max_depth = NULL, $only_active_trail = FALSE) {
    $language_interface = $this->languageManager->getCurrentLanguage();

    // Load the request corresponding to the current page.
    $request = $this->requestStack->getCurrentRequest();
    $page_is_403 = FALSE;
    $system_path = NULL;
    if ($route_name = $request->attributes->get(RouteObjectInterface::ROUTE_NAME)) {
      $system_path = $request->attributes->get('_system_path');
      $page_is_403 = $request->attributes->get('_exception_statuscode') == 403;
    }

    if (isset($max_depth)) {
      $max_depth = min($max_depth, $this->treeStorage->maxDepth());
    }
    // Generate a cache ID (cid) specific for this page.
    $cid = 'links:' . $menu_name . ':page:' . $system_path . ':' . $language_interface->id . ':' . $page_is_403 . ':' . (int) $max_depth;
    // If we are asked for the active trail only, and $menu_name has not been
    // built and cached for this page yet, then this likely means that it
    // won't be built anymore, as this function is invoked from
    // template_preprocess_page(). So in order to not build a giant menu tree
    // that needs to be checked for access on all levels, we simply check
    // whether we have the menu already in cache, or otherwise, build a
    // minimum tree containing the active trail only.
    // @see menu_set_active_trail()
    if (!isset($this->menuPageTrees[$cid]) && $only_active_trail) {
      $cid .= ':trail';
    }

    // @TODO Decide whether it makes sense to static cache page menu trees.
    if (!isset($this->menuPageTrees[$cid])) {
      // If the static variable doesn't have the data, check {cache_menu}.
      $cache = $this->treeCacheBackend->get($cid);
      if ($cache && isset($cache->data)) {
        // If the cache entry exists, it contains the parameters for
        // menu_build_tree().
        $tree_parameters = $cache->data;
      }
      else {
        $tree_parameters = $this->doBuildPageDataTreeParameters($menu_name, $max_depth, $only_active_trail, $page_is_403);

        // Cache the tree building parameters using the page-specific cid.
        $this->treeCacheBackend->set($cid, $tree_parameters, Cache::PERMANENT, array('menu' => $menu_name));
      }

      // Build the tree using the parameters; the resulting tree will be cached
      // by $this->buildTree()).
      $this->menuPageTrees[$cid] = $this->buildTree($menu_name, $tree_parameters);
    }
    return $this->menuPageTrees[$cid];
  }

  /**
   * Determines the required tree parameters used for the page menu tree.
   *
   * This method takes into account the active trail of the current page.
   *
   * @param string $menu_name
   *   The menu name.
   * @param int $max_depth
   *   The maximum allowed depth of menus.
   * @param bool $only_active_trail
   *   If TRUE, just load level 0 plus the active trail, otherwise load the full
   *   menu tree.
   * @param bool $page_is_403
   *   Is the current request happening on a 403 subrequest.
   *
   * @return array
   */
  protected function doBuildPageDataTreeParameters($menu_name, $max_depth, $only_active_trail, $page_is_403) {
    $tree_parameters = array(
      'min_depth' => 1,
      'max_depth' => $max_depth,
    );

    // If this page is accessible to the current user, build the tree
    // parameters accordingly.
    if (!$page_is_403) {
      $active_trail = $this->getActiveTrailIds($menu_name);
      // The active trail contains more than only array(0 => 0).
      if (count($active_trail) > 1) {
        // If we are asked to build links for the active trail only,skip
        // the entire 'expanded' handling.
        if ($only_active_trail) {
          $tree_parameters['only_active_trail'] = TRUE;
        }
      }
      $parents = $active_trail;

      if (!$only_active_trail) {
        // Collect all the links set to be expanded, and then add all of
        // their children to the list as well.
        $parents = $this->treeStorage->getExpanded($menu_name, $parents);
      }
    }
    else {
      // If access is denied, we only show top-level links in menus.
      $active_trail = array('' => '');
      $parents = $active_trail;
    }
    $tree_parameters['expanded'] = $parents;
    $tree_parameters['active_trail'] = $active_trail;
    return $tree_parameters;
  }

  /**
   * @TODO should this accept a menu link instance or just the ID?
   */
  public function buildAllData($menu_name, $id = NULL, $max_depth = NULL) {
    $language_interface = $this->languageManager->getCurrentLanguage();

    // Use ID as a flag for whether the data being loaded is for the whole
    // tree.
    $id = isset($id) ? $id : '%';
    // Generate a cache ID (cid) specific for this $menu_name, $link, $language,
    // and depth.
    $cid = 'links:' . $menu_name . ':all:' . $id . ':' . $language_interface->id . ':' . (int) $max_depth;
    if (!isset($this->buildAllDataParameters[$cid])) {
      $tree_parameters = array(
        'min_depth' => 1,
        'max_depth' => $max_depth,
      );
      if ($id != '%') {
        // The tree is for a single item, so we need to match the values in
        // of all the IDs on the path to root.
        $tree_parameters['active_trail'] = $this->treeStorage->getMaterializedPathIds($id);
        $tree_parameters['expanded'] = $tree_parameters['active_trail'];
        // Include top-level links.
        $tree_parameters['expanded'][''] = '';
      }
      $this->buildAllDataParameters[$cid] = $tree_parameters;
    }
    // Build the tree using the parameters; the resulting tree will be cached
    // by buildTree().
    return $this->buildTree($menu_name, $this->buildAllDataParameters[$cid]);
  }

  public function getChildLinks($id, $max_relative_depth = NULL) {
    $links = array();
    $definitions = $this->treeStorage->loadAllChildLinks($id, $max_relative_depth);
    foreach($definitions as $id => $definition) {
      $instance = $this->menuLinkTranslate($definition);
      if ($instance) {
        $links[$id] = $instance;
      }
    }
    return $links;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentIds($id) {
    if ($this->getDefinition($id)) {
      return $this->treeStorage->getMaterializedPathIds($id);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  function getChildIds($id) {
    if ($this->getDefinition($id)) {
      return $this->treeStorage->getAllChildIds($id);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildSubtree($id, $max_relative_depth = NULL) {
    $subtree = $this->treeStorage->loadSubtree($id, $max_relative_depth);
    if ($subtree) {
      // Check access and instantiate. @todo rename these methods.
      $instance = $this->menuLinkTranslate($subtree['definition']);
      if ($instance) {
        $subtree['link'] = $instance;
        $route_names = $this->collectRoutes($subtree['below']);
        // Pre-load all the route objects in the tree for access checks.
        if ($route_names) {
          $this->routeProvider->getRoutesByNames($route_names);
        }
        $this->checkAccess($subtree['below']);
        return $subtree;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildTree($menu_name, array $parameters = array()) {
    $language_interface = $this->languageManager->getCurrentLanguage();

    // Build the cache id; sort parents to prevent duplicate storage and remove
    // default parameter values.
    asort($parameters);
    if (isset($parameters['expanded'])) {
      sort($parameters['expanded']);
    }
    $tree_cid = 'links:' . $menu_name . ':tree-data:' . $language_interface->id . ':' . hash('sha256', serialize($parameters));

    // If we do not have this tree in the static cache, check cache.menu.
    if (!isset($this->menuTree[$tree_cid])) {
      $cache = $this->treeCacheBackend->get($tree_cid);
      if ($cache && isset($cache->data)) {
        $this->menuTree[$tree_cid] = $cache->data;
      }
    }

    if (!isset($this->menuTree[$tree_cid])) {
      // Rebuild the links which are stored
      $data['tree'] = $this->treeStorage->loadTree($menu_name, $parameters);
      $data['route_names'] = $this->collectRoutes($data['tree']);
      // Cache the data, if it is not already in the cache.
      $this->treeCacheBackend->set($tree_cid, $data, Cache::PERMANENT, array('menu' => $menu_name));
      $this->menuTree[$tree_cid] = $data;
    }
    else {
      $data = $this->menuTree[$tree_cid];
    }

    // Pre-load all the route objects in the tree for access checks.
    if ($data['route_names']) {
      $this->routeProvider->getRoutesByNames($data['route_names']);
    }
    $tree = $data['tree'];
    $this->checkAccess($tree);
    return $tree;
  }

  /**
   * Traverses the menu tree and collects all the route names.
   *
   * @param array $tree
   *   The menu tree you wish to operate on.
   *
   * @return array
   *   Array of route names, with all values being unique.
   */
  protected function collectRoutes($tree) {
    return array_values($this->doCollectRoutes($tree));
  }

  /**
   * Recursive helper function to collect all the route names.
   */
  protected function doCollectRoutes($tree) {
    $route_names = array();
    foreach ($tree as $key => $v) {
      $definition = $tree[$key]['definition'];
      if (!empty($definition['route_name'])) {
        $route_names[$definition['route_name']] = $definition['route_name'];
      }
      if ($tree[$key]['below']) {
        $route_names += $this->doCollectRoutes($tree[$key]['below']);
      }
    }
    return $route_names;
  }

  /**
   * Sorts the menu tree and recursively checks access for each item.
   *
   * @param array $tree
   *   The menu tree you wish to operate on.
   */
  protected function checkAccess(&$tree) {
    $new_tree = array();
    foreach ($tree as $key => $v) {
      $definition = $tree[$key]['definition'];
      // Setting the definition here means it will be used by getDefinition()
      // which is called by createInstance() from the factory.
      $this->definitions[$definition['id']] = $definition;
      $instance = $this->menuLinkTranslate($definition);
      if ($instance) {
        $tree[$key]['link'] = $instance;
        if ($tree[$key]['below']) {
          $this->checkAccess($tree[$key]['below']);
        }
        unset($tree[$key]['definition']);
        // The weights are made a uniform 5 digits by adding 50000 as an offset.
        // After $this->menuLinkTranslate(), $item['title'] has the localized
        // link title. Adding the plugin id to the end of the index insures that
        // it is unique.
        $new_tree[(50000 + $instance->getWeight()) . ' ' . $instance->getTitle() . ' ' . $instance->getPluginId()] = $tree[$key];
      }
    }
    // Sort siblings in the tree based on the weights and localized titles.
    ksort($new_tree);
    $tree = $new_tree;
  }

  /**
   * Check access for the item and create an instance if it is accessible.
   *
   * @param array $definition
   *   The menu link definition.
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface|NULL
   */
  protected function menuLinkTranslate(array $definition) {

    // url should only be populated for external links.
    if (!empty($definition['url']) && empty($definition['route_name'])) {
      $access = TRUE;
    }
    else {
      $access = $this->accessManager->checkNamedRoute($definition['route_name'], $definition['route_parameters'], $this->account);
    }
    // For performance, don't instantiate a link the user can't access.
    if ($access) {
      return $this->createInstance($definition['id']);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function createLink($id, array $definition) {
    // Add defaults and other stuff, so there is no requirement to specify
    // everything.
    $this->processDefinition($definition, $id);

    // Store the new link in the tree and invalidate some caches.
    $affected_menus = $this->treeStorage->save($definition);
    Cache::invalidateTags(array('menu' => $affected_menus));
    return $this->createInstance($id);
  }

  /**
   * {@inheritdoc}
   */
  public function updateLink($id, array $new_definition_values, $persist = TRUE) {
    $instance = $this->createInstance($id);
    if ($instance) {
      $new_definition_values['id'] = $id;
      $changed_definition = $instance->updateLink($new_definition_values, $persist);
      $affected_menus = $this->treeStorage->save($changed_definition);
      $this->resetDefinitions();
      Cache::invalidateTags(array('menu' => $affected_menus));
    }
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginForm(MenuLinkInterface $menu_link) {
    $class_name = $menu_link->getFormClass();
    /** @var \Drupal\Core\Menu\Form\MenuLinkFormInterface $form */
    if (in_array('Drupal\Core\DependencyInjection\ContainerInjectionInterface', class_implements($class_name))) {
      $form = $class_name::create(\Drupal::getContainer());
    }
    else {
      $form = new $class_name();
    }
    $form->setMenuLinkInstance($menu_link);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentSelectOptions($id = '', array $menus = array()) {
    // @todo: Core allows you to replace the select element ... this is a sign
    // that we might want to write a form element as well, which can be swapped.
    if (empty($menus)) {
      $menus = $this->getMenuOptions();
    }

    $options = array();
    $depth_limit = $this->getParentDepthLimit($id);
    foreach ($menus as $menu_name => $menu_title) {
      $options[$menu_name . ':'] = '<' . $menu_title . '>';

      $tree = $this->buildAllData($menu_name, NULL, $depth_limit);
      $this->parentSelectOptionsTreeWalk($tree, $menu_name, '--', $options, $id, $depth_limit);
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentDepthLimit($id) {
    $limit = $this->treeStorage->maxDepth() - 1;
    if ($id) {
      $limit = $limit - $this->treeStorage->findChildrenRelativeDepth($id);
    }
    return $limit;
  }

  protected function parentSelectOptionsTreeWalk(array $tree, $menu_name, $indent, &$options, $exclude, $depth_limit) {
    foreach ($tree as $data) {
      if ($data['depth'] > $depth_limit) {
        // Don't iterate through any links on this level.
        break;
      }
      /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
      $link = $data['link'];
      if ($link->getPluginId() != $exclude) {
        $title = $indent . ' ' . Unicode::truncate($link->getTitle(), 30, TRUE, FALSE);
        if ($link->isHidden()) {
          $title .= ' (' . t('disabled') . ')';
        }
        $options[$menu_name . ':' . $link->getPluginId()] = $title;
        if ($data['below']) {
          $this->parentSelectOptionsTreeWalk($data['below'], $menu_name, $indent . '--', $options, $exclude, $depth_limit);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuOptions(array $menu_names = NULL) {
    $menus = $this->entityManager->getStorage('menu')->loadMultiple($menu_names);
    $options = array();
    foreach ($menus as $menu) {
      $options[$menu->id()] = $menu->label();
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function menuNameExists($menu_name) {
    $this->treeStorage->menuNameExists($menu_name);
  }

  /**
   * {@inheritdoc}
   */
  public function resetLink($id) {
    $instance = $this->createInstance($id);
    $affected_menus[$instance->getMenuName()] = $instance->getMenuName();
    $new_instance = $this->resetInstance($instance);
    $affected_menus[$new_instance->getMenuName()] = $new_instance->getMenuName();
    Cache::invalidateTags(array('menu' => $affected_menus));
    return $new_instance;
  }

  protected function resetInstance(MenuLinkInterface $instance) {
    $id = $instance->getPluginId();

    if (!$instance->isResetable()) {
      throw new PluginException(String::format('Menu link %id is not resetable', array('%id' => $id)));
    }
    // Get the original data from disk, reset the override and re-save the menu
    // tree for this link.
    $definition = $this->getDefinitions()[$id];
    $this->overrides->deleteOverride($id);
    $this->resetDefinition($id, $definition);
    $this->treeStorage->save($definition);
    return $this->createInstance($id);
  }

  /**
   * {@inheritdoc}
   */
  public function resetDefinitions() {
    $this->definitions = array();
    $this->menuTree = array();
    $this->buildAllDataParameters = array();
    $this->menuPageTrees = array();
  }

  /**
   * Resets the local definition cache for one plugin.
   *
   * @param string $id
   *   The menu link plugin ID.
   * @param array $definition
   */
  protected function resetDefinition($id, $definition = NULL) {
    $this->definitions[$id] = $definition;
  }

}
