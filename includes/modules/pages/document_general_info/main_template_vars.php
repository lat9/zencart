<?php
/**
 *  document_general_info main_template_vars.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 24 Modified in v2.1.0-alpha1 $
 */
/*
 * Extracts and constructs the data to be used in the product-type template tpl_TYPEHANDLER_info_display.php
 */

  // This should be first line of the script:
  $zco_notifier->notify('NOTIFY_MAIN_TEMPLATE_VARS_START_DOCUMENT_GENERAL_INFO');

  $product_info = $product_info ?? new Product((int)$_GET['products_id']);

  if (!isset($product_info->fields['products_id'], $product_info->fields['products_status']) || (int)$product_info->fields['products_id'] !== (int)$_GET['products_id']) {
      $product_info = new Product((int)$_GET['products_id']);
  }

  $product_not_found = !$product_info->exists();

  if (!defined('DISABLED_PRODUCTS_TRIGGER_HTTP200') || DISABLED_PRODUCTS_TRIGGER_HTTP200 !== 'true') {
      if (!$product_not_found && $product_info->fields['products_status'] != 1) {
      $product_not_found = true;
    }
  }

  if ($product_not_found) {
    $tpl_page_body = '/tpl_product_info_noproduct.php';
  } else {

    $tpl_page_body = '/tpl_document_general_info_display.php';

    $zco_notifier->notify('NOTIFY_PRODUCT_VIEWS_HIT_INCREMENTOR', (int)$_GET['products_id']);

    $products_price_sorter = $product_info->fields['products_price_sorter'];

    $products_price = $currencies->display_price($product_info->fields['products_price'], zen_get_tax_rate($product_info->fields['products_tax_class_id']));

    $manufacturers_name = $product_info->fields['manufacturers_name'];

    if ($new_price = zen_get_products_special_price($product_info->fields['products_id'])) {
      $specials_price = $currencies->display_price($new_price, zen_get_tax_rate($product_info->fields['products_tax_class_id']));
    }

// set flag for attributes module usage:
    $flag_show_weight_attrib_for_this_prod_type = 0;
// get attributes
    require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_ATTRIBUTES));

