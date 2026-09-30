<?php
/**
 * Remove the per-widget config caches on uninstall.
 *
 * @package KM_Catalog_Builder
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '\_kmcb\_cfg\_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_kmcb\_cfg\_%'" );
