<?php

/**
 * @file
 * Contains \Drupal\block\BlockListController.
 */

namespace Drupal\block;

use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\block\Plugin\Core\Entity\Block;

/**
 * Defines the block list controller.
 */
class BlockListController extends ConfigEntityListController {

  /**
   * The regions containing the blocks.
   *
   * @var array
   */
  protected $regions;

  /**
   * The theme containing the blocks.
   *
   * @var string
   */
  protected $theme;

  /**
   * Overrides \Drupal\Core\Config\Entity\ConfigEntityListController::load().
   */
  public function load() {
    // If no theme was specified, use the current theme.
    if (!$this->theme) {
      $this->theme = $GLOBALS['theme'];
    }

    // Store the region list.
    $this->regions = system_region_list($this->theme, REGIONS_VISIBLE);

    // Load only blocks for this theme, and sort them.
    // @todo Move the functionality of _block_rehash() out of the listing page.
    $entities = _block_rehash($this->theme);
    uasort($entities, 'static::sort');
    return $entities;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityListController::render().
   */
  public function render($theme = NULL) {
    // If no theme was specified, use the current theme.
    $this->theme = $theme ?: $GLOBALS['theme_key'];

    return drupal_get_callback_form('block_admin_display_form', array($this, 'form'));
  }

  /**
   * Sorts active blocks by region then weight; sorts inactive blocks by name.
   */
  protected function sort(Block $a, Block $b) {
    static $regions;
    // We need the region list to correctly order by region.
    if (!isset($regions)) {
      $regions = array_flip(array_keys($this->regions));
      $regions[BLOCK_REGION_NONE] = count($regions);
    }

    // Separate enabled from disabled.
    $status = $b->get('status') - $a->get('status');
    if ($status) {
      return $status;
    }
    // Sort by region (in the order defined by theme .info file).
    $aregion = $a->get('region');
    $bregion = $b->get('region');
    if ((!empty($aregion) && !empty($bregion)) && ($place = ($regions[$aregion] - $regions[$bregion]))) {
      return $place;
    }
    // Sort by weight, unless disabled.
    if ($a->get('region') != BLOCK_REGION_NONE) {
      $weight = $a->get('weight') - $b->get('weight');
      if ($weight) {
        return $weight;
      }
    }
    // Sort by label.
    return strcmp($a->label(), $b->label());
  }

  /**
   * Form constructor for the main block administration form.
   */
  public function form($form, &$form_state) {
    $entities = $this->load();
    $form['#attached']['css'][] = drupal_get_path('module', 'block') . '/block.admin.css';
    $form['#attached']['library'][] = array('system', 'drupal.tableheader');
    $form['#attached']['library'][] = array('block', 'drupal.block');

    // Add a last region for disabled blocks.
    $block_regions_with_disabled = $this->regions + array(BLOCK_REGION_NONE => BLOCK_REGION_NONE);

    foreach ($block_regions_with_disabled as $region => $title) {
      $form['#attached']['drupal_add_tabledrag'][] = array('blocks', 'match', 'sibling', 'block-region-select', 'block-region-' . $region, NULL, FALSE);
      $form['#attached']['drupal_add_tabledrag'][] = array('blocks', 'order', 'sibling', 'block-weight', 'block-weight-' . $region);
    }
    $form['block_regions'] = array(
      '#type' => 'value',
      '#value' => $block_regions_with_disabled,
    );

    // Weights range from -delta to +delta, so delta should be at least half
    // of the amount of blocks present. This makes sure all blocks in the same
    // region get an unique weight.
    $weight_delta = round(count($entities) / 2);

    // Build the form tree.
    $form['edited_theme'] = array(
      '#type' => 'value',
      '#value' => $this->theme,
    );
    $form['blocks'] = array();
    $form['#tree'] = TRUE;

    foreach ($entities as $entity_id => $entity) {
      $info = $entity->getPlugin()->getDefinition();
      $form['blocks'][$entity_id]['info'] = array(
        '#markup' => check_plain($info['subject']),
      );
      $form['blocks'][$entity_id]['theme'] = array(
        '#type' => 'hidden',
        '#value' => $this->theme,
      );
      $form['blocks'][$entity_id]['weight'] = array(
        '#type' => 'weight',
        '#default_value' => $entity->get('weight'),
        '#delta' => $weight_delta,
        '#title_display' => 'invisible',
        '#title' => t('Weight for @block block', array('@block' => $info['subject'])),
      );
      $form['blocks'][$entity_id]['region'] = array(
        '#type' => 'select',
        '#default_value' => $entity->get('region') != BLOCK_REGION_NONE ? $entity->get('region') : NULL,
        '#empty_value' => BLOCK_REGION_NONE,
        '#title_display' => 'invisible',
        '#title' => t('Region for @block block', array('@block' => $info['subject'])),
        '#options' => $this->regions,
      );
      $links['configure'] = array(
        'title' => t('configure'),
        'href' => 'admin/structure/block/manage/' . $entity_id . '/configure',
      );
      $links['delete'] = array(
        'title' => t('delete'),
        'href' => 'admin/structure/block/manage/' . $entity_id . '/delete',
      );
      $form['blocks'][$entity_id]['operations'] = array(
        '#type' => 'operations',
        '#links' => $links,
      );
    }
    // Do not allow disabling the main system content block when it is present.
    if (isset($form['blocks']['system_main']['region'])) {
      $form['blocks']['system_main']['region']['#required'] = TRUE;
    }

    $form['actions'] = array(
      '#tree' => FALSE,
      '#type' => 'actions',
    );
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save blocks'),
      '#button_type' => 'primary',
      '#submit' => array(array($this, 'submit')),
    );
    return $form;
  }

  /**
   * Form submission handler for the main block administration form.
   */
  public function submit($form, &$form_state) {
    $entities = entity_load_multiple('block', array_keys($form_state['values']['blocks']));
    foreach ($entities as $entity_id => $entity) {
      $entity->set('weight', $form_state['values']['blocks'][$entity_id]['weight']);
      $entity->set('region', $form_state['values']['blocks'][$entity_id]['region']);
      $entity->save();
    }
    drupal_set_message(t('The block settings have been updated.'));
    cache_invalidate_tags(array('content' => TRUE));
  }

}
