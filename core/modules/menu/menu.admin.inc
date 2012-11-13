<?php

/**
 * @file
 * Administrative page callbacks for menu module.
 */

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\menu_link\Plugin\Core\Entity\MenuLink;

/**
 * Menu callback which shows an overview page of all the custom menus and their descriptions.
 */
function menu_overview_page() {
  $result = db_query("SELECT * FROM {menu_custom} ORDER BY title", array(), array('fetch' => PDO::FETCH_ASSOC));
  $header = array(t('Title'), t('Operations'));
  $rows = array();
  foreach ($result as $menu) {
    $row = array();
    $row[] = theme('menu_admin_overview', array('title' => $menu['title'], 'name' => $menu['menu_name'], 'description' => $menu['description']));
    $links = array();
    $links['list'] = array(
      'title' => t('list links'),
      'href' => 'admin/structure/menu/manage/' . $menu['menu_name'],
    );
    $links['edit'] = array(
      'title' => t('edit menu'),
      'href' => 'admin/structure/menu/manage/' . $menu['menu_name'] . '/edit',
    );
    $links['add'] = array(
      'title' => t('add link'),
      'href' => 'admin/structure/menu/manage/' . $menu['menu_name'] . '/add',
    );
    $row[] = array(
      'data' => array(
        '#type' => 'operations',
        '#links' => $links,
      ),
    );
    $rows[] = $row;
  }

  return theme('table', array('header' => $header, 'rows' => $rows));
}

/**
 * Returns HTML for a menu title and description for the menu overview page.
 *
 * @param $variables
 *   An associative array containing:
 *   - title: The menu's title.
 *   - description: The menu's description.
 *
 * @ingroup themeable
 */
function theme_menu_admin_overview($variables) {
  $output = check_plain($variables['title']);
  $output .= '<div class="description">' . filter_xss_admin($variables['description']) . '</div>';

  return $output;
}

/**
 * Form for editing an entire menu tree at once.
 *
 * Shows for one menu the menu links accessible to the current user and
 * relevant operations.
 */
function menu_overview_form($form, &$form_state, $menu) {
  global $menu_admin;
  $form['#attached']['css'] = array(drupal_get_path('module', 'menu') . '/menu.admin.css');
  $links = array();
  $query = entity_query('menu_link')
    ->condition('menu_name', $menu['menu_name']);
  for ($i = 1; $i <= MENU_MAX_DEPTH; $i++) {
    $query->sort('p' . $i, 'ASC');
  }
  $result = $query->execute();

  if (!empty($result)) {
    $links = menu_link_load_multiple($result);
  }

  $delta = max(count($links), 50);
  $tree = menu_tree_data($links);
  $node_links = array();
  menu_tree_collect_node_links($tree, $node_links);
  // We indicate that a menu administrator is running the menu access check.
  $menu_admin = TRUE;
  menu_tree_check_access($tree, $node_links);
  $menu_admin = FALSE;

  $form = array_merge($form, _menu_overview_tree_form($tree, $delta));
  $form['#menu'] =  $menu;

  if (element_children($form)) {
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save configuration'),
    );
  }
  else {
    $form['#empty_text'] = t('There are no menu links yet. <a href="@link">Add link</a>.', array('@link' => url('admin/structure/menu/manage/'. $form['#menu']['menu_name'] .'/add')));
  }
  return $form;
}

/**
 * Recursive helper function for menu_overview_form().
 *
 * @param $tree
 *   The menu_tree retrieved by menu_tree_data.
 * @param $delta
 *   The default number of menu items used in the menu weight selector is 50.
 */
