<?php

/**
 * @file
 * Contains \Drupal\Core\Condition\ConditionPluginBase.
 */

namespace Drupal\Core\Condition;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Executable\ExecutablePluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContextAwarePluginAssignmentTrait;

/**
 * Provides a basis for fulfilling contexts for condition plugins.
 *
 * @see \Drupal\Core\Condition\Annotation\Condition
 * @see \Drupal\Core\Condition\ConditionInterface
 * @see \Drupal\Core\Condition\ConditionManager
 *
 * @ingroup plugin_api
 */
abstract class ConditionPluginBase extends ExecutablePluginBase implements ConditionInterface {

  use ContextAwarePluginAssignmentTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function isNegated() {
    return !empty($this->configuration['negate']);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $contexts = $form_state->getTemporaryValue('gathered_contexts') ?: [];
    $form['context_mapping'] = $this->addContextAssignmentElement($this, $contexts);
    $form['negate'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Negate the condition'),
      '#default_value' => $this->configuration['negate'],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['negate'] = $form_state->getValue('negate');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    return $this->executableManager->execute($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return array(
      'id' => $this->getPluginId(),
    ) + $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'negate' => FALSE,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $cache_contexts = [];
    foreach ($this->getContexts() as $context) {
      /** @var $context \Drupal\Core\Cache\CacheableDependencyInterface */
      if ($context instanceof CacheableDependencyInterface) {
        $cache_contexts = Cache::mergeContexts($cache_contexts, $context->getCacheContexts());
      }
    }
    return $cache_contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = [];
    foreach ($this->getContexts() as $context) {
      /** @var $context \Drupal\Core\Cache\CacheableDependencyInterface */
      if ($context instanceof CacheableDependencyInterface) {
        $tags = Cache::mergeTags($tags, $context->getCacheTags());
      }
    }
    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $max_age = Cache::PERMANENT;
    foreach ($this->getContexts() as $context) {
      /** @var $context \Drupal\Core\Cache\CacheableDependencyInterface */
      if ($context instanceof CacheableDependencyInterface) {
        $max_age = Cache::mergeMaxAges($max_age, $context->getCacheMaxAge());
      }
    }
    return $max_age;
  }

}
