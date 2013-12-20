<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityType.
 */

namespace Drupal\Core\Entity;

/**
 */
class EntityType {

  /**
   * The name of the entity type.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the provider providing the type.
   *
   * @var string
   */
  public $provider;

  /**
   * The name of the entity type class.
   *
   * This is not provided manually, it will be added by the discovery mechanism.
   *
   * @var string
   */
  public $class;

  /**
   * The name of the entity type's base table.
   *
   * @todo This is only used by \Drupal\Core\Entity\DatabaseStorageController.
   *
   * @var string
   */
  public $base_table;

  /**
   * An associative array where the keys are the names of different controller
   * types (listed below) and the values are the names of the classes that
   * implement that controller:
   * - storage: The name of the class that is used to load the objects. The
   *   class must implement \Drupal\Core\Entity\EntityStorageControllerInterface.
   * - form: An associative array where the keys are the names of the different
   *   form operations (such as 'create', 'edit', or 'delete') and the values
   *   are the names of the controller classes for those operations. The name of
   *   the operation is passed also to the form controller's constructor, so
   *   that one class can be used for multiple entity forms when the forms are
   *   similar. The classes must implement
   *   \Drupal\Core\Entity\EntityFormControllerInterface
   * - list: The name of the class that provides listings of the entities. The
   *   class must implement \Drupal\Core\Entity\EntityListControllerInterface.
   * - render: The name of the class that is used to render the entities. The
   *   class must implement \Drupal\Core\Entity\EntityRenderControllerInterface.
   * - access: The name of the class that is used for access checks. The class
   *   must implement \Drupal\Core\Entity\EntityAccessControllerInterface.
   *   Defaults to \Drupal\Core\Entity\EntityAccessController.
   * - translation: The name of the controller class that should be used to
   *   handle the translation process. The class must implement
   *   \Drupal\translation_entity\EntityTranslationControllerInterface.
   *
   * @todo Interfaces from outside \Drupal\Core or \Drupal\Component should not
   *   be used here.
   *
   * @var array
   */
  public $controllers = array(
    'access' => 'Drupal\Core\Entity\EntityAccessController',
  );

  /**
   * Boolean indicating whether fields can be attached to entities of this type.
   *
   * @var bool (optional)
   */
  public $fieldable = FALSE;

  /**
   * Boolean indicating if the persistent cache of field data should be used.
   *
   * The persistent cache should usually only be disabled if a higher level
   * persistent cache is available for the entity type. Defaults to TRUE.
   *
   * @var bool (optional)
   */
  public $field_cache = TRUE;

  /**
   * The human-readable name of the type.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The human-readable name of the entity bundles, e.g. Vocabulary.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $bundle_label;

  /**
   * The name of a function that returns the label of the entity.
   *
   * The function takes an entity and optional langcode argument, and returns
   * the label of the entity. If langcode is omitted, the entity's default
   * language is used. The entity label is the main string associated with an
   * entity; for example, the title of a node or the subject of a comment. If
   * there is an entity object property that defines the label, use the 'label'
   * element of the 'entity_keys' return value component to provide this
   * information (see below). If more complex logic is needed to determine the
   * label of an entity, you can instead specify a callback function here, which
   * will be called to determine the entity label. See also the
   * \Drupal\Core\Entity\EntityInterface::label() method, which implements this
   * logic.
   *
   * @var string (optional)
   */
  public $label_callback;

  /**
   * Boolean indicating whether entities should be statically cached during a page request.
   *
   * @todo This is only used by \Drupal\Core\Entity\DatabaseStorageController.
   *
   * @var bool (optional)
   */
  public $static_cache = TRUE;

  /**
   * Boolean indicating whether entities of this type have multilingual support.
   *
   * At an entity level, this indicates language support and at a bundle level
   * this indicates translation support.
   *
   * @var bool (optional)
   */
  public $translatable = FALSE;

  /**
   * @todo content_translation_entity_info_alter() uses this but it is undocumented.
   *
   * @var array
   */
  public $translation = array();

