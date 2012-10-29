<?php

/**
 * @file
 * Hooks provided the Entity module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the entity type definition.
 *
 * Modules may implement this hook to alter the information that defines an
 * entity. All properties that are available in
 * \Drupal\Core\Entity\EntityManager can be altered here.
 *
 * In addition, the following properties should be added here:
 * - bundles: An array describing all bundles for this object type. Keys are
 *   bundle machine names, as found in the objects' 'bundle' property
 *   (defined in the 'entity_keys' entry for the entity type in the
 *   EntityManager). Elements:
 *   - label: The human-readable name of the bundle.
 *   - uri_callback: The same as the 'uri_callback' key defined for the entity
 *     type in the EntityManager, but for the bundle only. When determining
 *     the URI of an entity, if a 'uri_callback' is defined for both the
 *     entity type and the bundle, the one for the bundle is used.
 *   - admin: An array of information that allows Field UI pages to attach
 *     themselves to the existing administration pages for the bundle.
 *     Elements:
 *     - path: the path of the bundle's main administration page, as defined
 *       in hook_menu(). If the path includes a placeholder for the bundle,
 *       the 'bundle argument', 'bundle helper' and 'real path' keys below
 *       are required.
 *     - bundle argument: The position of the placeholder in 'path', if any.
 *     - real path: The actual path (no placeholder) of the bundle's main
 *       administration page. This will be used to generate links.
 *     - access callback: As in hook_menu(). 'user_access' will be assumed if
 *       no value is provided.
 *     - access arguments: As in hook_menu().
 * - view_modes: An array describing the view modes for the entity type. View
 *   modes let entities be displayed differently depending on the context.
 *   For instance, a node can be displayed differently on its own page
 *   ('full' mode), on the home page or taxonomy listings ('teaser' mode), or
 *   in an RSS feed ('rss' mode). Modules taking part in the display of the
 *   entity (notably the Field API) can adjust their behavior depending on
 *   the requested view mode. An additional 'default' view mode is available
 *   for all entity types. This view mode is not intended for actual entity
 *   display, but holds default display settings. For each available view
 *   mode, administrators can configure whether it should use its own set of
 *   field display settings, or just replicate the settings of the 'default'
 *   view mode, thus reducing the amount of display configurations to keep
 *   track of. Keys of the array are view mode names. Each view mode is
 *   described by an array with the following key/value pairs:
 *   - label: The human-readable name of the view mode.
 *   - custom settings: A boolean specifying whether the view mode should by
 *     default use its own custom field display settings. If FALSE, entities
 *     displayed in this view mode will reuse the 'default' display settings
 *     by default (e.g. right after the module exposing the view mode is
 *     enabled), but administrators can later use the Field UI to apply custom
 *     display settings specific to the view mode.
 *
 * @param array $entity_info
 *   An associative array of all entity type definitions, keyed by the entity
 *   type name.
 *
 * @see \Drupal\Core\Entity\Entity
 * @see \Drupal\Core\Entity\EntityManager
 * @see entity_get_info()
 *
 * @todo Allow a module to add its bundles and view modes before other modules
 *   alter the definition.
 */
function hook_entity_info_alter(&$entity_info) {
  // Set the controller class for nodes to an alternate implementation of the
  // Drupal\Core\Entity\EntityStorageControllerInterface interface.
  $entity_info['node']['controller_class'] = 'Drupal\mymodule\MyCustomNodeStorageController';
}

/**
 * Act on entities when loaded.
 *
 * This is a generic load hook called for all entity types loaded via the
 * entity API.
 *
 * @param array $entities
 *   The entities keyed by entity ID.
 * @param string $entity_type
 *   The type of entities being loaded (i.e. node, user, comment).
 */
function hook_entity_load($entities, $entity_type) {
  foreach ($entities as $entity) {
    $entity->foo = mymodule_add_something($entity);
  }
}

/**
 * Act on an entity before it is about to be created or updated.
 *
 * @param Drupal\Core\Entity\EntityInterface $entity
 *   The entity object.
 */
function hook_entity_presave(Drupal\Core\Entity\EntityInterface $entity) {
  $entity->changed = REQUEST_TIME;
}

/**
 * Act on entities when inserted.
 *
 * @param Drupal\Core\Entity\EntityInterface $entity
 *   The entity object.
 */
function hook_entity_insert(Drupal\Core\Entity\EntityInterface $entity) {
  // Insert the new entity into a fictional table of all entities.
  db_insert('example_entity')
    ->fields(array(
      'type' => $entity->entityType(),
      'id' => $entity->id(),
      'created' => REQUEST_TIME,
      'updated' => REQUEST_TIME,
    ))
    ->execute();
}

