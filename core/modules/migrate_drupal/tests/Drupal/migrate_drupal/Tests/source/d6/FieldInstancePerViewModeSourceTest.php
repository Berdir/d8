<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\source\d6\FieldInstancePerViewModeSourceTest.
 */

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\migrate\Tests\MigrateSqlSourceTestCase;

/**
 * Tests per view mode sources from D6 to D8.
 *
 * @group migrate_drupal
 * @group Drupal
 */
class FieldInstancePerViewModeSourceTest extends MigrateSqlSourceTestCase {

  // The plugin system is not working during unit testing so the source plugin
  // class needs to be manually specified.
  const PLUGIN_CLASS = 'Drupal\migrate_drupal\Plugin\migrate\source\d6\FieldInstancePerViewMode';

  /**
   * @var bool
   */
  protected $mapJoinable = FALSE;

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The ID of the entity, can be any string.
    'id' => 'view_mode_test',
    // Leave it empty for now.
    'idlist' => array(),
    'source' => array(
      'plugin' => 'drupal6_field_instance_per_view_mode',
    ),
    'sourceIds' => array(
      'type_name' => array(
        // This is where the field schema would go but for now we need to
        // specify the table alias for the key. Most likely this will be the
        // same as BASE_ALIAS.
        'alias' => 'cnfi',
      ),
      'view_mode' => array(
        'alias' => 'cnfi',
      ),
    ),
    'destinationIds' => array(
      'id' => array(
        // This is where the field schema would go.
      ),
    ),
  );

  protected $expectedResults = array(
    array(
      'entity_type' => 'node',
      'view_mode' => 4,
      'type_name' => 'article',
      'fields' => array(
        'field_test' => array(
          'field_name' => 'field_test',
          'type' => 'text',
          'module' => 'text',
          'weight' => 1,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 1,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            4 => array(
              'format' => 'trimmed',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
      ),
    ),
    array(
      'entity_type' => 'node',
      'view_mode' => 'teaser',
      'type_name' => 'story',
      'fields' => array(
        'field_test' => array(
          'field_name' => 'field_test',
          'type' => 'text',
          'module' => 'text',
          'weight' => 1,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 1,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            'teaser' => array(
              'format' => 'trimmed',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
        'field_test_date' => array(
          'field_name' => 'field_test_date',
          'type' => 'date',
          'module' => 'date',
          'weight' => 10,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 10,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            'teaser' => array(
              'format' => 'default',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
        'field_test_datestamp' => array(
          'field_name' => 'field_test_datestamp',
          'type' => 'datestamp',
          'module' => 'date',
          'weight' => 11,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 11,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            'teaser' => array(
              'format' => 'medium',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
        'field_test_datetime' => array(
          'field_name' => 'field_test_datetime',
          'type' => 'datetime',
          'module' => 'date',
          'weight' => 12,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 12,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            'teaser' => array(
              'format' => 'short',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
        'field_test_email' => array(
          'field_name' => 'field_test_email',
          'type' => 'email',
          'module' => 'email',
          'weight' => 4,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 4,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            'teaser' => array(
              'format' => 'default',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
        'field_test_filefield' => array(
          'field_name' => 'field_test_filefield',
          'type' => 'filefield',
          'module' => 'filefield',
          'weight' => 7,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 7,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            'teaser' => array(
              'format' => 'default',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
        'field_test_imagefield' => array(
          'field_name' => 'field_test_imagefield',
          'type' => 'filefield',
          'module' => 'filefield',
          'weight' => 8,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 8,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            'teaser' => array(
              'format' => 'image_imagelink',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
        'field_test_link' => array(
          'field_name' => 'field_test_link',
          'type' => 'link',
          'module' => 'link',
          'weight' => 5,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 5,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            'teaser' => array(
              'format' => 'default',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
        'field_test_phone' => array(
          'field_name' => 'field_test_phone',
          'type' => 'au_phone',
          'module' => 'phone',
          'weight' => 9,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 9,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            'teaser' => array(
              'format' => 'default',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
        'field_test_three' => array(
          'field_name' => 'field_test_three',
          'type' => 'number_decimal',
          'module' => 'number',
          'weight' => 3,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 3,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            'teaser' => array(
              'format' => 'unformatted',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
        'field_test_two' => array(
          'field_name' => 'field_test_two',
          'type' => 'number_integer',
          'module' => 'number',
          'weight' => 2,
          'label' => 'above',
          'display_settings' => array(
            'weight' => 2,
            'parent' => '',
            'label' => array(
              'format' => 'above',
            ),
            'teaser' => array(
              'format' => 'unformatted',
              'exclude' => 0,
            ),
          ),
          'widget_settings' => array(),
        ),
      ),
    ),
  );


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 per view mode source functionality',
      'description' => 'Tests D6 fields per view mode source plugin.',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    foreach ($this->expectedResults as $k => $view_mode) {
      foreach ($view_mode['fields'] as $i => $field) {
        // These are stored as serialized strings.
        $field['display_settings'] = serialize($field['display_settings']);
        $field['widget_settings'] = serialize($field['widget_settings']);

        $data = $field + array(
          'entity_type' => $view_mode['entity_type'],
          'view_mode' => $view_mode['view_mode'],
          'type_name' => $view_mode['type_name'],
        );

        $this->databaseContents['content_node_field_instance'][] = $data;
        $this->databaseContents['content_node_field'][$field['field_name']] = $field;

        $this->expectedResults[$k]['fields'][$i]['display_settings'] = $this->expectedResults[$k]['fields'][$i]['display_settings'][$view_mode['view_mode']];

      }
    }
    parent::setUp();
  }

}

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d6\FieldInstancePerViewMode;

class TestFieldInstancePerViewMode extends FieldInstancePerViewMode {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
  function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }
}
