<?php

/**
 * @file
 * Contains \Drupal\menu_link\Plugin\Core\Entity\MenuLink.
 */

namespace Drupal\menu_link\Plugin\Core\Entity;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityNG;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Language\Language;
use Drupal\menu_link\MenuLinkInterface;
use Drupal\menu_link\MenuLinkStorageControllerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the menu link entity class.
 *
 * @EntityType(
 *   id = "menu_link",
 *   label = @Translation("Menu link"),
 *   module = "menu_link",
 *   controllers = {
 *     "storage" = "Drupal\menu_link\MenuLinkStorageController",
 *     "render" = "Drupal\Core\Entity\EntityRenderController",
 *     "form" = {
 *       "default" = "Drupal\menu_link\MenuLinkFormController"
 *     }
 *   },
 *   static_cache = FALSE,
 *   base_table = "menu_links",
 *   uri_callback = "menu_link_uri",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "mlid",
 *     "label" = "link_title",
 *     "uuid" = "uuid",
 *     "bundle" = "bundle"
 *   },
 *   bundle_keys = {
 *     "bundle" = "bundle"
 *   }
 * )
 */
class MenuLink extends EntityNG implements \ArrayAccess, MenuLinkInterface {

  /**
   * The link's menu name.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $menu_name;

  /**
   * The link's bundle.
   *
   * @var string
   */
  public $bundle = 'tools';

  /**
   * The menu link ID.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $mlid;

  /**
   * The menu link UUID.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $uuid;

  /**
   * The parent link ID.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
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
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $router_path;

  /**
   * The entity label.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $link_title;

  /**
   * A serialized array of options to be passed to the url() or l() function,
   * such as a query string or HTML attributes.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $options;

  /**
   * The name of the module that generated this link.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $module;

  /**
   * A flag for whether the link should be rendered in menus.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $hidden;

  /**
   * A flag to indicate if the link points to a full URL starting with a
   * protocol, like http:// (1 = external, 0 = internal).
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $external;

  /**
   * Flag indicating whether any links have this link as a parent.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $has_children;

  /**
   * Flag for whether this link should be rendered as expanded in menus.
   * Expanded links always have their child links displayed, instead of only
   * when the link is in the active trail.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $expanded;

  /**
   * Link weight among links in the same menu at the same depth.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $weight;

  /**
   * The depth relative to the top level. A link with plid == 0 will have
   * depth == 1.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $depth;

  /**
   * A flag to indicate that the user has manually created or edited the link.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $customized;

  /**
   * The first entity ID in the materialized path.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   *
   * @todo Investigate whether the p1, p2, .. pX properties can be moved to a
   * single array property.
   */
  public $p1;

