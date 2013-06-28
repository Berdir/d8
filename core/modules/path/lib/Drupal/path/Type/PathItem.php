<?php

/**
 * @file
 * Contains \Drupal\path\Type\PathItem.
 */

namespace Drupal\path\Type;

use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'path_field' entity field item.
 */
class PathItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['alias'] = array(
        'type' => 'string',
        'label' => t('Path alias'),
      );
      static::$propertyDefinitions['pid'] = array(
        'type' => 'integer',
        'label' => t('Path id'),
      );
      dpm(static::$propertyDefinitions);
    }
    return static::$propertyDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    // Delete aliases associated with this entity.
    $entity = $this->getParent()->getParent();
    $uri = $entity->uri();
    \Drupal::service('path.crud')->delete(array('source' => $uri['path']));
  }

  /**
   * {@inheritdoc}
   */
  public function insert() {
    $entity = $this->getParent()->getParent();
    $this->set('alias', trim($this->get('alias')->getValue()));
    // Only save a non-empty alias.
    if ($alias = $this->get('alias')->getValue()) {
      // Ensure fields for programmatic executions.
      $uri = $entity->uri();
      $langcode = $entity->language()->langcode;
      \Drupal::service('path.crud')->save($uri['path'], $alias, $langcode);
    }
  }

  /**
   * {@inheritdoc}.
   */
  function update() {
    dpm('hi');
    $entity = $this->getParent()->getParent();
    $this->set('alias', trim($this->get('alias')->getValue()));
    // Delete old alias if user erased it.
    if ($this->get('pid')->getValue() && !$this->get('alias')->getValue()) {
      Drupal::service('path.crud')->delete(array('pid' => $this->pid));
    }
      // Only save a non-empty alias.
    if ($alias = $this->get('alias')->getValue()) {
      // Ensure fields for programmatic executions.
      $uri = $entity->uri();
      $langcode = $entity->language()->langcode;
      \Drupal::service('path.crud')->save($uri['path'], $entity->get('alias')->getValue(), $langcode, $this->get('pid')->getValue());
    }
  }

}
