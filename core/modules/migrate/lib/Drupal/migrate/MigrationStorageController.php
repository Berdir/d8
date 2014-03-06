<?php

/**
 * @file
 * Contains \Drupal\migrate\MigrateStorageController.
 */

namespace Drupal\migrate;

use Drupal\Component\Graph\Graph;
use Drupal\Component\Utility\String;
use Drupal\Core\Config\Entity\ConfigStorageController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;

/**
 * Storage controller for migration entities.
 */
class MigrationStorageController extends ConfigStorageController implements MigrateBuildDependencyInterface {

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL) {
    $ids_to_load = array();
    $dynamic_ids = array();
    if (isset($ids)) {
      foreach ($ids as $id) {
        // Evaluate whether or not this migration is dynamic in the form of
        // migration_id:* to load all the additional migrations.
        if (($n = strpos($id, ':')) !== FALSE) {
          $base_id = substr($id, 0, $n);
          $ids_to_load[] = $base_id;
          // Get the ids of the additional migrations.
          $sub_id = substr($id, $n + 1);
          if ($sub_id == '*') {
            // If the id of the additional migration is '*', get all of them.
            $dynamic_ids[$base_id] = NULL;
          }
          elseif (!isset($dynamic_ids[$base_id]) || is_array($dynamic_ids[$base_id])) {
            $dynamic_ids[$base_id][] = $sub_id;
          }
        }
        else {
          $ids_to_load[] = $id;
        }
      }
      $ids = array_flip($ids);
    }
    else {
      $ids_to_load = NULL;
    }

    /** @var \Drupal\migrate\Entity\MigrationInterface[] $entities */
    $entities = parent::loadMultiple($ids_to_load);
    if (!isset($ids)) {
      // Changing the array being foreach()'d is not a good idea.
      $return = array();
      foreach ($entities as $entity_id => $entity) {
        if ($plugin = $entity->getLoadPlugin()) {
          $new_entities = $plugin->loadMultiple($this);
          $this->getDynamicIds($dynamic_ids, $new_entities);
          $return += $new_entities;
        }
        else {
          $return[$entity_id] = $entity;
        }
      }
      $entities = $return;
    }
    else {
      foreach ($dynamic_ids as $base_id => $sub_ids) {
        $entity = $entities[$base_id];
        if ($plugin = $entity->getLoadPlugin()) {
          unset($entities[$base_id]);
          $new_entities = $plugin->loadMultiple($this, $sub_ids);
          if (!isset($sub_ids)) {
            unset($dynamic_ids[$base_id]);
            $this->getDynamicIds($dynamic_ids, $new_entities);
          }
          $entities += $new_entities;
        }
      }
    }

    // Build an array of dependencies and set the order of the migrations.
    return $this->buildDependencyMigration($entities, $dynamic_ids);
  }

  /**
   * Extract the dynamic id mapping from entities loaded by plugin.
   *
   * @param array $dynamic_ids
   *   Get the dynamic migration ids.
   * @param array $entities
   *   An array of entities.
   */
  protected function getDynamicIds(array &$dynamic_ids, array $entities) {
    foreach (array_keys($entities) as $new_id) {
      list($base_id, $sub_id) = explode(':', $new_id, 2);
      $dynamic_ids[$base_id][] = $sub_id;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity) {
    if (strpos($entity->id(), ':') !== FALSE) {
      throw new EntityStorageException(String::format("Dynamic migration %id can't be saved", array('$%id' => $entity->id())));
    }
    return parent::save($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function buildDependencyMigration(array $migrations, array $dynamic_ids) {
    // Dependencies defined in the migration storage controller can be soft
    // dependencies: if a soft dependency does not run, the current migration
    // is still OK to go. This is indicated by adding ": false" (without
    // quotes) after the name of the dependency. Hard dependencies (default)
    // are called requirements. Both hard and soft dependencies (if run at
    // all) must run before the current one.
    $dependency_graph = array();
    $requirement_graph = array();
    $different = FALSE;
    /** @var \Drupal\migrate\Entity\MigrationInterface $migration */
    foreach ($migrations as $migration) {
      $id = $migration->id();
      $requirements[$id] = array();
      $dependency_graph[$id]['edges'] = array();
      if (isset($migration->dependencies) && is_array($migration->dependencies)) {
        foreach ($migration->dependencies as $dependency) {
          if (is_string($dependency) && !isset($dynamic_ids[$dependency])) {
            $this->addDependency($requirement_graph, $id, $dependency, $dynamic_ids);
          }
          if (is_array($dependency)) {
            list($dependency_string, $required) = each($dependency);
            $dependency = $dependency_string;
            if ($required) {
              $this->addDependency($requirement_graph, $id, $dependency, $dynamic_ids);
            }
            else {
              $different = TRUE;
            }
          }
          $this->addDependency($dependency_graph, $id, $dependency, $dynamic_ids);
        }
      }
    }
    $graph_object = new Graph($dependency_graph);
    $dependency_graph = $graph_object->searchAndSort();
    if ($different) {
      $graph_object = new Graph($requirement_graph);
      $requirement_graph = $graph_object->searchAndSort();
    }
    else {
      $requirement_graph = $dependency_graph;
    }
    $weights = array();
    foreach ($migrations as $migration_id => $migration) {
      // Populate a weights array to use with array_multisort later.
      $weights[] = $dependency_graph[$migration_id]['weight'];
      if (!empty($requirement_graph[$migration_id]['paths'])) {
        $migration->set('requirements', $requirement_graph[$migration_id]['paths']);
      }
    }
    array_multisort($weights, SORT_DESC, SORT_NUMERIC, $migrations);

    return $migrations;
  }

  /**
   * Add one or more dependencies to a graph.
   *
   * @param array $graph
   *   The graph so far.
   * @param int $id
   *   The migration id.
   * @param string $dependency
   *   The dependency string.
   * @param array $dynamic_ids
   *   The dynamic id mapping.
   */
  protected function addDependency(array &$graph, $id, $dependency, $dynamic_ids) {
    $dependencies = isset($dynamic_ids[$dependency]) ? $dynamic_ids[$dependency] : array($dependency);
    if (!isset($graph[$id]['edges'])) {
      $graph[$id]['edges'] = array();
    }
    $graph[$id]['edges'] += array_combine($dependencies, $dependencies);
  }

}
