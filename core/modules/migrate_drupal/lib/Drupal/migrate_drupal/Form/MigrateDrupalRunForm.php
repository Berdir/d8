<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Form\MigrateDrupalRun.
 */

namespace Drupal\migrate_drupal\Form;

use Drupal\Component\Utility\MapArray;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Form\FormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MigrateDrupalRunForm extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface
   */
  protected $storageController;

  /**
   * @param EntityStorageControllerInterface $storage_controller
   */
  public function __construct(EntityStorageControllerInterface $storage_controller) {
    $this->storageController = $storage_controller;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager')->getStorageController('migration'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'migrate_drupal_run_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    // The multistep is for testing only. The final version will run a fixed
    // set of migrations.
    if (isset($form_state['database'])) {
      Database::addConnectionInfo('migrate', 'default', $form_state['storage']['database']);
      $migrations = $this->storageController->loadMultiple('migration');
      $form['migrations'] = array(
        '#type' => 'checkboxes',
        '#options' => MapArray::copyValuesToKeys(array_keys($migrations)),
      );
    }
    else {
      $form['db_url'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Database URL from D6'),
        '#size' => 40,
      );
    }
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    if (isset($form_state['values']['db_url'])) {
      $form_state['rebuild'] = TRUE;
      $form_state['database'] = $this->convertDbUrl($form_state['values']['db_url']);
    }
    else {
      $migration_ids = array_keys(array_filter($form_state['values']['migrations']));
      $batch = array(
        'title' => t('Running migrations'),
        'operations' => array(
          array(array('Drupal\migrate_drupal\MigrateDrupalRunBatch', 'run'), array($migration_ids, $form_state['storage']['database'])),
        ),
        'finished' => array('Drupal\migrate_drupal\MigrateDrupalRunBatch', 'finished'),
        'progress_message' => '',
        'init_message' => t('Processing migration @num of @max.', array('@num' => '1', '@max' => count($migration_ids))),
      );
      $this->batchSet($batch);
    }
  }

  /**
   * Converts a D6 database URL to a new style DB configuration array.
   *
   * @param $db_url
   *  The D6 database url.
   * @return array
   *   The new style database array.
   */
  protected function convertDbUrl($db_url) {
    $url = parse_url($db_url);
    // Fill in defaults to prevent notices.
    $url += array(
      'driver' => NULL,
      'user' => NULL,
      'pass' => NULL,
      'host' => NULL,
      'port' => NULL,
      'path' => NULL,
      'database' => NULL,
    );
    $url = (object) array_map('urldecode', $url);
    return array(
      'driver' => $url->scheme == 'mysqli' ? 'mysql' : $url->scheme,
      'username' => $url->user,
      'password' => $url->pass,
      'port' => $url->port,
      'host' => $url->host,

      'database' => substr($url->path, 1),
    );
  }

  protected function batchSet($batch) {
    batch_set($batch);
  }

}
