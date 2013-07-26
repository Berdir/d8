<?php

/**
 * @file
 * Contains \Drupal\file\FileField.
 */

namespace Drupal\file;

use Drupal\field\Plugin\Type\FieldType\ConfigField;

/**
 * Represents a configurable entity file field.
 */
class FileField extends ConfigField {

  /**
   * {@inheritdoc}
   */
  public function update() {
    parent::update();
    $this->updateFileUsage();
  }

  protected function updateFileUsage() {
    $entity = $this->getRoot();

    // On new revisions, all files are considered to be a new usage and no
    // deletion of previous file usages are necessary.
    if (!empty($entity->original) && $entity->getRevisionId() != $entity->original->getRevisionId()) {
      foreach ($this->list as $item) {
        $target_id = $item->get('target_id')->getValue();
        file_usage()->add(file_load($target_id), 'file', $entity->entityType(), $entity->id());
      }
      return;
    }

    // Get the field id.
    $field_id = $this->getInstance()->getField()->id();

    // Build a list of the current target IDs.
    $current_fids = array();
    foreach ($this->list as $item) {
      $target_id = $item->get('target_id')->getValue();
      $current_fids[] = $target_id;
    }

    // Compare the original field values with the ones that are being saved.
    $original = $entity->original->getBCEntity();
    $langcode = $original->language()->id;

    $original_fids = array();
    if (!empty($original->{$field_id}[$langcode])) {
      foreach ($original->{$field_id}[$langcode] as $original_item) {
        $original_fids[] = $original_item['target_id'];
        if (isset($original_item['target_id']) && !in_array($original_item['target_id'], $current_fids)) {
          // Decrement the file usage count by 1.
          file_usage()->delete(file_load($original_item['target_id']), 'file', $entity->entityType(), $entity->id());
        }
      }
    }

    // Add new usage entries for newly added files.
    foreach ($this->list as $item) {
      $target_id = $item->get('target_id')->getValue();
      if (!in_array($target_id, $original_fids)) {
        file_usage()->add(file_load($target_id), 'file', $entity->entityType(), $entity->id());
      }
    }
  }

}
