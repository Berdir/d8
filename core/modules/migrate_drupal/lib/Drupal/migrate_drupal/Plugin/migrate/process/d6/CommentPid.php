<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\Process\d6\CommentPid.
 */


namespace Drupal\migrate_drupal\Plugin\migrate\Process\d6;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;


/**
 * @MigrateProcessPlugin(
 *   id = "drupal6_comment_pid"
 * )
 */
class CommentPid extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   *
   * Skip the rest of the processing on 0.
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    if (!$value) {
      throw new MigrateSkipProcessException();
    }
    return $value;
  }

}
