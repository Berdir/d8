<?php

/**
 * @file
 * Functions to support theming in the Seven theme.
 */

/**
 * Implements hook_preprocess_HOOK() for maintenance-page.tpl.php.
 */
function seven_preprocess_maintenance_page(&$vars) {
  // While markup for normal pages is split into page.tpl.php and html.tpl.php,
  // the markup for the maintenance page is all in the single
  // maintenance-page.tpl.php template. So, to have what's done in
  // seven_preprocess_html() also happen on the maintenance page, it has to be
  // called here.
  seven_preprocess_html($vars);
}

/**
 * Implements hook_preprocess_HOOK() for html.tpl.php.
 */
function seven_preprocess_html(&$vars) {
  drupal_add_library('system', 'normalize');
  // Add conditional CSS for IE8 and below.
  drupal_add_css(path_to_theme() . '/ie.css', array('group' => CSS_THEME, 'browsers' => array('IE' => 'lte IE 8', '!IE' => FALSE), 'weight' => 999, 'preprocess' => FALSE));
}

/**
 * Implements hook_preprocess_HOOK() for page.tpl.php.
 */
function seven_preprocess_page(&$vars) {
  $vars['primary_local_tasks'] = $vars['tabs'];
  unset($vars['primary_local_tasks']['#secondary']);
  $vars['secondary_local_tasks'] = array(
    '#theme' => 'menu_local_tasks',
    '#secondary' => $vars['tabs']['#secondary'],
  );
}

/**
 * Displays the list of available node types for node creation.
 */
function seven_node_add_list($variables) {
  $content = $variables['content'];
  $output = '';
  if ($content) {
    $output = '<ul class="admin-list">';
    foreach ($content as $type) {
      $output .= '<li class="clearfix">';
      $content = '<span class="label">' . check_plain($type->name) . '</span>';
      $content .= '<div class="description">' . filter_xss_admin($type->description) . '</div>';
      $options['html'] = TRUE;
      $output .= l($content, 'node/add/' . $type->type, $options);
      $output .= '</li>';
    }
    $output .= '</ul>';
  }
  else {
    $output = '<p>' . t('You have not created any content types yet. Go to the <a href="@create-content">content type creation page</a> to add a new content type.', array('@create-content' => url('admin/structure/types/add'))) . '</p>';
  }
  return $output;
}

/**
 * Overrides theme_admin_block_content().
 *
 * Uses an unordered list markup in both compact and extended mode.
 */
function seven_admin_block_content($variables) {
  $content = $variables['content'];
  $output = '';
  if (!empty($content)) {
    $output = system_admin_compact_mode() ? '<ul class="admin-list compact">' : '<ul class="admin-list">';
    foreach ($content as $item) {
      $output .= '<li>';
      $content = '<span class="label">' . filter_xss_admin($item['title']) . '</span>';
      $options = $item['localized_options'];
      $options['html'] = TRUE;
      if (isset($item['description']) && !system_admin_compact_mode()) {
        $content .= '<div class="description">' . filter_xss_admin($item['description']) . '</div>';
      }
      $output .= l($content, $item['href'], $options);
      $output .= '</li>';
    }
    $output .= '</ul>';
  }
  return $output;
}

/**
 * Overrides theme_tablesort_indicator().
 *
 * Uses Seven's image versions, so the arrows show up as black and not gray on
 * gray.
 */
function seven_tablesort_indicator($variables) {
  $style = $variables['style'];
  $theme_path = drupal_get_path('theme', 'seven');
  if ($style == 'asc') {
    return theme('image', array('uri' => $theme_path . '/images/arrow-asc.png', 'alt' => t('sort ascending'), 'width' => 13, 'height' => 13, 'title' => t('sort ascending')));
  }
  else {
    return theme('image', array('uri' => $theme_path . '/images/arrow-desc.png', 'alt' => t('sort descending'), 'width' => 13, 'height' => 13, 'title' => t('sort descending')));
  }
}

/**
 * Implements hook_preprocess_install_page().
 */
function seven_preprocess_install_page(&$variables) {
  drupal_add_js(drupal_get_path('theme', 'seven') . '/js/mobile.install.js');
}
