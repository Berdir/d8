<?php

/**
 * @file
 * Contains \Drupal\book\Form\BookRemoveForm.
 */

namespace Drupal\book\Form;

use Drupal\book\BookManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Remove form for book module.
 */
class BookRemoveForm extends ConfirmFormBase {

  /**
   * The book manager.
   *
   * @var \Drupal\book\BookManagerInterface
   */
  protected $bookManager;

  /**
   * The node representing the book.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Constructs a BookRemoveForm object.
   *
   * @param \Drupal\book\BookManagerInterface $book_manager
   *   The book manager.
   */
  public function __construct(BookManagerInterface $book_manager) {
    $this->bookManager = $book_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('book.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'book_remove_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, NodeInterface $node = NULL) {
    $this->node = $node;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $title = array('%title' => $this->node->label());
    if ($this->node->book['has_children']) {
      return $this->t('%title has associated child pages, which will be relocated automatically to maintain their connection to the book. To recreate the hierarchy (as it was before removing this page), %title may be added again using the Outline tab, and each of its former child pages will need to be relocated manually.', $title);
    }
    else {
      return $this->t('%title may be added to hierarchy again using the Outline tab.', $title);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Remove');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to remove %title from the book hierarchy?', array('%title' => $this->node->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return array(
      'route_name' => 'node.view',
      'route_parameters' => array(
        'node' => $this->node->id(),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    if ($this->bookManager->checkNodeIsRemovable($this->node)) {
      $this->bookManager->deleteFromBook($this->node->id());
      drupal_set_message($this->t('The post has been removed from the book.'));
    }
    $form_state['redirect_route'] = array(
      'route_name' => 'node.view',
      'route_parameters' => array(
        'node' => $this->node->id(),
      ),
    );
  }

}