function _menu_overview_tree_form($tree, $delta = 50) {
  $form = &drupal_static(__FUNCTION__, array('#tree' => TRUE));
  foreach ($tree as $data) {
    $title = '';
    $item = $data['link'];
    // Don't show callbacks; these have $item['hidden'] < 0.
    if ($item && $item['hidden'] >= 0) {
      $mlid = 'mlid:' . $item['mlid'];
      $form[$mlid]['#item'] = $item;
      $form[$mlid]['#attributes'] = $item['hidden'] ? array('class' => array('menu-disabled')) : array('class' => array('menu-enabled'));
      $form[$mlid]['title']['#markup'] = l($item['title'], $item['href'], $item['localized_options']);
      if ($item['hidden']) {
        $form[$mlid]['title']['#markup'] .= ' (' . t('disabled') . ')';
      }
      elseif ($item['link_path'] == 'user' && $item['module'] == 'system') {
        $form[$mlid]['title']['#markup'] .= ' (' . t('logged in users only') . ')';
      }

      $form[$mlid]['hidden'] = array(
        '#type' => 'checkbox',
        '#title' => t('Enable @title menu link', array('@title' => $item['title'])),
        '#title_display' => 'invisible',
        '#default_value' => !$item['hidden'],
      );
      $form[$mlid]['weight'] = array(
        '#type' => 'weight',
        '#delta' => $delta,
        '#default_value' => $item['weight'],
        '#title_display' => 'invisible',
        '#title' => t('Weight for @title', array('@title' => $item['title'])),
      );
      $form[$mlid]['mlid'] = array(
        '#type' => 'hidden',
        '#value' => $item['mlid'],
      );
      $form[$mlid]['plid'] = array(
        '#type' => 'hidden',
        '#default_value' => $item['plid'],
      );
      // Build a list of operations.
      $operations = array();
      $links = array();
      $links['edit'] = array(
        'title' => t('edit'),
        'href' => 'admin/structure/menu/item/' . $item['mlid'] . '/edit',
      );
      $operations['edit'] = array('#type' => 'link', '#title' => t('edit'), '#href' => 'admin/structure/menu/item/' . $item['mlid'] . '/edit');
      // Only items created by the menu module can be deleted.
      if ($item['module'] == 'menu' || $item['updated'] == 1) {
        $links['delete'] = array(
          'title' => t('delete'),
          'href' => 'admin/structure/menu/item/' . $item['mlid'] . '/delete',
        );
        $operations['delete'] = array('#type' => 'link', '#title' => t('delete'), '#href' => 'admin/structure/menu/item/' . $item['mlid'] . '/delete');
      }
      // Set the reset column.
      elseif ($item['module'] == 'system' && $item['customized']) {
        $links['reset'] = array(
          'title' => t('reset'),
          'href' => 'admin/structure/menu/item/' . $item['mlid'] . '/reset',
        );
        $operations['reset'] = array('#type' => 'link', '#title' => t('reset'), '#href' => 'admin/structure/menu/item/' . $item['mlid'] . '/reset');
      }
      $form[$mlid]['operations'] = array(
        '#type' => 'operations',
        '#links' => $links,
      );
    }

    if ($data['below']) {
      _menu_overview_tree_form($data['below'], $delta);
    }
  }
  return $form;
}

/**
 * Submit handler for the menu overview form.
 *
 * This function takes great care in saving parent items first, then items
 * underneath them. Saving items in the incorrect order can break the menu tree.
 *
 * @see menu_overview_form()
 */
function menu_overview_form_submit($form, &$form_state) {
  // When dealing with saving menu items, the order in which these items are
  // saved is critical. If a changed child item is saved before its parent,
  // the child item could be saved with an invalid path past its immediate
  // parent. To prevent this, save items in the form in the same order they
  // are sent by $_POST, ensuring parents are saved first, then their children.
  // See http://drupal.org/node/181126#comment-632270
  $order = array_flip(array_keys($form_state['input'])); // Get the $_POST order.
  $form = array_intersect_key(array_merge($order, $form), $form); // Update our original form with the new order.

  $updated_items = array();
  $fields = array('weight', 'plid');
  foreach (element_children($form) as $mlid) {
    if (isset($form[$mlid]['#item'])) {
      $element = $form[$mlid];
      // Update any fields that have changed in this menu item.
      foreach ($fields as $field) {
        if ($element[$field]['#value'] != $element[$field]['#default_value']) {
          $element['#item'][$field] = $element[$field]['#value'];
          $updated_items[$mlid] = $element['#item'];
        }
      }
      // Hidden is a special case, the value needs to be reversed.
      if ($element['hidden']['#value'] != $element['hidden']['#default_value']) {
        // Convert to integer rather than boolean due to PDO cast to string.
        $element['#item']['hidden'] = $element['hidden']['#value'] ? 0 : 1;
        $updated_items[$mlid] = $element['#item'];
      }
    }
  }

  // Save all our changed items to the database.
  foreach ($updated_items as $item) {
    $item['customized'] = 1;
    menu_link_save($item);
  }
  drupal_set_message(t('Your configuration has been saved.'));
}

/**
 * Returns HTML for the menu overview form into a table.
 *
 * @param $variables
 *   An associative array containing:
 *   - form: A render element representing the form.
 *
 * @ingroup themeable
 */
