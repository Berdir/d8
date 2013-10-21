<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\SqlBase.
 */

namespace Drupal\migrate\Plugin\migrate\source;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\MigrateIdMapInterface;

abstract class SqlBase extends SourceBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Database\Query\SelectInterface
   */
  protected $query;

  function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, CacheBackendInterface $cache, KeyValueStoreInterface $highwater_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $cache, $highwater_storage);
    $this->mapJoinable = FALSE;
  }

  protected function getDatabase() {
    if (!isset($this->database)) {
      $key = 'migrate_' . $this->migration->id();
      Database::addConnectionInfo('default', $key, $this->configuration['connection']);
      $this->database = Database::getConnection('default', $key);
    }
  }

  /**
   * Implementation of MigrateSource::performRewind().
   *
   * We could simply execute the query and be functionally correct, but
   * we will take advantage of the PDO-based API to optimize the query up-front.
   */
  protected function performRewind() {
    $this->result = NULL;
    $this->query = clone $this->query();

    // Get the key values, for potential use in joining to the map table, or
    // enforcing idlist.
    $keys = array();
    foreach ($this->migration->get('sourceKeys') as $field_name => $field_schema) {
      if (isset($field_schema['alias'])) {
        $field_name = $field_schema['alias'] . '.' . $field_name;
      }
      $keys[] = $field_name;
    }

    // The rules for determining what conditions to add to the query are as
    // follows (applying first applicable rule)
    // 1. If idlist is provided, then only process items in that list (AND key
    //    IN (idlist)). Only applicable with single-value keys.
    if ($this->idList) {
      $this->query->condition($keys[0], $this->idList, 'IN');
    }
    else {
      // 2. If the map is joinable, join it. We will want to accept all rows
      //    which are either not in the map, or marked in the map as NEEDS_UPDATE.
      //    Note that if highwater fields are in play, we want to accept all rows
      //    above the highwater mark in addition to those selected by the map
      //    conditions, so we need to OR them together (but AND with any existing
      //    conditions in the query). So, ultimately the SQL condition will look
      //    like (original conditions) AND (map IS NULL OR map needs update
      //      OR above highwater).
      $conditions = new Condition('OR');
      $condition_added = FALSE;
      if ($this->mapJoinable) {
        // Build the join to the map table. Because the source key could have
        // multiple fields, we need to build things up.
        $count = 1;

        foreach ($this->migration->get('sourceKeys') as $field_name => $field_schema) {
          if (isset($field_schema['alias'])) {
            $field_name = $field_schema['alias'] . '.' . $field_name;
          }
          $map_key = 'sourceid' . $count++;
          if (!isset($map_join)) {
            $map_join = '';
          }
          else {
            $map_join .= ' AND ';
          }
          $map_join .= "$field_name = map.$map_key";
        }

        $alias = $this->query->leftJoin($this->idMap->getQualifiedMapTable(),
                                        'map', $map_join);
        $conditions->isNull($alias . '.sourceid1');
        $conditions->condition($alias . '.needs_update', MigrateIdMapInterface::STATUS_NEEDS_UPDATE);
        $condition_added = TRUE;

        // And as long as we have the map table, add its data to the row.
        $n = count($this->migration->get('sourceKeys'));
        for ($count = 1; $count <= $n; $count++) {
          $map_key = 'sourceid' . $count;
          $this->query->addField($alias, $map_key, "migrate_map_$map_key");
        }
        $n = count($this->migration->get('destinationKeys'));
        for ($count = 1; $count <= $n; $count++) {
          $map_key = 'destid' . $count++;
          $this->query->addField($alias, $map_key, "migrate_map_$map_key");
        }
        $this->query->addField($alias, 'needs_update', 'migrate_map_needs_update');
      }
      // 3. If we are using highwater marks, also include rows above the mark.
      //    But, include all rows if the highwater mark is not set.
      if (isset($this->highwaterProperty['name']) && ($highwater = $this->getHighwater()) !== '') {
        if (isset($this->highwaterProperty['alias'])) {
          $highwater = $this->highwaterProperty['alias'] . '.' . $this->highwaterProperty['name'];
        }
        else {
          $highwater = $this->highwaterProperty['name'];
        }
        $conditions->condition($highwater, $highwater, '>');
        $condition_added = TRUE;
      }
      if ($condition_added) {
        $this->query->condition($conditions);
      }
    }

    $this->result = $this->query->execute();
  }

  /**
   * Implementation of MigrateSource::getNextRow().
   *
   * @return array
   */
  public function getNextRow() {
    return $this->result->fetchAssoc();
  }

  /**
   * @return \Drupal\Core\Database\Query\SelectInterface
   */
  abstract function query();

}
