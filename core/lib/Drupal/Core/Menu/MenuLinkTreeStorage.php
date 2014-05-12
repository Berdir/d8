<?php

/**
 * @file
 * Contains \Drupal\Core\Menu\MenuLinkTreeStorage.
 */

namespace Drupal\Core\Menu;

use Drupal\Core\Database\Connection;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Database\SchemaObjectExistsException;
use Drupal\Core\Database\Database;
use Drupal\Core\Routing\UrlGeneratorInterface;

class MenuLinkTreeStorage implements MenuLinkTreeStorageInterface {

  /**
   * The maximum depth of a menu links tree.
   */
  const MAX_DEPTH = 9;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGenerator
   */
  protected $urlGenerator;

  /**
   * The database table name.
   *
   * @var string
   */
  protected $table;

  /**
   * Additional database connection options to use in queries.
   *
   * @var array
   */
  protected $options = array();

  /**
   * Stores the menu tree used by the doBuildTree method, keyed by a cache ID.
   *
   * This cache ID is built using the $menu_name, the current language and
   * some parameters passed into an entity query.
   */
  protected $menuTree;

  /**
   * List of serialized fields.
   *
   * @var array
   */
  protected $serializedFields;

  /**
   * List of plugin definition fields.
   *
   * @todo - inject this from the plugin manager?
   *
   * @var array
   */
  protected $definitionFields = array(
    'menu_name',
    'route_name',
    'route_parameters',
    'url',
    'title',
    'title_arguments',
    'title_context',
    'description',
    'parent',
    'weight',
    'options',
    'expanded',
    'hidden',
    'discovered',
    'provider',
    'metadata',
    'class',
    'form_class',
    'id',
  );

  /**
   * Constructs a new \Drupal\Core\Menu\MenuLinkTreeStorage.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   A Database connection to use for reading and writing configuration data.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   * @param string $table
   *   A database table name to store configuration data in.
   * @param array $options
   *   (optional) Any additional database connection options to use in queries.
   */
  public function __construct(Connection $connection, UrlGeneratorInterface $url_generator,  $table = 'menu_tree', array $options = array()) {
    $this->connection = $connection;
    $this->urlGenerator = $url_generator;
    $this->table = $table;
    $this->options = $options;
  }

  /**
   * {@inheritdoc}
   */
  public function maxDepth() {
    return static::MAX_DEPTH;
  }

  /**
   * {@inheritdoc}
   */
  public function rebuild(array $definitions) {
    $links = array();
    $children = array();
    $top_links = array();
    if ($definitions) {
      foreach ($definitions as $id => $link) {
        if (!empty($link['parent'])) {
          $children[$link['parent']][$id] = $id;
        }
        else {
          // A top level link - we need them to root our tree.
          $top_links[$id] = $id;
          $link['parent'] = '';
        }
        $links[$id] = $link;
      }
    }
    foreach ($top_links as $id) {
      $this->saveRecursive($id, $children, $links);
    }
    // Handle any children we didn't find starting from top-level links.
    foreach ($children as $orphan_links) {
      foreach ($orphan_links as $id) {
        // Force it to the top level.
        $links[$id]['parent'] = '';
        $this->saveRecursive($id, $children, $links);
      }
    }
    // Find any previously discovered menu links that no longer exist.
    if ($definitions) {
      $query = $this->connection->select($this->table, NULL, $this->options);
      $query->addField($this->table, 'id');
      $query->condition('discovered', 1);
      $query->condition('id', array_keys($definitions), 'NOT IN');
      $query->orderBy('depth', 'DESC');
      $result = $query->execute()->fetchCol();
    }
    else {
      $result = array();
    }

    // Remove all such items. Starting from those with the greatest depth will
    // minimize the amount of re-parenting done by the menu link controller.
    if ($result) {
      $this->purgeMultiple($result);
    }
  }

