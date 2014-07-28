<?php

/**
 * @file
 * Contains \Drupal\user\UserSchemaHandler.
 */

namespace Drupal\user;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Schema\ContentEntitySchemaHandler;

/**
 * Defines the user schema handler.
 */
class UserSchemaHandler extends ContentEntitySchemaHandler {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type) {
    $schema = parent::getEntitySchema($entity_type);

    // Marking the respective fields as NOT NULL makes the indexes more
    // performant.
    $schema['users']['fields']['access']['not null'] = TRUE;
    $schema['users']['fields']['created']['not null'] = TRUE;
    $schema['users']['fields']['name']['not null'] = TRUE;

    // The "users" table does not use serial identifiers.
    $schema['users']['fields']['uid']['type'] = 'int';
    $schema['users']['indexes'] += array(
      'user__access' => array('access'),
      'user__created' => array('created'),
      'user__mail' => array('mail'),
    );
    $schema['users']['unique keys'] += array(
      'user__name' => array('name'),
    );

    $schema['users_roles'] = array(
      'description' => 'Maps users to roles.',
      'fields' => array(
        'uid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Primary Key: {users}.uid for user.',
        ),
        'rid' => array(
          'type' => 'varchar',
          'length' => 64,
          'not null' => TRUE,
          'description' => 'Primary Key: ID for the role.',
        ),
      ),
      'primary key' => array('uid', 'rid'),
      'indexes' => array(
        'rid' => array('rid'),
      ),
      'foreign keys' => array(
        'user' => array(
          'table' => 'users',
          'columns' => array('uid' => 'uid'),
        ),
      ),
    );

    return $schema;
  }

}
