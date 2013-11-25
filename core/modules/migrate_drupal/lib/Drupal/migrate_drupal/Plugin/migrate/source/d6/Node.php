<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Comment.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Entity\MigrationInterface;


/**
 * Drupal 6 node source from database.
 *
 * @PluginId("drupal6_node")
 */
class Node extends Drupal6SqlBase {

  /**
   * The node type this source provides.
   *
   * @var string
   */
  protected $type;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    if (empty($configuration['node_type'])) {
      // @todo MigrateException?
      throw new \Exception('A node type is required to instanciate a D6 Node source.');
    }
    $this->type = $configuration['node_type'];
  }

  /**
   * {@inheritdoc}
   *
   * This also includes data from CCK fields.
   *
   * @todo Support importing all revisions.
   */
  public function query() {
    // Select node in its last revision.
    $query = $this->database
      ->select('node', 'n')
      ->fields('n', array(
        'nid',
        'vid',
        'type',
        'language',
        'title',
        'uid',
        'status',
        'created',
        'changed',
        'comment',
        'promote',
        'moderate',
        'sticky',
        'tnid',
        'translate',
      ))
      ->condition('n.type', $this->type);
    $query->innerJoin('node_revisions', 'nr', 'n.vid = nr.vid');
    $query->fields('nr', array('body', 'teaser', 'format'));

    // Pick up simple CCK fields.
    $cck_table = 'content_type_' . $this->type;
    if ($this->database->schema()->tableExists($cck_table)) {
      $query->leftJoin($cck_table, 'f', 'n.vid = f.vid');
      // The main column for the field should be rendered with the field name,
      // not the column name (e.g., field_foo rather than field_foo_value).
      $field_info = $this->version->getSourceFieldInfo();
      foreach ($field_info as $field_name => $info) {
        if (isset($info['columns']) && !$info['multiple'] && $info['db_storage']) {
          $i = 0;
          $data = FALSE;
          foreach ($info['columns'] as $display_name => $column_name) {
            if ($i++ == 0) {
              $query->addField('f', $column_name, $field_name);
            }
            else {
              // The database API won't allow colons in column aliases, so we
              // will accept the default alias, and fix up the field names later.
              // Remember how to translate the field names.
              $clean_name = str_replace(':', '_', $display_name);
              $this->fixFieldNames[$clean_name] = $display_name;
              if ($info['type'] == 'filefield' &&
                (strpos($display_name, ':list') || strpos($display_name, ':description'))) {
                  if (!$data) {
                    $this->fileDataFields[] = $field_name . '_data';
                    $query->addField('f', $field_name . '_data');
                    $data = TRUE;
                  }
                }
              else {
                $query->addField('f', $column_name);
              }
            }
          }
        }
      }
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    // @fixme Implement.
  }

}
