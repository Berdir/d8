<?php

/**
 * @file
 * Contains \Drupal\menu_link_content\Form\MenuLinkContentDeleteForm.
 */

namespace Drupal\menu_link_content\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a delete form for content menu links.
 */
class MenuLinkContentDeleteForm extends ContentEntityDeleteForm {

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a MenuLinkContentDeleteForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   */
  public function __construct(EntityManagerInterface $entity_manager, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($entity_manager);
    $this->logger = $logger_factory->get('menu');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.menu.edit_form', array('menu' => $this->entity->getMenuName()));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $t_args = array('%title' => $this->entity->getTitle());
    $this->logger->notice('Deleted menu link %title.', $t_args);
    $form_state->setRedirect('<front>');
  }

}
