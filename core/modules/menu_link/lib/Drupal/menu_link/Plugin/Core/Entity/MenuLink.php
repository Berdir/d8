<?php

/**
 * @file
 * Definition of Drupal\menu_link\Plugin\Core\Entity\MenuLink.
 */

namespace Drupal\menu_link\Plugin\Core\Entity;

use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity;

/**
 * Defines the menu link entity class.
 *
 * @Plugin(
 *   id = "menu_link",
 *   label = @Translation("Menu link"),
 *   module = "menu_link",
 *   controller_class = "Drupal\menu_link\MenuLinkStorageController",
 *   form_controller_class = {
 *     "default" = "Drupal\menu_link\MenuLinkFormController"
 *   },
 *   static_cache = FALSE,
 *   base_table = "menu_links",
 *   uri_callback = "menu_link_uri",
 *   entity_keys = {
 *     "id" = "mlid",
 *     "label" = "link_title",
 *     "uuid" = "uuid"
 *   },
 *   bundles = {
 *     "menu_link" = {
 *       "label" = "Menu link",
 *     }
 *   }
 * )
 */
class MenuLink extends Entity implements \ArrayAccess, ContentEntityInterface {

  /**
   * The link's menu name.
   *
   * @var string
   */
  public $menu_name = 'tools';

  /**
   * The menu link ID.
   *
   * @var integer
   */
  public $mlid;

  /**
   * The menu link UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * The parent link ID.
   *
   * @var integer
   */
  public $plid;

  /**
   * The Drupal path or external path this link points to.
   *
   * @var string
   */
  public $link_path;

  /**
   * For links corresponding to a Drupal path (external = 0), this connects the
   * link to a {menu_router}.path for joins.
   *
   * @var string
   */
  public $router_path;

  /**
   * @var string
   */
  public $link_title = '';

  /**
   * @var array
   */
  public $options = array();

  /**
   * @var string
   */
  public $module = 'menu';

  /**
   * @var integer
   */
  public $hidden = 0;

  /**
   * @var integer
   */
  public $external;

  /**
   * @var integer
   */
  public $has_children = 0;

  /**
   * @var integer
   */
  public $expanded = 0;

  /**
   * @var integer
   */
  public $weight = 0;

  /**
   * @var integer
   */
  public $depth;

  /**
   * @var integer
   */
  public $customized = 0;

  /**
   * @var integer
   *
   * @todo Investigate whether the p1, p2, .. pX properties can be moved to a
   * single array property.
   */
  public $p1;

  /**
   * @var integer
   */
  public $p2;

  /**
   * @var integer
   */
  public $p3;

  /**
   * @var integer
   */
  public $p4;

  /**
   * @var integer
   */
  public $p5;

  /**
   * @var integer
   */
  public $p6;

  /**
   * @var integer
   */
  public $p7;

  /**
   * @var integer
   */
  public $p8;

  /**
   * @var integer
   */
  public $p9;

  /**
   * The menu link modification timestamp.
   *
   * @var integer
   */
  public $updated = 0;

  /**
   * Overrides Drupal\Entity\Entity::id().
   */
  public function id() {
    return $this->mlid;
  }

  /**
   * Overrides Drupal\entity\Entity::createDuplicate().
   */
  public function createDuplicate() {
    $duplicate = parent::createDuplicate();
    $duplicate->plid = NULL;
    return $duplicate;
  }

  /**
   * Resets a system-defined menu link.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A menu link entity.
   */
  public function reset() {
    // To reset the link to its original values, we need to retrieve its
    // definition from hook_menu(). Otherwise, for example, the link's menu
    // would not be reset, because properties like the original 'menu_name' are
    // not stored anywhere else. Since resetting a link happens rarely and this
    // is a one-time operation, retrieving the full menu router does no harm.
    $menu = menu_get_router();
    $router_item = $menu[$this->router_path];
    $new_link = self::buildFromRouterItem($router_item);
    // Merge existing menu link's ID and 'has_children' property.
    foreach (array('mlid', 'has_children') as $key) {
      $new_link->{$key} = $this->{$key};
    }
    $new_link->save();
    return $new_link;
  }

  /**
   * Builds a menu link entity from a router item.
   *
   * @param array $item
   *   A menu router item.
   *
   * @return MenuLink
   *   A menu link entity.
   */
  public static function buildFromRouterItem(array $item) {
    // Suggested items are disabled by default.
    if ($item['type'] == MENU_SUGGESTED_ITEM) {
      $item['hidden'] = 1;
    }
    // Hide all items that are not visible in the tree.
    elseif (!($item['type'] & MENU_VISIBLE_IN_TREE)) {
      $item['hidden'] = -1;
    }
    // Note, we set this as 'system', so that we can be sure to distinguish all
    // the menu links generated automatically from entries in {menu_router}.
    $item['module'] = 'system';
    $item += array(
      'link_title' => $item['title'],
      'link_path' => $item['path'],
      'options' => empty($item['description']) ? array() : array('attributes' => array('title' => $item['description'])),
    );
    return entity_get_controller('menu_link')->create($item);
  }

  /**
   * Implements ArrayAccess::offsetExists().
   */
  public function offsetExists($offset) {
    return isset($this->{$offset});
  }

  /**
   * Implements ArrayAccess::offsetGet().
   */
  public function &offsetGet($offset) {
    return $this->{$offset};
  }

  /**
   * Implements ArrayAccess::offsetSet().
   */
  public function offsetSet($offset, $value) {
    $this->{$offset} = $value;
  }

  /**
   * Implements ArrayAccess::offsetUnset().
   */
  public function offsetUnset($offset) {
    unset($this->{$offset});
  }
}