  /**
   * An array describing how the Field API can extract certain information from
   * objects of this entity type:
   * - id: The name of the property that contains the primary ID of the entity.
   *   Every entity object passed to the Field API must have this property and
   *   its value must be numeric.
   * - revision: (optional) The name of the property that contains the revision
   *   ID of the entity. The Field API assumes that all revision IDs are unique
   *   across all entities of a type. This entry can be omitted if the entities
   *   of this type are not versionable.
   * - bundle: (optional) The name of the property that contains the bundle name
   *   for the entity. The bundle name defines which set of fields are attached
   *   to the entity (e.g. what nodes call "content type"). This entry can be
   *   omitted if this entity type exposes a single bundle (such that all
   *   entities have the same collection of fields). The name of this single
   *   bundle will be the same as the entity type.
   * - label: The name of the property that contains the entity label. For
   *   example, if the entity's label is located in $entity->subject, then
   *   'subject' should be specified here. If complex logic is required to build
   *   the label, a 'label_callback' should be defined instead (see the
   *   $label_callback block above for details).
   * - uuid (optional): The name of the property that contains the universally
   *   unique identifier of the entity, which is used to distinctly identify an
   *   entity across different systems.
   *
   * @var array
   */
  public $entity_keys = array(
    'revision' => '',
    'bundle' => '',
  );

  /**
   * An array describing how the Field API can extract the information it needs
   * from the bundle objects for this type (e.g Vocabulary objects for terms;
   * not applicable for nodes):
   * - bundle: The name of the property that contains the name of the bundle
   *   object.
   *
   * This entry can be omitted if this type's bundles do not exist as standalone
   * objects.
   *
   * @var array
   */
  public $bundle_keys;

  /**
   * The base router path for the entity type's field administration page.
   *
   * If the entity type has a bundle, include {bundle} in the path.
   *
   * For example, the node entity type specifies
   * "admin/structure/types/manage/{bundle}" as its base field admin path.
   *
   * @var string (optional)
   */
  public $route_base_path;

  /**
   * The prefix for the bundles of this entity type.
   *
   * For example, the comment bundle is prefixed with 'comment_node_'.
   *
   * @var string (optional)
   */
  public $bundle_prefix;

  /**
   * The base menu router path to which the entity admin user interface responds.
   *
   * It can be used to generate UI links and to attach additional router items
   * to the entity UI in a generic fashion.
   *
   * @var string (optional)
   */
  public $menu_base_path;

  /**
   * The menu router path to be used to view the entity.
   *
   * @var string (optional)
   */
  public $menu_view_path;

  /**
   * The menu router path to be used to edit the entity.
   *
   * @var string (optional)
   */
  public $menu_edit_path;

  /**
   * A string identifying the menu loader in the router path.
   *
   * @var string (optional)
   */
  public $menu_path_wildcard;

  /**
   * Link templates using the URI template syntax.
   *
   * Links are an array of standard link relations to the URI template that
   * should be used for them. Where possible, link relationships should use
   * established IANA relationships rather than custom relationships.
   *
   * Every entity type should, at minimum, define "canonical", which is the
   * pattern for URIs to that entity. Even if the entity will have no HTML page
   * exposed to users it should still have a canonical URI in order to be
   * compatible with web services. Entities that will be user-editable via an
   * HTML page must also define an "edit-form" relationship.
   *
   * By default, the following placeholders are supported:
   * - entityType: The machine name of the entity type.
   * - bundle: The bundle machine name of the entity.
   * - id: The unique ID of the entity.
   * - uuid: The UUID of the entity.
   * - [entityType]: The entity type itself will also be a valid token for the
   *   ID of the entity. For instance, a placeholder of {node} used on the Node
   *   class would have the same value as {id}. This is generally preferred
   *   over "id" for better self-documentation.
   *
   * Specific entity types may also expand upon this list by overriding the
   * uriPlaceholderReplacements() method.
   *
   * @link http://www.iana.org/assignments/link-relations/link-relations.xml @endlink
   * @link http://tools.ietf.org/html/rfc6570 @endlink
   *
   * @var array
   */
  public $links = array(
    'canonical' => '/entity/{entityType}/{id}',
  );

  /**
   * The default operation for a form controller.
   *
   * @var string
   */
  public $default_operation = 'default';

  /**
   * @var callable
   */
  public $uri_callback;

  /**
   * Specifies whether a module exposing permissions for the current entity type
   * should use entity-type level granularity, bundle level granularity or just
   * skip this entity. The allowed values are respectively "entity_type",
   * "bundle" or FALSE.
   *
   * @var string|bool (optional)
   */
  public $permission_granularity = 'entity_type';

