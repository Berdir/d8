<?php

/**
 * @file
 * Contains \Drupal\menu_link_content\Entity\MenuLinkContentInterface.
 */

namespace Drupal\menu_link_content\Entity;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines an interface for custom menu links.
 */
interface MenuLinkContentInterface extends ContentEntityInterface {

  /**
   * Gets the title of the menu link.
   *
   * @return mixed
   */
  public function getTitle();

  /**
   * Gets the route name of the custom menu link.
   *
   * @return string|NULL
   *   Returns the route name, unless it is an internal link.
   */
  public function getRouteName();

  /**
   * Gets the route parameters of the custom menu link.
   *
   * @return array
   */
  public function getRouteParameters();

  /**
   * Sets the route paramters of the custom menu link.
   *
   * @param array $route_parameters
   *   The route parameters
   *
   * @return $this
   */
  public function setRouteParameters(array $route_parameters);

  /**
   * Gets the external URL.
   *
   * @return string|NULL
   *   Returns the external URL if the menu link points to an external URL,
   *   otherwise NULL.
   */
  public function getUrl();

  /**
   * Gets the url object pointing to the URL of the custom menu link.
   *
   * @return \Drupal\Core\Url
   */
  public function getUrlObject();

  /**
   * Gets the menu name of the custom menu link.
   *
   * @return string
   *   The menu ID.
   */
  public function getMenuName();

  /**
   * Gets the query options of the custom menu link.
   *
   * @return array
   */
  public function getOptions();

  public function setOptions(array $options);

  /**
   * Gets the description of the custom menu link for the UI.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Gets the menu plugin ID associated with this entity.
   *
   * @return string
   */
  public function getPluginId();

  /**
   * Returns whether the menu link is marked as hidden.
   *
   * @return bool
   *   TRUE if is not enabled, otherwise FALSE.
   */
  public function isHidden();

  /**
   * Returns whether the menu link is marked as always expanded.
   *
   * @return bool
   */
  public function isExpanded();

  /**
   * Gets the menu plugin ID of the parent menu link.
   *
   * @return string
   */
  public function getParentId();

  /**
   * Returns the weight of the custom menu link.
   *
   * @return int
   */
  public function getWeight();

}