function theme_menu_overview_form($variables) {
  $form = $variables['form'];

  drupal_add_tabledrag('menu-overview', 'match', 'parent', 'menu-plid', 'menu-plid', 'menu-mlid', TRUE, MENU_MAX_DEPTH - 1);
  drupal_add_tabledrag('menu-overview', 'order', 'sibling', 'menu-weight');

  $header = array(
    t('Menu link'),
    array('data' => t('Enabled'), 'class' => array('checkbox')),
    t('Weight'),
    t('Operations'),
  );

  $rows = array();
  foreach (element_children($form) as $mlid) {
    if (isset($form[$mlid]['hidden'])) {
      $element = &$form[$mlid];

      // Add special classes to be used for tabledrag.js.
      $element['plid']['#attributes']['class'] = array('menu-plid');
      $element['mlid']['#attributes']['class'] = array('menu-mlid');
      $element['weight']['#attributes']['class'] = array('menu-weight');

      // Change the parent field to a hidden. This allows any value but hides the field.
      $element['plid']['#type'] = 'hidden';

      $row = array();
      $row[] = theme('indentation', array('size' => $element['#item']['depth'] - 1)) . drupal_render($element['title']);
      $row[] = array('data' => drupal_render($element['hidden']), 'class' => array('checkbox', 'menu-enabled'));
      $row[] = drupal_render($element['weight']) . drupal_render($element['plid']) . drupal_render($element['mlid']);
      $row[] = drupal_render($element['operations']);

      $row = array_merge(array('data' => $row), $element['#attributes']);
      $row['class'][] = 'draggable';
      $rows[] = $row;
    }
  }
  $output = '';
  if (empty($rows)) {
    $rows[] = array(array('data' => $form['#empty_text'], 'colspan' => '7'));
  }
  $output .= theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'menu-overview')));
  $output .= drupal_render_children($form);
  return $output;
}

/**
 * Menu callback; Build the form that handles the adding/editing of a custom menu.
 */
function menu_edit_menu($form, &$form_state, $type, $menu = array()) {
  $system_menus = menu_list_system_menus();
  $menu += array(
    'menu_name' => '',
    'old_name' => !empty($menu['menu_name']) ? $menu['menu_name'] : '',
    'title' => '',
    'description' => '',
  );
  // Allow menu_edit_menu_submit() and other form submit handlers to determine
  // whether the menu already exists.
  $form['#insert'] = empty($menu['old_name']);
  $form['old_name'] = array(
    '#type' => 'value',
    '#value' => $menu['old_name'],
  );

  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#default_value' => $menu['title'],
    '#required' => TRUE,
    // The title of a system menu cannot be altered.
    '#access' => !isset($system_menus[$menu['menu_name']]),
  );

  $form['menu_name'] = array(
    '#type' => 'machine_name',
    '#title' => t('Menu name'),
    '#default_value' => $menu['menu_name'],
    '#maxlength' => MENU_MAX_MENU_NAME_LENGTH_UI,
    '#description' => t('A unique name to construct the URL for the menu. It must only contain lowercase letters, numbers and hyphens.'),
    '#machine_name' => array(
      'exists' => 'menu_edit_menu_name_exists',
      'source' => array('title'),
      'replace_pattern' => '[^a-z0-9-]+',
      'replace' => '-',
    ),
    // A menu's machine name cannot be changed.
    '#disabled' => !empty($menu['old_name']) || isset($system_menus[$menu['menu_name']]),
  );

  $form['description'] = array(
    '#type' => 'textarea',
    '#title' => t('Description'),
    '#default_value' => $menu['description'],
  );
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  // Only custom menus may be deleted.
  $form['actions']['delete'] = array(
    '#type' => 'submit',
    '#value' => t('Delete'),
    '#access' => $type == 'edit' && !isset($system_menus[$menu['menu_name']]),
    '#submit' => array('menu_custom_delete_submit'),
  );

  return $form;
}

/**
 * Submit function for the 'Delete' button on the menu editing form.
 */
function menu_custom_delete_submit($form, &$form_state) {
  $form_state['redirect'] = 'admin/structure/menu/manage/' . $form_state['values']['menu_name'] . '/delete';
}

/**
 * Menu callback; check access and get a confirm form for deletion of a custom menu.
 */
function menu_delete_menu_page($menu) {
  // System-defined menus may not be deleted.
  $system_menus = menu_list_system_menus();
  if (isset($system_menus[$menu['menu_name']])) {
    throw new AccessDeniedHttpException();
  }
  return drupal_get_form('menu_delete_menu_confirm', $menu);
}

/**
 * Build a confirm form for deletion of a custom menu.
 */
