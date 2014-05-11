<?php

/**
 * @file
 * Contains \Drupal\Core\Menu\Form\MenuLinkFormInterface.
 */

namespace Drupal\Core\Menu\Form;

use Drupal\Core\Menu\MenuLinkInterface;

interface MenuLinkFormInterface {

  /**
   * Injects the menu link.
   *
   * @param MenuLinkInterface $menu_link
   */
  public function setMenuLinkInstance(MenuLinkInterface $menu_link);

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildEditForm(array &$form, array &$form_state);

  /**
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   */
  public function validateEditForm(array &$form, array &$form_state);

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface
   *   The updated instance.
   */
  public function submitEditForm(array &$form, array &$form_state);

  /**
   * Form plugin helper.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   The new plugin definition values takes from the form values.
   */
  public function extractFormValues(array &$form, array &$form_state);

}

