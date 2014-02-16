<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityRevision.
 */

namespace Drupal\migrate\Plugin\migrate\destination;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;

/**
 * @MigrateDestinationPlugin(
 *   id = "entity_revision",
 *   derivative = "Drupal\migrate\Plugin\Derivative\MigrateEntityRevision"
 * )
 */
class EntityRevision extends EntityContentBase {

  /**
   * {@inheritdoc}
   */
  protected static function getEntityType($plugin_id) {
    // Remove entity_revision:
    return substr($plugin_id, 16);
  }

  protected function getEntity(Row $row) {
    $revision_id = $row->getDestinationProperty($this->getKey('revision'));
    if (!empty($revision_id) && ($entity = $this->storageController->loadRevision($revision_id))) {
      $this->updateEntity($entity, $row);
      $entity->setNewRevision(FALSE);
    }
    else {
      $values = $row->getDestination();
      // Stubs might not have the bundle specified.
      if ($row->stub()) {
        $bundle_key = $this->getKey('bundle');
        if ($bundle_key && !isset($values[$bundle_key])) {
          $values[$bundle_key] = reset($this->bundles);
        }
      }
      $entity = $this->storageController->create($values);
      $entity->enforceIsNew(FALSE);
      $entity->setNewRevision(TRUE);
     }
    $entity->isDefaultRevision(FALSE);
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function save(ContentEntityInterface $entity, array $old_destination_id_values = array()) {
    $entity->save();
    return array($entity->getRevisionId());
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    if ($key = $this->getKey('revision')) {
      $ids[$key]['type'] = 'integer';
      return $ids;
    }
    throw new MigrateException('This entity type does not support revisions.');
  }
}