function menu_delete_menu_confirm($form, &$form_state, $menu) {
  $form['#menu'] = $menu;
  $caption = '';
  $num_links = entity_get_controller('menu_link')->countMenuLinks($menu['menu_name']);
  if ($num_links) {
    $caption .= '<p>' . format_plural($num_links, '<strong>Warning:</strong> There is currently 1 menu link in %title. It will be deleted (system-defined items will be reset).', '<strong>Warning:</strong> There are currently @count menu links in %title. They will be deleted (system-defined links will be reset).', array('%title' => $menu['title'])) . '</p>';
  }
  $caption .= '<p>' . t('This action cannot be undone.') . '</p>';
  return confirm_form($form, t('Are you sure you want to delete the custom menu %title?', array('%title' => $menu['title'])), 'admin/structure/menu/manage/' . $menu['menu_name'], $caption, t('Delete'));
}

/**
 * Delete a custom menu and all links in it.
 */
function menu_delete_menu_confirm_submit($form, &$form_state) {
  $menu = $form['#menu'];
  $form_state['redirect'] = 'admin/structure/menu';

  // System-defined menus may not be deleted - only menus defined by this module.
  $system_menus = menu_list_system_menus();
  if (isset($system_menus[$menu['menu_name']])  || !(db_query("SELECT 1 FROM {menu_custom} WHERE menu_name = :menu", array(':menu' => $menu['menu_name']))->fetchField())) {
    return;
  }

  // Reset all the menu links defined by the system via hook_menu().
  // @todo Convert this to an EFQ once we figure out 'ORDER BY m.number_parts'.
  $result = db_query("SELECT mlid FROM {menu_links} ml INNER JOIN {menu_router} m ON ml.router_path = m.path WHERE ml.menu_name = :menu AND ml.module = 'system' ORDER BY m.number_parts ASC", array(':menu' => $menu['menu_name']), array('fetch' => PDO::FETCH_ASSOC))->fetchCol();
  $menu_links = menu_link_load_multiple($result);
  foreach ($menu_links as $link) {
    $link->reset();
  }

  // Delete all links to the overview page for this menu.
  $menu_links = entity_load_multiple_by_properties('menu_link', array('link_path' => 'admin/structure/menu/manage/' . $menu['menu_name']));
  menu_link_delete_multiple(array_keys($menu_links));

  // Delete the custom menu and all its menu links.
  menu_delete($menu);

  $t_args = array('%title' => $menu['title']);
  drupal_set_message(t('The custom menu %title has been deleted.', $t_args));
  watchdog('menu', 'Deleted custom menu %title and all its menu links.', $t_args, WATCHDOG_NOTICE);
}

/**
 * Returns whether a menu name already exists.
 *
 * @see menu_edit_menu()
 * @see form_validate_machine_name()
 */
function menu_edit_menu_name_exists($value) {
  // 'menu-' is added to the menu name to avoid name-space conflicts.
  $value = 'menu-' . $value;
  $custom_exists = db_query_range('SELECT 1 FROM {menu_custom} WHERE menu_name = :menu', 0, 1, array(':menu' => $value))->fetchField();
  $link_exists = entity_query('menu_link')->condition('menu_name', $value)->range(0,1)->count()->execute();

  return $custom_exists || $link_exists;
}

/**
 * Submit function for adding or editing a custom menu.
 */
function menu_edit_menu_submit($form, &$form_state) {
  $menu = $form_state['values'];
  $path = 'admin/structure/menu/manage/';
  if ($form['#insert']) {
    // Add 'menu-' to the menu name to help avoid name-space conflicts.
    $menu['menu_name'] = 'menu-' . $menu['menu_name'];
    $system_link = entity_load_multiple_by_properties('menu_link', array('link_path' => 'admin/structure/menu', 'module' => 'system'));
    $system_link = reset($system_link);
    $menu_link = entity_create('menu_link', array(
      'link_title' => $menu['title'],
      'link_path' => $path . $menu['menu_name'],
      'router_path' => $path . '%',
      'plid' => $system_link->id(),
    ));
    $menu_link->save();
    menu_save($menu);
  }
  else {
    menu_save($menu);
    $menu_links = entity_load_multiple_by_properties('menu_link', array('link_path' => $path . $menu['menu_name']));
    foreach ($menu_links as $menu_link) {
      $menu_link->link_title = $menu['title'];
      $menu_link->save();
    }
  }
  drupal_set_message(t('Your configuration has been saved.'));
  $form_state['redirect'] = $path . $menu['menu_name'];
}

/**
 * Menu callback: Provides the menu link submission form.
 *
 * @param array $menu
 *   An array representing a custom menu.
 *
 * @return
 *   Returns the menu link submission form.
 */
