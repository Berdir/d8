<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Annotation\EntityType.
 */

namespace Drupal\Core\Entity\Annotation;


/**
 * Defines an Entity type annotation object.
 *
 * @Annotation
 */
class ConfigEntityType extends EntityType {

  /**
   * {@inheritdoc}
   */
  public $controllers = array(
    'access' => 'Drupal\Core\Entity\EntityAccessController',
    'storage' => 'Drupal\Core\Config\Entity\ConfigStorageController'
  );


  public $group = 'Configuration';

}