  /**
   * @todo
   */
  public $config_prefix;

  /**
   * Constructs a EntityType object.
   *
   * Builds up the plugin definition and invokes the get() method for any
   * classed annotations that were used.
   */
  public function __construct($values) {
    foreach ($values as $key => $value) {
      $this->{$key} = $value;
    }
  }

  /**
   * Returns the name of the provider providing the entity type.
   *
   * @return string
   */
  public function getProvider() {
    return $this->provider;
  }

  /**
   * Returns the name of the entity type class.
   *
   * This is not provided manually, it will be added by the discovery mechanism.
   *
   * @return string
   */
  public function getClass() {
    return $this->class;
  }

  /**
   * Sets the name of the entity type class.
   *
   * @param string $class
   *   The class + full namespace of the entity type.
   *
   * @return string
   */
  public function setClass($class) {
    return $this->class = $class;
  }

  /**
   * The name of the entity type's base table.
   *
   * @todo This is only used by \Drupal\Core\Entity\DatabaseStorageController.
   *
   * @return string
   */
  public function getBaseTable() {
    return $this->base_table;
  }

  /**
   * Whether the entity type has a base table
   *
   * @return bool
   *   Returns TRUE if the entity type has a base table else FALSE.
   */
  public function hasBaseTable() {
    return isset($this->base_table);
  }

  /**
   * The data table of the entity type.
   *
   * @return string|FALSE
   *   Returns the data table else FALSE.
   */
  public function getDataTable() {
    return $this->hasDataTable() ? $this->data_table : FALSE;
  }

  /**
   * Whether the entity type has a data table.
   *
   * @return bool
   *   Returns TRUE if the entity type has a data table else FALSE.
   */
  public function hasDataTable() {
    return isset($this->data_table);
  }

  /**
   * Whether the entity type has a revision table.
   *
   * @return bool
   *   Returns TRUE if the entity type has a revision table else FALSE.
   */
  public function hasRevisionTable() {
    return isset($this->revision_table);
  }

  /**
   * The revision table of the entity type.
   *
   * @return string|FALSE
   *   Returns the revision table else FALSE.
   */
  public function getRevisionTable() {
    return $this->hasRevisionTable() ? $this->revision_table : FALSE;
  }

  /**
   * An associative array where the keys are the names of different controller
   * types (listed below) and the values are the names of the classes that
   * implement that controller:
   * - storage: The name of the class that is used to load the objects. The
   *   class must implement \Drupal\Core\Entity\EntityStorageControllerInterface.
   * - form: An associative array where the keys are the names of the different
   *   form operations (such as 'create', 'edit', or 'delete') and the values
   *   are the names of the controller classes for those operations. The name of
   *   the operation is passed also to the form controller's constructor, so
   *   that one class can be used for multiple entity forms when the forms are
   *   similar. The classes must implement
   *   \Drupal\Core\Entity\EntityFormControllerInterface
   * - list: The name of the class that provides listings of the entities. The
   *   class must implement \Drupal\Core\Entity\EntityListControllerInterface.
   * - render: The name of the class that is used to render the entities. The
   *   class must implement \Drupal\Core\Entity\EntityRenderControllerInterface.
   * - access: The name of the class that is used for access checks. The class
   *   must implement \Drupal\Core\Entity\EntityAccessControllerInterface.
   *   Defaults to \Drupal\Core\Entity\EntityAccessController.
   * - translation: The name of the controller class that should be used to
   *   handle the translation process. The class must implement
   *   \Drupal\translation_entity\EntityTranslationControllerInterface.
   *
   * @todo Interfaces from outside \Drupal\Core or \Drupal\Component should not
   *   be used here.
   *
   * @return array
   */
  public function getControllers() {
    return $this->controllers + array(
      'form' => array(),
      'access' => 'Drupal\Core\Entity\EntityAccessController',
    );
  }

  /**
   * @param string $controller
   *
   * @return string|array
   */
  public function getController($controller) {
    $controllers = $this->getControllers();
    return $controllers[$controller];
  }

  /**
   * @param string $controller
   * @param string|array $value
   */
  public function setController($controller, $value) {
    $this->controllers[$controller] = $value;
  }

