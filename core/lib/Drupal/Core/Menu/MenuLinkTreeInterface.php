<?php

/**
 * @file
 * Contains \Drupal\Core\Menu\MenuLinkTreeInterface.
 */

namespace Drupal\Core\Menu;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Provides an object which returns the available menu links.
 */
interface MenuLinkTreeInterface extends PluginManagerInterface {

  /**
   * Trigger discover save, and cleanup of static links
   *
   * @todo find a better name?
   */
  public function rebuild();

  /**
   * The maximum depth of tree that is supported.
   *
   * @return int
   */
  public function maxDepth();

  /**
   * Deletes or resets all links for a menu.
   *
   * @param string $menu_name
   *   The name of the menu whose links will be deleted or reset.
   */
  public function deleteLinksInMenu($menu_name);

  /**
   * Deletes a single link from the menu tree.
   *
   * This should only be called when the link data has already been removed from
   * any external storage.  This method will not attempt to persist the deletion
   * except from the tree storage used by the plugin manager.
   *
   * @param string $id
   *   The menu link plugin ID.
   * @param bool $persist
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the $id is not valid, existing, plugin ID or if the link cannot be
   *   deleted.
   */
  public function deleteLink($id, $persist = TRUE);

  /**
   * Counts the total number of menu links.
   *
   * @param string $menu_name
   *   (optional) The menu name to count by, defaults to NULL.
   */
  public function countMenuLinks($menu_name = NULL);

  /**
   * Load multiple plugin instances based on route.
   *
   * @param string $route_name
   * @param array $route_parameters
   * @param bool $include_hidden
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface[]
   *   An array of instances keyed by ID.
   */
  public function loadLinksByRoute($route_name, array $route_parameters = array(), $include_hidden = FALSE);

  /**
   * Returns a rendered menu tree.
   *
   * The menu item's LI element is given one of the following classes:
   * - expanded: The menu item is showing its submenu.
   * - collapsed: The menu item has a submenu which is not shown.
   * - leaf: The menu item has no submenu.
   *
   * @param array $tree
   *   A data structure representing the tree as returned from menu_tree_data.
   *
   * @return array
   *   A structured array to be rendered by drupal_render().
   */
  public function renderTree($tree);

  /**
   * Gets the active trail IDs of the specified menu tree.
   *
   * @param string $menu_name
   *   The menu name of the requested tree.
   *
   * @return array
   *   An array containing the active trail: a list of plugin ids.
   */
  public function getActiveTrailIds($menu_name);

  /**
   * Gets the data structure for a named menu tree, based on the current page.
   *
   * Only visible links (hidden == 0) are returned in the data.
   *
   * @param string $menu_name
   *   The named menu links to return.
   * @param int $max_depth
   *   (optional) The maximum depth of links to retrieve.
   * @param bool $only_active_trail
   *   (optional) Whether to only return the links in the active trail (TRUE)
   *   instead of all links on every level of the menu link tree (FALSE).
   *   Defaults to FALSE.
   *
   * @return array
   *   An array of menu links, in the order they should be rendered. The array
   *   is a list of associative arrays -- these have several keys:
   *   - link: the menu link plugin instance
   *   - below: the subtree below the link, or empty array. It has the same
   *            structure as the top level array.
   *   - depth:
   *   - has_children: boolean. even if the below value may be empty the link
   *                   may have children in the tree that are not shown. This
   *                   is a hint for adding appropriate classes for theming.
   *   - in_active_trail: boolean
   */
  public function buildPageData($menu_name, $max_depth = NULL, $only_active_trail = FALSE);

  /**
   * Gets the data structure representing a named menu tree.
   *
   * Since this can be the full tree including hidden items, the data returned
   * may be used for generating an an admin interface or a select.
   *
   * @param string $menu_name
   *   The named menu links to return
   * @param array $link
   *   A fully loaded menu link, or NULL. If a link is supplied, only the
   *   path to root will be included in the returned tree - as if this link
   *   represented the current page in a visible menu.
   * @param int $max_depth
   *   Optional maximum depth of links to retrieve. Typically useful if only one
   *   or two levels of a sub tree are needed in conjunction with a non-NULL
   *   $link, in which case $max_depth should be greater than $link['depth'].
   *
   * @return array
   *   An tree of menu links in an array, in the order they should be rendered.
   */
  public function buildAllData($menu_name, $link = NULL, $max_depth = NULL);

  /**
   * Renders a menu tree based on the current path.
   *
   * @param string $menu_name
   *   The name of the menu.
   *
   * @return array
   *   A structured array representing the specified menu on the current page,
   *   to be rendered by drupal_render().
   */
  public function renderMenu($menu_name);

