<?php

/**
 * @file
 * Contains \Drupal\Core\Menu\Form\MenuLinkDefaultForm.
 */

namespace Drupal\Core\Menu\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuLinkTreeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MenuLinkDefaultForm implements MenuLinkFormInterface, ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Menu\MenuLinkInterface
   */
  protected $menuLink;

  /**
   * The translation manager service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translationManager;

  /**
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  public function __construct(MenuLinkTreeInterface $menu_tree) {
    $this->menuTree = $menu_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('menu.link_tree')
    );
  }

  /**
   * Injects the menu link.
   *
   * @param MenuLinkInterface $menu_link
   */
  public function setMenuLinkInstance(MenuLinkInterface $menu_link) {
    $this->menuLink = $menu_link;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEditForm(array &$form, array &$form_state) {
    $form['#title'] = $this->t('Edit menu link %title', array('%title' => $this->menuLink->getTitle()));

    $form['info'] = array(
      '#type' => 'item',
      '#title' => $this->t('This is a module-provided link. The label and path cannot be changed.'),
    );
    $form['path'] = array(
      $this->menuLink->build(),
      '#type' => 'item',
      '#title' => $this->t('Link'),
    );

    $form['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable menu link'),
//      '#title_display' => 'invisible',
      '#description' => $this->t('Menu links that are not enabled will not be listed in any menu.'),
      '#default_value' => !$this->menuLink->isHidden(),
    );
     $form['expanded'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show as expanded'),
       '#description' => $this->t('If selected and this menu link has children, the menu will always appear expanded.'),
      '#default_value' => $this->menuLink->isExpanded(),
    );
    // @TODO Should we expose expanded?
    $delta = max(abs($this->menuLink->getWeight()), 50);
    $form['weight'] = array(
      '#type' => 'weight',
      '#delta' => $delta,
      '#default_value' => $this->menuLink->getWeight(),
      '#title' => $this->t('Weight'),
      '#description' => $this->t('Link weight among links in the same menu at the same depth.'),
    );

    $options = $this->menuTree->getParentSelectOptions($this->menuLink->getPluginId());
    $menu_parent =  $this->menuLink->getMenuName() . ':' . $this->menuLink->getParent();

    if (!isset($options[$menu_parent])) {
      // Put it at the top level in the current menu.
      $menu_parent = $this->menuLink->getMenuName() . ':';
    }
    $form['menu_parent'] = array(
      '#type' => 'select',
      '#title' => $this->t('Parent link'),
      '#options' => $options,
      '#default_value' => $menu_parent,
      '#description' => $this->t('The maximum depth for a link and all its children is fixed at !maxdepth. Some menu links may not be available as parents if selecting them would exceed this limit.', array('!maxdepth' => $this->menuTree->maxDepth())),
      '#attributes' => array('class' => array('menu-title-select')),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(array &$form, array &$form_state) {
    $new_definition = array();
    $new_definition['hidden'] = $form_state['values']['enabled'] ? 0 : 1;
    $new_definition['weight'] = (int) $form_state['values']['weight'];
    $new_definition['expanded'] = $form_state['values']['expanded'] ? 1 : 0;
    list($menu_name, $parent) = explode(':', $form_state['values']['menu_parent'], 2);
    if (!empty($menu_name)) {
      $new_definition['menu_name'] = $menu_name;
    }
    if (isset($parent)) {
      $new_definition['parent'] = $parent;
    }
    return $new_definition;
  }

  /**
   * {@inheritdoc}
   */
  public function validateEditForm(array &$form, array &$form_state) {
  }

  /**
   * Translates a string to the current language or to a given language.
   *
   * See the t() documentation for details.
   */
  protected function t($string, array $args = array(), array $options = array()) {
    return $this->translationManager()->translate($string, $args, $options);
  }

  /**
   * Gets the translation manager.
   *
   * @return \Drupal\Core\StringTranslation\TranslationInterface
   *   The translation manager.
   */
  protected function translationManager() {
    if (!$this->translationManager) {
      $this->translationManager = \Drupal::translation();
    }
    return $this->translationManager;
  }

  /**
   * {@inheritdoc}
   */
  public function submitEditForm(array &$form, array &$form_state) {
    $new_definition = $this->extractFormValues($form, $form_state);

    return $this->menuTree->updateLink($this->menuLink->getPluginId(), $new_definition);
  }

}

