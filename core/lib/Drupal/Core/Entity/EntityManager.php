<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityManager.
 */

namespace Drupal\Core\Entity;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Manages entity type plugin definitions.
 *
 * Each entity type definition array is set in the entity type plugin's
 * annotation and altered by hook_entity_info_alter(). The definition includes
 * the following keys:
 * - module: The name of the module providing the type.
 * - class: The name of the entity type class. Defaults to
 *   Drupal\Core\Entity\Entity.
 * - base_table: The name of the entity type's base table. Used by
 *   Drupal\Core\Entity\DatabaseStorageController.
 * - controller_class: The name of the class that is used to load the objects.
 *   The class must implement
 *   Drupal\Core\Entity\EntityStorageControllerInterface. Defaults to
 *   Drupal\Core\Entity\DatabaseStorageController.
 * - fieldable: (optional) Boolean indicating whether fields can be attached
 *   to entities of this type. Defaults to FALSE.
 * - field_cache: (optional) Boolean indicating whether the Field API's
 *   Field API's persistent cache of field data should be used. The persistent
 *   cache should usually only be disabled if a higher level persistent cache
 *   is available for the entity type. Defaults to TRUE.
 * - form_controller_class: (optional) An associative array where the keys
 *   are the names of the different form operations (such as 'create',
 *   'edit', or 'delete') and the values are the names of the controller
 *   classes for those operations. The name of the operation is passed also
 *   to the form controller's constructor, so that one class can be used for
 *   multiple entity forms when the forms are similar. Defaults to
 *   Drupal\Core\Entity\EntityFormController.
 * - label: The human-readable name of the type.
 * - label_callback: (optional) A function taking an entity and optional
 *   langcode argument, and returning the label of the entity. If langcode is
 *   omitted, the entity's default language is used.
 *   The entity label is the main string associated with an entity; for
 *   example, the title of a node or the subject of a comment. If there is an
 *   entity object property that defines the label, use the 'label' element
 *   of the 'entity_keys' return value component to provide this information
 *   (see below). If more complex logic is needed to determine the label of
 *   an entity, you can instead specify a callback function here, which will
 *   be called to determine the entity label. See also the
 *   Drupal\Core\Entity\Entity::label() method, which implements this logic.
 * - list_controller_class: (optional) The name of the class that provides
 *   listings of the The class must implement
 *   Drupal\Core\Entity\EntityListControllerInterface. Defaults to
 *   Drupal\Core\Entity\EntityListController.
 * - render_controller_class: The name of the class that is used to render the
 *   entities. Defaults to Drupal\Core\Entity\EntityRenderController.
 * - static_cache: (optional) Boolean indicating whether entities should be
 *   statically cached during a page request. Used by
 *   Drupal\Core\Entity\DatabaseStorageController. Defaults to TRUE.
 * - translation: (optional) An associative array of modules registered as
 *   field translation handlers. Array keys are the module names, and array
 *   values can be any data structure the module uses to provide field
 *   translation. If the value is empty, the module will not be used as a
 *   translation handler.
 * - entity_keys: An array describing how the Field API can extract certain
 *   information from objects of this entity type. Elements:
 *   - id: The name of the property that contains the primary ID of the
 *     entity. Every entity object passed to the Field API must have this
 *     property and its value must be numeric.
 *   - revision: (optional) The name of the property that contains the
 *     revision ID of the entity. The Field API assumes that all revision IDs
 *     are unique across all entities of a type. This entry can be omitted if
 *     the entities of this type are not versionable.
 *   - bundle: (optional) The name of the property that contains the bundle
 *     name for the entity. The bundle name defines which set of fields are
 *     attached to the entity (e.g. what nodes call "content type"). This
 *     entry can be omitted if this entity type exposes a single bundle (such
 *     that all entities have the same collection of fields). The name of
 *     this single bundle will be the same as the entity type.
 *   - label: The name of the property that contains the entity label. For
 *     example, if the entity's label is located in $entity->subject, then
 *     'subject' should be specified here. If complex logic is required to
 *     build the label, a 'label_callback' should be defined instead (see
 *     the 'label_callback' section above for details).
 *   - uuid (optional): The name of the property that contains the universally
 *     unique identifier of the entity, which is used to distinctly identify
 *     an entity across different systems.
 * - bundle_keys: An array describing how the Field API can extract the
 *   information it needs from the bundle objects for this type (e.g
 *   Vocabulary objects for terms; not applicable for nodes). This entry can
 *   be omitted if this type's bundles do not exist as standalone objects.
 *   Elements:
 *   - bundle: The name of the property that contains the name of the bundle
 *     object.
 *
 * The defaults for the plugin definition are provided in
 * \Drupal\Core\Entity\EntityManager::defaults.
 *
 * Additional keys may be added to the entity definition with
 * hook_entity_info_alter(). In particular, lists of the entity type's bundles
 * and view modes are added there. See hook_entity_info_alter() for more
 * information.
 *
 * @see \Drupal\Core\Entity\Entity
 * @see entity_get_info()
 * @see hook_entity_info_alter()
 *
 * @todo Allow a module to add its bundles and view modes before other modules
 *   alter the definition.
 */
