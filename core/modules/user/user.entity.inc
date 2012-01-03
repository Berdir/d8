<?php

/**
 * @file Controller class for users.
 */

/**
 * Defines the user entity class.
 */
class User extends Entity {

  /**
   * The user ID.
   *
   * @var integer
   */
  public $uid;

  /**
   * The unique user name.
   *
   * @var string
   */
  public $name = '';

  /**
   * The user's password (hashed).
   *
   * @var string
   */
  public $pass;

  /**
   * The user's email address.
   *
   * @var string
   */
  public $mail = '';

  /**
   * The user's default theme.
   *
   * @var string
   */
  public $theme;

  /**
   * The user's signature.
   *
   * @var string
   */
  public $signature;

  /**
   * The user's signature format.
   *
   * @var string
   */
  public $signature_format = NULL;

  /**
   * The timestamp when the user was created.
   *
   * @var integer
   */
  public $created = 0;

  /**
   * The timestamp when the user last accessed the site.
   *
   * @var integer
   */
  public $access = 0;

  /**
   * The timestamp when the user lasted logged in.
   *
   * @var integer
   */
  public $login = 0;

  /**
   * Whether the user is active(1) or blocked(0).
   *
   * @var integer
   */
  public $status = 0;

  /**
   * The user's timezone.
   *
   * @var string
   */
  public $timezone = NULL;

  /**
   * The user's default language.
   *
   * @var string
   */
  public $language = '';

  /**
   * The fid of the user's picture.
   *
   * @var integer
   */
  public $picture = 0;

  /**
   * The email address used for initial account creation.
   *
   * @var string
   */
  public $init = '';

  /**
   * The user's roles.
   *
   * @var array
   */
  public $roles = array();

  public function __construct(array $values = array(), $entity_type = NULL) {
    parent::__construct($values, 'user');
  }
}

/**
 * Controller class for users.
 *
 * This extends the DrupalDefaultEntityController class, adding required
 * special handling for user objects.
 */
class UserController extends EntityDatabaseStorageController {

  function attachLoad(&$queried_users, $revision_id = FALSE) {
    // Build an array of user picture IDs so that these can be fetched later.
    $picture_fids = array();
    foreach ($queried_users as $key => $record) {
      $picture_fids[] = $record->picture;
      $queried_users[$key]->data = unserialize($record->data);
      $queried_users[$key]->roles = array();
      if ($record->uid) {
        $queried_users[$record->uid]->roles[DRUPAL_AUTHENTICATED_RID] = 'authenticated user';
      }
      else {
        $queried_users[$record->uid]->roles[DRUPAL_ANONYMOUS_RID] = 'anonymous user';
      }
    }

    // Add any additional roles from the database.
    $result = db_query('SELECT r.rid, r.name, ur.uid FROM {role} r INNER JOIN {users_roles} ur ON ur.rid = r.rid WHERE ur.uid IN (:uids)', array(':uids' => array_keys($queried_users)));
    foreach ($result as $record) {
      $queried_users[$record->uid]->roles[$record->rid] = $record->name;
    }

    // Add the full file objects for user pictures if enabled.
    if (!empty($picture_fids) && variable_get('user_pictures', 1) == 1) {
      $pictures = file_load_multiple($picture_fids);
      foreach ($queried_users as $entity) {
        if (!empty($entity->picture) && isset($pictures[$entity->picture])) {
          $entity->picture = $pictures[$entity->picture];
        }
        else {
          $entity->picture = NULL;
        }
      }
    }
    // Call the default attachLoad() method. This will add fields and call
    // hook_user_load().
    parent::attachLoad($queried_users, $revision_id);
  }

  public function save(EntityInterface $entity) {
    $entity->is_new = $entity->isNew();
    if (empty($entity->uid)) {
      $entity->uid = db_next_id(db_query('SELECT MAX(uid) FROM {users}')->fetchField());
    }
    parent::save($entity);
  }

