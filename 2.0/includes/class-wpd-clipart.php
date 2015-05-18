<?php

/**
 * Cliparts code
 *
 * @link       http://orionorigin.com
 * @since      3.0
 *
 * @package    Wpd
 * @subpackage Wpd/includes
 */

/**
 * Cliparts code
 *
 * This class defines all code necessary for templates
 *
 * @since      3.0
 * @package    Wpd
 * @subpackage Wpd/includes
 * @author     ORION <support@orionorigin.com>
 */
class WPD_Clipart {

    function register_cpt_cliparts() {

        $labels = array(
            'name' => __('Cliparts', 'wpc-cliparts'),
            'singular_name' => __('Cliparts', 'wpc-cliparts'),
            'add_new' => __('New cliparts group', 'wpc-cliparts'),
            'add_new_item' => __('New cliparts group', 'wpc-cliparts'),
            'edit_item' => __('Edit cliparts group', 'wpc-cliparts'),
            'new_item' => __('New cliparts group', 'wpc-cliparts'),
            'view_item' => __('View group', 'wpc-cliparts'),
    //        'search_items' => __('Search a group', 'wpc-cliparts'),
            'not_found' => __('No cliparts group found', 'wpc-cliparts'),
            'not_found_in_trash' => __('No cliparts group in the trash', 'wpc-cliparts'),
            'menu_name' => __('Cliparts', 'wpc-cliparts'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'description' => 'Cliparts for the product customizer',
            'supports' => array( 'title' ),
            'public' => false,
            'menu_icon' => 'dashicons-images-alt',
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'query_var' => false,
            'can_export' => true
        );

        register_post_type('wpc-cliparts', $args);

    }

    function get_cliparts_metabox() {

        $screens = array( 'wpc-cliparts' );

        foreach ( $screens as $screen ) {

                add_meta_box(
                        'wpc-cliparts-box',
                        __( 'Cliparts', 'wpd' ),
                        array($this, 'get_cliparts_page'),
                        $screen
                );
        }
    }

    function get_cliparts_page()
    {
        $post_id=  get_the_ID();
        wp_enqueue_media();
        ?>
        <div class="wrap">

            <div id="lost-connection-notice" class="error hidden below-h2">
                <p><span class="spinner"></span> <strong>Connection lost.</strong> Saving has been disabled until you’re reconnected.	<span class="hide-if-no-sessionstorage">We’re backing up this post in your browser, just in case.</span>
                </p>
            </div>
            <div id="wp-content-media-buttons" class="wp-media-buttons">
                <a href="#" id="wpc-add-clipart" class="button" data-editor="content" title="Add Cliparts">Add Cliparts</a>
            </div>
            <div id="cliparts-container">
                <?php
                $cliparts= get_post_meta($post_id, "wpc-cliparts", true);
                $cliparts_prices= get_post_meta($post_id, "wpc-cliparts-prices", true);
                if(!empty($cliparts))
                {
                    foreach($cliparts as $i=> $clipart_id)
                    {
                        $attachment_url=wp_get_attachment_url($clipart_id,"full");
                        $price=0;
                        if(isset($cliparts_prices[$i]))
                            $price=$cliparts_prices[$i];

                        echo "
                            <span class='wpc-clipart-holder'>
                                <input type='hidden' value='$clipart_id' name='selected-cliparts[]'>
                                <img src='$attachment_url'>
                                <label>Price: <input type='text' value='$price' name='wpc-cliparts-prices[]'></label>
                                <a href='#' class='button wpc-remove-clipart' data-id='$clipart_id'>Remove</a>
                            </span>";
                    }
                }
                ?>
            </div>
        </div>
        <?php

    }

    function save_cliparts($post_id) {
        if(isset($_POST["selected-cliparts"]))
            update_post_meta($post_id, "wpc-cliparts", $_POST["selected-cliparts"]);

        if(isset($_POST["wpc-cliparts-prices"]))
            update_post_meta ($post_id, "wpc-cliparts-prices", $_POST["wpc-cliparts-prices"]);
    }
}
