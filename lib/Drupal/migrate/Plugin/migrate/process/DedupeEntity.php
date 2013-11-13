<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\DedupeEntity.
 */


namespace Drupal\migrate\Plugin\migrate\process;

/**
 * @PluginId("dedupe_entity")
 */
class DedupeEntity extends DedupeBase {

  /**
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $entityQuery;

  /**
   * {@inheritdoc}
   */
  protected function exists($value) {
    return $this->entityQuery->condition($this->configuration['field'], $value)->count()->execute();
  }

  /**
   * @return \Drupal\Core\Entity\Query\QueryInterface
   */
  protected function getEntityQuery() {
    if (!isset($this->entityQuery)) {
      $this->entityQuery = \Drupal::entityQuery($this->configuration['entity_type']);
    }
    return $this->entityQuery;
  }
}
