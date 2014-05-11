<?php

/**
 * @file
 * Contains \Drupal\Core\Menu\MenuLinkTreeStorageInterface.
 */

namespace Drupal\Core\Menu;

interface MenuLinkTreeStorageInterface {

  /**
   * The maximum depth of tree the storage implementation supports.
   *
   * @return int
   */
  public function maxDepth();

  /**
   * @param array $definitions
   *
   * @todo give this a better name.
   */
  public function rebuild(array $definitions);

  /**
   * Load a plugin definition from the storage.
   *
   * @param string $id
   *   The menu link plugin ID.
   * @return array|FALSE
   *   Menu Link definition
   */
  public function load($id);

  /**
   * Load multiple plugin definitions from the storage.
   *
   * @param array $ids
   *  An array of plugin IDs.
   *
   * @return array
   *   Array of menu Link definitions
   */
  public function loadMultiple(array $ids);

  /**
   * Load multiple plugin definitions from the storage based on properties.
   *
   * @param array $properties
   * @return array
   *   Array of menu link definitions
   */
  public function loadByProperties(array $properties);

  /**
   * Load multiple plugin definitions from the storage based on route.
   *
   * @param string $route_name
   * @param array $route_parameters
   * @param bool $include_hidden
   *
   * @return array
   *  Array of menu link definitions keyed by ID.
   */
  public function loadByRoute($route_name, array $route_parameters = array(), $include_hidden = FALSE);

  /**
   * Save a plugin definition to the storage.
   *
   * @param array $definition
   *   A definition for a \Drupal\Core\Menu\MenuLinkInterface plugin.
   *
   * @return array
   *   The names of the menus affected by the save operation (1 or 2).
   *
   * @throws \Exception
   *   If the storage back-end does not exist and could not be created.
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the definition is invalid - for example, if the specified parent
   *   would cause the links children to be moved to greater than the maximum
   *   depth.
   */
  public function save(array $definition);

  /**
   * Deletes a menu plugin from the storage.
   *
   * @param string $id
   *   The menu link plugin ID.
   */
  public function delete($id);

  /**
   * Loads a menu tree from the storage.
   *
   * This function may be used build the data for a menu tree only, for example
   * to further massage the data manually before further processing happens.
   * MenuLinkTree::checkAccess() needs to be invoked afterwards.
   *
   * The tree order is maintained using an optimized algorithm, for example by
   * storing each parent in an individual field, see
   * http://drupal.org/node/141866 for more details. However, any details
   * of the storage should not be relied upon since it may be swapped with
   * a different implementation.
   *
   * @param string $menu_name
   *   The name of the menu.
   * @param array $parameters
   *   (optional) An associative array of build parameters. Possible keys:
   *   - expanded: An array of parent plugin ids to return only menu links that
   *     are children of one of the ids in this list. If empty, the whole menu
   *     tree is built, unless 'only_active_trail' is TRUE.
   *   - active_trail: An array of ids, representing the coordinates of the
   *     currently active menu link.
   *   - only_active_trail: Whether to only return links that are in the active
   *     trail. This option is ignored if 'expanded' is non-empty.
   *   - min_depth: The minimum depth of menu links in the resulting tree.
   *     Defaults to 1, which is the default to build a whole tree for a menu
   *     (excluding menu container itself).
   *   - max_depth: The maximum depth of menu links in the resulting tree.
   *   - conditions: An associative array of custom condition key/value pairs
   *     to restrict the links loaded. Each key must be one of the keys
   *     in the plugin definition.
   *
   * @return array
   *   A fully built menu tree.
   */
  public function loadTree($menu_name, array $parameters = array());

  /**
   * Load all the visible links that are below the given ID.
   *
   * The returned links are not ordered, and visible children will be
   * included even if they have a hidden parent or ancestor so would not
   * normally appear in a rendered tree.
   *
   * @param string $id
   * @param int $max_relative_depth
   *
   * @return array
   *   Array of link definitions, keyed by ID.
   */
  public function loadAllChildLinks($id, $max_relative_depth = NULL);

  /**
   * Load a subtree rooted by the given ID.
   *
   * The returned links are structured like those from loadTree().
   *
   * @param string $id
   *   The menu link plugin ID.
   * @param int $max_relative_depth
   *   The maximum depth of child menu links relative to the passed in.
   *
   * @return array
   */
  public function loadSubtree($id, $max_relative_depth = NULL);

  /**
   * @param $id
   * @return array
   *   An array of plugin IDs that represents the path from this plugin ID
   *   to the root of the tree.
   */
  public function getMaterializedPathIds($id);

  /**
   * @param string $menu_name
   * @param array $parents
   * @return array
   *   The menu link ID that are flagged as expanded in this menu.
   */
  public function getExpanded($menu_name, array $parents);

  public function findChildrenRelativeDepth($id);

  public function menuNameExists($menu_name);

  public function getMenuNames();

  /**
   * Counts the total amount of menu links.
   *
   * @param string $menu_name
   *   (optional) The menu name to count by, defaults to NULL.
   */
  public function countMenuLinks($menu_name = NULL);
}
