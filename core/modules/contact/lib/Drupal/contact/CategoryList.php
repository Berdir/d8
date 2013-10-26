<?php

/**
 * Definition of Drupal\contact\CategoryList.
 */

namespace Drupal\contact;

use Drupal\Core\Config\Entity\ConfigEntityList;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of contact categories.
 */
class CategoryList extends ConfigEntityList {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['category'] = t('Category');
    $header['recipients'] = t('Recipients');
    $header['selected'] = t('Selected');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['category'] = $this->getLabel($entity);
    // Special case the personal category.
    if ($entity->id() == 'personal') {
      $row['recipients'] = t('Selected user');
      $row['selected'] = t('No');
    }
    else {
      $row['recipients'] = check_plain(implode(', ', $entity->recipients));
      $default_category = \Drupal::config('contact.settings')->get('default_category');
      $row['selected'] = ($default_category == $entity->id() ? t('Yes') : t('No'));
    }
    return $row + parent::buildRow($entity);
  }

}
