<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityBaseContent.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Row;

class EntityContentBase extends Entity {


  public function import(Row $row) {
    // @TODO: add field handling. https://drupal.org/node/2164451
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntity($row);
    $violations = $entity->validate();
    if ($violations) {
      throw new MigrateSkipRowException();
    }
    $entity->save();
    return array($entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $id_key = $this->getKey('id');
    $ids[$id_key]['type'] = 'integer';
    return $ids;
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param array $parents
   * @param mixed $value
   * @throws \Drupal\migrate\MigrateException
   */
  protected function updateEntityProperty(EntityInterface $entity, array $parents, $value) {
    $ref = $entity;
    while ($parent = array_shift($parents)) {
      if ($ref instanceof ListInterface && is_numeric($parent)) {
        $ref = $ref->offsetGet($parent);
      }
      elseif ($ref instanceof ComplexDataInterface) {
        $ref = $ref->get($parent);
      }
      elseif ($ref instanceof TypedDataInterface) {
        // At this point we should have no more parents as there is nowhere to
        // descend.
        if ($parents) {
          throw new MigrateException(String::format('Unexpected extra keys @parents', array('@parents' => $parents)));
        }
      }
    }
    $ref->setValue($value);
  }

}
