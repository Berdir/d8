<?php

/**
 * @file
 * Contains Drupal\comment\Plugin\views\field\StatisticsLastCommentName.
 */

namespace Drupal\comment\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Annotation\Plugin;

/**
 * Field handler to present the name of the last comment poster.
 *
 * @ingroup views_field_handlers
 *
 * @Plugin(
 *   id = "comment_ces_last_comment_name",
 *   module = "comment"
 * )
 */
class StatisticsLastCommentName extends FieldPluginBase {

  public function query() {
    // last_comment_name only contains data if the user is anonymous. So we
    // have to join in a specially related user table.
    $this->ensureMyTable();
    // join 'users' to this table via vid
    $definition = array(
      'table' => 'users',
      'field' => 'uid',
      'left_table' => $this->tableAlias,
      'left_field' => 'last_comment_uid',
      'extra' => array(
        array(
          'field' => 'uid',
          'operator' => '!=',
          'value' => '0'
        )
      )
    );
    $join = drupal_container()->get('plugin.manager.views.join')->createInstance('standard', $definition);

    // nes_user alias so this can work with the sort handler, below.
//    $this->user_table = $this->query->add_relationship(NULL, $join, 'users', $this->relationship);
    $this->user_table = $this->query->ensure_table('ces_users', $this->relationship, $join);

    $this->field_alias = $this->query->add_field(NULL, "COALESCE($this->user_table.name, $this->tableAlias.$this->field)", $this->tableAlias . '_' . $this->field);

    $this->user_field = $this->query->add_field($this->user_table, 'name');
    $this->uid = $this->query->add_field($this->tableAlias, 'last_comment_uid');
  }

  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['link_to_user'] = array('default' => TRUE, 'bool' => TRUE);

    return $options;
  }

  function render($values) {
    if (!empty($this->options['link_to_user'])) {
      $account = entity_create('user', array());
      $account->name = $this->get_value($values);
      $account->uid = $values->{$this->uid};
      return theme('username', array(
        'account' => $account
      ));
    }
    else {
      return $this->sanitizeValue($this->get_value($values));
    }
  }

}