  /**
   * Builds a menu tree, translates links, and checks access.
   *
   * @param string $menu_name
   *   The name of the menu.
   * @param array $parameters
   *   (optional) An associative array of build parameters. Possible keys:
   *   - expanded: An array of parent link ids to return only menu links that
   *     are children of one of the ids in this list. If empty, the whole menu
   *     tree is built, unless 'only_active_trail' is TRUE.
   *   - active_trail: An array of ids, representing the coordinates of the
   *     currently active menu link.
   *   - only_active_trail: Whether to only return links that are in the active
   *     trail. This option is ignored, if 'expanded' is non-empty.
   *   - min_depth: The minimum depth of menu links in the resulting tree.
   *     Defaults to 1, which is the default to build a whole tree for a menu
   *     (excluding menu container itself).
   *   - max_depth: The maximum depth of menu links in the resulting tree.
   *   - conditions: An associative array of custom database select query
   *     condition key/value pairs; see _menu_build_tree() for the actual query.
   *
   * @return array
   *   A fully built menu tree.
   */
  public function buildTree($menu_name, array $parameters = array());

  /**
   * Returns a subtree starting with the passed in menu link plugin ID.
   *
   * @param string $id
   *   The menu link plugin ID.
   * @param int $max_relative_depth
   *   The maximum depth of child menu links relative to the passed in.
   *
   * @return array
   */
  public function buildSubtree($id, $max_relative_depth = NULL);

  /**
   * Loads all child links of a given menu link.
   *
   * @param string $id
   *   The menu link plugin ID.
   *
   * @param null $max_relative_depth
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface[]
   *   An array of child links keyed by ID.
   */
  public function getChildLinks($id, $max_relative_depth = NULL);

  /**
   * Fetches a menu link which matches the route name, parameters and menu name.
   * @param string $route_name
   *   (optional) The route name
   * @param array $route_parameters
   * @param null $selected_menu
   * @return mixed
   */
  public function menuLinkGetPreferred($route_name = NULL, array $route_parameters = array(), $selected_menu = NULL);

  /**
   * Adds a new link to the tree storage.
   *
   * Use this function in case you know there is no entry in the tree. This is
   * the case if you don't use plugin definition to fill in the tree.
   *
   * @param string $id
   *   The menu link plugin ID.
   * @param array $definition
   *   The values of the link.
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface
   *   The updated menu link instance.
   */
  public function createLink($id, array $definition);

  /**
   * Updates the values for a menu link in the tree storage.
   *
   * @param string $id
   *   The menu link plugin ID.
   * @param array $new_definition_values
   *   The new values for the link definition. This will usually be just a
   *   subset of the plugin definition.
   * @param bool $persist
   *   TRUE to also have the link instance itself persist the changed values
   *   to any additional storage.
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface
   *   The updated menu link instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the $id is not valid, existing, plugin ID.
   */
  public function updateLink($id, array $new_definition_values, $persist = TRUE);

  /**
   * Resets the values for a menu link based on the values found by discovery.
   *
   * @param string $id
   *   The menu link plugin ID.
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface
   *   The menu link instance after being reset.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the $id is not valid, existing, plugin ID or if the link cannot be
   *   reset.
   */
  public function resetLink($id);

  /**
   * Get a form instance for editing a menu link plugin.
   *
   * @TODO Use the class resolver at some point.
   *
   * @param \Drupal\Core\Menu\MenuLinkInterface $menu_link
   *   The menu link.
   *
   * @return \Drupal\Core\Menu\Form\MenuLinkFormInterface
   */
  public function getPluginForm(MenuLinkInterface $menu_link);

  /**
   * Get the options for a select element to choose and menu and parent.
   *
   * @param string $id
   *   Optional ID of a link plugin. This will exclude the link and its
   *   children from the select options.
   * @param array $menus
   *   Optional array of menu names as keys and titles as values to limit
   *   the select options.
   *
   * @return array
   *   Keyed array where the keys are contain a menu name and parent ID and
   *   the values are a menu name or link title indented by depth.
   */
  public function getParentSelectOptions($id = '', array $menus = array());

  /**
   * Get a list of menu names for use as options.
   *
   * @param array $menu_names
   *   Optional array of menu names to limit the options, or NULL to load all.
   *
   * @return array
   *   Keys are menu names (ids) values are the menu labels.
   */
  public function getMenuOptions(array $menu_names = NULL);

  public function menuNameExists($menu_name);

  public function getParentDepthLimit($id);

}