/**
 * Act on entities when updated.
 *
 * @param Drupal\Core\Entity\EntityInterface $entity
 *   The entity object.
 */
function hook_entity_update(Drupal\Core\Entity\EntityInterface $entity) {
  // Update the entity's entry in a fictional table of all entities.
  db_update('example_entity')
    ->fields(array(
      'updated' => REQUEST_TIME,
    ))
    ->condition('type', $entity->entityType())
    ->condition('id', $entity->id())
    ->execute();
}

/**
 * Act before entity deletion.
 *
 * This hook runs after the entity type-specific predelete hook.
 *
 * @param Drupal\Core\Entity\EntityInterface $entity
 *   The entity object for the entity that is about to be deleted.
 */
function hook_entity_predelete(Drupal\Core\Entity\EntityInterface $entity) {
  // Count references to this entity in a custom table before they are removed
  // upon entity deletion.
  $id = $entity->id();
  $type = $entity->entityType();
  $count = db_select('example_entity_data')
    ->condition('type', $type)
    ->condition('id', $id)
    ->countQuery()
    ->execute()
    ->fetchField();

  // Log the count in a table that records this statistic for deleted entities.
  $ref_count_record = (object) array(
    'count' => $count,
    'type' => $type,
    'id' => $id,
  );
  drupal_write_record('example_deleted_entity_statistics', $ref_count_record);
}

/**
 * Respond to entity deletion.
 *
 * This hook runs after the entity type-specific delete hook.
 *
 * @param Drupal\Core\Entity\EntityInterface $entity
 *   The entity object for the entity that has been deleted.
 */
function hook_entity_delete(Drupal\Core\Entity\EntityInterface $entity) {
  // Delete the entity's entry from a fictional table of all entities.
  db_delete('example_entity')
    ->condition('type', $entity->entityType())
    ->condition('id', $entity->id())
    ->execute();
}

/**
 * Alter or execute an Drupal\Core\Entity\EntityFieldQuery.
 *
 * @param Drupal\Core\Entity\EntityFieldQuery $query
 *   An EntityFieldQuery. One of the most important properties to be changed is
 *   EntityFieldQuery::executeCallback. If this is set to an existing function,
 *   this function will get the query as its single argument and its result
 *   will be the returned as the result of EntityFieldQuery::execute(). This can
 *   be used to change the behavior of EntityFieldQuery entirely. For example,
 *   the default implementation can only deal with one field storage engine, but
 *   it is possible to write a module that can query across field storage
 *   engines. Also, the default implementation presumes entities are stored in
 *   SQL, but the execute callback could instead query any other entity storage,
 *   local or remote.
 *
 *   Note the $query->altered attribute which is TRUE in case the query has
 *   already been altered once. This happens with cloned queries.
 *   If there is a pager, then such a cloned query will be executed to count
 *   all elements. This query can be detected by checking for
 *   ($query->pager && $query->count), allowing the driver to return 0 from
 *   the count query and disable the pager.
 */
function hook_entity_query_alter(Drupal\Core\Entity\EntityFieldQuery $query) {
  $query->executeCallback = 'my_module_query_callback';
}

/**
 * Act on entities being assembled before rendering.
 *
 * @param Drupal\Core\Entity\EntityInterface $entity
 *   The entity object.
 * @param $view_mode
 *   The view mode the entity is rendered in.
 * @param $langcode
 *   The language code used for rendering.
 *
 * The module may add elements to $entity->content prior to rendering. The
 * structure of $entity->content is a renderable array as expected by
 * drupal_render().
 *
 * @see hook_entity_view_alter()
 * @see hook_comment_view()
 * @see hook_node_view()
 * @see hook_user_view()
 */
function hook_entity_view(Drupal\Core\Entity\EntityInterface $entity, $view_mode, $langcode) {
  $entity->content['my_additional_field'] = array(
    '#markup' => $additional_field,
    '#weight' => 10,
    '#theme' => 'mymodule_my_additional_field',
  );
}

/**
 * Alter the results of ENTITY_view().
 *
 * This hook is called after the content has been assembled in a structured
 * array and may be used for doing processing which requires that the complete
 * entity content structure has been built.
 *
 * If a module wishes to act on the rendered HTML of the entity rather than the
 * structured content array, it may use this hook to add a #post_render
 * callback. Alternatively, it could also implement hook_preprocess_HOOK() for
 * the particular entity type template, if there is one (e.g., node.tpl.php).
 * See drupal_render() and theme() for details.
 *
 * @param $build
 *   A renderable array representing the entity content.
 * @param Drupal\Core\Entity\EntityInterface $entity
 *   The entity object being rendered.
 *
 * @see hook_entity_view()
 * @see hook_comment_view_alter()
 * @see hook_node_view_alter()
 * @see hook_taxonomy_term_view_alter()
 * @see hook_user_view_alter()
 */
