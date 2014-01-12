<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\Dump\Drupal6FieldInstance.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

/**
 * Database dump for testing entity display migration.
 */
class Drupal6FieldInstance {


  /**
   * Setup tables for the unit tests.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database object.
   */
  public static function load(Connection $database) {
    $database->schema()->createTable('content_node_field_instance', array(
      'description' => 'Table that contains field instance settings.',
      'fields' => array(
        'field_name' => array(
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
          'default' => '',
        ),
        'type_name' => array(
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
          'default' => '',
        ),
        'weight' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ),
        'label' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'widget_type' => array(
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
          'default' => '',
        ),
        'widget_settings' => array(
          'type' => 'text',
          'size' => 'medium',
          'not null' => TRUE,
          'serialize' => TRUE,
        ),
        'display_settings' => array(
          'type' => 'text',
          'size' => 'medium',
          'not null' => TRUE,
          'serialize' => TRUE,
        ),
        'description' => array(
          'type' => 'text',
          'size' => 'medium',
          'not null' => TRUE,
        ),
        'widget_module' => array(
          'type' => 'varchar',
          'length' => 127,
          'not null' => TRUE,
          'default' => '',
        ),
        'widget_active' => array(
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
        ),
      ),
      'primary key' => array('field_name', 'type_name'),
    ));

