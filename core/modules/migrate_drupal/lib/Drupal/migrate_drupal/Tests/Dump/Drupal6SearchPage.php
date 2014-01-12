<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6SearchPage.
 */

namespace Drupal\migrate_drupal\Tests\Dump;
use Drupal\Core\Database\Connection;

/**
 * Database dump for testing search page migration.
 */
class Drupal6SearchPage {


  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public static function load(Connection $database) {
    Drupal6DumpCommon::createVariable($database);
    $database->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'node_rank_comments',
      'value' => 's:1:"5";',
    ))
    ->values(array(
      'name' => 'node_rank_promote',
      'value' => 's:1:"0";',
    ))
    ->values(array(
      'name' => 'node_rank_recent',
      'value' => 's:1:"0";',
    ))
    ->values(array(
      'name' => 'node_rank_relevance',
      'value' => 's:1:"2";',
    ))
    ->values(array(
      'name' => 'node_rank_sticky',
      'value' => 's:1:"8";',
    ))
    ->values(array(
      'name' => 'node_rank_views',
      'value' => 's:1:"1";',
    ))
    ->execute();

  }
}
