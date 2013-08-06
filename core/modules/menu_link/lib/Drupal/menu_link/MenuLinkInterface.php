<?php

/**
 * @file
 * Contains \Drupal\menu_link\Plugin\Core\Entity\MenuLinkInterface.
 */

namespace Drupal\menu_link;

use Symfony\Component\Routing\Route;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an interface defining a menu link entity.
 */
interface MenuLinkInterface extends ContentEntityInterface {

  /**
   * Returns the Route object associated with this link, if any.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The route object for this menu link, or NULL if there isn't one.
   */
  public function getRoute();

  /**
   * Sets the route object for this link.
   *
   * This should only be called by MenuLinkStorageController when loading
   * the link object. Calling it at other times could result in unpredictable
   * behavior.
   *
   * @param \Symfony\Component\Routing\Route $route
   */
  public function setRouteObject(Route $route);

  /**
   * Resets a system-defined menu link.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A menu link entity.
   */
  public function reset();

  /**
   * Builds a menu link entity from a router item.
   *
   * @param array $item
   *   A menu router item.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   A menu link entity.
   */
  public static function buildFromRouterItem(array $item);

  /**
   * Returns the route_name matching a URL.
   *
   * @param string $link_path
   *   The link path to find a route name for.
   *
   * @return string
   *   The route name.
   */
  public static function findRouteName($link_path);

  /**
   * Sets the p1 through p9 properties for a menu link entity being saved.
   *
   * @param \Drupal\Core\Entity\EntityInterface $parent
   *   A menu link entity.
   */
  public function setParents(EntityInterface $parent);

  /**
   * Finds a possible parent for a given menu link entity.
   *
   * Because the parent of a given link might not exist anymore in the database,
   * we apply a set of heuristics to determine a proper parent:
   *
   *  - use the passed parent link if specified and existing.
   *  - else, use the first existing link down the previous link hierarchy
   *  - else, for system menu links (derived from hook_menu()), reparent
   *    based on the path hierarchy.
   *
   * @param \Drupal\menu_link\MenuLinkStorageControllerInterface $storage_controller
   *   Storage controller object.
   * @param array $parent_candidates
   *   An array of menu link entities keyed by mlid.
   *
   * @return \Drupal\Core\Entity\EntityInterface|false
   *   A menu link entity structure of the possible parent or FALSE if no valid
   *   parent has been found.
   */
  public function findParent(MenuLinkStorageControllerInterface $storage_controller, array $parent_candidates = array());

  /**
   * Returns the menu name of this menu link.
   *
   * @return string
   *   The name of the menu.
   */
  public function getMenuName();

  /**
   * Sets the menu name of this menu link.
   *
   * @param string $menu_name
   *   The name of the menu.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setMenuName($menu_name);

  /**
   * Returns the parent menu link ID of this menu link.
   *
   * @return int
   *   The parent link ID of the menu.
   */
  public function getParentLinkId();

  /**
   * Sets the parent menu link id of this menu link.
   *
   * @param int $plid
   *   The parent link ID of the menu.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setParentLinkId($plid);

  /**
   * Returns the Drupal path or external path this link points to.
   *
   * @return string
   *   The Drupal path or external path this link points to.
   */
  public function getLinkPath();

  /**
   * Sets the Drupal path or external path this link points to.
   *
   * @param string $link_path
   *   The the Drupal path or external path this link points to.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setLinkPath($link_path);

  /**
   * Returns the title of this menu link.
   *
   * @return string
   *   The title of this menu link.
   */
  public function getLinkTitle();

  /**
   * Sets the title of this menu link.
   *
   * @param string $link_title
   *   The title of this menu link.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setLinkTitle($link_title);

  /**
   * Returns the menu router path for this link.
   *
   * @return string
   *   The menu router path for this link.
   */
  public function getRouterPath();

  /**
   * Sets the Drupal path or external path this link points to.
   *
   * @param string $router_path
   *   The menu router path for this link.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setRouterPath($router_path);

  /**
   * Returns the menu link options.
   *
   * @return array
   *   The menu link options, to be passed to to l() or url().
   */
  public function getOptions();

  /**
   * Sets the menu link options.
   *
   * @todo: Add a method to add options?
   *
   * @param array $options
   *   The menu link options.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setOptions(array $options);

  /**
   * Returns the name of the module that generated this link.
   *
   * @return string
   *   The name of the module that generated this link.
   */
  public function getModule();

  /**
   * Sets the name of the module that generated this link.
   *
   * @param string $module
   *   The name of the module that generated this link.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setModule($module);

  /**
   * Sets whether the menu link is expanded.
   *
   * @param bool $expanded
   *   TRUE if the menu link is expanded, FALSE if not.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setExpanded($expanded);

  /**
   * Returns whether the menu link is expanded
   *
   * @return string
   *   TRUE if the menu link is expanded, FALSE if not.
   */
  public function isExpanded();

  /**
   * Sets whether the menu link is external.
   *
   * @param bool $external
   *   TRUE if the menu link is external, FALSE if not.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setExternal($external);

  /**
   * Returns whether the menu link is external.
   *
   * @return string
   *   TRUE if the menu link is external, FALSE if not.
   */
  public function isExternal();

  /**
   * Sets if the menu link is customized.
   *
   * @param bool $customized
   *   TRUE if the menu link is customized, FALSE if not.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setCustomized($customized);

  /**
   * Returns whether the menu link is customized.
   *
   * @return string
   *   TRUE if the menu link is customized, FALSE if not.
   */
  public function isCustomized();

  /**
   * Sets whether the menu link is hidden.
   *
   * @param bool $module
   *   TRUE if the menu link is hidden, FALSE if not.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setHidden($hidden);

  /**
   * Returns whether the menu link is hidden.
   *
   * @return string
   *   TRUE if the menu link is hidden, FALSE if not.
   */
  public function isHidden();

  /**
   * Sets whether the menu link has children.
   *
   * @param bool $module
   *   TRUE if the menu link has children, FALSE if not.
   *
   * @return \Drupal\menu_link\MenuLinkInterface
   *   The called menu link entity.
   */
  public function setHasChildren($has_children);

  /**
   * Returns whether the menu link is expanded
   *
   * @return string
   *   TRUE if the menu link has children, FALSE if not.
   */
  public function hasChildren();

  /**
   * @param \Drupal\Core\Entity\Field\FieldInterface $depth
   */
  public function setDepth($depth);

  /**
   * @return \Drupal\Core\Entity\Field\FieldInterface
   */
  public function getDepth();

  /**
   * @param \Drupal\Core\Entity\Field\FieldInterface $weight
   */
  public function setWeight($weight);

  /**
   * @return \Drupal\Core\Entity\Field\FieldInterface
   */
  public function getWeight();

}
