<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\NodeType.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\d6\Drupal6SqlBase;

/**
 * Drupal 6 Node types source from database.
 *
 * @PluginId("drupal6_nodetype")
 */
class NodeType extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->database
      ->select('node_type', 't')
      ->fields('t', array(
        'type',
        'name',
        'module',
        'description',
        'help',
        'has_title',
        'title_label',
        'has_body',
        'body_label',
        'min_word_count',
        'custom',
        'modified',
        'locked',
        'orig_type'
      ));

    $query->orderBy('type');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'type' => t('Machine name of the node type.'),
      'name' => t('Human name of the node type.'),
      'module' => t('The module providing the node type.'),
      'description' => t('Description of the node type.'),
      'help' => t('Help text for the node type.'),
      'has_title' => t('Flag indicating the node type has a title.'),
      'title_label' => t('Title label.'),
      'has_body' => t('Flag indicating the node type has a body field.'),
      'body_label' => t('Body label.'),
      'min_word_count' => t('Minimum word count for the body field.'),
      'custom' => t('Flag.'),
      'modified' => t('Flag.'),
      'locked' => t('Flag.'),
      'orig_type' => t('The original type.'),
    );
  }

}
