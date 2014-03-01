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
          $return += $plugin->loadMultiple($this);
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
          $entities += $plugin->loadMultiple($this, $sub_ids);
        }
      }
    }

    // Build an array of dependencies and set the order of the migrations.
    return $this->buildDependencyMigration($entities);
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
  public function buildDependencyMigration(array $migrations) {
    $dependency_graph = array();
    $requirement_graph = array();
    $different = FALSE;
    /** @var \Drupal\migrate\Entity\MigrationInterface $migration */
    foreach ($migrations as $migration) {
      $requirements[$migration->id()] = array();
      $dependency_graph[$migration->id()]['edges'] = array();
      if (isset($migration->dependencies) && is_array($migration->dependencies)) {
        foreach ($migration->dependencies as $dependency) {
          if (is_string($dependency)) {
            $requirement_graph[$migration->id()]['edges'][$dependency] = $dependency;
          }
          if (is_array($dependency)) {
            list($dependency_string, $required) = $dependency;
            $dependency = $dependency_string;
            if ($required) {
              $requirement_graph[$migration->id()]['edges'][$dependency] = $dependency;
            }
            else {
              $different = TRUE;
            }
          }
          $dependency_graph[$migration->id()]['edges'][$dependency] = $dependency;
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
      $migration->set('requirements', $requirement_graph[$migration_id]['paths']);
    }
    array_multisort($weights, SORT_DESC, SORT_NUMERIC, $migrations);

    return $migrations;
  }

}