  protected function preSave(EntityInterface $entity) {
    // Update the user password if it has changed.
    if ($entity->is_new || $entity->pass != $entity->original->pass) {
      // Allow alternate password hashing schemes.
      require_once DRUPAL_ROOT . '/' . variable_get('password_inc', 'core/includes/password.inc');
      $entity->pass = user_hash_password(trim($entity->pass));
      // Abort if the hashing failed and returned FALSE.
      if (!$entity->pass) {
        return FALSE;
      }
    }

    if (!empty($entity->picture_upload)) {
      $entity->picture = $entity->picture_upload;
    }
    // Delete picture if requested, and if no replacement picture was given.
    elseif (!empty($entity->picture_delete)) {
      $entity->picture = NULL;
    }

    if (!$entity->isNew()) {
      // Process picture uploads.
      if (!empty($entity->picture->fid) && (!isset($entity->original->picture->fid) || $entity->picture->fid != $entity->original->picture->fid)) {
        $picture = $entity->picture;
        // If the picture is a temporary file move it to its final location and
        // make it permanent.
        if (!$picture->status) {
          $info = image_get_info($picture->uri);
          $picture_directory =  file_default_scheme() . '://' . variable_get('user_picture_path', 'pictures');

          // Prepare the pictures directory.
          file_prepare_directory($picture_directory, FILE_CREATE_DIRECTORY);
          $destination = file_stream_wrapper_uri_normalize($picture_directory . '/picture-' . $entity->uid . '-' . REQUEST_TIME . '.' . $info['extension']);

          // Move the temporary file into the final location.
          if ($picture = file_move($picture, $destination, FILE_EXISTS_RENAME)) {
            $picture->status = FILE_STATUS_PERMANENT;
            $entity->picture = file_save($picture);
            file_usage_add($picture, 'user', 'user', $entity->uid);
          }
        }
        // Delete the previous picture if it was deleted or replaced.
        if (!empty($entity->original->picture->fid)) {
          file_usage_delete($entity->original->picture, 'user', 'user', $entity->uid);
          file_delete($entity->original->picture);
        }
      }
      $entity->picture = empty($entity->picture->fid) ? 0 : $entity->picture->fid;

      // Do not allow 'uid' to be changed.
      $entity->uid = $entity->original->uid;

      // If the password changed, delete all open sessions and recreate
      // the current one.
      if ($entity->pass != $entity->original->pass) {
        drupal_session_destroy_uid($entity->uid);
        if ($entity->uid == $GLOBALS['user']->uid) {
          drupal_session_regenerate();
        }
      }

      // Remove not enabled roles.
      $entity->roles = array_filter($entity->roles);

      // Reload user roles if provided.
      if ($entity->roles != $entity->original->roles) {
        db_delete('users_roles')
          ->condition('uid', $entity->uid)
          ->execute();

        $query = db_insert('users_roles')->fields(array('uid', 'rid'));
        foreach (array_keys($entity->roles) as $rid) {
          if (!in_array($rid, array(DRUPAL_ANONYMOUS_RID, DRUPAL_AUTHENTICATED_RID))) {
            $query->values(array(
              'uid' => $entity->uid,
              'rid' => $rid,
            ));
          }
        }
        $query->execute();
      }

      // Delete a blocked user's sessions to kick them if they are online.
      if ($entity->original->status != $entity->status && $entity->status == 0) {
        drupal_session_destroy_uid($entity->uid);
      }

      // Send emails after we have the new user object.
      if ($entity->status != $entity->original->status) {
        // The user's status is changing; conditionally send notification email.
        $op = $entity->status == 1 ? 'status_activated' : 'status_blocked';
        _user_mail_notify($op, $entity);
      }
    }
    else {
      // Allow 'created' to be set by the caller.
      if (!isset($entity->created)) {
        $entity->created = REQUEST_TIME;
      }

      // Make sure $entity is properly initialized.
      $entity->roles[DRUPAL_AUTHENTICATED_RID] = 'authenticated user';

      // Save user roles.
      if (count($entity->roles) > 1) {
        $query = db_insert('users_roles')->fields(array('uid', 'rid'));
        foreach (array_keys($entity->roles) as $rid) {
          if (!in_array($rid, array(DRUPAL_ANONYMOUS_RID, DRUPAL_AUTHENTICATED_RID))) {
            $query->values(array(
              'uid' => $entity->uid,
              'rid' => $rid,
            ));
          }
        }
        $query->execute();
      }
    }

    // Prepare user roles.
    if (isset($entity->roles)) {
      $entity->roles = array_filter($entity->roles);
    }

    // Move account cancellation information into $user->data.
    foreach (array('user_cancel_method', 'user_cancel_notify') as $key) {
      if (isset($entity->{$key})) {
        $entity->data[$key] = $entity->{$key};
      }
    }
  }
}
