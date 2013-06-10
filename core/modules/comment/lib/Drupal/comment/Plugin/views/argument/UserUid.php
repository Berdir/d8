<?php

/**
 * @file
 * Definition of Drupal\comment\Plugin\views\argument\UserUid.
 */

namespace Drupal\comment\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\ArgumentPluginBase;
use Drupal\Component\Annotation\PluginID;

/**
 * Argument handler to accept a user id to check for nodes that
 * user posted or commented on.
 *
 * @ingroup views_argument_handlers
 *
 * @PluginID("argument_comment_user_uid")
 */
class UserUid extends ArgumentPluginBase {

  function title() {
    if (!$this->argument) {
      $title = config('user.settings')->get('anonymous');
    }
    else {
      $query = db_select('users', 'u');
      $query->addField('u', 'name');
      $query->condition('u.uid', $this->argument);
      $title = $query->execute()->fetchField();
    }
    if (empty($title)) {
      return t('No user');
    }

    return check_plain($title);
  }

  protected function defaultActions($which = NULL) {
    // Disallow summary views on this argument.
    if (!$which) {
      $actions = parent::defaultActions();
      unset($actions['summary asc']);
      unset($actions['summary desc']);
      return $actions;
    }

    if ($which != 'summary asc' && $which != 'summary desc') {
      return parent::defaultActions($which);
    }
  }

  public function query($group_by = FALSE) {
    $this->ensureMyTable();

    // Load the table information to get the entity information to finally
    // be able to join to filter by the original entity type this join is
    // attached to.
    if ($this->table != 'comment') {
      $subselect = db_select('comment', 'c');
      $subselect->addField('c', 'cid');
      $subselect->condition('c.uid', $this->argument);

      $entity_id = $this->definition['entity_id'];
      $entity_type = $this->definition['entity_type'];
      $subselect->where("c.entity_id = $this->tableAlias.$entity_id");
      $subselect->condition('c.entity_type', $entity_type);

      $condition = db_or()
        ->condition("$this->tableAlias.uid", $this->argument, '=')
        ->exists($subselect);

      $this->query->addWhere(0, $condition);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSortName() {
    return t('Numerical', array(), array('context' => 'Sort order'));
  }

}
