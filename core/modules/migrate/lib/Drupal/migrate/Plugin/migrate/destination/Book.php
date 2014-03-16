<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityNodeBook.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\migrate\Row;

/**
 * @MigrateDestination(
 *   id = "book",
 *   provider = "book"
 * )
 */
class Book extends EntityContentBase {

  /**
   * {@inheritdoc}
   */
  protected static function getEntityTypeId($plugin_id) {
    return 'node';
  }

  protected function save(ContentEntityInterface $entity, array $old_destination_id_values = array()) {
    drupal_save_session(FALSE);
    $container = \Drupal::getContainer();
    $current_user = \Drupal::currentUser();
    $container->set('current_user', user_load(1));
    $return = parent::save($entity, $old_destination_id_values);
    $container->set('current_user', $current_user);
    drupal_save_session(TRUE);
    return $return;
  }


  /**
   * {@inheritdoc}
   */
  protected function updateEntity(EntityInterface $entity, Row $row) {
    $entity->book = $row->getDestinationProperty('book');
  }

}