// if review must be approved or disabled do not show review
    $review_status = " AND r.status = 1";

    $reviews_query = "SELECT count(*) AS count FROM " . TABLE_REVIEWS . " r, "
                                                       . TABLE_REVIEWS_DESCRIPTION . " rd
                       WHERE r.products_id = " . (int)$_GET['products_id'] . "
                       AND r.reviews_id = rd.reviews_id
                       AND rd.languages_id = " . (int)$_SESSION['languages_id'] .
                       $review_status;

    $reviews = $db->Execute($reviews_query);

  $products_name = $product_info->fields['lang'][$_SESSION['languages_code']]['products_name'];
  $products_model = $product_info->fields['products_model'];
  // if no common markup tags in description, add line breaks for readability:
  $products_description = (!preg_match('/(<br|<p|<div|<dd|<li|<span)/i', $product_info->fields['lang'][$_SESSION['languages_code']]['products_description']) ? nl2br($product_info->fields['lang'][$_SESSION['languages_code']]['products_description']) : $product_info->fields['lang'][$_SESSION['languages_code']]['products_description']);

  $products_image = (($product_not_found || $product_info->fields['products_image'] == '') && PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') ? PRODUCTS_IMAGE_NO_IMAGE : '';
  if ($product_info->fields['products_image'] != '' || PRODUCTS_IMAGE_NO_IMAGE_STATUS != '1') {
    $products_image = $product_info->fields['products_image'];
  }

  $products_url = $product_info->fields['lang'][$_SESSION['languages_code']]['products_url'];
  $products_date_available = $product_info->fields['products_date_available'];
  $products_date_added = $product_info->fields['products_date_added'];
  $products_manufacturer = $manufacturers_name;
  $products_weight = $product_info->fields['products_weight'];
  $products_quantity = $product_info->fields['products_quantity'];

  $products_qty_box_status = $product_info->fields['products_qty_box_status'];
  $products_quantity_order_max = $product_info->fields['products_quantity_order_max'];
  $products_get_buy_now_qty = zen_get_buy_now_qty($_GET['products_id']);

  $products_base_price = $currencies->display_price(zen_get_products_base_price((int)$_GET['products_id']),
                      zen_get_tax_rate($product_info->fields['products_tax_class_id']));

  $product_is_free = $product_info->fields['product_is_free'];

  $products_tax_class_id = $product_info->fields['products_tax_class_id'];

    $products_discount_type = $product_info->fields['products_discount_type'];
    $products_discount_type_from = $product_info->fields['products_discount_type_from'];

    require(DIR_WS_MODULES . zen_get_module_directory('product_prev_next.php'));
  
    $module_show_categories = PRODUCT_INFO_CATEGORIES;
    $module_next_previous = PRODUCT_INFO_PREVIOUS_NEXT;
  
    $products_id_current = (int)$_GET['products_id'];
  
  /**
   * Load product-type-specific main_template_vars
   */
    $prod_type_specific_vars_info = DIR_WS_MODULES . 'pages/' . $current_page_base . '/main_template_vars_product_type.php';
    if (file_exists($prod_type_specific_vars_info)) {
      include_once($prod_type_specific_vars_info);
    }
    $zco_notifier->notify('NOTIFY_MAIN_TEMPLATE_VARS_PRODUCT_TYPE_VARS_DOCUMENT_GENERAL_INFO');
  
  
  /**
   * Load all *.PHP files from the /includes/templates/MYTEMPLATE/PAGENAME/extra_main_template_vars
   */
    $extras_dir = $template->get_template_dir('.php', DIR_WS_TEMPLATE, $current_page_base . 'extra_main_template_vars', $current_page_base . '/' . 'extra_main_template_vars');
    foreach(zen_get_files_in_directory($extras_dir) as $file) {
        include($file);
    }
  
  // build show flags from product type layout settings
    $flag_show_product_info_starting_at = 0;// zen_get_show_product_switch($_GET['products_id'], 'starting_at');
    $flag_show_product_info_model = 0;// zen_get_show_product_switch($_GET['products_id'], 'model');
    $flag_show_product_info_weight = 0;// zen_get_show_product_switch($_GET['products_id'], 'weight');
    $flag_show_product_info_quantity = 0;// zen_get_show_product_switch($_GET['products_id'], 'quantity');
    $flag_show_product_info_manufacturer = 0;// zen_get_show_product_switch($_GET['products_id'], 'manufacturer');
    $flag_show_product_info_in_cart_qty = 0;// zen_get_show_product_switch($_GET['products_id'], 'in_cart_qty');
  
    $flag_show_product_info_reviews = zen_get_show_product_switch($_GET['products_id'], 'reviews');
    $flag_show_product_info_reviews_count = zen_get_show_product_switch($_GET['products_id'], 'reviews_count');
    $flag_show_product_info_date_available = zen_get_show_product_switch($_GET['products_id'], 'date_available');
    $flag_show_product_info_date_added = zen_get_show_product_switch($_GET['products_id'], 'date_added');
    $flag_show_product_info_url = zen_get_show_product_switch($_GET['products_id'], 'url');
    $flag_show_product_info_additional_images = zen_get_show_product_switch($_GET['products_id'], 'additional_images');
    $flag_show_product_info_free_shipping = zen_get_show_product_switch($_GET['products_id'], 'always_free_shipping_image_switch');
    $flag_show_ask_a_question = !empty(zen_get_show_product_switch($_GET['products_id'], 'ask_a_question'));
    require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCTS_QUANTITY_DISCOUNTS));
  
    $zco_notifier->notify('NOTIFY_MAIN_TEMPLATE_VARS_EXTRA_DOCUMENT_GENERAL_INFO');
  }

  require($template->get_template_dir($tpl_page_body,DIR_WS_TEMPLATE, $current_page_base,'templates'). $tpl_page_body);

  //require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_ALSO_PURCHASED_PRODUCTS));

  // This should be last line of the script:
  $zco_notifier->notify('NOTIFY_MAIN_TEMPLATE_VARS_END_DOCUMENT_GENERAL_INFO');