  /**
   * @param string $controller
   *
   * @return bool
   */
  public function hasController($controller) {
    $controllers = $this->getControllers();
    return isset($controllers[$controller]);
  }

  /**
   * Boolean indicating whether fields can be attached to entities of this type.
   *
   * @return bool (optional)
   *   Returns TRUE if the entity type can has fields, otherwise FALSE.
   */
  public function isFieldable() {
    return isset($this->fieldable) ? $this->fieldable : FALSE;
  }

  /**
   * Boolean indicating if the persistent cache of field data should be used.
   *
   * The persistent cache should usually only be disabled if a higher level
   * persistent cache is available for the entity type. Defaults to TRUE.
   *
   * @return bool (optional)
   *   Returns TRUE if fields on this entity type can be cached, otherwise FALSE.
   */
  public function fieldsCacheable() {
    return isset($this->field_cache) ? $this->field_cache : TRUE;
  }

  /**
   * The human-readable name of the type.
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * The human-readable name of the entity bundles, e.g. Vocabulary.
   */
  public function getBundleLable() {
    return $this->bundle_label;
  }

  /**
   * The name of a function that returns the label of the entity.
   *
   * The function takes an entity and optional langcode argument, and returns
   * the label of the entity. If langcode is omitted, the entity's default
   * language is used. The entity label is the main string associated with an
   * entity; for example, the title of a node or the subject of a comment. If
   * there is an entity object property that defines the label, use the 'label'
   * element of the 'entity_keys' return value component to provide this
   * information (see below). If more complex logic is needed to determine the
   * label of an entity, you can instead specify a callback function here, which
   * will be called to determine the entity label. See also the
   * \Drupal\Core\Entity\EntityInterface::label() method, which implements this
   * logic.
   *
   * @return callable
   *   A callback for the label.
   */
  public function getLabelCallback() {
    return isset($this->label_callback) ? $this->label_callback : '';
  }

  /**
   * @return callable
   */
  public function getUriCallback() {
    return $this->uri_callback;
  }

  /**
   * @param callable $callback
   */
  public function setUriCallback($callback) {
    $this->uri_callback = $callback;
  }

  /**
   * Boolean indicating whether entities should be statically cached during a page request.
   *
   * @todo This is only used by \Drupal\Core\Entity\DatabaseStorageController.
   *
   * @return bool (optional)
   *   Returns TRUE if the entity type should be statically cached, otherwise FALSE.
   */
  public function staticCacheable() {
    return isset($this->static_cache) ? $this->static_cache : TRUE;
  }

  /**
   * An array describing how the Field API can extract certain information from
   * objects of this entity type:
   * - id: The name of the property that contains the primary ID of the entity.
   *   Every entity object passed to the Field API must have this property and
   *   its value must be numeric.
   * - revision: (optional) The name of the property that contains the revision
   *   ID of the entity. The Field API assumes that all revision IDs are unique
   *   across all entities of a type. This entry can be omitted if the entities
   *   of this type are not versionable.
   * - bundle: (optional) The name of the property that contains the bundle name
   *   for the entity. The bundle name defines which set of fields are attached
   *   to the entity (e.g. what nodes call "content type"). This entry can be
   *   omitted if this entity type exposes a single bundle (such that all
   *   entities have the same collection of fields). The name of this single
   *   bundle will be the same as the entity type.
   * - label: The name of the property that contains the entity label. For
   *   example, if the entity's label is located in $entity->subject, then
   *   'subject' should be specified here. If complex logic is required to build
   *   the label, a 'label_callback' should be defined instead (see the
   *   $label_callback block above for details).
   * - uuid (optional): The name of the property that contains the universally
   *   unique identifier of the entity, which is used to distinctly identify an
   *   entity across different systems.
   *
   * @var array
   */
  public function getKeys() {
    return $this->entity_keys + array('revision' => '', 'bundle' => '');
  }

  /**
   * @param $key
   *
   * @return bool
   */
  public function getKey($key) {
    $keys = $this->getKeys();
    return isset($keys[$key]) ? $keys[$key] : FALSE ;
  }

  /**
   * @param $key
   *
   * @return bool
   */
  public function hasKey($key) {
    $keys = $this->getKeys();
    return !empty($keys[$key]);
  }

