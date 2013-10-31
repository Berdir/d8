<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\File.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\d6\Drupal6SqlBase;

/**
 * Drupal 6 file source from database.
 *
 * @PluginId("drupal6_file")
 */
class File extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  function query() {
    $query = $this->database
      ->select('files', 'f')
      ->fields('f', array('fid', 'uid', 'filename',
        'filepath', 'filemime', 'filesize', 'status', 'timestamp'));
    $query->orderBy('timestamp');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'fid' => t('File ID'),
      'uid' => t('The {users}.uid who added the file. If set to 0, this file was added by an anonymous user.'),
      'filename' => t('File name'),
      'filepath' => t('File path'),
      'filemime' => t('File Mime Type'),
      'status' => t('The published status of a file.'),
      'timestamp' => t('The time that the file was added.'),
    );
  }

}
