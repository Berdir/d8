<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUserTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6DumpCommon;
use Drupal\migrate_drupal\Tests\Dump\Drupal6User;
use Drupal\migrate_drupal\Tests\Dump\Drupal6UserProfileFields;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateUserTest extends MigrateDrupalTestBase{

  /**
   * The modules to be enabled during the test.
   *
   * @var array
   */
  static $modules = array(
    'link',
    'options',
    'datetime',
    'number',
    'text',
    'file',
    'image',
  );

  /**
   * Storing user profile data keyed by $user->id().
   *
   * @var array
   */
  static $profileData = array();

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate users',
      'description'  => 'Users migration',
      'group' => 'Migrate Drupal',
    );
  }

  public function testUser() {

    // Populate static::$profileData.
    $this->setProfileData();

    // Create the user profile field and instance.
    entity_create('field_entity', array(
      'entity_type' => 'user',
      'name' => 'user_picture',
      'type' => 'image',
      'translatable' => '0',
    ))->save();
    entity_create('field_instance', array(
      'label' => 'User Picture',
      'description' => '',
      'field_name' => 'user_picture',
      'entity_type' => 'user',
      'bundle' => 'user',
      'required' => 0,
    ))->save();

    // Load database dumps to provide source data.
    $path = drupal_get_path('module', 'migrate_drupal');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FilterFormat.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UserProfileFields.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UserRole.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6User.php',
    );
    $this->loadDumps($dumps);

    // Migrate text formats first.
    $migration_format = entity_load('migration', 'd6_filter_format');
    $executable = new MigrateExecutable($migration_format, $this);
    $executable->import();

    // Migrate user profile fields.
    $migration_field = entity_load('migration', 'd6_user_profile_field');
    $executable = new MigrateExecutable($migration_field, $this);
    $executable->import();

    // Migrate user profile field instances.
    $migration_instance = entity_load('migration', 'd6_user_profile_field_instance');
    $executable = new MigrateExecutable($migration_instance, $this);
    $executable->import();

    // Migrate user roles.
    $migration_role = entity_load('migration', 'd6_user_role');
    $executable = new MigrateExecutable($migration_role, $this);
    $executable->import();

    // Migrate user pictures.
    $migration_user_picture = entity_load('migration', 'd6_user_picture_file');
    $executable = new MigrateExecutable($migration_user_picture, $this);
    $executable->import();

    // Migrate users.
    $migration = entity_load('migration', 'd6_user:user');
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $users = Database::getConnection('default', 'migrate')
      ->select('users', 'u')
      ->fields('u')
      ->execute()
      ->fetchAll();

    foreach ($users as $source) {
      // Get roles directly from the source.
      $rids = Database::getConnection('default', 'migrate')
        ->select('users_roles', 'ur')
        ->fields('ur', array('rid'))
        ->condition('ur.uid', $source->uid)
        ->execute()
        ->fetchCol();
      $roles = array(DRUPAL_AUTHENTICATED_RID);
      foreach ($rids as $rid) {debug($rid);
        $role = $migration_role->getIdMap()->lookupDestinationId(array($rid));
        $roles[] = reset($role);
      }

      $user = user_load($source->uid);

      $this->assertEqual($user->id(), $source->uid);
      $this->assertEqual($user->label(), $source->name);
      $this->assertEqual($user->getEmail(), $source->mail);
      $this->assertEqual($user->getSignature(), $source->signature);
      $signature_format = $migration_format->getIdMap()->lookupDestinationId(array($source->signature_format));
      $this->assertEqual($user->getSignatureFormat(), reset($signature_format));
      $this->assertEqual($user->getCreatedTime(), $source->created);
      $this->assertEqual($user->getLastAccessedTime(), $source->access);
      $this->assertEqual($user->getLastLoginTime(), $source->login);
      $is_blocked = $source->status == 0;
      $this->assertEqual($user->isBlocked(), $is_blocked);
      // $user->getPreferredLangcode() might fallback to default language if the
      // user preferred language is not configured on the site. We just want to
      // test if the value was imported correctly.
      $this->assertEqual($user->preferred_langcode->value, $source->language);
      $time_zone =  $source->expected_timezone ?: \Drupal::config('system.date')->get('timezone.default');
      $this->assertEqual($user->getTimeZone(), $time_zone);
      $this->assertEqual($user->getInitialEmail(), $source->init);
      $this->assertEqual($user->getRoles(), $roles);

      // Test each profile field.
      $profile = static::$profileData[$source->uid];
      foreach ($profile as $name => $field) {
        $key = key($field);
        $this->assertEqual($user->{$name}->{$key}, $field[$key]);
      }

      // Test the user picture.
      $file = file_load($user->user_picture->target_id);
      $this->assertEqual($file->getFilename(), basename($source->picture));

      // Use the UI to check if the password has been salted and re-hashed to
      // conform the Drupal >= 7.
      $credentials = array('name' => $source->name, 'pass' => $source->pass_plain);
      $this->drupalPostForm('user/login', $credentials, t('Log in'));
      $this->assertNoRaw(t('Sorry, unrecognized username or password. <a href="@password">Have you forgotten your password?</a>', array('@password' => url('user/password', array('query' => array('name' => $source->name))))));
      $this->drupalLogout();
    }

  }

  /**
   * Sets the user profile test data in an array keyed by user id.
   */
  protected function setProfileData() {
    if (!static::$profileData) {
      $fields = array();
      foreach (Drupal6UserProfileFields::getData('profile_fields') as $row) {
        $fields[(int) $row['fid']] = array(
          'name' => $row['name'],
          'type' => $row['type'],
        );
      }
      static::$profileData = array();
      foreach (Drupal6User::getData('profile_values') as $row) {
        $fid = (int) $row['fid'];
        $key = $fields[$fid]['type'] == 'url' ? 'url' : 'value';
        if ($fields[$fid]['type'] == 'date') {
          $date = unserialize($row['value']);
          $row['value'] = date('Y-m-d', mktime(0, 0, 0, $date['month'], $date['day'], $date['year']));
        }
        static::$profileData[(int) $row['uid']][$fields[$fid]['name']] = array($key => $row['value']);
      }
    }
  }

}
