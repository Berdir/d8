<?php

/**
 * @file
 * Definition of Drupal\user\Plugin\Core\Entity\User.
 */

namespace Drupal\user\Plugin\Core\Entity;

use Drupal\Core\Entity\EntityNG;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\user\UserBCDecorator;
use Drupal\user\UserInterface;

/**
 * Defines the user entity class.
 *
 * @EntityType(
 *   id = "user",
 *   label = @Translation("User"),
 *   module = "user",
 *   controllers = {
 *     "storage" = "Drupal\user\UserStorageController",
 *     "access" = "Drupal\user\UserAccessController",
 *     "render" = "Drupal\Core\Entity\EntityRenderController",
 *     "form" = {
 *       "profile" = "Drupal\user\ProfileFormController",
 *       "register" = "Drupal\user\RegisterFormController"
 *     },
 *     "translation" = "Drupal\user\ProfileTranslationController"
 *   },
 *   default_operation = "profile",
 *   base_table = "users",
 *   uri_callback = "user_uri",
 *   route_base_path = "admin/config/people/accounts",
 *   label_callback = "user_label",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "uid",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class User extends EntityNG implements UserInterface {

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
  public $name;

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
  public $mail;

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
  public $access;

  /**
   * The timestamp when the user last logged in. A value of 0 means the user has
   * never logged in.
   *
   * @var integer
   */
  public $login;

  /**
   * Whether the user is active (1) or blocked (0).
   *
   * @var integer
   */
  public $status;

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
  public $langcode;

  /**
   * The user's preferred langcode for receiving emails and viewing the site.
   *
   * @var string
   */
  public $preferred_langcode;

  /**
   * The user's preferred langcode for viewing administration pages.
   *
   * @var string
   */
  public $preferred_admin_langcode;

  /**
   * The email address used for initial account creation.
   *
   * @var string
   */
  public $init;

  /**
   * The user's roles.
   *
   * @var array
   */
  public $roles;

    /**
   * The plain data values of the contained properties.
   *
   * Define default values.
   *
   * @var array
   */
  protected $values = array(
    'langcode' => array(LANGUAGE_DEFAULT => array(0 => array('value' => LANGUAGE_NOT_SPECIFIED))),
    'preferred_langcode' => array(LANGUAGE_DEFAULT => array(0 => array('value' => LANGUAGE_NOT_SPECIFIED))),
    'admin_preffered_langcode' => array(LANGUAGE_DEFAULT => array(0 => array('value' => LANGUAGE_NOT_SPECIFIED))),
    'name' => array(LANGUAGE_DEFAULT => array(0 => array('value' => ''))),
    'mail' => array(LANGUAGE_DEFAULT => array(0 => array('value' => ''))),
    'init' => array(LANGUAGE_DEFAULT => array(0 => array('value' => ''))),
    'access' => array(LANGUAGE_DEFAULT => array(0 => array('value' => 0))),
    'login' => array(LANGUAGE_DEFAULT => array(0 => array('value' => 0))),
    'status' => array(LANGUAGE_DEFAULT => array(0 => array('value' => 1))),
  );

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('uid')->value;
  }

  protected function init() {
    parent::init();
    unset($this->access);
    unset($this->created);
    unset($this->init);
    unset($this->login);
    unset($this->mail);
    unset($this->name);
    unset($this->pass);
    unset($this->preferred_admin_langcode);
    unset($this->preferred_langcode);
    unset($this->roles);
    unset($this->signature);
    unset($this->signature_format);
    unset($this->status);
    unset($this->theme);
    unset($this->timezone);
    unset($this->uid);
    unset($this->uuid);
  }

  public function getBCEntity() {
    if (!isset($this->bcEntity)) {
      // Initialize field definitions so that we can pass them by reference.
      $this->getPropertyDefinitions();
      $this->bcEntity = new UserBCDecorator($this, $this->fieldDefinitions);
    }
    return $this->bcEntity;
  }


}