  /**
   * Get all bundle keys defined on the annotation.
   *
   * @return array
   *   An array describing how the Field API can extract the information it needs
   *   from the bundle objects for this type (e.g Vocabulary objects for terms;
   *   not applicable for nodes):
   *   - bundle: The name of the property that contains the name of the bundle
   *     object.
   */
  public function getBundleKeys() {
    return isset($this->bundle_keys) ? $this->bundle_keys : array();
  }

  /**
   * Returns a single bundle key.
   *
   * @param string $name
   *   The name of the bundle key.
   *
   * @return string
   *   The value of the bundle key.
   */
  public function getBundleKey($name) {
    return isset($this->bundle_keys[$name]) ? $this->bundle_keys[$name] : '';
  }

  /**
   * @return string
   */
  public function getConfigPrefix() {
    return isset($this->config_prefix) ? $this->config_prefix : '';
  }

  /**
   * Get the base router path for the entity type's field administration page.
   *
   * @return string
   *   The router base path.
   *
   * @see \Drupal\Core\Entity\Annotation\EntityType::$route_base_path
   */
  public function getRouteBasePath() {
    return isset($this->route_base_path) ? $this->route_base_path : NULL;
  }

  /**
   * Get the prefix for the bundles of this entity type.
   *
   * @return string
   *   The prefix.
   *
   * @see \Drupal\Core\Entity\Annotation\EntityType::$bundle_prefix
   */
  public function getBundlePrefix() {
    return isset($this->bundle_prefix) ? $this->bundle_prefix : '';
  }

  /**
   * Get the base menu router path for entity admin user interface paths.
   *
   * @return string
   *   The base menu router path.
   *
   * @see \Drupal\Core\Entity\Annotation\EntityType::$menu_base_path
   */
  public function getMenuBasePath() {
    return isset($this->menu_base_path) ? $this->menu_base_path : '';
  }

  /**
   * Get the menu router path to be used to view the entity.
   *
   * @return string
   *   A menu router path.
   *
   * @see \Drupal\Core\Entity\Annotation\EntityType::$menu_view_path
   */
  public function getMenuViewPath() {
    return isset($this->menu_view_path) ? $this->menu_view_path : '';
  }

  /**
   * Get the menu router path to be uesd to edit the entity.
   *
   * @return string
   *   A menu router path.
   *
   * @see \Drupal\Core\Entity\Annotation\EntityType::$menu_edit_path
   */
  public function getMenuEditPath() {
    return isset($this->menu_edit_path) ? $this->menu_edit_path : '';
  }

  /**
   * Get the identifier in the router path.
   *
   * @return string
   *   A identifier.
   *
   * @see \Drupal\Core\Entity\Annotation\EntityType::$menu_path_wildcard
   */
  public function getMenuPathWildcard() {
    return isset($this->menu_path_wildcard) ? $this->menu_path_wildcard : '';
  }

  /**
   * Returns the granularity of the permissions.
   *
   * @return string|FALSE
   *   Returns either "entity_type", "bundle" or FALSE.
   *
   * @see \Drupal\Core\Entity\Annotation\EntityType::$permission_granularity
   */
  public function getPermissionGranularity() {
    return $this->permission_granularity;
  }

  /**
   * Returns whether the entity type is translatable.
   *
   * @return bool
   *   Returns TRUE if the entity type is translatable, otherwise FALSE.
   *
   * @see \Drupal\Core\Entity\Annotation\\Drupal\Core\Entity\Annotation\EntityType::$translatable
   */
  public function isTranslatable() {
    return isset($this->translatable) ? (bool) $this->translatable : FALSE;
  }

  public function setTranslatable($bool = TRUE) {
    $this->translatable = $bool;
  }

  /**
   * @todo
   *
   * @see \Drupal\Core\Entity\Annotation\\Drupal\Core\Entity\Annotation\EntityType::$translation
   */
  public function getTranslation() {
    return isset($this->translation) ? $this->translation : array();
  }

  /**
   * @return string
   */
  public function getDefaultOperation() {
    return isset($this->default_operation) ? $this->default_operation : 'default';
  }

  /**
   * Returns an arbitrary value of the entity type definition.
   *
   * @param string $name
   *   The name of the entity type definition.
   *
   * @return mixed
   *   The value of the entity type definition.
   */
  public function get($name) {
    return isset($this->values[$name]) ? $this->values[$name] : NULL;
  }

}
