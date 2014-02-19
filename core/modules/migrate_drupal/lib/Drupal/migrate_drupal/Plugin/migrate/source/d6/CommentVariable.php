<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\source\d6\CommentVariable.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

/**
 * @PluginID("drupal6_comment_variable")
 */
class CommentVariable extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function runQuery() {
    return new \ArrayIterator($this->getCommentVariables());
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return count($this->getCommentVariables());
  }

  /**
   * Retrieves the values of the comment variables grouped by node type.
   *
   * @return array
   */
  protected function getCommentVariables() {
    $comment_prefixes = array_keys($this->commentPrefixes());
    $variables = array();
    $node_types = $this->getDatabase()->query('SELECT type FROM {node_type}')->fetchCol();
    foreach ($node_types as $node_type) {
      foreach ($comment_prefixes as $prefix) {
        $variables[] = $prefix . '_' . $node_type;
      }
    }
    $return = array();
    $values = $this->getDatabase()->query('SELECT name, value FROM {variable} WHERE name IN (:name)', array(':name' => $variables))->fetchAllKeyed();
    foreach ($node_types as $node_type) {
      foreach ($comment_prefixes as $prefix) {
        $name = $prefix . '_' . $node_type;
        if (isset($values[$name])) {
          $return[$node_type][$prefix] = unserialize($values[$name]);
        }
      }
    }
    // The return key will not be used so move it inside the row. This could
    // not be done sooner because otherwise empty rows would be created with
    // just the node type in it.
    foreach ($return as $node_type => $data) {
      $return[$node_type]['node_type'] = $node_type;
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return $this->commentPrefixes() + array(
      'node_type' => $this->t('The node type'),
    );
  }

  /**
   * Comment related data for fields.
   */
  protected function commentPrefixes() {
    return array(
      'comment' => $this->t('Default comment setting'),
      'comment_default_mode' => $this->t('Default display mode'),
      'comment_default_order' => $this->t('Default display order'),
      'comment_default_per_page' => $this->t('Default comments per page'),
      'comment_controls' => $this->t('Comment controls'),
      'comment_anonymous' => $this->t('Anonymous commenting'),
      'comment_subject_field' => $this->t('Comment subject field'),
      'comment_preview' => $this->t('Preview comment'),
      'comment_form_location' => $this->t('Location of comment submission form'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Nothing to do here.
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['node_type']['type'] = 'string';
    return $ids;
  }

}
