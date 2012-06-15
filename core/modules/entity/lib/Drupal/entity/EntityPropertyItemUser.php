<?php

/**
 * @file
 * Definition of Drupal\entity\EntityPropertyItemUser
 */

namespace Drupal\entity;

/**
 * A list of PropertyContainer items.
 */
class EntityPropertyItemUser extends EntityPropertyItem {

  public function get($name) {
    if ($name == 'entity') {
      if (!isset($this->values['entity'])) {
        $this->values['entity'] = user_load($this->values['value']);
      }
      return $this->values['entity'];
    }
    return parent::get($name);
  }

  public function set($name, $value) {
     if ($name == 'entity') {
      $this->values['value'] = $value->id();
      $this->values['entity'] = $value;
    }
    parent::set($name, $value);
  }

}