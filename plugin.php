<?php
define('BEAMMEUP_PLUGIN_VERSION', '0.1');

/** Plugin hooks */
add_plugin_hook('install', 'beam_install');
add_plugin_hook('uninstall', 'beam_uninstall');
add_plugin_hook('config_form', 'beam_config_form');
add_plugin_hook('config', 'beam_config');
add_plugin_hook('admin_append_to_items_form_files', 'beam_admin_append_to_items_form_files');
add_plugin_hook('after_save_item', 'beam_after_save_item');//the big one
add_plugin_hook('admin_append_to_items_show_secondary', 'beam_admin_append_to_items_show_secondary');
add_plugin_hook('admin_theme_header', 'beam_admin_theme_header');

/** Plugin filters */
add_filter('admin_items_form_tabs', 'beam_admin_item_form_tabs');