    $database->insert('content_node_field_instance')->fields(array(
      'field_name',
      'type_name',
      'weight',
      'label',
      'widget_type',
      'widget_settings',
      'display_settings',
      'description',
    ))
      ->values(array(
        'field_name' => 'field_test',
        'type_name' => 'story',
        'weight' => 1,
        'label' => 'Text Field',
        'widget_type' => 'text_textfield',
        'widget_settings' => serialize(array(
          'rows' => 5,
          'size' => '60',
          'default_value' => array(
            0 => array(
              'value' => '',
              '_error_element' => 'default_value_widget][field_test][0][value',
            ),
          ),
          'default_value_php' => NULL,
        )),
        'display_settings' => serialize(array(
          'weight' => 1,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'trimmed',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'trimmed',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example text field.',
      ))
      ->values(array(
        'field_name' => 'field_test',
        'type_name' => 'article',
        'weight' => 1,
        'label' => 'Text Field',
        'widget_type' => 'text_textfield',
        'widget_settings' => serialize(array(
          'rows' => 5,
          'size' => '60',
          'default_value' => array(
            0 => array(
              'value' => '',
              '_error_element' => 'default_value_widget][field_test][0][value',
            ),
          ),
          'default_value_php' => NULL,
        )),
        'display_settings' => serialize(array(
          'weight' => 1,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'trimmed',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'trimmed',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example textfield.',
      ))
      ->values(array(
        'field_name' => 'field_test_two',
        'type_name' => 'story',
        'weight' => 2,
        'label' => 'Integer Field',
        'widget_type' => 'number',
        'widget_settings' => 'a:0:{}',
        'display_settings' => serialize(array(
          'weight' => 2,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'unformatted',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'us_0',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'unformatted',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example integer field.',
      ))
      ->values(array(
        'field_name' => 'field_test_three',
        'type_name' => 'story',
        'weight' => 3,
        'label' => 'Float Field',
        'widget_type' => 'number',
        'widget_settings' => serialize(array(
          'default_value' => array(
            0 => array(
              'value' => '101',
              '_error_element' => 'default_value_widget][field_float][0][value',
            ),
          ),
          'default_value_php' => NULL,
        )),
        'display_settings' => serialize(array(
          'weight' => 3,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'unformatted',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'us_2',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'unformatted',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example float field.',
      ))
      ->values(array(
        'field_name' => 'field_test_email',
        'type_name' => 'story',
        'weight' => 4,
        'label' => 'Email Field',
        'widget_type' => 'email_textfield',
        'widget_settings' => serialize(array(
          'size' => '60',
          'default_value' => array(
            0 => array(
              'email' => '',
            ),
          ),
          'default_value_php' => NULL,
        )),
        'display_settings' => serialize(array(
          'weight' => 4,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example email field.',
      ))
      ->values(array(
        'field_name' => 'field_test_link',
        'type_name' => 'story',
        'weight' => 5,
        'label' => 'Link Field',
        'widget_type' => 'link',
        'widget_settings' => serialize(array(
          'default_value' => array(
            0 => array(
              'title' => '',
              'url' => '',
            ),
          ),
          'default_value_php' => NULL,
        )),
        'display_settings' => serialize(array(
          'weight' => 5,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'absolute',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example link field.',
      ))
      ->values(array(
        'field_name' => 'field_test_filefield',
        'type_name' => 'story',
        'weight' => 7,
        'label' => 'File Field',
        'widget_type' => 'filefield_widget',
        'widget_settings' => serialize(array(
          'file_extensions' => 'txt',
          'file_path' => '',
          'progress_indicator' => 'bar',
          'max_filesize_per_file' => '',
          'max_filesize_per_node' => '',
        )),
        'display_settings' => serialize(array(
          'weight' => 7,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'url_plain',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example image field.',
      ))
      ->values(array(
        'field_name' => 'field_test_imagefield',
        'type_name' => 'story',
        'weight' => 8,
        'label' => 'Image Field',
        'widget_type' => 'imagefield_widget',
        'widget_settings' => serialize(array(
          'file_extensions' => 'png gif jpg jpeg',
          'file_path' => '',
          'progress_indicator' => 'bar',
          'max_filesize_per_file' => '',
          'max_filesize_per_node' => '',
          'max_resolution' => '0',
          'min_resolution' => '0',
          'alt' => '',
          'custom_alt' => 0,
          'title' => '',
          'custom_title' => 0,
          'title_type' => 'textfield',
          'default_image' => NULL,
          'use_default_image' => 0,
        )),
        'display_settings' => serialize(array(
          'weight' => 8,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'image_imagelink',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'image_plain',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example image field.',
      ))
      ->values(array(
        'field_name' => 'field_test_phone',
        'type_name' => 'story',
        'weight' => 9,
        'label' => 'Phone Field',
        'widget_type' => 'phone_textfield',
        'widget_settings' => serialize(array(
          'size' => '60',
          'default_value' => array(
            0 => array(
              'value' => '',
              '_error_element' => 'default_value_widget][field_phone][0][value',
            ),
          ),
          'default_value_php' => NULL,
        )),
        'display_settings' => serialize(array(
          'weight' => 9,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example image field.',
      ))
      ->values(array(
        'field_name' => 'field_test_date',
        'type_name' => 'story',
        'weight' => 10,
        'label' => 'Date Field',
        'widget_type' => 'date_select',
        'widget_settings' => serialize(array(
          'default_value' => 'blank',
          'default_value_code' => '',
          'default_value2' => 'same',
          'default_value_code2' => '',
          'input_format' => 'm/d/Y - H:i:s',
          'input_format_custom' => '',
          'increment' => '1',
          'text_parts' => array(),
          'year_range' => '-3:+3',
          'label_position' => 'above',
        )),
        'display_settings' => serialize(array(
          'weight' => 10,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'long',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example date field.',
      ))
      ->values(array(
        'field_name' => 'field_test_datestamp',
        'type_name' => 'story',
        'weight' => 11,
        'label' => 'Date Stamp Field',
        'widget_type' => 'date_select',
        'widget_settings' => serialize(array(
          'default_value' => 'blank',
          'default_value_code' => '',
          'default_value2' => 'same',
          'default_value_code2' => '',
          'input_format' => 'm/d/Y - H:i:s',
          'input_format_custom' => '',
          'increment' => '1',
          'text_parts' => array(),
          'year_range' => '-3:+3',
          'label_position' => 'above',
        )),
        'display_settings' => serialize(array(
          'weight' => 11,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'medium',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example date stamp field.',
      ))
      ->values(array(
        'field_name' => 'field_test_datetime',
        'type_name' => 'story',
        'weight' => 12,
        'label' => 'Datetime Field',
        'widget_type' => 'date_select',
        'widget_settings' => serialize(array(
          'default_value' => 'blank',
          'default_value_code' => '',
          'default_value2' => 'same',
          'default_value_code2' => '',
          'input_format' => 'm/d/Y - H:i:s',
          'input_format_custom' => '',
          'increment' => '1',
          'text_parts' => array(),
          'year_range' => '-3:+3',
          'label_position' => 'above',
        )),
        'display_settings' => serialize(array(
          'weight' => 12,
          'parent' => '',
          'label' => array(
            'format' => 'above',
          ),
          'teaser' => array(
            'format' => 'short',
            'exclude' => 0,
          ),
          'full' => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          4 => array(
            'format' => 'default',
            'exclude' => 0,
          ),
          5 => array(
            'format' => 'default',
            'exclude' => 1,
          ),
        )),
        'description' => 'An example datetime field.',
      ))
      ->execute();

    // Create the field table.
    $database->schema()->createTable('content_node_field', array(
      'description' => 'Table that contains field instance settings.',
      'fields' => array(
        'field_name' => array(
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
          'default' => '',
        ),
        'type' => array(
          'type' => 'varchar',
          'length' => 127,
          'not null' => TRUE,
          'default' => '',
        ),
        'global_settings' => array(
          'type' => 'text',
          'size' => 'medium',
          'not null' => TRUE,
          'serialize' => TRUE,
        ),
        'required' => array(
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
        ),
        'multiple' => array(
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
        ),
        'db_storage' => array(
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 1,
        ),
        'module' => array(
          'type' => 'varchar',
          'length' => 127,
          'not null' => TRUE,
          'default' => '',
        ),
        'db_columns' => array(
          'type' => 'text',
          'size' => 'medium',
          'not null' => TRUE,
          'serialize' => TRUE,
        ),
        'active' => array(
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
        ),
        'locked' => array(
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
        ),
      ),
      'primary key' => array('field_name'),
    ));

    $database->insert('content_node_field')->fields(array(
      'field_name',
      'module',
      'type',
      'global_settings',
      'db_columns',
    ))
      ->values(array(
        'field_name' => 'field_test',
        'module' => 'text',
        'type' => 'text',
        'global_settings' => 'a:0:{}',
        'db_columns' => 'a:0:{}',
      ))
      ->values(array(
        'field_name' => 'field_test_two',
        'module' => 'number',
        'type' => 'number_integer',
        'global_settings' => 'a:0:{}',
        'db_columns' => 'a:0:{}',
      ))
      ->values(array(
        'field_name' => 'field_test_three',
        'module' => 'number',
        'type' => 'number_decimal',
        'global_settings' => 'a:0:{}',
        'db_columns' => 'a:0:{}',
      ))
      ->values(array(
        'field_name' => 'field_test_email',
        'module' => 'email',
        'type' => 'email',
        'global_settings' => 'a:0:{}',
        'db_columns' => 'a:0:{}',
      ))
      ->values(array(
        'field_name' => 'field_test_link',
        'module' => 'link',
        'type' => 'link',
        'global_settings' => 'a:0:{}',
        'db_columns' => 'a:0:{}',
      ))
      ->values(array(
        'field_name' => 'field_test_filefield',
        'module' => 'filefield',
        'type' => 'filefield',
        'global_settings' => 'a:0:{}',
        'db_columns' => 'a:0:{}',
      ))
      ->values(array(
        'field_name' => 'field_test_imagefield',
        'module' => 'filefield',
        'type' => 'filefield',
        'global_settings' => 'a:0:{}',
        'db_columns' => 'a:0:{}',
      ))
      ->values(array(
        'field_name' => 'field_test_phone',
        'module' => 'phone',
        'type' => 'au_phone',
        'global_settings' => 'a:0:{}',
        'db_columns' => 'a:0:{}',
      ))
      ->values(array(
        'field_name' => 'field_test_date',
        'module' => 'date',
        'type' => 'date',
        'global_settings' => 'a:0:{}',
        'db_columns' => 'a:0:{}',
      ))
      ->values(array(
        'field_name' => 'field_test_datestamp',
        'module' => 'date',
        'type' => 'datestamp',
        'global_settings' => 'a:0:{}',
        'db_columns' => 'a:0:{}',
      ))
      ->values(array(
        'field_name' => 'field_test_datetime',
        'module' => 'date',
        'type' => 'datetime',
        'global_settings' => 'a:0:{}',
        'db_columns' => 'a:0:{}',
      ))
      ->execute();
  }
}