class EntityManager extends PluginManagerBase {

  /**
   * The cache bin used for entity plugin definitions.
   *
   * @var string
   */
  protected $cacheBin = 'cache';

  /**
   * The cache key used for entity plugin definitions.
   *
   * @var string
   */
  protected $cacheKey = 'entity_info';

  /**
   * The cache expiration for entity plugin definitions.
   *
   * @var int
   */
  protected $cacheExpire = CacheBackendInterface::CACHE_PERMANENT;

  /**
   * The cache tags used for entity plugin definitions.
   *
   * @var array
   */
  protected $cacheTags = array('entity_info' => TRUE);

  /**
   * The default values for optional keys of the entity plugin definition.
   *
   * @var array
   */
  protected $defaults = array(
    'class' => 'Drupal\Core\Entity\Entity',
    'controller_class' => 'Drupal\Core\Entity\DatabaseStorageController',
    'entity_keys' => array(
      'revision' => '',
      'bundle' => '',
    ),
    'fieldable' => FALSE,
    'field_cache' => TRUE,
    'form_controller_class' => array(
      'default' => 'Drupal\Core\Entity\EntityFormController',
    ),
    'list_controller_class' => 'Drupal\Core\Entity\EntityListController',
    'render_controller_class' => 'Drupal\Core\Entity\EntityRenderController',
    'static_cache' => TRUE,
    'translation' => array(),
    // Bundles and view modes are added in hook_entity_info_alter(), but we
    // provide empty lists of them by default.
    'bundles' => array(),
    'view_modes' => array(),
  );

  /**
   * Constructs a new Entity plugin manager.
   */
  public function __construct() {
    // Allow the plugin definition to be altered by hook_entity_info_alter().
    $this->discovery = new AlterDecorator(new AnnotatedClassDiscovery('Core', 'Entity'), 'entity_info');
    $this->factory = new DefaultFactory($this);

    // Entity type plugins includes translated strings, so each language is
    // cached separately.
    $this->cacheKey .= ':' . language(LANGUAGE_TYPE_INTERFACE)->langcode;
  }

  /**
   * Overrides Drupal\Component\Plugin\PluginManagerBase::getDefinition().
   */
  public function getDefinition($plugin_id) {
    $definitions = $this->getDefinitions();
    return isset($definitions[$plugin_id]) ? $definitions[$plugin_id] : NULL;
  }

  /**
   * Overrides Drupal\Component\Plugin\PluginManagerBase::getDefinitions().
   */
  public function getDefinitions() {
    // Because \Drupal\Core\Plugin\Discovery\CacheDecorator runs before
    // definitions are processed and does not support cache tags, we perform our
    // own caching.
    if ($cache = cache($this->cacheBin)->get($this->cacheKey)) {
      return $cache->data;
    }
    else {
      // @todo Remove array_filter() once http://drupal.org/node/1780396 is
      //   resolved.
      $definitions = array_filter(parent::getDefinitions());
      cache($this->cacheBin)->set($this->cacheKey, $definitions, $this->cacheExpire, $this->cacheTags);
      return $definitions;
    }
  }

  /**
   * Overrides Drupal\Component\Plugin\PluginManagerBase::processDefinition().
   */
  protected function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    // @todo Remove this check once http://drupal.org/node/1780396 is resolved.
    if (!module_exists($definition['module'])) {
      $definition = NULL;
      return;
    }

    foreach ($definition['view_modes'] as $view_mode => $view_mode_info) {
      $definition['view_modes'][$view_mode] += array(
        'custom settings' => FALSE,
      );
    }

    // If no bundle key is provided, assume a single bundle, named after
    // the entity type.
    if (empty($definition['entity_keys']['bundle']) && empty($definition['bundles'])) {
      $definition['bundles'] = array($plugin_id => array('label' => $definition['label']));
    }
    // Prepare entity schema fields SQL info for
    // Drupal\Core\Entity\DatabaseStorageControllerInterface::buildQuery().
    if (isset($definition['base_table'])) {
      $definition['schema_fields_sql']['base_table'] = drupal_schema_fields_sql($definition['base_table']);
      if (isset($definition['revision_table'])) {
        $definition['schema_fields_sql']['revision_table'] = drupal_schema_fields_sql($definition['revision_table']);
      }
    }
  }

}