function hook_entity_view_alter(&$build, Drupal\Core\Entity\EntityInterface $entity) {
  if ($build['#view_mode'] == 'full' && isset($build['an_additional_field'])) {
    // Change its weight.
    $build['an_additional_field']['#weight'] = -10;

    // Add a #post_render callback to act on the rendered HTML of the entity.
    $build['#post_render'][] = 'my_module_node_post_render';
  }
}

/**
 * Act on entities as they are being prepared for view.
 *
 * Allows you to operate on multiple entities as they are being prepared for
 * view. Only use this if attaching the data during the entity loading phase
 * is not appropriate, for example when attaching other 'entity' style objects.
 *
 * @param array $entities
 *   The entities keyed by entity ID.
 * @param string $entity_type
 *   The type of entities being viewed (i.e. node, user, comment).
 */
function hook_entity_prepare_view($entities, $entity_type) {
  // Load a specific node into the user object for later theming.
  if (!empty($entities) && $entity_type == 'user') {
    $nodes = mymodule_get_user_nodes(array_keys($entities));
    foreach ($entities as $uid => $entity) {
      $entity->user_node = $nodes[$uid];
    }
  }
}

/**
 * Change the view mode of an entity that is being displayed.
 *
 * @param string $view_mode
 *   The view_mode that is to be used to display the entity.
 * @param Drupal\Core\Entity\EntityInterface $entity
 *   The entity that is being viewed.
 * @param array $context
 *   Array with additional context information, currently only contains the
 *   langcode the entity is viewed in.
 */
function hook_entity_view_mode_alter(&$view_mode, Drupal\Core\Entity\EntityInterface $entity, $context) {
  // For nodes, change the view mode when it is teaser.
  if ($entity->entityType() == 'node' && $view_mode == 'teaser') {
    $view_mode = 'my_custom_view_mode';
  }
}

/**
 * Define custom entity properties.
 *
 * @param string $entity_type
 *   The entity type for which to define entity properties.
 *
 * @return array
 *   An array of property information having the following optional entries:
 *   - definitions: An array of property definitions to add all entities of this
 *     type, keyed by property name. See
 *     Drupal\Core\TypedData\TypedDataManager::create() for a list of supported
 *     keys in property definitions.
 *   - optional: An array of property definitions for optional properties keyed
 *     by property name. Optional properties are properties that only exist for
 *     certain bundles of the entity type.
 *   - bundle map: An array keyed by bundle name containing the names of
 *     optional properties that entities of this bundle have.
 *
 * @see Drupal\Core\TypedData\TypedDataManager::create()
 * @see hook_entity_field_info_alter()
 * @see Drupal\Core\Entity\StorageControllerInterface::getPropertyDefinitions()
 */
function hook_entity_field_info($entity_type) {
  if (mymodule_uses_entity_type($entity_type)) {
    $info = array();
    $info['definitions']['mymodule_text'] = array(
      'type' => 'string_item',
      'list' => TRUE,
      'label' => t('The text'),
      'description' => t('A text property added by mymodule.'),
      'computed' => TRUE,
      'class' => '\Drupal\mymodule\EntityComputedText',
    );
    if ($entity_type == 'node') {
      // Add a property only to entities of the 'article' bundle.
      $info['optional']['mymodule_text_more'] = array(
        'type' => 'string_item',
        'list' => TRUE,
        'label' => t('More text'),
        'computed' => TRUE,
        'class' => '\Drupal\mymodule\EntityComputedMoreText',
      );
      $info['bundle map']['article'][0] = 'mymodule_text_more';
    }
    return $info;
  }
}

/**
 * Alter defined entity properties.
 *
 * @param array $info
 *   The property info array as returned by hook_entity_field_info().
 * @param string $entity_type
 *   The entity type for which entity properties are defined.
 *
 * @see hook_entity_field_info()
 */
function hook_entity_field_info_alter(&$info, $entity_type) {
  if (!empty($info['definitions']['mymodule_text'])) {
    // Alter the mymodule_text property to use a custom class.
    $info['definitions']['mymodule_text']['class'] = '\Drupal\anothermodule\EntityComputedText';
  }
}
