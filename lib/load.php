<?php
/**
 * Load the HRSWP Documents plugin.
 *
 * @package HRSWP_Documents
 * @since 1.0.0
 */

namespace HrswpDocuments\lib\load;

use HrswpDocuments\inc\Documents_Post_Type;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Silence is golden.' );
}

require dirname( __FILE__ ) . '/client-assets.php';
require dirname( __FILE__ ) . '/data.php';

require dirname( __DIR__ ) . '/inc/class-documents-post-type.php';

Documents_Post_Type\Documents_Post_Type::factory();
