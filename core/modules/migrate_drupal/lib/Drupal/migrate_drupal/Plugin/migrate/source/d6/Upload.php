<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Upload.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\SourceEntityInterface;
use Drupal\migrate\Row;

/**
 * Drupal 6 upload source from database.
 *
 * @PluginID("drupal6_upload")
 */
class Upload extends Drupal6SqlBase implements SourceEntityInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('upload', 'u')
      ->fields('u', array_keys($this->fields()));
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'fid' => $this->t('The file Id.'),
      'nid' => $this->t('The node Id.'),
      'vid' => $this->t('The version Id.'),
      'description' => $this->t('The file description.'),
      'list' => $this->t('Whether the list should be visible on the node page.'),
      'weight' => $this->t('The file weight.'),
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array(
      'fid' => array(
        'type' => 'integer',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'file';
  }

}
