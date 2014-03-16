<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityNodeBook.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\field\FieldInfo;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\MigratePluginManager;
use Drupal\migrate\Row;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

  /**
   * {@inheritdoc}
   */
  protected function updateEntity(EntityInterface $entity, Row $row) {
    $entity->beingMigrated = TRUE;
    $entity->book = $row->getDestinationProperty('book');
  }

}
