<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityBaseConfig.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityInterface;
use Drupal\migrate\Row;

/**
 * Class for importing configuration entities.
 *
 * This class serves as the import class for most configuration entities.
 * It can be necessary to provide a specific entity class if the configuration
 * entity has a compound id (see EntityFieldEntity) or it has specific setter
 * methods (see EntityDateFormat). When implementing an entity destination for
 * the latter case, make sure to add a test not only for importing but also
 * for re-importing (if that is supported).
 */
class EntityConfigBase extends Entity {

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    $ids = $this->getIds();
    $id_key = $this->getKey('id');
    if (count($ids) > 1) {
      $id_keys = array_keys($ids);
      if (!$row->getDestinationProperty($id_key)) {
        $row->setDestinationProperty($id_key, $this->generateId($row, $id_keys));
      }
    }
    $entity = $this->getEntity($row);
    $entity->save();
    if (count($ids) > 1) {
      // This can only be a config entity, content entities have their id key
      // and that's it.
      $return = array();
      foreach ($id_keys as $id_key) {
        $return[] = $entity->get($id_key);
      }
      return $return;
    }
    return array($entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $id_key = $this->getKey('id');
    $ids[$id_key]['type'] = 'string';
    return $ids;
  }


  /**
   * Updates an entity with the contents of a row.
   *
   * @param EntityInterface $entity
   * @param Row $row
   */
  protected function updateEntity(EntityInterface $entity, Row $row) {
    foreach ($row->getRawDestination() as $property => $value) {
      $this->updateEntityProperty($entity, explode('.', $property), $value);
    }
  }

  /**
   * Updates a (possible nested) entity property with a value.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   * @param array $parents
   * @param $value
   */
  protected function updateEntityProperty(EntityInterface $entity, array $parents, $value) {
    $top_key = array_shift($parents);
    $entity_value = $entity->get($top_key);
    if (is_array($entity_value)) {
      NestedArray::setValue($entity_value, $parents, $value);
    }
    else {
      $entity_value = $value;
    }
    $entity->set($top_key, $entity_value);
  }

  /**
   * Generate an entity id.
   *
   * @param Row $row
   *   The current row.
   * @param array $ids
   *   The destination ids.
   *
   * @return string
   *   The generated entity id.
   */
  protected function generateId(Row $row, array $ids) {
    $id_values = array();
    foreach ($ids as $id) {
      $id_values[] = $row->getDestinationProperty($id);
    }
    return implode('.', $id_values);
  }

}