function menu_link_add(array $menu) {
  $menu_link = entity_create('menu_link', array(
    'mlid' => 0,
    'plid' => 0,
    'menu_name' => $menu['menu_name'],
  ));
  drupal_set_title(t('Add menu link'));
  return entity_get_form($menu_link);
}

/**
 * Menu callback; Check access and present a confirm form for deleting a menu
 * link.
 */
function menu_link_delete_page(MenuLink $menu_link) {
  // Links defined via hook_menu may not be deleted. Updated items are an
  // exception, as they can be broken.
  if ($menu_link->module == 'system' && !$menu_link->updated) {
    throw new AccessDeniedHttpException();
  }
  return drupal_get_form('menu_link_delete_form', $menu_link);
}

/**
 * Build a confirm form for deletion of a single menu link.
 */
function menu_link_delete_form($form, &$form_state, MenuLink $menu_link) {
  $form['#menu_link'] = $menu_link;
  return confirm_form($form,
    t('Are you sure you want to delete the custom menu link %item?', array('%item' => $menu_link->link_title)),
    'admin/structure/menu/manage/' . $menu_link->menu_name
  );
}

/**
 * Processes menu link delete form submissions.
 */
function menu_link_delete_form_submit($form, &$form_state) {
  $menu_link = $form['#menu_link'];
  menu_link_delete($menu_link->id());
  $t_args = array('%title' => $menu_link->link_title);
  drupal_set_message(t('The menu link %title has been deleted.', $t_args));
  watchdog('menu', 'Deleted menu link %title.', $t_args, WATCHDOG_NOTICE);
  $form_state['redirect'] = 'admin/structure/menu/manage/' . $menu_link->menu_name;
}

/**
 * Menu callback; Reset a single modified menu link.
 */
function menu_link_reset_form($form, &$form_state, MenuLink $menu_link) {
  $form['#menu_link'] = $menu_link;
  return confirm_form($form,
    t('Are you sure you want to reset the link %item to its default values?', array('%item' => $menu_link->link_title)),
    'admin/structure/menu/manage/' . $menu_link->menu_name,
    t('Any customizations will be lost. This action cannot be undone.'),
    t('Reset'));
}

/**
 * Processes menu link reset form submissions.
 */
function menu_link_reset_form_submit($form, &$form_state) {
  $menu_link = $form['#menu_link'];
  $new_menu_link = $menu_link->reset();
  drupal_set_message(t('The menu link was reset to its default settings.'));
  $form_state['redirect'] = 'admin/structure/menu/manage/' . $new_menu_link->menu_name;
}

/**
 * Menu callback; Build the form presenting menu configuration options.
 *
 * @ingroup forms
 * @see menu_configure_submit()
 */
function menu_configure($form, &$form_state) {
  $config = config('menu.settings');
  $form['intro'] = array(
    '#type' => 'item',
    '#markup' => t('The menu module allows on-the-fly creation of menu links in the content authoring forms. To configure these settings for a particular content type, visit the <a href="@content-types">Content types</a> page, click the <em>edit</em> link for the content type, and go to the <em>Menu settings</em> section.', array('@content-types' => url('admin/structure/types'))),
  );

  $menu_options = menu_get_menus();

  $main = $config->get('main_links');
  $form['menu_main_links_source'] = array(
    '#type' => 'select',
    '#title' => t('Source for the Main links'),
    '#default_value' => $main,
    '#empty_option' => t('No Main links'),
    '#options' => $menu_options,
    '#tree' => FALSE,
    '#description' => t('Select what should be displayed as the Main links (typically at the top of the page).'),
  );

  $form['menu_secondary_links_source'] = array(
    '#type' => 'select',
    '#title' => t('Source for the Secondary links'),
    '#default_value' => $config->get('secondary_links'),
    '#empty_option' => t('No Secondary links'),
    '#options' => $menu_options,
    '#tree' => FALSE,
    '#description' => t('Select the source for the Secondary links. An advanced option allows you to use the same source for both Main links (currently %main) and Secondary links: if your source menu has two levels of hierarchy, the top level menu links will appear in the Main links, and the children of the active link will appear in the Secondary links.', array('%main' => $main ? $menu_options[$main] : t('none'))),
  );

  return system_config_form($form, $form_state);
}

/**
 * Form submission handler for menu_configure().
 */
function menu_configure_submit($form, &$form_state) {
  config('menu.settings')
    ->set('main_links', $form_state['values']['menu_main_links_source'])
    ->set('secondary_links', $form_state['values']['menu_secondary_links_source'])
    ->save();
}
