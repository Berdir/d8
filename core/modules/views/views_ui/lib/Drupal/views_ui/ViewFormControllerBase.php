<?php

/**
 * @file
 * Contains Drupal\views_ui\ViewFormControllerBase.
 */

namespace Drupal\views_ui;

use Drupal\Core\Entity\EntityFormController;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Base form controller for Views forms.
 */
abstract class ViewFormControllerBase extends EntityFormController {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::prepareForm().
   */
  protected function prepareEntity(EntityInterface $view) {
    // Determine the displays available for editing.
    if ($tabs = $this->getDisplayTabs($view)) {
      // If a display isn't specified, use the first one.
      if (empty($view->displayID)) {
        foreach ($tabs as $id => $tab) {
          if (!isset($tab['#access']) || $tab['#access']) {
            $view->displayID = $id;
            break;
          }
        }
      }
      // If a display is specified, but we don't have access to it, return
      // an access denied page.
      if ($view->displayID && !isset($tabs[$view->displayID])) {
        throw new NotFoundHttpException();
      }
      elseif ($view->displayID && (isset($tabs[$view->displayID]['#access']) && !$tabs[$view->displayID]['#access'])) {
        throw new AccessDeniedHttpException();
      }

    }
    elseif ($view->displayID) {
      throw new NotFoundHttpException();
    }
  }

  /**
   * Creates an array of Views admin CSS for adding or attaching.
   *
   * This returns an array of arrays. Each array represents a single
   * file. The array format is:
   * - file: The fully qualified name of the file to send to drupal_add_css
   * - options: An array of options to pass to drupal_add_css.
   */
  public static function getAdminCSS() {
    $module_path = drupal_get_path('module', 'views_ui');
    $list = array();
    $list[$module_path . '/css/views-admin.css'] = array();
    $list[$module_path . '/css/views-admin.theme.css'] = array();

    if (module_exists('contextual')) {
      $list[$module_path . '/css/views-admin.contextual.css'] = array();
    }

    return $list;
  }

  /**
   * Adds tabs for navigating across Displays when editing a View.
   *
   * This function can be called from hook_menu_local_tasks_alter() to implement
   * these tabs as secondary local tasks, or it can be called from elsewhere if
   * having them as secondary local tasks isn't desired. The caller is responsible
   * for setting the active tab's #active property to TRUE.
   *
   * @param $display_id
   *   The display_id which is edited on the current request.
   */
  public function getDisplayTabs(ViewUI $view) {
    $display_id = $view->displayID;
    $tabs = array();

    // Create a tab for each display.
    $displays = $view->get('display');
    uasort($displays, array($view, 'sortPosition'));
    $view->set('display', $displays);
    foreach ($displays as $id => $display) {
      $tabs[$id] = array(
        '#theme' => 'menu_local_task',
        '#link' => array(
          'title' => $this->getDisplayLabel($view, $id),
          'href' => 'admin/structure/views/view/' . $view->id() . '/edit/' . $id,
          'localized_options' => array(),
        ),
      );
      if (!empty($display['deleted'])) {
        $tabs[$id]['#link']['localized_options']['attributes']['class'][] = 'views-display-deleted-link';
      }
      if (isset($display['display_options']['enabled']) && !$display['display_options']['enabled']) {
        $tabs[$id]['#link']['localized_options']['attributes']['class'][] = 'views-display-disabled-link';
      }
    }

    // If the default display isn't supposed to be shown, don't display its tab, unless it's the only display.
    if ((!$this->isDefaultDisplayShown($view) && $display_id != 'default') && count($tabs) > 1) {
      $tabs['default']['#access'] = FALSE;
    }

    // Mark the display tab as red to show validation errors.
    $view->get('executable')->validate();
    foreach ($view->get('display') as $id => $display) {
      if (!empty($view->display_errors[$id])) {
        // Always show the tab.
        $tabs[$id]['#access'] = TRUE;
        // Add a class to mark the error and a title to make a hover tip.
        $tabs[$id]['#link']['localized_options']['attributes']['class'][] = 'error';
        $tabs[$id]['#link']['localized_options']['attributes']['title'] = t('This display has one or more validation errors; please review it.');
      }
    }

    return $tabs;
  }

  /**
   * Controls whether or not the default display should have its own tab on edit.
   */
  public function isDefaultDisplayShown(ViewUI $view) {
    // Always show the default display for advanced users who prefer that mode.
    $advanced_mode = config('views.settings')->get('ui.show.master_display');
    // For other users, show the default display only if there are no others, and
    // hide it if there's at least one "real" display.
    $additional_displays = (count($view->get('executable')->displayHandlers) == 1);

    return $advanced_mode || $additional_displays;
  }

  /**
   * Placeholder function for overriding $display['display_title'].
   *
   * @todo Remove this function once editing the display title is possible.
   */
  public function getDisplayLabel(ViewUI $view, $display_id, $check_changed = TRUE) {
    $display = $view->get('display');
    $title = $display_id == 'default' ? t('Master') : $display[$display_id]['display_title'];
    $title = views_ui_truncate($title, 25);

    if ($check_changed && !empty($view->changed_display[$display_id])) {
      $changed = '*';
      $title = $title . $changed;
    }

    return $title;
  }

}
