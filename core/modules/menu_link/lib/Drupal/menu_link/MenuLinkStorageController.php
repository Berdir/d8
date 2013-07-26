<?php

/**
 * @file
 * Contains \Drupal\menu_link\MenuLinkStorageController.
 */

namespace Drupal\menu_link;

use Drupal\Core\Entity\DatabaseStorageControllerNG;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;

/**
 * Defines the storage controller class for menu links.
 */
class MenuLinkStorageController extends DatabaseStorageControllerNG implements MenuLinkStorageControllerInterface {

  /**
   * Indicates whether the delete operation should re-parent children items.
   *
   * @var bool
   */
  protected $preventReparenting = FALSE;

  /**
   * Holds an array of router item schema fields.
   *
   * @var array
   */
  protected static $routerItemFields = array();

  /**
   * The route provider service.
   *
   * @var \Symfony\Cmf\Component\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * {@inheritdoc}
   */
  public function __construct($entity_type, array $entity_info, Connection $database, RouteProviderInterface $route_provider) {
    parent::__construct($entity_type, $entity_info, $database);

    $this->routeProvider = $route_provider;

    if (empty(static::$routerItemFields)) {
      static::$routerItemFields = array_diff(drupal_schema_fields_sql('menu_router'), array('weight'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $values) {
    // The bundle of menu links being the menu name is not enforced but is the
    // default behavior if no bundle is set.
    if (!isset($values['bundle']) && isset($values['menu_name'])) {
      $values['bundle'] = $values['menu_name'];
    }
    return parent::create($values);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, $entity_type, array $entity_info) {
    return new static(
      $entity_type,
      $entity_info,
      $container->get('database'),
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $revision_id);
    // Specify additional fields from the {menu_router} table.
    $query->leftJoin('menu_router', 'm', 'base.router_path = m.path');
    $query->fields('m', static::$routerItemFields);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function attachLoad(&$queried_entities, $load_revision = FALSE) {
    $routes = array();

    foreach ($queried_entities as &$record) {
      $record->options = unserialize($record->options);

      // Use the weight property from the menu link.
      $record->router_item['weight'] = $record->weight;

      // By default use the menu_name as type.
      $record->bundle = $record->menu_name;

      // For all links that have an associated route, load the route object now
      // and save it on the object. That way we avoid a select N+1 problem later.
      if ($record->route_name) {
        $routes[$record->{$this->idKey}] = $record->route_name;
      }
    }

    parent::attachLoad($queried_entities, $load_revision);

    // Now mass-load any routes needed and associate them.
    if ($routes) {
      $route_objects = $this->routeProvider->getRoutesByNames($routes);
      foreach ($routes as $entity_id => $route) {
        // Not all stored routes will be valid on load.
        if (isset($route_objects[$route])) {
          $queried_entities[$entity_id]->setRouteObject($route_objects[$route]);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function mapFromStorageRecords(array $records, $load_revision = FALSE) {
    $entities = parent::mapFromStorageRecords($records, $load_revision);

    foreach ($entities as &$entity) {
      foreach (static::$routerItemFields as $router_field) {
        $entity->offsetSet($router_field, $records[$entity->id()]->{$router_field});
      }
    }

    return $entities;
  }

  /**
   * Overrides DatabaseStorageController::save().
   */
  public function save(EntityInterface $entity) {
    // We return SAVED_UPDATED by default because the logic below might not
    // update the entity if its values haven't changed, so returning FALSE
    // would be confusing in that situation.
    $return = SAVED_UPDATED;

    $transaction = $this->database->startTransaction();
    try {
      // Ensure we are dealing with the actual entity.
      $entity = $entity->getNGEntity();

      // Sync the changes made in the fields array to the internal values array.
      $entity->updateOriginalValues();

      // Load the stored entity, if any.
      if (!$entity->isNew() && !isset($entity->original)) {
        $entity->original = entity_load_unchanged($this->entityType, $entity->id());
      }

      if ($entity->isNew()) {
        $entity->mlid->value = $this->database->insert($this->entityInfo['base_table'])->fields(array('menu_name' => 'tools'))->execute();
        $entity->enforceIsNew();
      }

      // Unlike the save() method from DatabaseStorageController, we invoke the
      // 'presave' hook first because we want to allow modules to alter the
      // entity before all the logic from our preSave() method.
      $this->invokeFieldMethod('preSave', $entity);
      $this->invokeHook('presave', $entity);
      $entity->preSave($this);

      // If every value in $entity->original is the same in the $entity, there
      // is no reason to run the update queries or clear the caches. We use
      // array_intersect_key() with the $entity as the first parameter because
      // $entity may have additional keys left over from building a router entry.
      // The intersect removes the extra keys, allowing a meaningful comparison.
      if ($entity->isNew() || (array_intersect_key($entity->getPropertyValues(), $entity->original->getPropertyValues()) != $entity->original->getPropertyValues())) {
        // Create the storage record to be saved.
        $record = $this->mapToStorageRecord($entity);
        $return = drupal_write_record($this->entityInfo['base_table'], $record, $this->idKey);

        if ($return) {
          if (!$entity->isNew()) {
            // @todo, should a different value be returned when saving an entity
            // with $isDefaultRevision = FALSE?
            if (!$entity->isDefaultRevision()) {
              $return = FALSE;
            }

            if ($this->revisionKey) {
              $record->{$this->revisionKey} = $this->saveRevision($entity);
            }
            if ($this->dataTable) {
              $this->savePropertyData($entity);
            }
            $this->resetCache(array($entity->id()));
            $entity->postSave($this, TRUE);
            $this->invokeFieldMethod('update', $entity);
            $this->invokeHook('update', $entity);
            if ($this->dataTable) {
              $this->invokeTranslationHooks($entity);
            }
          }
          else {
            $return = SAVED_NEW;
            if ($this->revisionKey) {
              $record->{$this->revisionKey} = $this->saveRevision($entity);
            }
            $entity->{$this->idKey}->value = $record->{$this->idKey};
            if ($this->dataTable) {
              $this->savePropertyData($entity);
            }

            // Reset general caches, but keep caches specific to certain entities.
            $this->resetCache(array());

            $entity->enforceIsNew(FALSE);
            $entity->postSave($this, FALSE);
            $this->invokeFieldMethod('insert', $entity);
            $this->invokeHook('insert', $entity);
          }
        }
      }

      // Ignore slave server temporarily.
      db_ignore_slave();
      unset($entity->original);

      return $return;
    }
    catch (\Exception $e) {
      $transaction->rollback();
      watchdog_exception($this->entityType, $e);
      throw new EntityStorageException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPreventReparenting($value = FALSE) {
    $this->preventReparenting = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreventReparenting() {
    return $this->preventReparenting;
  }

  /**
   * {@inheritdoc}
   */
  public function loadUpdatedCustomized(array $router_paths) {
    $menu_links = array();

    // @todo This doesn't really make sense anymore with EntityNG.. and EFQ got
    // OR condition support in the meantime, so convert this query.
    $query = parent::buildQuery(NULL);
    $query
      ->condition(db_or()
      ->condition('updated', 1)
      ->condition(db_and()
        ->condition('router_path', $router_paths, 'NOT IN')
        ->condition('external', 0)
        ->condition('customized', 1)
        )
      );

    if ($ids = $query->execute()->fetchCol(1)) {
      $menu_links = $this->load($ids);
    }

    return $menu_links;
  }

  /**
   * {@inheritdoc}
   */
  public function loadModuleAdminTasks() {
    $query = $this->buildQuery(NULL);
    $query
      ->condition('base.link_path', 'admin/%', 'LIKE')
      ->condition('base.hidden', 0, '>=')
      ->condition('base.module', 'system')
      ->condition('m.number_parts', 1, '>')
      ->condition('m.page_callback', 'system_admin_menu_block_page', '<>');
    $ids = $query->execute()->fetchCol(1);

    return $this->loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function updateParentalStatus(EntityInterface $entity, $exclude = FALSE) {
    // If plid == 0, there is nothing to update.
    if ($entity->plid->target_id) {
      // Check if at least one visible child exists in the table.
      $query = \Drupal::entityQuery($this->entityType);
      $query
        ->condition('menu_name', $entity->menu_name->value)
        ->condition('hidden', 0)
        ->condition('plid', $entity->plid->target_id)
        ->count();

      if ($exclude) {
        $query->condition('mlid', $entity->id(), '<>');
      }

      $parent_has_children = ((bool) $query->execute()) ? 1 : 0;
      $this->database->update('menu_links')
        ->fields(array('has_children' => $parent_has_children))
        ->condition('mlid', $entity->plid->target_id)
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function findChildrenRelativeDepth(EntityInterface $entity) {
    // @todo Since all we need is a specific field from the base table, does it
    // make sense to convert to EFQ?
    $query = $this->database->select('menu_links');
    $query->addField('menu_links', 'depth');
    $query->condition('menu_name', $entity->menu_name->value);
    $query->orderBy('depth', 'DESC');
    $query->range(0, 1);

    $i = 1;
    $p = 'p1';
    while ($i <= MENU_MAX_DEPTH && $entity->{$p}->value) {
      $query->condition($p, $entity->{$p}->value);
      $p = 'p' . ++$i;
    }

    $max_depth = $query->execute()->fetchField();

    return ($max_depth > $entity->depth->value) ? $max_depth - $entity->depth->value : 0;
  }

  /**
   * {@inheritdoc}
   */
  public function moveChildren(EntityInterface $entity) {
    $query = $this->database->update($this->entityInfo['base_table']);

    $query->fields(array('menu_name' => $entity->menu_name->value));

    $p = 'p1';
    $expressions = array();
    for ($i = 1; $i <= $entity->depth->value; $p = 'p' . ++$i) {
      $expressions[] = array($p, ":p_$i", array(":p_$i" => $entity->{$p}->value));
    }
    $j = $entity->original->depth->value + 1;
    while ($i <= MENU_MAX_DEPTH && $j <= MENU_MAX_DEPTH) {
      $expressions[] = array('p' . $i++, 'p' . $j++, array());
    }
    while ($i <= MENU_MAX_DEPTH) {
      $expressions[] = array('p' . $i++, 0, array());
    }

    $shift = $entity->depth->value - $entity->original->depth->value;
    if ($shift > 0) {
      // The order of expressions must be reversed so the new values don't
      // overwrite the old ones before they can be used because "Single-table
      // UPDATE assignments are generally evaluated from left to right"
      // @see http://dev.mysql.com/doc/refman/5.0/en/update.html
      $expressions = array_reverse($expressions);
    }
    foreach ($expressions as $expression) {
      $query->expression($expression[0], $expression[1], $expression[2]);
    }

    $query->expression('depth', 'depth + :depth', array(':depth' => $shift));
    $query->condition('menu_name', $entity->original->menu_name->value);
    $p = 'p1';
    for ($i = 1; $i <= MENU_MAX_DEPTH && $entity->original->{$p}->value; $p = 'p' . ++$i) {
      $query->condition($p, $entity->original->{$p}->value);
    }

    $query->execute();

    // Check the has_children status of the parent, while excluding this item.
    $this->updateParentalStatus($entity->original, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function countMenuLinks($menu_name) {
    $query = \Drupal::entityQuery($this->entityType);
    $query
      ->condition('menu_name', $menu_name)
      ->count();
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getParentFromHierarchy(EntityInterface $entity) {
    $parent_path = $entity->link_path->value;
    do {
      $parent = FALSE;
      $parent_path = substr($parent_path, 0, strrpos($parent_path, '/'));

      $query = \Drupal::entityQuery($this->entityType);
      $query
        ->condition('mlid', $entity->id(), '<>')
        ->condition('module', 'system')
        // We always respect the link's 'menu_name'; inheritance for router
        // items is ensured in _menu_router_build().
        ->condition('menu_name', $entity->menu_name->value)
        ->condition('link_path', $parent_path);

      $result = $query->execute();
      // Only valid if we get a unique result.
      if (count($result) == 1) {
        $parent = $this->load(reset($result));
      }
    } while ($parent === FALSE && $parent_path);

    return $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function baseFieldDefinitions() {
    $properties['menu_name'] = array(
      'label' => t('Menu name'),
      'description' => t('The menu name. All links with the same menu name (such as "tools") are part of the same menu.'),
      'type' => 'string_field',
    );
    $properties['mlid'] = array(
      'label' => t('Menu link ID'),
      'description' => t('The menu link ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'description' => t('The menu link UUID.'),
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['plid'] = array(
      'label' => t('Parent ID'),
      'description' => t('The parent menu link ID.'),
      'type' => 'entity_reference_field',
      'settings' => array('target_type' => 'menu_link'),
    );
    $properties['link_path'] = array(
      'label' => t('Link path'),
      'description' => t('The Drupal path or external path this link points to.'),
      'type' => 'string_field',
    );
    $properties['router_path'] = array(
      'label' => t('Router path'),
      'description' => t('For links corresponding to a Drupal path (external = 0), this connects the link to a {menu_router}.path.'),
      'type' => 'string_field',
    );
    $properties['langcode'] = array(
      'label' => t('Language code'),
      'description' => t('The menu link language code.'),
      'type' => 'language_field',
    );
    $properties['link_title'] = array(
      'label' => t('Title'),
      'description' => t('The text displayed for the link, which may be modified by a title callback stored in {menu_router}.'),
      'type' => 'string_field',
    );
    $properties['options'] = array(
      'label' => t('Options'),
      'description' => t('A serialized array of options to be passed to the url() or l() function, such as a query string or HTML attributes.'),
      'type' => 'map_field',
    );
    $properties['module'] = array(
      'label' => t('Module'),
      'description' => t('The name of the module that generated this link.'),
      'type' => 'string_field',
    );
    $properties['hidden'] = array(
      'label' => t('Hidden'),
      'description' => t('A flag for whether the link should be rendered in menus. (1 = a disabled menu item that may be shown on admin screens, -1 = a menu callback, 0 = a normal, visible link).'),
      'type' => 'boolean_field',
    );
    $properties['external'] = array(
      'label' => t('External'),
      'description' => t('A flag to indicate if the link points to a full URL starting with a protocol, like http:// (1 = external, 0 = internal).'),
      'type' => 'boolean_field',
    );
    $properties['has_children'] = array(
      'label' => t('Has children'),
      'description' => t('Flag indicating whether any links have this link as a parent (1 = children exist, 0 = no children).'),
      'type' => 'boolean_field',
    );
    $properties['expanded'] = array(
      'label' => t('Expanded'),
      'description' => t('Flag for whether this link should be rendered as expanded in menus - expanded links always have their child links displayed, instead of only when the link is in the active trail (1 = expanded, 0 = not expanded).'),
      'type' => 'boolean_field',
    );
    $properties['weight'] = array(
      'label' => t('Weight'),
      'description' => t('Link weight among links in the same menu at the same depth.'),
      'type' => 'integer_field',
    );
    $properties['depth'] = array(
      'label' => t('Depth'),
      'description' => t('The depth relative to the top level. A link with plid == 0 will have depth == 1.'),
      'type' => 'integer_field',
    );
    $properties['customized'] = array(
      'label' => t('Customized'),
      'description' => t('A flag to indicate that the user has manually created or edited the link (1 = customized, 0 = not customized).'),
      'type' => 'boolean_field',
    );
    // @todo Declaring these pX properties as integer for the moment, we need to
    // investigate if using 'entity_reference_field' cripples performance.
    $properties['p1'] = array(
      'label' => t('Parent 1'),
      'description' => t('The first mlid in the materialized path.'),
      'type' => 'integer_field',
    );
    $properties['p2'] = array(
      'label' => t('Parent 2'),
      'description' => t('The second mlid in the materialized path.'),
      'type' => 'integer_field',
    );
    $properties['p3'] = array(
      'label' => t('Parent 3'),
      'description' => t('The third mlid in the materialized path.'),
      'type' => 'integer_field',
    );
    $properties['p4'] = array(
      'label' => t('Parent 4'),
      'description' => t('The fourth mlid in the materialized path.'),
      'type' => 'integer_field',
    );
    $properties['p5'] = array(
      'label' => t('Parent 5'),
      'description' => t('The fifth mlid in the materialized path.'),
      'type' => 'integer_field',
    );
    $properties['p6'] = array(
      'label' => t('Parent 6'),
      'description' => t('The sixth mlid in the materialized path.'),
      'type' => 'integer_field',
    );
    $properties['p7'] = array(
      'label' => t('Parent 7'),
      'description' => t('The seventh mlid in the materialized path.'),
      'type' => 'integer_field',
    );
    $properties['p8'] = array(
      'label' => t('Parent 8'),
      'description' => t('The eighth mlid in the materialized path.'),
      'type' => 'integer_field',
    );
    $properties['p9'] = array(
      'label' => t('Parent 9'),
      'description' => t('The ninth mlid in the materialized path.'),
      'type' => 'integer_field',
    );
    $properties['updated'] = array(
      'label' => t('Updated'),
      'description' => t('Flag that indicates that this link was generated during the update from Drupal 5.'),
      'type' => 'boolean_field',
    );
    $properties['route_name'] = array(
      'label' => t('Route name'),
      'description' => t('The machine name of a defined Symfony Route this menu item represents.'),
      'type' => 'string_field',
    );

    // @todo Most of these should probably go away.
    $properties['access'] = array(
      'label' => t('(old router) Access'),
      'description' => t(''),
      'type' => 'boolean_field',
      'computed' => TRUE,
    );
    $properties['in_active_trail'] = array(
      'label' => t('In active trail'),
      'description' => t(''),
      'type' => 'boolean_field',
      'computed' => TRUE,
    );
    $properties['localized_options'] = array(
      'label' => t('Localized options'),
      'description' => t(''),
      'type' => 'map_field',
      'computed' => TRUE,
    );
    return $properties;
  }

}
