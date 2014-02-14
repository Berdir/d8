<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityRevision.
 */

namespace Drupal\migrate\Plugin\migrate\destination;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\migrate\MigrateException;

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

  /**
   * {@inheritdoc}
   */
  protected function save(ContentEntityInterface $entity, array $old_destination_id_values = array()) {
    $entity->isDefaultRevision(FALSE);
    $entity->setNewRevision(empty($old_destination_id_values));
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
