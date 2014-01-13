<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\NodeType.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;
use Drupal\migrate\Row;


/**
 * Drupal 6 Node types source from database.
 *
 * @PluginId("drupal6_nodetype")
 */
class NodeType extends Drupal6SqlBase {

  /**
   * The teaser length
   *
   * @var int
   */
  protected $teaserLength;

  /**
   * Node preview optional / required.
   *
   * @var int
   */
  protected $nodePreview;

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('node_type', 't')
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
        'orig_type',
      ))
      ->orderBy('t.type');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'type' => $this->t('Machine name of the node type.'),
      'name' => $this->t('Human name of the node type.'),
      'module' => $this->t('The module providing the node type.'),
      'description' => $this->t('Description of the node type.'),
      'help' => $this->t('Help text for the node type.'),
      'has_title' => $this->t('Flag indicating the node type has a title.'),
      'title_label' => $this->t('Title label.'),
      'has_body' => $this->t('Flag indicating the node type has a body field.'),
      'body_label' => $this->t('Body label.'),
      'min_word_count' => $this->t('Minimum word count for the body field.'),
      'custom' => $this->t('Flag.'),
      'modified' => $this->t('Flag.'),
      'locked' => $this->t('Flag.'),
      'orig_type' => $this->t('The original type.'),
      'teaser_length' => $this->t('Teaser length'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function runQuery() {
    $this->teaserLength = $this->variableGet('teaser_length', 600);
    $this->nodePreview = $this->variableGet('node_preview', 0);
    return parent::runQuery();
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $row->setSourceProperty('teaser_length', $this->teaserLength);
    $row->setSourceProperty('node_preview', $this->nodePreview);
  }
}
