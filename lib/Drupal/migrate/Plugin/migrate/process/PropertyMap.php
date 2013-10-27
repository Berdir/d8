<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\PropertyMap.
 */

namespace Drupal\migrate\Plugin\migrate\process;
use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\ProcessInterface;
use Drupal\migrate\Row;

/**
 * This class tracks mappings between source and destination.
 *
 * @PluginId("property_map")
 */
class PropertyMap extends PluginBase implements ProcessInterface {

  /**
   * Destination field name for the mapping. If empty, the mapping is just a
   * stub for annotating the source field.
   *
   * @var string
   */
  protected $destination;

  /**
   * Source field name for the mapping. If empty, the defaultValue will be
   * applied.
   *
   * @var string
   */
  protected $source;

  /**
   * @var int
   */
  const MAPPING_SOURCE_CODE = 1;
  const MAPPING_SOURCE_DB = 2;
  protected $mappingSource = self::MAPPING_SOURCE_CODE;

  /**
   * Default value for simple mappings, when there is no source mapping or the
   * source field is empty. If both this and the sourceProperty are omitted, the
   * mapping is just a stub for annotating the destination field.
   *
   * @var mixed
   */
  protected $default;

  /**
   * Separator string. If present, the destination field will be set up as an
   * array of values exploded from the corresponding source field.
   *
   * @var string
   */
  protected $separator;

  /**
   * Array of callbacks to be called on a source value.
   *
   * @var array
   */
  protected $callbacks = array();

  /**
   * An associative array with keys:
   *   - table: The table for querying for a duplicate.
   *   - property: The property for querying for a duplicate.
   *
   * @todo: Let fields declare this data and a replacement pattern. Then
   * developers won't have to specify this.
   *
   * @var string
   */
  protected $dedupe;

  /**
   * Optional notes about a mapping.
   *
   * @var string
   */
  protected $description = '';

  protected $issueGroup;

  /**
   * An optional issue ID corresponding to a mapping.
   *
   * @var string
   */
  protected $issueNumber;

  /**
   * An optional priority corresponding to a mapping.
   *
   * @var string
   */
  protected $issuePriority = self::ISSUE_PRIORITY_OK;

  /**
   * Priority levels that are available for mappings.
   *
   * @var string
   */
  const ISSUE_PRIORITY_OK = 1;
  const ISSUE_PRIORITY_LOW = 2;
  const ISSUE_PRIORITY_MEDIUM = 3;
  const ISSUE_PRIORITY_BLOCKER = 4;

  public static $priorities = array();

  protected $configuration = array();

  protected $source_migration = array();

  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    // Must have one or the other
    if (empty($configuration['destination'])) {
      throw new \Exception('Property mappings must have a destination property.');
    }
    if (!isset($configuration['default']) && empty($configuration['source'])) {
      throw new \Exception('Property mappings must have a source property or a default.');
    }
    $defined_properties = array_keys(get_class_vars(__CLASS__));
    $this->issueGroup = $this->t('Done');
    foreach ($defined_properties as $key) {
      if ($key != 'configuration' && isset($configuration[$key])) {
        $this->$key = $configuration[$key];
        unset($configuration[$key]);
      }
    }
    $this->configuration = $configuration;
    if (count(self::$priorities) == 0) {
      self::$priorities[self::ISSUE_PRIORITY_OK] = $this->t('OK');
      self::$priorities[self::ISSUE_PRIORITY_LOW] = $this->t('Low');
      self::$priorities[self::ISSUE_PRIORITY_MEDIUM] = $this->t('Medium');
      self::$priorities[self::ISSUE_PRIORITY_BLOCKER] = $this->t('Blocker');
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Apply field mappings to a data row received from the source, returning
   * a populated destination object.
   */
  public function apply(Row $row, MigrateExecutable $migrate_executable) {
    $destination_values = NULL;

    // If there's a source mapping, and a source value in the data row, copy
    // to the destination
    if ($this->source && $row->hasSourceProperty($this->source)) {
      $destination_values = $row->getSourceProperty($this->source);
    }
    // Otherwise, apply the default value (if any)
    elseif (isset($this->default)) {
      $destination_values = $this->default;
    }

    // If there's a separator specified for this destination, then it
    // will be populated as an array exploded from the source value
    if ($this->separator && isset($destination_values)) {
      $destination_values = explode($this->separator, $destination_values);
    }

    // If a source migration is supplied, use the current value for this property
    // to look up a destination ID from the provided migration
    if ($this->source_migration && isset($destination_values)) {
      $destination_values = $migrate_executable->handleSourceMigration($this->source_migration, $destination_values, $this->default, $this);
    }

    // Call any designated callbacks
    foreach ($this->callbacks as $callback) {
      if (isset($destination_values) && is_callable($callback)) {
        $destination_values = call_user_func($callback, $destination_values);
      }
    }

    // If specified, assure a unique value for this property.
    if ($this->dedupe && isset($destination_values)) {
      $destination_values = $migrate_executable->handleDedupe($this->dedupe, $destination_values);
    }

    // Store the destination together with possible configuration.
    if (isset($destination_values)) {
      $keys = explode(':', $this->destination);
      $row->setDestinationPropertyDeep(array_merge($keys, array('values')), $destination_values);
      $row->setDestinationPropertyDeep(array_merge($keys, array('configuration')), $this->configuration);
    }
  }
}