  /**
   * Purges multiple menu links that no longer exist.
   *
   * @param array $ids
   *   An array of menu link IDs.
   * @param bool $prevent_reparenting
   *   (optional) Disables the re-parenting logic from the deletion process.
   *   Defaults to FALSE.
   */
  protected function purgeMultiple(array $ids, $prevent_reparenting = FALSE) {
    if (!$prevent_reparenting) {
      $loaded = $this->loadFullMultiple($ids);
      foreach ($loaded as $id => $link) {
        if ($link['has_children']) {
          $children = $this->loadByProperties(array('parent' => $id));
          foreach ($children as $child) {
            $child['parent'] = $link['parent'];
            $this->save($child);
          }
        }
      }
    }
    $query = $this->connection->delete($this->table, $this->options);
    $query->condition('id', $ids, 'IN');
    $query->execute();
  }

  /**
   * Execute a select query while making sure the database table exists.
   *
   * @param SelectInterface $query
   *
   * @return \Drupal\Core\Database\StatementInterface|null
   *   A prepared statement, or NULL if the query is not valid.
   *
   * @throws \Exception
   *   If the table could not be created or the database connection failed.
   */
  protected function safeExecuteSelect(SelectInterface $query) {
    try {
      return $query->execute();
    }
    catch (\Exception $e) {
      // If there was an exception, try to create the table.
      if ($this->ensureTableExists()) {
        return $query->execute();
      }
      // Some other failure that we can not recover from.
      throw $e;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $link) {
    $original = $this->loadFull($link['id']);
    // @todo - should we just return here if the links values match the original
    //  values completely?.

    $affected_menus = array();

    $transaction = $this->connection->startTransaction();
    try {
      if ($original) {
        $link['mlid'] = $original['mlid'];
        $link['has_children'] = $original['has_children'];
        $affected_menus[$original['menu_name']] = $original['menu_name'];
      }
      else {
        // Generate a new mlid.
        $options = array('return' => Database::RETURN_INSERT_ID) + $this->options;
        $link['mlid'] = $this->connection->insert($this->table, $options)
          ->fields(array('id' => $link['id'], 'menu_name' => $link['menu_name']))
          ->execute();
      }
      $fields = $this->preSave($link, $original);
      // We may be moving the link to a new menu.
      $affected_menus[$fields['menu_name']] = $fields['menu_name'];
      $query = $this->connection->update($this->table, $this->options);
      $query->condition('mlid', $link['mlid']);
      $query->fields($fields)
        ->execute();
      if ($original) {
        $this->updateParentalStatus($original);
      }
      $this->updateParentalStatus($link);
      // Ignore slave server temporarily.
      db_ignore_slave();
    }
    catch (\Exception $e) {
      $transaction->rollback();
      throw $e;
    }
    return $affected_menus;
  }

  /**
   * Using the link definition, but up all the fields needed for database save.
   */
  protected function preSave(&$link, $original) {
    static $schema_fields, $schema_defaults;
    if (empty($schema_fields)) {
      $schema = static::schemaDefinition();
      $schema_fields = $schema['fields'];
      foreach ($schema_fields as $name => $spec) {
        if (isset($spec['default'])) {
          $schema_defaults[$name] = $spec['default'];
        }
      }
    }

    // Try to find a parent link. If found, assign it and derive its menu.
    $parent = $this->findParent($link, $original);
    if ($parent) {
      $link['parent'] = $parent['id'];
      $link['menu_name'] = $parent['menu_name'];
    }
    else {
      $link['parent'] = '';
    }

    // If no corresponding parent link was found, move the link to the top-level.
    foreach ($schema_defaults as $name => $default) {
      if (!isset($link[$name])) {
        $link[$name] = $default;
      }
    }
    $fields = array_intersect_key($link, $schema_fields);
    asort($fields['route_parameters']);
    // Since this will be urlencoded, it's safe to store and match against a
    // text field.
    $fields['route_param_key'] = $fields['route_parameters'] ? UrlHelper::buildQuery($fields['route_parameters']) : '';

    foreach ($this->serializedFields() as $name) {
      $fields[$name] = serialize($fields[$name]);
    }

    // Directly fill parents for top-level links.
    if (empty($link['parent'])) {
      $fields['p1'] = $link['mlid'];
      for ($i = 2; $i <= $this->maxDepth(); $i++) {
        $fields["p$i"] = 0;
      }
      $fields['depth'] = 1;
    }
    // Otherwise, ensure that this link's depth is not beyond the maximum depth
    // and fill parents based on the parent link.
    else {
      // @todo - we want to also check $original['has_children'] here, but that
      //  will be 0 even if there are children if those are hidden. has_children
      //  is really just the rendering hint. So, we either need to define
      //  another column (has_any_children), or always do the extra query here.
      if ($original) {
        $limit = $this->maxDepth() - $this->doFindChildrenRelativeDepth($original) - 1;
      }
      else {
        $limit = $this->maxDepth() - 1;
      }
      if ($parent['depth'] > $limit) {
        throw new PluginException(sprintf('The link with ID %s or its children exceeded the maximum depth of %d', $link['id'], $this->maxDepth()));
      }
      $this->setParents($fields, $parent);
    }

    // Need to check both parent and menu_name, since parent can be NULL in any menu.
    if ($original && ($link['parent'] != $original['parent'] || $link['menu_name'] != $original['menu_name'])) {
      $this->moveChildren($fields, $original);
    }
    // We needed the mlid above, but not in the update query.
    unset($fields['mlid']);

    // Cast booleans to int, if needed.
    $fields['hidden'] = (int) $fields['hidden'];
    $fields['expanded'] = (int) $fields['expanded'];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($id) {
    // Children get re-attached to the menu link's parent.
    $item = $this->loadFull($id);
    // It's possible the link is already deleted.
    if ($item) {
      $parent = $item['parent'];
      $children = $this->loadByProperties(array('parent' => $id));
      foreach ($children as $child) {
        $child['parent'] = $parent;
        $this->save($child);
      }

      $this->connection->delete($this->table, $this->options)
        ->condition('id', $id)
        ->execute();

      $this->updateParentalStatus($item);
    }
  }

  /**
   * @TODO Figure out whether public methods should always pass along the
   * menu link instance or never ...
   */
  public function findChildrenRelativeDepth($id) {
    return $this->doFindChildrenRelativeDepth($this->loadFull($id));
  }

  /**
   * Find the relative depth of this link's deepest child.
   */
  protected function doFindChildrenRelativeDepth($original) {
    $query = $this->connection->select($this->table, $this->options);
    $query->addField($this->table, 'depth');
    $query->condition('menu_name', $original['menu_name']);
    $query->orderBy('depth', 'DESC');
    $query->range(0, 1);

    for ($i = 1; $i <= static::MAX_DEPTH && $original["p$i"]; $i++) {
      $query->condition("p$i", $original["p$i"]);
    }

    $max_depth = $this->safeExecuteSelect($query)->fetchField();

    return ($max_depth > $original['depth']) ? $max_depth - $original['depth'] : 0;
  }

  /**
   * Set the materialized path field values based on the parent.
   */
  protected function setParents(&$fields, $parent) {
    $fields['depth'] = $parent['depth'] + 1;
    $i = 1;
    while ($i < $fields['depth']) {
      $p = 'p' . $i++;
      $fields[$p] = $parent[$p];
    }
    $p = 'p' . $i++;
    // The parent (p1 - p9) corresponding to the depth always equals the mlid.
    $fields[$p] = $fields['mlid'];
    while ($i <= static::MAX_DEPTH) {
      $p = 'p' . $i++;
      $fields[$p] = 0;
    }
  }

  /**
   * Using the query field values and original values, move the link's children.
   */
  protected function moveChildren($fields, $original) {
    $query = $this->connection->update($this->table, $this->options);

    $query->fields(array('menu_name' => $fields['menu_name']));

    $expressions = array();
    for ($i = 1; $i <= $fields['depth']; $i++) {
      $expressions[] = array("p$i", ":p_$i", array(":p_$i" => $fields["p$i"]));
    }
    $j = $original['depth'] + 1;
    while ($i <= $this->maxDepth() && $j <= $this->maxDepth()) {
      $expressions[] = array('p' . $i++, 'p' . $j++, array());
    }
    while ($i <= $this->maxDepth()) {
      $expressions[] = array('p' . $i++, 0, array());
    }

    $shift = $fields['depth'] - $original['depth'];
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
    $query->condition('menu_name', $original['menu_name']);

    for ($i = 1; $i <= $this->maxDepth() && $original["p$i"]; $i++) {
      $query->condition("p$i", $original["p$i"]);
    }

    $query->execute();
  }

  /**
   * Load the parent definition if it exists.
   */
  protected function findParent($link, $original) {
    $parent = FALSE;

    // This item is explicitly top-level, skip the rest of the parenting.
    if (isset($link['parent']) && empty($link['parent'])) {
      return $parent;
    }

    // If we have a parent link ID, try to use that.
    $candidates = array();
    if (isset($link['parent'])) {
      $candidates[] = $link['parent'];
    }
    elseif ($original['parent'] && $link['menu_name'] == $original['menu_name']) {
      $candidates[] = $original['parent'];
    }

    // Else, if we have a link hierarchy try to find a valid parent in there.
    // @todo - why does this make sense to do at all?

    foreach ($candidates as $id) {
      $parent = $this->loadFull($id);
      if ($parent) {
        break;
      }
    }
    return $parent;
  }

  /**
   * Set the has_children flag for the link's parent if it has visible children.
   *
   * @param array $link
   */
  protected function updateParentalStatus(array $link) {
    // If parent is empty, there is nothing to update.
    if (!empty($link['parent'])) {
      // Check if at least one visible child exists in the table.
      $query = $this->connection->select($this->table, $this->options);
      $query->addExpression('1');
      $query->range(0, 1);
      $query
        ->condition('menu_name', $link['menu_name'])
        ->condition('parent', $link['parent'])
        ->condition('hidden', 0);

      $parent_has_children = ((bool) $query->execute()->fetchField()) ? 1 : 0;
      $this->connection->update($this->table, $this->options)
        ->fields(array('has_children' => $parent_has_children))
        ->condition('id', $link['parent'])
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loadByProperties(array $properties) {
    // @todo - only allow loading by plugin definition properties.
    $query = $this->connection->select($this->table, $this->options);
    $query->fields($this->table, $this->definitionFields());
    foreach ($properties as $name => $value) {
      $query->condition($name, $value);
    }
    $loaded = $this->safeExecuteSelect($query)->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    foreach ($loaded as &$link) {
      foreach ($this->serializedFields() as $name) {
        $link[$name] = unserialize($link[$name]);
      }
    }
    return $loaded;
  }

  /**
   * {@inheritdoc}
   */
  public function loadByRoute($route_name, array $route_parameters = array(), $include_hidden = FALSE) {
    asort($route_parameters);
    // Since this will be urlencoded, it's safe to store and match against a
    // text field.
    // @todo - does this make more sense than using the system path?
    $param_key = $route_parameters ? UrlHelper::buildQuery($route_parameters) : '';
    $query = $this->connection->select($this->table, $this->options);
    $query->fields($this->table, $this->definitionFields());
    $query->condition('route_name', $route_name);
    $query->condition('route_param_key', $param_key);
    if (!$include_hidden) {
      $query->condition('hidden', 0);
    }
    $loaded = $this->safeExecuteSelect($query)->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    foreach ($loaded as &$link) {
      foreach ($this->serializedFields() as $name) {
        $link[$name] = unserialize($link[$name]);
      }
    }
    return $loaded;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids) {
    $query = $this->connection->select($this->table, $this->options);
    $query->fields($this->table, $this->definitionFields());
    $query->condition('id', $ids, 'IN');
    $loaded = $this->safeExecuteSelect($query)->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    foreach ($loaded as &$link) {
      foreach ($this->serializedFields() as $name) {
        $link[$name] = unserialize($link[$name]);
      }
    }
    return $loaded;
  }

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    $loaded = $this->loadMultiple(array($id));
    return isset($loaded[$id]) ? $loaded[$id] : FALSE;
  }

  /**
   * Load all table fields, not just those that are in the plugin definition.
   */
  protected function loadFull($id) {
    $loaded = $this->loadFullMultiple(array($id));
    return isset($loaded[$id]) ? $loaded[$id] : FALSE;
  }

  protected  function loadFullMultiple(array $ids) {
    $query = $this->connection->select($this->table, $this->options);
    $query->fields($this->table);
    $query->condition('id', $ids, 'IN');
    $loaded = $this->safeExecuteSelect($query)->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    foreach ($loaded as &$link) {
      foreach ($this->serializedFields() as $name) {
        $link[$name] = unserialize($link[$name]);
      }
    }
    return $loaded;
  }

  /**
   * {@inheritdoc}
   */
  public function getMaterializedPathIds($id) {
    $subquery = $this->connection->select($this->table, $this->options);
    // @todo: consider making this dynamic based on static::MAX_DEPTH
    //   or from the schema if that is generated using static::MAX_DEPTH.
    $subquery->fields($this->table, array('p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'p9'));
    $subquery->condition('id', $id);
    $result = current($subquery->execute()->fetchAll(\PDO::FETCH_ASSOC));
    $ids = array_filter($result);
    $query = $this->connection->select($this->table, $this->options);
    $query->fields($this->table, array('id'));
    $query->orderBy('depth', 'ASC');
    $query->condition('mlid', $ids, 'IN');
    // @todo: cache this result in memory if we find it's being used more
    //   than once per page load.
    return $this->safeExecuteSelect($query)->fetchAllKeyed(0, 0);
  }

  /**
   * {@inheritdoc}
   */
  public function getExpanded($menu_name, array $parents) {
    // @todo go back to tracking in state or some other way
    // which menus have expanded links?
    do {
      $query = $this->connection->select($this->table, $this->options);
      $query->fields($this->table, array('id'));
      $query->condition('menu_name', $menu_name);
      $query->condition('expanded', 1);
      $query->condition('has_children', 1);
      $query->condition('hidden', 0);
      $query->condition('parent', $parents, 'IN');
      $query->condition('id', $parents, 'NOT IN');
      $result = $this->safeExecuteSelect($query)->fetchAllKeyed(0, 0);
      $parents += $result;
    } while (!empty($result));
    return $parents;
  }

  /**
   * Saves menu links recursively.
   */
  protected function saveRecursive($id, &$children, &$links) {

    if (!empty($links[$id]['parent']) && empty($links[$links[$id]['parent']])) {
      // Invalid parent ID, so remove it
      $links[$id]['parent'] = '';
    }
    $this->save($links[$id]);

    if (!empty($children[$id])) {
      foreach ($children[$id] as $next_id) {
        $this->saveRecursive($next_id, $children, $links);
      }
    }
    // Remove processed link names so we can find stragglers.
    unset($children[$id]);
  }

  /**
   * {@inheritdoc}
   */
  public function loadTree($menu_name, array $parameters = array()) {
    $query = $this->connection->select($this->table, $this->options);
    $query->fields($this->table);
    for ($i = 1; $i <= $this->maxDepth(); $i++) {
      $query->orderBy('p' . $i, 'ASC');
    }

    $query->condition('menu_name', $menu_name);

    if (!empty($parameters['expanded'])) {
      $query->condition('parent', $parameters['expanded'], 'IN');
    }
    elseif (!empty($parameters['only_active_trail'])) {
      $query->condition('id', $parameters['active_trail'], 'IN');
    }
    $min_depth = (isset($parameters['min_depth']) ? $parameters['min_depth'] : NULL);
    if ($min_depth) {
      $query->condition('depth', $min_depth, '>=');
    }
    if (isset($parameters['max_depth'])) {
      $query->condition('depth', $parameters['max_depth'], '<=');
    }
    // Add custom query conditions, if any were passed.
    if (!empty($parameters['conditions'])) {
      // Only allow conditions that are testing definition fields.
      $parameters['conditions'] = array_intersect_key($parameters['conditions'], array_flip($this->definitionFields()));
      foreach ($parameters['conditions'] as $column => $value) {
        $query->condition($column, $value);
      }
    }
    $active_trail = (isset($parameters['active_trail']) ? $parameters['active_trail'] : array());
    $links = $this->safeExecuteSelect($query)->fetchAll(\PDO::FETCH_ASSOC);
    if (!isset($min_depth)) {
      $first_link = reset($links);
      if ($first_link) {
        $min_depth = $first_link['depth'];
      }
    }
    $tree = $this->doBuildTreeData($links, $active_trail, $min_depth);
    return $tree;
  }

  /**
   * {@inheritdoc}
   */
  public function loadSubtree($id, $max_relative_depth = NULL) {
    $tree = array();
    $root = $this->loadFull($id);
    if (!$root) {
      return $tree;
    }
    $query = $this->connection->select($this->table, $this->options);
    $query->fields($this->table);
    for ($i = 1; $i <= $this->maxDepth(); $i++) {
      $query->orderBy('p' . $i, 'ASC');
    }
    $query->condition('hidden', 0);
    $query->condition('menu_name', $root['menu_name']);
    for ($i = 1; $i <= $root['depth']; $i++) {
      $query->condition("p$i", $root["p$i"]);
    }
    if (!empty($max_relative_depth)) {
      $query->condition('depth', (int) $root['depth'] + $max_relative_depth, '<=');
    }
    $links = $this->safeExecuteSelect($query)->fetchAll(\PDO::FETCH_ASSOC);
    $tree = $this->doBuildTreeData($links, array(), $root['depth']);
    $subtree = current($tree);
    return $subtree;
  }

  /**
   * {@inheritdoc}
   */
  public function menuNameExists($menu_name) {
    $query = $this->connection->select($this->table, $this->options);
    $query->addField($this->table, 'mlid');
    $query->condition('menu_name', $menu_name);
    $query->range(0, 1);
    return (bool) $this->safeExecuteSelect($query);
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuNames() {
    $query = $this->connection->select($this->table, $this->options);
    $query->addField($this->table, 'menu_name');
    $query->distinct();
    return $this->safeExecuteSelect($query)->fetchAllKeyed(0, 0);
  }

  /**
   * {@inheritdoc}
   */
  public function countMenuLinks($menu_name = NULL) {
    $query = $this->connection->select($this->table, $this->options);
    if ($menu_name) {
      $query->condition('menu_name', $menu_name);
    }
    return $this->safeExecuteSelect($query->countQuery())->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllChildLinks($id, $max_relative_depth = NULL) {
    $tree = array();
    $root = $this->loadFull($id);
    if (!$root || $root['depth'] == $this->maxDepth()) {
      return $tree;
    }
    $query = $this->connection->select($this->table, $this->options);
    $query->fields($this->table, $this->definitionFields());
    $query->condition('hidden', 0);
    $query->condition('menu_name', $root['menu_name']);
    for ($i = 1; $i <= $root['depth']; $i++) {
      $query->condition("p$i", $root["p$i"]);
    }
    // The next p column should not be empty. This excludes the root link.
    $query->condition("p$i", 0, '>');
    if (!empty($max_relative_depth)) {
      $query->condition('depth', (int) $root['depth'] + $max_relative_depth, '<=');
    }
    $loaded = $this->safeExecuteSelect($query)->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    foreach ($loaded as &$link) {
      foreach ($this->serializedFields() as $name) {
        $link[$name] = unserialize($link[$name]);
      }
    }
    return $loaded;
  }

  /**
   * Prepares the data for calling $this->treeDataRecursive().
   */
  protected function doBuildTreeData(array $links, array $parents = array(), $depth = 1) {
    // Reverse the array so we can use the more efficient array_pop() function.
    $links = array_reverse($links);
    return $this->treeDataRecursive($links, $parents, $depth);
  }

  /**
   * Builds the data representing a menu tree.
   *
   * The function is a bit complex because the rendering of a link depends on
   * the next menu link.
   *
   * @param array $links
   *   A flat array of menu links that are part of the menu. Each array element
   *   is an associative array of information about the menu link, containing
   *   the fields from the {menu_links} table, and optionally additional
   *   information from the {menu_router} table, if the menu item appears in
   *   both tables. This array must be ordered depth-first.
   *   See _menu_build_tree() for a sample query.
   * @param array $parents
   *   An array of the menu link ID values that are in the path from the current
   *   page to the root of the menu tree.
   * @param int $depth
   *   The minimum depth to include in the returned menu tree.
   *
   * @return array
   */
  protected function treeDataRecursive(&$links, $parents, $depth) {
    $tree = array();
    while ($item = array_pop($links)) {
      // We need to determine if we're on the path to root so we can later build
      // the correct active trail.
      foreach ($this->serializedFields() as $name) {
        $item[$name] = unserialize($item[$name]);
      }
      // Add the current link to the tree.
      $tree[$item['id']] = array(
        'definition' => array_intersect_key($item, array_flip($this->definitionFields())),
        'has_children' => $item['has_children'],
        'in_active_trail' => in_array($item['id'], $parents),
        'below' => array(),
        'depth' => $item['depth'],
      );
      for ($i = 1; $i <= $this->maxDepth(); $i++) {
        $tree[$item['id']]['p' . $i] = $item['p' . $i];
      }
      // Look ahead to the next link, but leave it on the array so it's
      // available to other recursive function calls if we return or build a
      // sub-tree.
      $next = end($links);
      // Check whether the next link is the first in a new sub-tree.
      if ($next && $next['depth'] > $depth) {
        // Recursively call doBuildTreeData to build the sub-tree.
        $tree[$item['id']]['below'] = $this->treeDataRecursive($links, $parents, $next['depth']);
        // Fetch next link after filling the sub-tree.
        $next = end($links);
      }
      // Determine if we should exit the loop and return.
      if (!$next || $next['depth'] < $depth) {
        break;
      }
    }
    return $tree;
  }

  /**
   * Check if the tree table exists and create it if not.
   *
   * @return bool
   *   TRUE if the table was created, FALSE otherwise.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If a database error occurs.
   */
  protected function ensureTableExists() {
    try {
      if (!$this->connection->schema()->tableExists($this->table)) {
        $this->connection->schema()->createTable($this->table, static::schemaDefinition());
        return TRUE;
      }
    }
      // If another process has already created the config table, attempting to
      // recreate it will throw an exception. In this case just catch the
      // exception and do nothing.
    catch (SchemaObjectExistsException $e) {
      return TRUE;
    }
    catch (\Exception $e) {
      throw new PluginException($e->getMessage(), NULL, $e);
    }
    return FALSE;
  }

  /**
   * Helper function to determine serialized fields.
   */
  protected function serializedFields() {
    // For now, build the list from the schema since it's in active development.
    if (empty($this->serializedFields)) {
      $schema = static::schemaDefinition();
      foreach ($schema['fields'] as $name => $field) {
        if (!empty($field['serialize'])) {
          $this->serializedFields[] = $name;
        }
      }
    }
    return $this->serializedFields;
  }

  /**
   * Helper function to determine fields that are part of the plugin definition.
   */
  protected function definitionFields() {
    return $this->definitionFields;
  }

  /**
   * Defines the schema for the tree table.
   */
  protected static function schemaDefinition() {
    $schema = array(
      'description' => 'Contains the menu tree hierarchy.',
      'fields' => array(
        'menu_name' => array(
          'description' => "The menu name. All links with the same menu name (such as 'tools') are part of the same menu.",
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
          'default' => '',
        ),
        'mlid' => array(
          'description' => 'The menu link ID (mlid) is the integer primary key.',
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'id' => array(
          'description' => 'Unique machine name: the plugin ID.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ),
        'parent' => array(
          'description' => 'The plugin ID for the parent of this link.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'route_name' => array(
          'description' => 'The machine name of a defined Symfony Route this menu item represents.',
          'type' => 'varchar',
          'length' => 255,
        ),
        'route_param_key' => array(
          'description' => 'An encoded string of route parameters for loading by route.',
          'type' => 'varchar',
          'length' => 255,
        ),
        'route_parameters' => array(
          'description' => 'Serialized array of route parameters of this menu link.',
          'type' => 'blob',
          'size' => 'big',
          'not null' => FALSE,
          'serialize' => TRUE,
        ),
        'url' => array(
          'description' => 'The external path this link points to (when not using a route).',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'title' => array(
          'description' => 'The text displayed for the link.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'title_arguments' => array(
          'description' => 'A serialized array of arguments to be passed to t() (if this plugin uses it).',
          'type' => 'blob',
          'size' => 'big',
          'not null' => FALSE,
          'serialize' => TRUE,
        ),
        'title_context' => array(
          'description' => 'The translation context for the link title.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'description' => array(
          'description' => 'The description of this link - used for admin pages and title attribute.',
          'type' => 'text',
          'not null' => FALSE,
        ),
        'class' => array(
          'description' => 'The class for this link plugin.',
          'type' => 'text',
          'not null' => FALSE,
        ),
        'options' => array(
          'description' => 'A serialized array of options to be passed to the url() or l() function, such as a query string or HTML attributes.',
          'type' => 'blob',
          'size' => 'big',
          'not null' => FALSE,
          'serialize' => TRUE,
        ),
        'provider' => array(
          'description' => 'The name of the module that generated this link.',
          'type' => 'varchar',
          'length' => DRUPAL_EXTENSION_NAME_MAX_LENGTH,
          'not null' => TRUE,
          'default' => 'system',
        ),
        'hidden' => array(
          'description' => 'A flag for whether the link should be rendered in menus. (1 = a disabled menu item that may be shown on admin screens, 0 = a normal, visible link)',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'small',
        ),
        'discovered' => array(
          'description' => 'A flag for whether the link was discovered, so can be purged on rebuild',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'small',
        ),
        'expanded' => array(
          'description' => 'Flag for whether this link should be rendered as expanded in menus - expanded links always have their child links displayed, instead of only when the link is in the active trail (1 = expanded, 0 = not expanded)',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'small',
        ),
        'weight' => array(
          'description' => 'Link weight among links in the same menu at the same depth.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ),
        'metadata' => array(
          'description' => 'A serialized array of data that may be used by the plugin instance.',
          'type' => 'blob',
          'size' => 'big',
          'not null' => FALSE,
          'serialize' => TRUE,
        ),
        'has_children' => array(
          'description' => 'Flag indicating whether any non-hidden links have this link as a parent (1 = children exist, 0 = no children).',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'small',
        ),
        'depth' => array(
          'description' => 'The depth relative to the top level. A link with empty parent will have depth == 1.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'small',
        ),
        'p1' => array(
          'description' => 'The first mlid in the materialized path. If N = depth, then pN must equal the mlid. If depth > 1 then p(N-1) must equal the parent link mlid. All pX where X > depth must equal zero. The columns p1 .. p9 are also called the parents.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'p2' => array(
          'description' => 'The second mlid in the materialized path. See p1.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'p3' => array(
          'description' => 'The third mlid in the materialized path. See p1.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'p4' => array(
          'description' => 'The fourth mlid in the materialized path. See p1.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'p5' => array(
          'description' => 'The fifth mlid in the materialized path. See p1.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'p6' => array(
          'description' => 'The sixth mlid in the materialized path. See p1.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'p7' => array(
          'description' => 'The seventh mlid in the materialized path. See p1.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'p8' => array(
          'description' => 'The eighth mlid in the materialized path. See p1.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'p9' => array(
          'description' => 'The ninth mlid in the materialized path. See p1.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'form_class' => array(
          'description' => 'meh',
          'type' => 'varchar',
          'length' => 255,
        ),
      ),
      'indexes' => array(
        'menu_parents' => array('menu_name', 'p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'p9'),
        // @todo test this index for effectiveness.
        'menu_parent_expand_child' => array('menu_name', 'expanded', 'has_children', array('parent', 16)),
        'route_values' => array(array('route_name', 32), array('route_param_key', 16)),
      ),
      'primary key' => array('mlid'),
      'unique keys' => array(
        'id' => array('id'),
      ),
    );

    return $schema;
  }

}
