<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\field_mapping\Simple.
 */

namespace Drupal\migrate\Plugin\migrate\column_mapping;
use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateRow;
use Drupal\migrate\Plugin\ColumnMappingInterface;

/**
 * This class tracks mappings between source and destination.
 *
 * @PluginId("simple")
 */
class Simple extends PluginBase implements ColumnMappingInterface {

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
   * source field is empty. If both this and the sourceColumn are omitted, the
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
   * @var string
   */
  protected $callbacks = array();

  /**
   * An associative array with keys:
   *   - table: The table for querying for a duplicate.
   *   - column: The column for querying for a duplicate.
   *
   * @todo: Let fields declare this data and a replacement pattern. Then
   * developers won't have to specify this.
   *
   * @var string
   */
  protected $dedupe;

  protected $description = '';

  protected $issueGroup;

  protected $issueNumber;

  protected $issuePriority = self::ISSUE_PRIORITY_OK;

  const ISSUE_PRIORITY_OK = 1;
  const ISSUE_PRIORITY_LOW = 2;
  const ISSUE_PRIORITY_MEDIUM = 3;
  const ISSUE_PRIORITY_BLOCKER = 4;

  public static $priorities = array();

  protected $configuration = array();

  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    // Must have one or the other
    if (!isset($configuration['destination']) && !isset($configuration['source'])) {
      throw new \Exception('Column mappings must have a destination field or a source field');
    }
    $defined_properties = array_keys(get_class_vars(__CLASS__));
    $this->issueGroup = t('Done');
    foreach ($defined_properties as $key) {
      if ($key != 'configuration' && isset($configuration[$key])) {
        $this->$key = $configuration[$key];
        unset($configuration[$key]);
      }
    }
    $this->configuration = $configuration;
    if (count(self::$priorities) == 0) {
      self::$priorities[self::ISSUE_PRIORITY_OK] = t('OK');
      self::$priorities[self::ISSUE_PRIORITY_LOW] = t('Low');
      self::$priorities[self::ISSUE_PRIORITY_MEDIUM] = t('Medium');
      self::$priorities[self::ISSUE_PRIORITY_BLOCKER] = t('Blocker');
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Apply field mappings to a data row received from the source, returning
   * a populated destination object.
   */
  public function apply(MigrateRow $row, MigrateExecutable $migrate_executable) {
    // When updating existing items, make sure we don't create a destination
    // column that is not mapped to anything (a source column or a default value)
    if ($this->destination && ($this->source || isset($this->default))) {
      $destination_values = NULL;

        // If there's a source mapping, and a source value in the data row, copy
        // to the destination
        if ($this->source && property_exists($row->source, $this->source)) {
          $destination_values = $row->{$this->source};
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

        // If a source migration is supplied, use the current value for this column
        // to look up a destination ID from the provided migration
        if ($this->source_migration && isset($destination_values)) {
          $destination_values = $migrate_executable->handleSourceMigration($this->source_migration, $destination_values, $this->default, $this);
        }

        // Call any designated callbacks
        foreach ($this->callbacks as $callback) {
          if (isset($destination_values)) {
            $destination_values = call_user_func($callback, $destination_values);
          }
        }

        // If specified, assure a unique value for this property.
        if ($this->dedupe && isset($destination_values)) {
          $destination_values = $migrate_executable->handleDedupe($dedupe, $destination_values);
        }

        // Assign any arguments
        if (isset($destination_values)) {
          $destination = explode(':', $this->destination);
          $destination_column = $destination[0];
          if (isset($destination[1])) {
            $subcolumn = $destination[1];
          }
          $row->destination->$destination_column = array(
            'configuration' => $configuration,

          );
        }

        // Are we dealing with the primary value of the destination column, or a
        // subcolumn?
        if (isset($destination[1])) {
          $subcolumn = $destination[1];
          // We're processing the subcolumn before the primary value, initialize it
          if (!property_exists($this->destinationValues, $destination_column)) {
            $this->destinationValues->$destination_column = array();
          }
          // We have a value, and need to convert to an array so we can add
          // arguments.
          elseif (!is_array($this->destinationValues->$destination_column)) {
            $this->destinationValues->$destination_column = array($this->destinationValues->$destination_column);
          }
          // Add the subcolumn value to the arguments array.
          $this->destinationValues->{$destination_column}['arguments'][$subcolumn] = $destination_values;
        }
        // Just the primary value, the first time through for this column, simply
        // set it.
        elseif (!property_exists($this->destinationValues, $destination_column)) {
          $this->destinationValues->$destination_column = $destination_values;
        }
        // We've seen a subcolumn, so add as an array value.
        else {
          $this->destinationValues->{$destination_column}[] = $destination_values;
        }
      }
    }
  }
}
