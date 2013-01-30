<?php

/**
 * @file
 * Definition of Drupal\user\Plugin\Core\Entity\User.
 */

namespace Drupal\user\Plugin\Core\Entity;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines the user entity class.
 *
 * @Plugin(
 *   id = "user",
 *   label = @Translation("User"),
 *   module = "user",
 *   controller_class = "Drupal\user\UserStorageController",
 *   access_controller_class = "Drupal\user\UserAccessController",
 *   form_controller_class = {
 *     "profile" = "Drupal\user\ProfileFormController",
 *     "register" = "Drupal\user\RegisterFormController"
 *   },
 *   default_operation = "profile",
 *   translation_controller_class = "Drupal\user\ProfileTranslationController",
 *   base_table = "users",
 *   uri_callback = "user_uri",
 *   label_callback = "user_label",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "uid",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class User extends Entity {

  /**
   * The user ID.
   *
   * @var integer
   */
  public $uid;

  /**
   * The user UUID.
   *
   * @var string
   */
  public $uuid;

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
  public $signature_format;

  /**
   * The timestamp when the user was created.
   *
   * @var integer
   */
  public $created;

  /**
   * The timestamp when the user last accessed the site. A value of 0 means the
   * user has never accessed the site.
   *
   * @var integer
   */
  public $access = 0;

  /**
   * The timestamp when the user last logged in. A value of 0 means the user has
   * never logged in.
   *
   * @var integer
   */
  public $login = 0;

  /**
   * Whether the user is active (1) or blocked (0).
   *
   * @var integer
   */
  public $status = 1;

  /**
   * The user's timezone.
   *
   * @var string
   */
  public $timezone;

  /**
   * The user's langcode.
   *
   * @var string
   */
  public $langcode = LANGUAGE_NOT_SPECIFIED;

  /**
   * The user's preferred langcode for receiving emails and viewing the site.
   *
   * @var string
   */
  public $preferred_langcode = LANGUAGE_NOT_SPECIFIED;

  /**
   * The user's preferred langcode for viewing administration pages.
   *
   * @var string
   */
  public $preferred_admin_langcode = LANGUAGE_NOT_SPECIFIED;

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

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->uid;
  }
}
