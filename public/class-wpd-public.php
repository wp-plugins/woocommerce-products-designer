<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://orionorigin.com
 * @since      3.0
 *
 * @package    Wpd
 * @subpackage Wpd/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpd
 * @subpackage Wpd/public
 * @author     ORION <support@orionorigin.com>
 */
class WPD_Public {

    /**
     * The ID of this plugin.
     *
     * @since    3.0
     * @access   private
     * @var      string    $wpd    The ID of this plugin.
     */
    private $wpd;

    /**
     * The version of this plugin.
     *
     * @since    3.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    3.0
     * @param      string    $wpd       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($wpd, $version) {

        $this->wpd = $wpd;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    3.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wpd_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wpd_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->wpd, plugin_dir_url(__FILE__) . 'css/wpd-public.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-simplegrid", WPD_URL . 'admin/css/simplegrid.min.css', array(), $this->version, 'all');
//                wp_enqueue_style( "SpryAccordion-css", WPD_URL . 'public/js/SpryAssets/SpryAccordion.min.css', array(), $this->version, 'all' );
        wp_enqueue_style("wpd-tooltip-css", WPD_URL . 'admin/css/tooltip.min.css', array(), $this->version, 'all');
//                wp_enqueue_style( "wpd-fancyselect-css", WPD_URL . 'public/css/fancySelect.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-colorpicker-css", WPD_URL . 'admin/js/colorpicker/css/colorpicker.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-bs-modal-css", WPD_URL . 'public/js/modal/modal.min.css', array(), $this->version, 'all');


//                $design_page = $general_options['wpc_page_id'];
//                if(function_exists("icl_object_id"))
//                    $design_page= icl_object_id($design_page, 'page', false,ICL_LANGUAGE_CODE);
//                
//                $current_page_id=  get_the_ID();
//                if ( $design_page ==$current_page_id)
//                {
//                    WPD_editor::register_fonts();
//                }
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    3.0
     */
    public function enqueue_scripts() {
        GLOBAL $wpc_options_settings;
        $options = $wpc_options_settings['wpc-general-options'];
        wp_enqueue_script('jquery');
        wp_enqueue_script("wpd-tooltip-js", WPD_URL . '/admin/js/tooltip.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script("wpd-colorpicker-js", WPD_URL . 'admin/js/colorpicker/js/colorpicker.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->wpd, plugin_dir_url(__FILE__) . 'js/wpd-public.min.js', array('jquery'), $this->version, false);
        if (!isset($options["wpc-load-bs-modal"]) || ($options["wpc-load-bs-modal"] == "1")) {
            wp_enqueue_script('wpd-bs-modal', WPD_URL . 'public/js/modal/modal.min.js', array('jquery'), $this->version, false);
        }
        wp_localize_script($this->wpd, 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }

    /**
     * Register the plugin shortcodes
     */
    public function register_shortcodes() {
        add_shortcode("wpc-templates", array($this, 'get_templates'));
        add_shortcode('wpc-products', array($this, 'get_products_display'));
        add_shortcode('wpc-editor', array($this, 'get_editor_shortcode_handler'));
    }

    public function get_editor_shortcode_handler() {
        global $wp_query;
        $item_id = $wp_query->query_vars["product_id"];
        $editor_obj = new WPD_Editor($item_id);
        return $editor_obj->get_editor();
    }

    function get_products_display($atts) {
        global $wpdb;
        extract(shortcode_atts(array(
            'cat' => '',
            'products' => '',
            'cols' => '3'
                        ), $atts, 'wpc-products'));

        $where = "";
        if (!empty($cat)) {
            $where.=" AND $wpdb->term_relationships.term_taxonomy_id IN ($cat)";
        } else if (!empty($products))
            $where.=" AND p.ID IN ($products)";
        else
            $where = "";
        $search = '"is-customizable";s:1:"1"';

        $products = $wpdb->get_results(
                "
                            SELECT distinct p.id
                            FROM $wpdb->posts p
                            JOIN $wpdb->postmeta pm on pm.post_id = p.id
                            INNER JOIN $wpdb->term_relationships ON (p.ID = $wpdb->term_relationships.object_id	) 
                            WHERE p.post_type = 'product'
                            AND p.post_status = 'publish'
                            AND pm.meta_key = 'wpc-metas'
                            $where
                            AND pm.meta_value like '%$search%'
                            ");
        ob_start();
        ?>
        <div class='container wp-products-container wpc-grid wpc-grid-pad'>
        <?php
        $shop_currency_symbol = get_woocommerce_currency_symbol();
        foreach ($products as $product) {
            $prod = wc_get_product($product->id);
            $url = get_permalink($product->id);
            $wpc_metas = get_post_meta($product->id, 'wpc-metas', true);
            $can_design_from_blank = WPD_Admin::get_proper_value($wpc_metas, 'can-design-from-blank', "");
            ?>
                <div class='wpc-col-1-<?php echo $cols; ?> cat-item-ctn'>
                    <div class='cat-item'>
                        <h3><?php echo $prod->post->post_title; ?> 
                            <span><?php echo $shop_currency_symbol . '' . $prod->price ?></span>
                        </h3>
            <?php echo get_the_post_thumbnail($product->id, 'medium'); ?>
                        <hr>
                        <?php
                        if (!empty($can_design_from_blank)) {
                            ?><a href="<?php echo WPD_Product::get_url($product->id) ?>" class='btn-choose'> <?php _e("Design from blank", "wpd"); ?></a><?php
                        }
                        ?>
                    </div>
                </div>
                        <?php
                    }
                    ?>
        </div>
            <?php
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

        function get_customize_btn() {
            $post_id = get_the_ID();
        $product = wc_get_product($post_id);
            $wpc_metas = get_post_meta($post_id, 'wpc-metas', true);
            if (isset($wpc_metas['is-customizable']) && !empty($wpc_metas['is-customizable'])) {
                if (isset($wpc_metas['can-design-from-blank'])) {
                    ?><input type="button" value="<?php _e("Design from blank", "wpd"); ?>" data-id="<?php echo $post_id; ?>" data-type="<?php echo $product->product_type; ?>" class="mg-top-10 wpc-customize-product"/><?php
            }
        }
    }

    function get_customize_btn_loop($html, $product) {
        $wpc_metas = get_post_meta($product->id, 'wpc-metas', true);
        if (isset($wpc_metas['is-customizable']) && !empty($wpc_metas['is-customizable'])) {
            if (isset($wpc_metas['can-design-from-blank']))
                $design_from_blank = $wpc_metas['can-design-from-blank'];
            if ($product->product_type == "simple" && $design_from_blank)
                $html.='<input type="button" value="' . __("Design from blank", "wpd") . '" data-id="' . $product->id . '" data-type="' . $product->product_type . '" class="mg-top-10 wpc-customize-product"/>';
        }
        return $html;
    }

    private function wpc_get_woo_version_number() {
        // If get_plugins() isn't available, require it
        if (!function_exists('get_plugins'))
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        // Create the plugins folder and file variables
        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';

        // If the plugin version number is set, return it 
        if (isset($plugin_folder[$plugin_file]['Version'])) {
            return $plugin_folder[$plugin_file]['Version'];
        } else {
            // Otherwise return null
            return NULL;
        }
    }

    function set_variable_action_filters() {
        GLOBAL $wpc_options_settings;
        $options = $wpc_options_settings['wpc-general-options'];
        $woo_version = $this->wpc_get_woo_version_number();
        if ($options['wpc-parts-position-cart'] == "name") {
            if ($woo_version < 2.1) {
                //Old WC versions
                add_filter("woocommerce_in_cart_product_title", array($this, "get_wpd_data"), 10, 3);
            } else {
                //New WC versions
                add_filter("woocommerce_cart_item_name", array($this, "get_wpd_data"), 10, 3);
            }
        } else {
            if ($woo_version < 2.1) {
                //Old WC versions
                add_filter("woocommerce_in_cart_product_thumbnail", array($this, "get_wpd_data"), 10, 3);
            } else {
                //New WC versions
                add_filter("woocommerce_cart_item_thumbnail", array($this, "get_wpd_data"), 10, 3);
            }
        }
        $append_content_filter = $options['wpc-content-filter'];

        if ($append_content_filter !== "0" && !is_admin()) {

            add_filter("the_content", array($this, "filter_content"), 99);
        }
    }

    function filter_content($content) {
        GLOBAL $wpc_options_settings;
        global $wp_query;
        $options = $wpc_options_settings['wpc-general-options'];
        $wpc_page_id = $options['wpc_page_id'];
        if (function_exists("icl_object_id"))
            $wpc_page_id = icl_object_id($wpc_page_id, 'page', false, ICL_LANGUAGE_CODE);
        $current_page_id = get_the_ID();
        if ($wpc_page_id == $current_page_id) {
            $item_id = $wp_query->query_vars["product_id"];
            //var_dump($item_id);
            $editor_obj = new WPD_Editor($item_id);
            $content.=$editor_obj->get_editor();
        }
        return $content;
    }

    function handle_picture_upload() {
        $nonce = $_POST['nonce'];
        if (!wp_verify_nonce($nonce, 'wpc-picture-upload-nonce')) {
            $busted = __("Cheating huh?", "wpd");
            die($busted);
        }

        $upload_dir = wp_upload_dir();
        $generation_path = $upload_dir["basedir"];
        $generation_url = $upload_dir["baseurl"];
        $file_name = uniqid();
        $options = get_option('wpc-upload-options');
        $valid_formats = $options['wpc-upl-extensions'];
        if (!$valid_formats)
            $valid_formats = array("jpg", "png", "gif", "bmp", "jpeg", "psd", "eps"); //wpc-upl-extensions
            
//    var_dump($valid_formats);
        $name = $_FILES['userfile']['name'];
        $size = $_FILES['userfile']['size'];

        if (isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST") {
            if (strlen($name)) {
                    $success = 0;
                    $message = "";
                    $img_url = "";
                $img_id = uniqid();
//                    list($txt, $ext) = explode(".", $name);
                $path_parts = pathinfo($name);
                $ext = $path_parts['extension'];
                if (in_array($ext, $valid_formats)) {
                    $tmp = $_FILES['userfile']['tmp_name'];
                    if (move_uploaded_file($tmp, $generation_path . "/" . $file_name . ".$ext")) {
                        $min_width = $options['wpc-min-upload-width'];
                        $min_height = $options['wpc-min-upload-height'];
                        $valid_formats_for_thumb = array("psd", "eps", "pdf");
                        if (in_array($ext, $valid_formats_for_thumb)) {
                            //                        $output_thumb=  uniqid().".png";
                            $thumb_generation_success = $this->generate_adobe_thumb($generation_path, $file_name . ".$ext", $file_name . ".png");
                            //If the thumb generation is a success, we force the extension to be png so the rest of the code can use it
                            if ($thumb_generation_success)
                                $ext = "png";
                        }
                        if ($min_width > 0 || $min_height > 0) {
                            list($width, $height, $type, $attr) = getimagesize($generation_path . "/" . $file_name . ".$ext");
                            if (($min_width > $width || $min_height > $height) && $ext != "svg") {
                                $success = 0;
                                $message = sprintf(__('Uploaded file dimensions: %1$spx x %2$spx, minimum required ', 'wpd'), $width, $height);
                                if ($min_width > 0 && $min_height > 0)
                                    $message.="dimensions: $min_height" . "px" . " x $min_height" . "px";
                                else if ($min_width > 0)
                                    $message.="width: $min_width" . "px";
                                else if ($min_height > 0)
                                    $message.="height: $min_height" . "px";
                            }
                            else {
                                $success = 1;
                                $message = "<span class='clipart-img'><img id='$img_id' src='$generation_url/$file_name.$ext'></span>";
                                $img_url = "$generation_url/$file_name.$ext";
                            }
                        } else {
                            $success = 1;
                            $message = "<span class='clipart-img'><img id='$img_id' src='$generation_url/$file_name.$ext'></span>";
                            $img_url = "$generation_url/$file_name.$ext";
                        }
                        if ($success == 0)
                            unlink($generation_path . "/" . $file_name . ".$ext");
                    }
                    else {
                        $success = 0;
                        $message = __('An error occured during the upload. Please try again later', 'wpd');
                    }
                } else {
                    $success = 0;
                    $message = __('Incorrect file extension: ' . $ext . '. Allowed extensions: ', 'wpd') . implode(", ", $valid_formats);
                }
                echo json_encode(
                        array(
                            "success" => $success,
                            "message" => $message,
                            "img_url" => $img_url
                        )
                );
            }
        }
        die();
    }

    private function generate_adobe_thumb($working_dir, $input_filename, $output_filename) {
        $pos = strrpos($input_filename, ".");
        $input_extension = substr($input_filename, $pos + 1);
        $input_path = $working_dir . "/$input_filename";
        $output_extension = "png";
        $image = new Imagick($input_path);
        $image->setResolution(300, 300);
        $image->setImageFormat($output_extension);
        if ($input_extension == "psd") {
            $image->setIteratorIndex(0);
        }
        $success = $image->writeImage($working_dir . "/$output_filename");
        return $success;
    }

    function get_wpd_data($thumbnail_code, $values, $cart_item_key) {
        $variation_id = $values["variation_id"];

        if (isset($_SESSION["wpc_design_pricing_options"][$cart_item_key]) && !empty($_SESSION["wpc_design_pricing_options"][$cart_item_key])) {
            $wpc_design_pricing_options_data = WPD_Design::get_design_pricing_options_data($_SESSION["wpc_design_pricing_options"][$cart_item_key]);
            $thumbnail_code.= "<br>" . $wpc_design_pricing_options_data;
        }

        if (isset($_SESSION["wpc_generated_data"][$variation_id][$cart_item_key]) && isset($_SESSION["wpc_generated_data"][$variation_id][$cart_item_key]["output"])) {
            $thumbnail_code.="<br>";
            $customization_list = $_SESSION["wpc_generated_data"][$variation_id][$cart_item_key];
            $upload_dir = wp_upload_dir();
            $modals = "";
            //        var_dump($customization_list["output"]);
            $i = 0;
            foreach ($customization_list["output"]["files"] as $customisation_key => $customization) {
                $tmp_dir = $customization_list["output"]["working_dir"];
                $generation_url = $upload_dir["baseurl"] . "/WPC/$tmp_dir/$customisation_key/";
                if (isset($customization["preview"]))
                    $image = $generation_url . $customization["preview"];
                else
                    $image = $generation_url . $customization["image"];
                $original_part_img_url = $customization_list[$customisation_key]["original_part_img"];
                //$modal_id=$variation_id."_".$cart_item_key."$customisation_key-". uniqid();//Creates issue on checkout page
                $modal_id = $variation_id . "_" . $cart_item_key . "$customisation_key-$i";

                $thumbnail_code.='<span><a class="button" data-toggle="modal" data-target="#' . $modal_id . '">' . ucfirst($customisation_key) . '</a></span>';
                $modals.='<div class="modal fade wpc-modal wpc_part" id="' . $modal_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                      <div class="modal-content">
                                        <div class="modal-header">
                                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                          <h4 class="modal-title">Preview</h4>
                                        </div>
                                        <div class="modal-body txt-center">
                                            <div style="background-image:url(' . $original_part_img_url . ')"><img src="' . $image . '"></div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>';
                $i++;
            }
            array_push(wpd_retarded_actions::$code, $modals);
            add_action('wp_footer', array('wpd_retarded_actions', 'display_code'), 10, 1);

            $edit_item_url = '';
            if (get_option('permalink_structure')) {
                $edit_item_url .= WPD_Product::get_url(false) . "edit/$variation_id/$cart_item_key/";
            } else {
                $edit_item_url = WPD_Product::get_url($variation_id) . '&edit=' . $cart_item_key;
            }
            $thumbnail_code.='<a class="button alt" href="' . $edit_item_url . '">Edit</a>';
        } else if (isset($_SESSION["wpc-uploaded-designs"][$cart_item_key])) {
            $thumbnail_code.="<br>";
            foreach ($_SESSION["wpc-uploaded-designs"][$cart_item_key] as $custom_design) {
                $thumbnail_code.='<span class="wpd-custom-design"><a class="button" href=' . $custom_design . '>' . __("Custom design", "wpd") . '</a></span>';
            }
        }
        return $thumbnail_code;
    }

    public function wpd_add_query_vars($aVars) {
        $aVars[] = "product_id";
        $aVars[] = "tpl";
        $aVars[] = "edit";
        $aVars[] = "design_index";
        $aVars[] = "oid";
        return $aVars;
    }

    public function wpd_add_rewrite_rules($param) {
        GLOBAL $wpc_options_settings;
        GLOBAL $wp_rewrite;
        $options = $wpc_options_settings['wpc-general-options'];
        $wpc_page_id = $options['wpc_page_id'];
        if (function_exists("icl_object_id"))
            $wpc_page_id = icl_object_id($wpc_page_id, 'page', false, ICL_LANGUAGE_CODE);
        $wpc_page = get_post($wpc_page_id);
        if (is_object($wpc_page)) {
            //$slug = $wpc_page->post_name;
            $slug = get_permalink($wpc_page->ID);
            $home_url = home_url('/');
            $slug = str_replace($home_url, '', $slug);
            add_rewrite_rule(
                    // The regex to match the incoming URL
                    $slug . 'design' . '/([^/]+)/?$',
                    // The resulting internal URL: `index.php` because we still use WordPress
                    // `pagename` because we use this WordPress page
                    // `designer_slug` because we assign the first captured regex part to this variable
                    'index.php?pagename=' . $slug . '&product_id=$matches[1]',
                    // This is a rather specific URL, so we add it to the top of the list
                    // Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
                    'top'
            );
            add_rewrite_rule(
                    // The regex to match the incoming URL
                    $slug . 'design' . '/([^/]+)/([^/]+)/?$',
                    // The resulting internal URL: `index.php` because we still use WordPress
                    // `pagename` because we use this WordPress page
                    // `designer_slug` because we assign the first captured regex part to this variable
                    'index.php?pagename=' . $slug . '&product_id=$matches[1]&tpl=$matches[2]',
                    // This is a rather specific URL, so we add it to the top of the list
                    // Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
                    'top'
            );
            add_rewrite_rule(
                    // The regex to match the incoming URL
                    $slug . 'edit' . '/([^/]+)/([^/]+)/?$',
                    // The resulting internal URL: `index.php` because we still use WordPress
                    // `pagename` because we use this WordPress page
                    // `designer_slug` because we assign the first captured regex part to this variable
                    'index.php?pagename=' . $slug . '&product_id=$matches[1]&edit=$matches[2]',
                    // This is a rather specific URL, so we add it to the top of the list
                    // Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
                    'top'
            );
            add_rewrite_rule(
                    // The regex to match the incoming URL
                    $slug . 'ordered-design' . '/([^/]+)/([^/]+)/?$',
                    // The resulting internal URL: `index.php` because we still use WordPress
                    // `pagename` because we use this WordPress page
                    // `designer_slug` because we assign the first captured regex part to this variable
                    'index.php?pagename=' . $slug . '&product_id=$matches[1]&oid=$matches[2]',
                    // This is a rather specific URL, so we add it to the top of the list
                    // Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
                    'top'
            );

            add_rewrite_rule(
                    // The regex to match the incoming URL
                    $slug . 'saved-design' . '/([^/]+)/([^/]+)/?$',
                    // The resulting internal URL: `index.php` because we still use WordPress
                    // `pagename` because we use this WordPress page
                    // `designer_slug` because we assign the first captured regex part to this variable
                    'index.php?pagename=' . $slug . '&product_id=$matches[1]&design_index=$matches[2]',
                    // This is a rather specific URL, so we add it to the top of the list
                    // Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
                    'top'
            );

            $wp_rewrite->flush_rules();
    }
    }

}
