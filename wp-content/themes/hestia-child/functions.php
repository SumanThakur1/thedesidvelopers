<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !function_exists( 'hestia_child_parent_css' ) ):
    function hestia_child_parent_css() {
        wp_enqueue_style( 'hestia_child_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array( 'bootstrap' ) );
	if( is_rtl() ) {
		wp_enqueue_style( 'hestia_child_parent_rtl', trailingslashit( get_template_directory_uri() ) . 'style-rtl.css', array( 'bootstrap' ) );
	}

    }
endif;
add_action( 'wp_enqueue_scripts', 'hestia_child_parent_css', 10 );

add_action("wp_head",function(){
	if(! is_user_logged_in() ) :?>
	<style>
		li#menu-item-63{
        display:none;
    }
    li#menu-item-101{
			display: inline-block;
		}
	</style><?php
	endif;
	if( is_user_logged_in() ):?>
	<style>
		li#menu-item-101{
			display: none;
		}
		li#menu-item-63{
        display:inline-block;
    }
	</style>
	<?php
	endif;
});