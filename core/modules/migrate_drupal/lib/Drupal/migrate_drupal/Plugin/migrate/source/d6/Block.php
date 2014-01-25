<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\source\d6\Block.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;


use Drupal\migrate\Row;

/**
 * Drupal 6 block source from database.
 *
 * @PluginID("drupal6_block")
 */
class Block extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('blocks', 'b')
      ->fields('b', array('bid', 'module', 'delta', 'theme', 'status', 'weight', 'region', 'visibility', 'pages', 'title', 'cache'))
      ->orderBy('bid');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'bid' => t('The block numeric identifier.'),
      'module' => t('The module providing the block.'),
      'delta' => t('The block\'s delta.'),
      'theme' => t('Which theme the block is placed in.'),
      'status' => t('Whether or not the block is enabled.'),
      'weight' => t('Weight of the block for ordering within regions.'),
      'region' => t('Region the block is placed in.'),
      'visibility' => t('Visibility expression.'),
      'pages' => t('Pages list.'),
      'title' => t('Block title.'),
      'cache' => t('Cache rule.'),

    );
  }

  public function prepareRow(Row $row) {
    $module = $row->getSourceProperty('module');
    $delta = $row->getSourceProperty('delta');
    $roles = $this->select('blocks_roles', 'br')
      ->fields('br', array('rid'))
      ->condition('module', $module)
      ->condition('delta', $delta)
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('permissions', $roles);
    $settings = array();
    // Contrib can use hook_migration_d6_block_prepare_row() to add similar variables
    // via $migration->getSource()->variableGet().
    switch ($module) {
      case 'aggregator':
        list($type, $id) = explode('-', $delta);
        if ($type == 'feed') {
          $item_count = $this->database->query('SELECT block FROM {aggregator_feed} WHERE fid = :fid', array(':fid' => $id))->fetchField();
        }
        else {
          $item_count = $this->database->query('SELECT block FROM {aggregator_category} WHERE cid = :cid', array(':cid' => $id))->fetchField();
        }
        $settings['aggregator']['item_count'] = $item_count;
        break;
      case 'book':
        $settings['book']['block_mode'] = $this->variableGet('book_block_mode', 'all pages');
        break;
      case 'forum':
        $settings['forum']['block_num'] = $this->variableGet('forum_block_num_'. $delta, 5);
        break;
      case 'statistics':
        foreach (array('statistics_block_top_day_num', 'statistics_block_top_all_num', 'statistics_block_top_last_num') as $name) {
          $settings['statistics'][$name] = $this->variableGet($name, 0);
        }
        break;
      case 'user':
        switch ($delta) {
          case 2:
            $settings['user']['block_whois_new_count'] = $this->variableGet('user_block_whois_new_count', 5);
            break;
          case 3:
            $settings['user']['block_seconds_online'] = $this->variableGet('user_block_seconds_online', 900);
            $settings['user']['max_list_count'] = $this->variableGet('user_block_max_list_count', 10);
            break;
        }
        break;
    }
    $row->setSourceProperty('settings', $settings);
    return parent::prepareRow($row);
  }
  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['module']['type'] = 'string';
    $ids['delta']['type'] = 'string';
    return $ids;
  }
}