  /**
   * The second entity ID in the materialized path.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $p2;

  /**
   * The third entity ID in the materialized path.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $p3;

  /**
   * The fourth entity ID in the materialized path.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $p4;

  /**
   * The fifth entity ID in the materialized path.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $p5;

  /**
   * The sixth entity ID in the materialized path.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $p6;

  /**
   * The seventh entity ID in the materialized path.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $p7;

  /**
   * The eighth entity ID in the materialized path.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $p8;

  /**
   * The ninth entity ID in the materialized path.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $p9;

  /**
   * The menu link modification timestamp.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $updated;

  /**
   * The name of the route associated with this menu link, if any.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $route_name;

  /**
   * Default values for the menu link.
   *
   * @var array
   */
  protected $values = array(
    'langcode' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => Language::LANGCODE_NOT_SPECIFIED))),
    'menu_name' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => 'tools'))),
    'link_title' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => ''))),
    'options' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => array()))),
    'module' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => 'menu'))),
    'hidden' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => 0))),
    'has_children' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => 0))),
    'expanded' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => 0))),
    'weight' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => 0))),
    'customized' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => 0))),
    'updated' => array(Language::LANGCODE_DEFAULT => array(0 => array('value' => 0))),
  );

  /**
   * The route object associated with this menu link, if any.
   *
   * @var \Symfony\Component\Routing\Route
   */
  protected $routeObject;

  /**
   * Crap coming from the old routing system.
   *
   * @todo Remove when we rip out the old routing system.
   *
   * @var array
   */
  protected $oldRouterItem = array();

  /**
   * Properties of the old routing system.
   *
   * @todo Remove when we rip out the old routing system.
   *
   * @var array
   */
  protected $oldRoutingProperties = array(
    'path', 'load_functions', 'to_arg_functions', 'access_callback',
    'access_arguments', 'page_callback', 'page_arguments', 'fit',
    'number_parts', 'context', 'tab_parent', 'tab_root', 'title',
    'title_callback', 'title_arguments', 'theme_callback', 'theme_arguments',
    'type', 'description', 'description_callback', 'description_arguments',
    'position', 'include_file', 'route_name',
  );

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->get('mlid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function bundle() {
    return $this->bundle;
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    parent::init();
    // We unset all defined properties, so magic getters apply.
    unset($this->menu_name);
    unset($this->mlid);
    unset($this->uuid);
    unset($this->plid);
    unset($this->link_path);
    unset($this->router_path);
    unset($this->link_title);
    unset($this->options);
    unset($this->module);
    unset($this->hidden);
    unset($this->external);
    unset($this->has_children);
    unset($this->expanded);
    unset($this->weight);
    unset($this->depth);
    unset($this->customized);
    unset($this->p1);
    unset($this->p2);
    unset($this->p3);
    unset($this->p4);
    unset($this->p5);
    unset($this->p6);
    unset($this->p7);
    unset($this->p8);
    unset($this->p9);
    unset($this->updated);
    unset($this->route_name);
  }

  /**
   * Overrides Entity::createDuplicate().
   */
  public function createDuplicate() {
    $duplicate = parent::createDuplicate();
    $duplicate->get('plid')->offsetGet(0)->set('value', NULL);
    return $duplicate;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoute() {
    if (!$this->route_name->value) {
      return NULL;
    }
    if (!($this->routeObject instanceof Route)) {
      $route_provider = \Drupal::service('router.route_provider');
      $this->routeObject = $route_provider->getRouteByName($this->route_name->value);
    }
    return $this->routeObject;
  }

  /**
   * {@inheritdoc}
   */
  public function setRouteObject(Route $route) {
    $this->routeObject = $route;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    // To reset the link to its original values, we need to retrieve its
    // definition from hook_menu(). Otherwise, for example, the link's menu
    // would not be reset, because properties like the original 'menu_name' are
    // not stored anywhere else. Since resetting a link happens rarely and this
    // is a one-time operation, retrieving the full menu router does no harm.
    $menu = menu_get_router();
    $router_item = $menu[$this->router_path->value];
    $new_link = self::buildFromRouterItem($router_item);
    // Merge existing menu link's ID and 'has_children' property.
    foreach (array('mlid', 'has_children') as $key) {
      $new_link->{$key}->value = $this->{$key}->value;
    }
    $new_link->save();
    return $new_link;
  }

  /**
   * {@inheritdoc}
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
    return \Drupal::entityManager()
      ->getStorageController('menu_link')->create($item);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetExists($offset) {
    if (in_array($offset, $this->oldRoutingProperties)) {
      return isset($this->oldRouterItem[$offset]);
    }

    return isset($this->{$offset}->value);
//    return isset($this->{$offset});
  }

  /**
   * {@inheritdoc}
   */
  public function offsetGet($offset) {
    if (in_array($offset, $this->oldRoutingProperties)) {
      return $this->oldRouterItem[$offset];
    }
    elseif ($offset == 'localized_options' || $offset == 'options') {
      return $this->{$offset}[0];
    }
    elseif ($this->getPropertyDefinition($offset)) {
      return $this->{$offset}->value;
    }
    else {
      return $this->{$offset};
    }
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet($offset, $value) {
    if (in_array($offset, $this->oldRoutingProperties)) {
      $this->oldRouterItem[$offset] = $value;
    }
    elseif ($this->getPropertyDefinition($offset)) {
      $this->{$offset}->value = $value;
    }
    else {
      $this->{$offset} = $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($offset) {
    if (in_array($offset, $this->oldRoutingProperties)) {
      unset($this->oldRouterItem[$offset]);
    }
    else {
      $this->{$offset}->value = NULL;
    }
//    unset($this->{$offset});
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageControllerInterface $storage_controller, array &$values) {
    if (empty($values['menu_name'])) {
      $values['menu_name'] = $values['bundle'] = 'tools';
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageControllerInterface $storage_controller, array $entities) {
    // Nothing to do if we don't want to reparent children.
    if ($storage_controller->getPreventReparenting()) {
      return;
    }

    foreach ($entities as $entity) {
      // Children get re-attached to the item's parent.
      if ($entity->has_children->value) {
        $children = $storage_controller->loadByProperties(array('plid' => $entity->plid->target_id));
        foreach ($children as $child) {
          $child->plid->target_id = $entity->plid->target_id;
          $storage_controller->save($child);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageControllerInterface $storage_controller, array $entities) {
    $affected_menus = array();
    // Update the has_children status of the parent.
    foreach ($entities as $entity) {
      if (!$storage_controller->getPreventReparenting()) {
        $storage_controller->updateParentalStatus($entity);
      }

      // Store all menu names for which we need to clear the cache.
      if (!isset($affected_menus[$entity->menu_name->value])) {
        $affected_menus[$entity->menu_name->value] = $entity->menu_name->value;
      }
    }

    foreach ($affected_menus as $menu_name) {
      menu_cache_clear($menu_name);
    }
    _menu_clear_page_cache();
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    // This is the easiest way to handle the unique internal path '<front>',
    // since a path marked as external does not need to match a router path.
    $this->external->value = (url_is_external($this->link_path->value) || $this->link_path->value == '<front>') ? 1 : 0;

    // Try to find a parent link. If found, assign it and derive its menu.
    $parent_candidates = !empty($this->parentCandidates) ? $this->parentCandidates : array();
    $parent = $this->findParent($storage_controller, $parent_candidates);
    if ($parent) {
      $this->plid->target_id = $parent->id();
      $this->menu_name->value = $parent->menu_name->value;
    }
    // If no corresponding parent link was found, move the link to the top-level.
    else {
      $this->plid->target_id = 0;
    }

    // Directly fill parents for top-level links.
    if ($this->plid->target_id == 0) {
      $this->p1->value = $this->id();
      for ($i = 2; $i <= MENU_MAX_DEPTH; $i++) {
        $parent_property = "p$i";
        $this->{$parent_property}->value = 0;
      }
      $this->depth->value = 1;
    }
    // Otherwise, ensure that this link's depth is not beyond the maximum depth
    // and fill parents based on the parent link.
    else {
      if ($this->has_children->value && $this->original) {
        $limit = MENU_MAX_DEPTH - $storage_controller->findChildrenRelativeDepth($this->original) - 1;
      }
      else {
        $limit = MENU_MAX_DEPTH - 1;
      }
      if ($parent->depth->value > $limit) {
        return FALSE;
      }
      $this->depth->value = $parent->depth->value + 1;
      $this->setParents($parent);
    }

    // Need to check both plid and menu_name, since plid can be 0 in any menu.
    if (isset($this->original) && ($this->plid->target_id != $this->original->plid->target_id || $this->menu_name->value != $this->original->menu_name->value)) {
      $storage_controller->moveChildren($this, $this->original);
    }
    // Find the router_path.
    if (empty($this->router_path->value) || empty($this->original) || (isset($this->original) && $this->original->link_path->value != $this->link_path->value)) {
      if ($this->external->value) {
        $this->router_path->value = '';
      }
      else {
        // Find the router path which will serve this path.
        // @todo Where do we need 'parts'?
        $this->parts = explode('/', $this->link_path->value, MENU_MAX_PARTS);
        $this->router_path->value = _menu_find_router_path($this->link_path->value);
      }
    }
    // Find the route_name.
    if (!isset($this->route_name->value)) {
      $this->route_name->value = $this::findRouteName($this->link_path->value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageControllerInterface $storage_controller, $update = TRUE) {
    // Check the has_children status of the parent.
    $storage_controller->updateParentalStatus($this);

    menu_cache_clear($this->menu_name->value);
    if (isset($this->original) && $this->menu_name->value != $this->original->menu_name->value) {
      menu_cache_clear($this->original->menu_name->value);
    }

    // Now clear the cache.
    _menu_clear_page_cache();
  }

  /**
   * {@inheritdoc}
   */
  public static function findRouteName($link_path) {
    // Look up the route_name used for the given path.
    $request = Request::create('/' . $link_path);
    $request->attributes->set('_system_path', $link_path);
    try {
      // Use router.dynamic instead of router, because router will call the
      // legacy router which will call hook_menu() and you will get back to
      // this method.
      $result = \Drupal::service('router.dynamic')->matchRequest($request);
      return isset($result['_route']) ? $result['_route'] : '';
    }
    catch (\Exception $e) {
      return '';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setParents(EntityInterface $parent) {
    $i = 1;
    while ($i < $this->depth->value) {
      $p = 'p' . $i++;
      $this->{$p}->value = $parent->{$p}->value;
    }
    $p = 'p' . $i++;
    // The parent (p1 - p9) corresponding to the depth always equals the mlid.
    $this->{$p}->value = $this->id();
    while ($i <= MENU_MAX_DEPTH) {
      $p = 'p' . $i++;
      $this->{$p}->value = 0;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function findParent(MenuLinkStorageControllerInterface $storage_controller, array $parent_candidates = array()) {
    $parent = FALSE;

    // This item is explicitely top-level, skip the rest of the parenting.
    if (isset($this->plid->target_id) && empty($this->plid->target_id)) {
      return $parent;
    }

    // If we have a parent link ID, try to use that.
    $candidates = array();
    if (isset($this->plid->target_id)) {
      $candidates[] = $this->plid->target_id;
    }

    // Else, if we have a link hierarchy try to find a valid parent in there.
    if (!empty($this->depth->value) && $this->depth->value > 1) {
      for ($depth = $this->depth->value - 1; $depth >= 1; $depth--) {
        $parent_property = "p$depth";
        $candidates[] = $this->$parent_property->value;
      }
    }

    foreach ($candidates as $mlid) {
      if (isset($parent_candidates[$mlid])) {
        $parent = $parent_candidates[$mlid];
      }
      else {
        $parent = $storage_controller->load($mlid);
      }
      if ($parent) {
        return $parent;
      }
    }

    // If everything else failed, try to derive the parent from the path
    // hierarchy. This only makes sense for links derived from menu router
    // items (ie. from hook_menu()).
    if ($this->module->value == 'system') {
      $parent = $storage_controller->getParentFromHierarchy($this);
    }

    return $parent;
  }

}
