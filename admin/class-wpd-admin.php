<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpd
 * @subpackage Wpd/admin
 * @author     ORION <support@orionorigin.com>
 */
class WPD_Admin {

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
     * @param      string    $wpd       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($wpd, $version) {

        $this->wpd = $wpd;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
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
        wp_enqueue_style($this->wpd, plugin_dir_url(__FILE__) . 'css/wpd-admin.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-simplegrid", plugin_dir_url(__FILE__) . 'css/simplegrid.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-tooltip-css", plugin_dir_url(__FILE__) . 'css/tooltip.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-colorpicker-css", plugin_dir_url(__FILE__) . 'js/colorpicker/css/colorpicker.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-bs-modal-css", WPD_URL . 'public/js/modal/modal.min.css', array(), $this->version, 'all');


//                if(is_admin()&&get_post_type()=="wpc-template")
//                {
//                    WPD_editor::register_fonts();
//                }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    3.0
     */
    public function enqueue_scripts() {

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
        wp_enqueue_script('wpd-tabs-js', plugin_dir_url(__FILE__) . 'js/SpryTabbedPanels.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('wpd-tooltip-js', plugin_dir_url(__FILE__) . 'js/tooltip.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('wpd-colorpicker-js', plugin_dir_url(__FILE__) . 'js/colorpicker/js/colorpicker.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('wpd-modal-js', WPD_URL . 'public/js/modal/modal.min.js', array('jquery'), false, false);
        wp_enqueue_script($this->wpd, plugin_dir_url(__FILE__) . 'js/wpd-admin.min.js', array('jquery'), $this->version, false);
        wp_localize_script($this->wpd, 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
        wp_enqueue_script('wpd-jquery-cookie-js', plugin_dir_url(__FILE__) . 'js/jquery.cookie.min.js', array('jquery'), $this->version, false);
    }

    /**
     * Builds all the plugin menu and submenu
     */
    public function add_woo_parts_submenu() {
        $icon = WPD_URL . 'admin/images/wpc-dashicon.png';
        add_menu_page('Woocommerce Product Designer', 'WPD', 'manage_product_terms', 'wpc-manage-dashboard', array($this, 'get_parts_page'), $icon);
        add_submenu_page('wpc-manage-dashboard', __('Parts', 'wpd'), __('Parts', 'wpd'), 'manage_product_terms', 'wpc-manage-parts', array($this, 'get_parts_page'));
        add_submenu_page('wpc-manage-dashboard', __('Fonts', 'wpd'), __('Fonts', 'wpd'), 'manage_product_terms', 'wpc-manage-fonts', array($this, 'get_fonts_page'));
        add_submenu_page('wpc-manage-dashboard', __('Cliparts', 'wpd'), __('Cliparts', 'wpd'), 'manage_product_terms', 'edit.php?post_type=wpc-cliparts', false);
        add_submenu_page('wpc-manage-dashboard', __('Settings', 'wpd'), __('Settings', 'wpd'), 'manage_product_terms', 'wpc-manage-settings', array($this, 'get_settings_page'));
        add_submenu_page('wpc-manage-dashboard', __('Get Started', 'wpd'), __('Get Started', 'wpd'), 'manage_product_terms', 'wpc-about', array($this, "get_about_page"));
        add_submenu_page( 'wpc-manage-dashboard', __('Advanced features', 'wpd' ), __( 'Advanced features', 'wpd' ), 'manage_product_terms', 'wpd-advanced-features', array($this, "get_wpd_advanced_features_page"));
    }

    /**
     * Builds the parts management page
     */
    function get_parts_page() {
        include_once( WPD_DIR . '/includes/wpd-add-parts.php' );
        woocommerce_add_parts();
    }

    /**
     * Builds the fonts management page
     */
    function get_fonts_page() {
        include_once( WPD_DIR . '/includes/wpd-add-fonts.php' );
        woocommerce_add_fonts();
    }

    /**
     * Initialize the plugin sessions
     */
    function init_sessions() {
        if (!session_id()) {
            session_start();
        }

        if (!isset($_SESSION["wpc_generated_data"]))
            $_SESSION["wpc_generated_data"] = array();
    }

    /**
     * Redirects the plugin to the about page after the activation
     */
    function wpc_redirect() {
        if (get_option('wpc_do_activation_redirect', false)) {
            delete_option('wpc_do_activation_redirect');
            wp_redirect(admin_url('admin.php?page=wpc-about'));
        }
    }

    /**
     * Builds the about page
     */
    function get_about_page() {
        $wpc_logo = WPD_URL . 'admin/images/wpc.jpg';
        $img1 = WPD_URL . 'admin/images/install-demo-package.jpg';
        $img2 = WPD_URL . 'admin/images/set-basic-settings.jpg';
        $img3 = WPD_URL . 'admin/images/create-customizable-product.jpg';
        $img4 = WPD_URL . 'admin/images/manage-templates.jpg';
        ?>
        <div id='wpd-about-page'>
            <div class="about-heading">
                <div>
                    <H2><?php echo __("Welcome to WooCommerce Products Designer", "wpd") . " " . WPD_VERSION; ?></H2>
                    <H4><?php printf(__("Thanks for installing! WooCommerce Products Designer %s is more powerful, stable and secure than ever before. We hope you enjoy using it.", "wpd"), WPD_VERSION); ?></H4>
                </div>
                <div class="about-logo">
                    <img src="<?php echo $wpc_logo; ?>" />
                </div>
            </div>
            <div class="about-button">
                <div><a href="<?php echo admin_url('admin.php?page=wpc-manage-settings'); ?>" class="button">Settings</a></div>
                <div><a href="<?php echo WPD_URL . 'User_manual.pdf'; ?>" class="button">Docs</a></div>
            </div>

            <div id="TabbedPanels1" class="TabbedPanels">
                <ul class="TabbedPanelsTabGroup ">
                    <li class="TabbedPanelsTab " tabindex="4"><span><?php _e('Getting Started', 'wpd'); ?></span> </li>
                    <li class="TabbedPanelsTab" tabindex="6"><span><?php _e('Follow Us', 'wpd'); ?></span></li>
                </ul>

                <div class="TabbedPanelsContentGroup">
                    <div class="TabbedPanelsContent">
                        <div class='wpc-grid wpc-grid-pad'>
                            <div class="wpc-col-4-12">
                                <div class="product-container">
                                    <a href="https://www.youtube.com/watch?v=AlSMCIoOLRA" target="blank">
                                        <div class="img-container"><img src="<?php echo $img1; ?>"></div>
                                        <div class="img-title"><?php _e('How to install the demo package?', 'wpd'); ?></div>
                                    </a>
                                </div>
                            </div>
                            <div class="wpc-col-4-12">
                                <div class="product-container">
                                    <a href="https://www.youtube.com/watch?v=NTvIvhJHueU" target="blank">
                                        <div class="img-container"><img src="<?php echo $img2; ?>"></div>
                                        <div class="img-title"><?php _e('How to set the basic settings?', 'wpd'); ?></div>
                                    </a>
                                </div>
                            </div>
                            <div class="wpc-col-4-12">
                                <div class="product-container">
                                    <a href="https://www.youtube.com/watch?v=FDnM7hjepqo" target="blank">
                                        <div class="img-container"><img src="<?php echo $img3; ?>"></div>
                                        <div class="img-title"><?php _e('How to create a customizable product?', 'wpd'); ?></div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                    
                    <div class="TabbedPanelsContent">
                        <div class="wpc-grid wpc-grid-pad follow-us">
                            <div class="wpc-col-6-12 ">
                                <h3>Why?</h3>
                                <ul class="follow-us-list">
                                    <li>
                                        <a href="#">
                                            <span class="rs-ico"><img src="<?php echo WPD_URL;?>/admin/images/love.png"></span>
                                            <span>
                                                <h4 class="title"> Show us some love of course!</h4>
                                                You like our product and you tried it. Cool! Then give us some boost by sharing it with friends or making interesting comments on our pages!
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <span class="rs-ico"><img src="<?php echo WPD_URL;?>/admin/images/update.png"></span>
                                            <span>
                                                <h4 class="title"> Receive regular updates from us on our products.</h4>
                                                This is the best way to enjoy the full of the news features added to our plugins. 
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <span class="rs-ico"><img src="<?php echo WPD_URL;?>/admin/images/features.png"></span>
                                            <span>
                                                <h4 class="title"> Suggest new features for the products you're interested in.</h4>
                                                One of our products arouses your interest but it’s not exactly what you want. If only some features can be added… You know what? Actually it’s possible! Just leave your suggestion and we’ll do our best! 
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <span class="rs-ico"><img src="<?php echo WPD_URL;?>/admin/images/bug.png"></span>
                                            <span>
                                                <h4 class="title"> Become a beta tester for our pre releases.</h4>
                                                For each couple of feature up-coming we need beta tester to improve the final product we are about to propose. So if you want to be part of this, freely apply here.
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <span class="rs-ico"><img src="<?php echo WPD_URL;?>/admin/images/free.png"></span>
                                            <span>
                                                <h4 class="title"> Access our freebies collection anytime.</h4>
                                                Find the coolest free collection of our plugins and make the most of it!
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div> 
                            <div id="separator"></div>
                            <div class="wpc-col-6-12 ">
                                <h3>How?</h3>
                                <div class="follow-us-text">
                                    <div>
                                        Easy!! Just access our social networks pages and follow/like us. Yeah just like that :).
                                    </div>

                                    <div class="btn-container">
                                        <a href="http://twitter.com/OrionOrigin" target="blank" style="display: inline-block;">
                                            <span class="rs-ico"><img src="<?php echo WPD_URL;?>/admin/images/twitter.png"></span>
                                        </a>
                                        <a href="https://www.facebook.com/OrionOrigin" target="blank" style="display: inline-block;">
                                            <span class="rs-ico"><img src="<?php echo WPD_URL;?>/admin/images/facebook-about.png"></span>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div> 
                    </div>

                </div>
            </div>
        </div>
        <?php
    }
    
    function get_wpd_advanced_features_page()
    {
        ?>
        <div id="wpc-advanced-features">
            <div class="wrap">
                <h2><a href="http://codecanyon.net/item/woocommerce-products-designer/7818029" target="blank">Need more features? Let's go pro!</a></h2>
                <div>
                    <a href="http://codecanyon.net/item/woocommerce-products-designer/7818029" target="blank">
                    <img src="<?php echo WPD_URL."admin/images/go-pro.png"?>">
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get a value by key in an array if defined
     * @param array $values Array to search into
     * @param string $search_key Searched key
     * @param mixed $default_value Value if the key does not exist in the array
     * @return mixed
     */
    public static function get_proper_value($values, $search_key, $default_value = "") {
        if (isset($values[$search_key]))
            $default_value = $values[$search_key];
        return $default_value;
    }

    /**
     * Gets the settings and put them in a global variable
     * @global array $wpc_options_settings Settings
     */
    function init_globals() {
        GLOBAL $wpc_options_settings;
        $wpc_options_settings['wpc-texts-options'] = get_option("wpc-texts-options");
        $wpc_options_settings['wpc-shapes-options'] = get_option("wpc-shapes-options");
        $wpc_options_settings['wpc-images-options'] = get_option("wpc-images-options");
        $wpc_options_settings['wpc-designs-options'] = get_option("wpc-designs-options");
        $wpc_options_settings['wpc-general-options'] = get_option("wpc-general-options");
        $wpc_options_settings['wpc-colors-options'] = get_option("wpc-colors-options");
        $wpc_options_settings['wpc-output-options'] = get_option("wpc-output-options");
        $wpc_options_settings['wpc_social_networks'] = get_option("wpc_social_networks");
        $wpc_options_settings['wpc-upload-options'] = get_option("wpc-upload-options");
        $wpc_options_settings['wpc-licence'] = get_option("wpc-licence");
    }

    /**
     * Builds the icon upload field form for the settings page
     * @global array $wpc_options_settings
     * @param type $option_title
     * @param type $option_name
     * @param type $option_group
     * @return type
     */
    private function get_upload_icon_field($option_title, $option_name, $option_group) {
        GLOBAL $wpc_options_settings;
        $options = $wpc_options_settings[$option_group];
        if (isset($options[$option_name]) && !empty($options[$option_name])) {
            $image_id = $options[$option_name];
            $attachment_url = wp_get_attachment_url($image_id, "full"); //$this->get_image_url($image_id);
        } else {
            $attachment_url = "";
            $image_id = "";
        }
        $upload_icon = "
                    <tr valign='top'>
                        <th scope='row' class='titledesc'>" . $option_title . "</th>
                        <td class='forminp wpc_images_data' id='" . $option_name . "_icon'>
                            <div><input type='hidden' value='" . $image_id . "'  name='" . $option_group . "[$option_name]'><img src='" . $attachment_url . "'><a href='#' class='add_icon button'  data-tab='$option_group' data-name='$option_name' title='Add Icon'>Add Icon</a></div>
                            <div class='remove_button'><a href='#' class='button wpc-remove-icon' data-tab='" . $option_name . "'>" . __("Remove", "wpd") . "</a></span></div>
                        </td>
                    </tr>";
        return array("type" => "wpc-icon-select", "value" => $upload_icon);
    }

    /**
     * Returns a media URL
     * @param type $image_id Media ID
     * @return type
     */
//        private function get_image_url($image_id){
//            $attachment=wp_get_attachment_image_src($image_id,"full"); 
//            $attachment_url=$attachment[0]; 
//            return $attachment_url;
//        }        

    /**
     * Callbacks which prints the icon selector field
     * @param type $field Field to print
     */
    public function get_icon_selector_field($field) {
        echo $field["value"];
    }

    /**
     * Builds the general settings options
     * @return array Settings
     */
    private function get_general_settings() {
        $options = array();

        $customizer_page = array(
            'title' => __('Design Page', 'wpd'),
            'desc' => __('This setting allows the plugin to locate the page where customizations are made. Please note that this page can only be accessed by our plugin and should not appear in any menu.', 'wpd'),
            'id' => 'wpc-general-options[wpc_page_id]',
            'type' => 'single_select_page',
            'default' => '',
            'class' => 'chosen_select_nostd',
            'css' => 'min-width:300px;',
            'desc_tip' => true,
        );

        $content_filter = array(
            'title' => __('Automatically append canvas to the customizer page', 'wpd'),
            'id' => 'wpc-general-options[wpc-content-filter]',
            'default' => '1',
            'type' => 'radio',
            'desc_tip' => __('This option allows you to define whether or not you want to use a shortcode to display the the customizer in the selected page.', 'wpd'),
            'options' => array(
                '0' => __('No', 'wpd'),
                '1' => __('Yes', 'wpd')
            )
        );
        $customizer_w = array(
            'title' => __('Canvas max width (px)', 'wpd'),
            //		'desc' 		=> __( 'This setting is mandatory so the user can use instagram connect', 'wpd' ),
            'id' => 'wpc-general-options[canvas-w]',
            'type' => 'text',
            'default' => '800',
            'class' => '',
            'css' => 'min-width:300px;',
            'desc_tip' => true,
        );
        $customizer_h = array(
            'title' => __('Canvas max height (px)', 'wpd'),
            //		'desc' 		=> __( 'This setting is mandatory so the user can use instagram connect', 'wpd' ),
            'id' => 'wpc-general-options[canvas-h]',
            'type' => 'text',
            'default' => '500',
            'class' => '',
            'css' => 'min-width:300px;',
            'desc_tip' => true,
        );

        $customizer_cart_display = array(
            'title' => __('Parts position in cart', 'wpd'),
            'id' => 'wpc-general-options[wpc-parts-position-cart]',
            'default' => 'thumbnail',
            'type' => 'radio',
            'desc_tip' => __('This option allows you to set where to show your customized products parts on the cart page', 'wpd'),
            'options' => array(
                'thumbnail' => __('Thumbnail column', 'wpd'),
                'name' => __('Name column', 'wpd')
            )
        );
        $download_button = array(
            'title' => __('Download design', 'wpd'),
            'id' => 'wpc-general-options[wpc-download-btn]',
            'default' => '1',
            'type' => 'radio',
            'desc_tip' => __('This option allows you to show/hide the download button on the customization page', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            )
        );
        $user_account_download_button = array(
            'title' => __('Download design from user account page', 'wpd'),
            'id' => 'wpc-general-options[wpc-user-account-download-btn]',
            'default' => '1',
            'type' => 'radio',
            'desc_tip' => __('This option allows you to show/hide the download button on user account page', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            )
        );
        $send_attachments = array(
            'title' => __('Send the design as an attachment', 'wpd'),
            'id' => 'wpc-general-options[wpc-send-design-mail]',
            'default' => '1',
            'type' => 'radio',
            'desc_tip' => __('This option allows you to send or not the design by mail after checkout', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            )
        );

        $preview_button = array(
            'title' => __('Preview design', 'wpd'),
            'id' => 'wpc-general-options[wpc-preview-btn]',
            'default' => '1',
            'type' => 'radio',
            'desc_tip' => __('This option allows you to show/hide the preview button on the customization page', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            )
        );

        $cart_button = array(
            'title' => __('Add to cart', 'wpd'),
            'id' => 'wpc-general-options[wpc-cart-btn]',
            'default' => '1',
            'type' => 'radio',
            'desc_tip' => __('This option allows you to show/hide the cart button on the customization page', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            )
        );
        $add_to_cart_action = array(
            'title' => __('Redirect after adding a custom design to the cart?', 'wpd'),
            'id' => 'wpc-general-options[wpc-redirect-after-cart]',
            'default' => '0',
            'type' => 'radio',
            'desc_tip' => __('This option allows you to define what to do after adding a design to the cart', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            )
        );

        $general_options_begin = array('type' => 'title',
            'id' => 'wpc_page_options',
            'title' => __('General Settings')
        );

        $general_options_end = array('type' => 'sectionend',
            'id' => 'wpc_page_options'
        );

        $icons_options_begin = array('type' => 'title',
            'id' => 'wpc_icons_options',
            'title' => 'Toolbar icons'
        );
        $icons_options_end = array('type' => 'sectionend',
            'id' => 'wpc_icons_options'
        );
        $conflicts_options_begin = array('type' => 'title',
            'id' => 'wpc_conflicts_options',
            'title' => 'Scripts management'
        );
        $load_bs_modal = array(
            'title' => __('Load bootsrap modal', 'wpd'),
            'id' => 'wpc-general-options[wpc-load-bs-modal]',
            'default' => '1',
            'type' => 'radio',
            'desc_tip' => __('This option allows you to enable/disable twitter\'s bootstrap modal script', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            )
        );
        $conflicts_options_end = array('type' => 'sectionend',
            'id' => 'wpc_conflicts_options'
        );
        array_push($options, $general_options_begin);
        array_push($options, $customizer_page);
        array_push($options, $content_filter);
        array_push($options, $customizer_w);
        array_push($options, $customizer_h);
        array_push($options, $customizer_cart_display);
        array_push($options, $preview_button);
        array_push($options, $download_button);
        array_push($options, $user_account_download_button);
        array_push($options, $send_attachments);
        array_push($options, $cart_button);
        array_push($options, $add_to_cart_action);
        array_push($options, $general_options_end);
        array_push($options, $conflicts_options_begin);
        array_push($options, $load_bs_modal);
        array_push($options, $conflicts_options_end);

        return $options;
    }

    /**
     * Builds the uploads settings options
     * @return array Settings
     * @return array
     */
    private function get_uploads_settings() {
        GLOBAL $wpc_options_settings;
        $upload_options = $wpc_options_settings['wpc-upload-options'];
        $options = array();

        $default_tab_color = $this->get_proper_value($upload_options, 'tab-color', "");
        $default_selected_color = $this->get_proper_value($upload_options, 'selected-color', "");

        $uploader_type = array(
            'title' => __('File upload script', 'wpd'),
            'id' => 'wpc-upload-options[wpc-uploader]',
            'default' => 'custom',
            'type' => 'radio',
            'desc_tip' => __('This option allows you to set which file upload script you would like to use', 'wpd'),
            'options' => array(
                'custom' => __('Custom with graphical enhancements', 'wpd'),
                'native' => __('Normal', 'wpd')
            )
        );
        $upl_extensions = array(
            'title' => __('Allowed uploads extensions', 'wpd'),
            'id' => 'wpc-upload-options[wpc-upl-extensions]',
            'default' => array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'),
            'type' => 'multiselect',
            'desc_tip' => __('Allowed extensions for uploads', 'wpd'),
            'options' => array(
                'jpg' => __('jpg', 'wpd'),
                'jpeg' => __('jpeg', 'wpd'),
                'png' => __('png', 'wpd'),
                'gif' => __('gif', 'wpd'),
                'bmp' => __('bmp', 'wpd'),
                'svg' => __('svg', 'wpd'),
                'psd' => __('psd', 'wpd'),
                'eps' => __('eps', 'wpd'),
            )
        );

        $custom_designs_extensions = array(
            'title' => __('Custom designs allowed extensions (separated by commas)', 'wpd'),
            'desc' => __('Allowed extensions for custom designs. If not set, all extensions will be accepted.', 'wpd'),
            'id' => 'wpc-upload-options[wpc-custom-designs-extensions]',
            'type' => 'text',
            'default' => '',
            'class' => 'chosen_select_nostd',
            'css' => 'min-width:300px;',
            'desc_tip' => true,
        );

        $uploads_tab_color = array(
            'title' => __('Normal color', 'wpd'),
            'id' => 'wpc-upload-options[tab-color]',
            'class' => 'wpc-color',
            'default' => $default_tab_color,
            'css' => "background-color:" . $default_tab_color,
            'type' => 'text',
        );
        $uploads_tab_selected_color = array(
            'title' => __('Active color', 'wpd'),
            'id' => 'wpc-upload-options[selected-color]',
            'class' => 'wpc-color',
            'default' => $default_selected_color,
            'css' => "background-color:" . $default_selected_color,
            'type' => 'text',
        );
        $uploads_tab_visible = array(
            'title' => __('Active controls', 'wpd'),
            'desc' => __('Show this tab', 'wpd'),
            'id' => 'wpc-upload-options[visible-tab]',
            'default' => 'yes',
            'type' => 'checkbox'
        );
        $uploads_tab_options = array(
            'desc' => __('Grayscale', 'wpd'),
            'id' => 'wpc-upload-options[grayscale]',
            'default' => 'yes',
            'type' => 'checkbox'
        );
        $uploads_all_options = array(
            array(
                'desc' => __('Grayscale', 'wpd'),
                'id' => 'wpc-upload-options[grayscale]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => 'start'
            ),
            array(
                'desc' => __('Invert', 'wpd'),
                'id' => 'wpc-upload-options[invert]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Sepia1', 'wpd'),
                'id' => 'wpc-upload-options[sepia1]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Sepia2', 'wpd'),
                'id' => 'wpc-upload-options[sepia2]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Blur', 'wpd'),
                'id' => 'wpc-upload-options[blur]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Sharpen', 'wpd'),
                'id' => 'wpc-upload-options[sharpen]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Opacity', 'wpd'),
                'id' => 'wpc-upload-options[opacity]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Emboss', 'wpd'),
                'id' => 'wpc-upload-options[emboss]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
        );
        $upload_settings_begin = array('type' => 'title',
            'id' => 'wpc_uploads',
            'title' => __('Uploads Settings')
        );
        $upload_settings_end = array(
            'type' => 'sectionend',
            'id' => 'wpc_uploads'
        );
        array_push($options, $upload_settings_begin);
        array_push($options, $uploads_tab_visible);
        $options = array_merge($options, $uploads_all_options);
        array_push($options, $uploader_type);
        array_push($options, $upl_extensions);
        array_push($options, $custom_designs_extensions);
        array_push($options, $upload_settings_end);
        return $options;
    }

    /**
     * Builds the output settings options
     * @return array Settings
     */
    private function get_output_settings() {
        $options = array();
        $output_formats = array(
            array(
                'title' => __('Generated files', 'wpd'),
                'desc' => __('PDF', 'wpd'),
                'id' => 'wpc-output-options[wpc-generate-pdf]',
                //				'default'	=> 'yes',
                'type' => 'checkbox',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Zip output folder', 'wpd'),
                'id' => 'wpc-output-options[wpc-generate-zip]',
                //				'default'	=> 'yes',
                'type' => 'checkbox',
                'checkboxgroup' => 'end'
            ),
        );
        
        $pdf_format = array(
            'title' => __('PDF Format', 'wpd'),
            'id' => 'wpc-output-options[pdf-format]',
            'type' => 'groupedselect',
            'options'=>array('ISO 216 A Series + 2 SIS 014711 extensions'=>array('A0'=>'A0 (841x1189 mm ; 33.11x46.81 in)','A1'=>'A1 (594x841 mm ; 23.39x33.11 in)','A2'=>'A2 (420x594 mm ; 16.54x23.39 in)','A3'=>'A3 (297x420 mm ; 11.69x16.54 in)','A4'=>'A4 (210x297 mm ; 8.27x11.69 in)','A5'=>'A5 (148x210 mm ; 5.83x8.27 in)','A6'=>'A6 (105x148 mm ; 4.13x5.83 in)','A7'=>'A7 (74x105 mm ; 2.91x4.13 in)','A8'=>'A8 (52x74 mm ; 2.05x2.91 in)','A9'=>'A9 (37x52 mm ; 1.46x2.05 in)','A10'=>'A10 (26x37 mm ; 1.02x1.46 in)','A11'=>'A11 (18x26 mm ; 0.71x1.02 in)','A12'=>'A12 (13x18 mm ; 0.51x0.71 in)',),'ISO 216 B Series + 2 SIS 014711 extensions'=>array('B0'=>'B0 (1000x1414 mm ; 39.37x55.67 in)','B1'=>'B1 (707x1000 mm ; 27.83x39.37 in)','B2'=>'B2 (500x707 mm ; 19.69x27.83 in)','B3'=>'B3 (353x500 mm ; 13.90x19.69 in)','B4'=>'B4 (250x353 mm ; 9.84x13.90 in)','B5'=>'B5 (176x250 mm ; 6.93x9.84 in)','B6'=>'B6 (125x176 mm ; 4.92x6.93 in)','B7'=>'B7 (88x125 mm ; 3.46x4.92 in)','B8'=>'B8 (62x88 mm ; 2.44x3.46 in)','B9'=>'B9 (44x62 mm ; 1.73x2.44 in)','B10'=>'B10 (31x44 mm ; 1.22x1.73 in)','B11'=>'B11 (22x31 mm ; 0.87x1.22 in)','B12'=>'B12 (15x22 mm ; 0.59x0.87 in)',),'ISO 216 C Series + 2 SIS 014711 extensions + 2 EXTENSION'=>array('C0'=>'C0 (917x1297 mm ; 36.10x51.06 in)','C1'=>'C1 (648x917 mm ; 25.51x36.10 in)','C2'=>'C2 (458x648 mm ; 18.03x25.51 in)','C3'=>'C3 (324x458 mm ; 12.76x18.03 in)','C4'=>'C4 (229x324 mm ; 9.02x12.76 in)','C5'=>'C5 (162x229 mm ; 6.38x9.02 in)','C6'=>'C6 (114x162 mm ; 4.49x6.38 in)','C7'=>'C7 (81x114 mm ; 3.19x4.49 in)','C8'=>'C8 (57x81 mm ; 2.24x3.19 in)','C9'=>'C9 (40x57 mm ; 1.57x2.24 in)','C10'=>'C10 (28x40 mm ; 1.10x1.57 in)','C11'=>'C11 (20x28 mm ; 0.79x1.10 in)','C12'=>'C12 (14x20 mm ; 0.55x0.79 in)','C76'=>'C76 (81x162 mm ; 3.19x6.38 in)','DL'=>'DL (110x220 mm ; 4.33x8.66 in)',),'SIS 014711 E Series'=>array('E0'=>'E0 (879x1241 mm ; 34.61x48.86 in)','E1'=>'E1 (620x879 mm ; 24.41x34.61 in)','E2'=>'E2 (440x620 mm ; 17.32x24.41 in)','E3'=>'E3 (310x440 mm ; 12.20x17.32 in)','E4'=>'E4 (220x310 mm ; 8.66x12.20 in)','E5'=>'E5 (155x220 mm ; 6.10x8.66 in)','E6'=>'E6 (110x155 mm ; 4.33x6.10 in)','E7'=>'E7 (78x110 mm ; 3.07x4.33 in)','E8'=>'E8 (55x78 mm ; 2.17x3.07 in)','E9'=>'E9 (39x55 mm ; 1.54x2.17 in)','E10'=>'E10 (27x39 mm ; 1.06x1.54 in)','E11'=>'E11 (19x27 mm ; 0.75x1.06 in)','E12'=>'E12 (13x19 mm ; 0.51x0.75 in)',),'SIS 014711 G Series'=>array('G0'=>'G0 (958x1354 mm ; 37.72x53.31 in)','G1'=>'G1 (677x958 mm ; 26.65x37.72 in)','G2'=>'G2 (479x677 mm ; 18.86x26.65 in)','G3'=>'G3 (338x479 mm ; 13.31x18.86 in)','G4'=>'G4 (239x338 mm ; 9.41x13.31 in)','G5'=>'G5 (169x239 mm ; 6.65x9.41 in)','G6'=>'G6 (119x169 mm ; 4.69x6.65 in)','G7'=>'G7 (84x119 mm ; 3.31x4.69 in)','G8'=>'G8 (59x84 mm ; 2.32x3.31 in)','G9'=>'G9 (42x59 mm ; 1.65x2.32 in)','G10'=>'G10 (29x42 mm ; 1.14x1.65 in)','G11'=>'G11 (21x29 mm ; 0.83x1.14 in)','G12'=>'G12 (14x21 mm ; 0.55x0.83 in)',),'ISO Press'=>array('RA0'=>'RA0 (860x1220 mm ; 33.86x48.03 in)','RA1'=>'RA1 (610x860 mm ; 23.02x33.86 in)','RA2'=>'RA2 (430x610 mm ; 16.93x23.02 in)','RA3'=>'RA3 (305x430 mm ; 12.01x16.93 in)','RA4'=>'RA4 (215x305 mm ; 8.46x12.01 in)','SRA0'=>'SRA0 (900x1280 mm ; 35.43x50.39 in)','SRA1'=>'SRA1 (640x900 mm ; 25.20x35.43 in)','SRA2'=>'SRA2 (450x640 mm ; 17.72x25.20 in)','SRA3'=>'SRA3 (320x450 mm ; 12.60x17.72 in)','SRA4'=>'SRA4 (225x320 mm ; 8.86x12.60 in)',),'German DIN 476'=>array('4A0'=>'4A0 (1682x2378 mm ; 66.22x93.62 in)','2A0'=>'2A0 (1189x1682 mm ; 46.81x66.22 in)',),'Variations on the ISO Standard'=>array('A2_EXTRA'=>'A2_EXTRA (445x619 mm ; 17.52x24.37 in)','A3+'=>'A3+ (329x483 mm ; 12.95x19.02 in)','A3_EXTRA'=>'A3_EXTRA (322x445 mm ; 12.68x17.52 in)','A3_SUPER'=>'A3_SUPER (305x508 mm ; 12.01x20.00 in)','SUPER_A3'=>'SUPER_A3 (305x487 mm ; 12.01x19.17 in)','A4_EXTRA'=>'A4_EXTRA (235x322 mm ; 9.25x12.68 in)','A4_SUPER'=>'A4_SUPER (229x322 mm ; 9.02x12.68 in)','SUPER_A4'=>'SUPER_A4 (227x356 mm ; 8.94x13.02 in)','A4_LONG'=>'A4_LONG (210x348 mm ; 8.27x13.70 in)','F4'=>'F4 (210x330 mm ; 8.27x12.99 in)','SO_B5_EXTRA'=>'SO_B5_EXTRA (202x276 mm ; 7.95x10.87 in)','A5_EXTRA'=>'A5_EXTRA (173x235 mm ; 6.81x9.25 in)',),'ANSI Series'=>array('ANSI_E'=>'ANSI_E (864x1118 mm ; 33.00x43.00 in)','ANSI_D'=>'ANSI_D (559x864 mm ; 22.00x33.00 in)','ANSI_C'=>'ANSI_C (432x559 mm ; 17.00x22.00 in)','ANSI_B'=>'ANSI_B (279x432 mm ; 11.00x17.00 in)','ANSI_A'=>'ANSI_A (216x279 mm ; 8.50x11.00 in)',),'Traditional "Loose" North American Paper Sizes'=>array('LEDGER, USLEDGER'=>'LEDGER, USLEDGER (432x279 mm ; 17.00x11.00 in)','TABLOID, USTABLOID, BIBLE, ORGANIZERK'=>'TABLOID, USTABLOID, BIBLE, ORGANIZERK (279x432 mm ; 11.00x17.00 in)','LETTER, USLETTER, ORGANIZERM'=>'LETTER, USLETTER, ORGANIZERM (216x279 mm ; 8.50x11.00 in)','LEGAL, USLEGAL'=>'LEGAL, USLEGAL (216x356 mm ; 8.50x13.00 in)','GLETTER, GOVERNMENTLETTER'=>'GLETTER, GOVERNMENTLETTER (203x267 mm ; 8.00x10.50 in)','JLEGAL, JUNIORLEGAL'=>'JLEGAL, JUNIORLEGAL (203x127 mm ; 8.00x5.00 in)',),'Other North American Paper Sizes'=>array('QUADDEMY'=>'QUADDEMY (889x1143 mm ; 35.00x45.00 in)','SUPER_B'=>'SUPER_B (330x483 mm ; 13.00x19.00 in)','QUARTO'=>'QUARTO (229x279 mm ; 9.00x11.00 in)','FOLIO, GOVERNMENTLEGAL'=>'FOLIO, GOVERNMENTLEGAL (216x330 mm ; 8.50x13.00 in)','EXECUTIVE, MONARCH'=>'EXECUTIVE, MONARCH (184x267 mm ; 7.25x10.50 in)','MEMO, STATEMENT, ORGANIZERL'=>'MEMO, STATEMENT, ORGANIZERL (140x216 mm ; 5.50x8.50 in)','FOOLSCAP'=>'FOOLSCAP (210x330 mm ; 8.27x13.00 in)','COMPACT'=>'COMPACT (108x171 mm ; 4.25x6.75 in)','ORGANIZERJ'=>'ORGANIZERJ (70x127 mm ; 2.75x5.00 in)',),'Canadian standard CAN 2-9.60M'=>array('P1'=>'P1 (560x860 mm ; 22.05x33.86 in)','P2'=>'P2 (430x560 mm ; 16.93x22.05 in)','P3'=>'P3 (280x430 mm ; 11.02x16.93 in)','P4'=>'P4 (215x280 mm ; 8.46x11.02 in)','P5'=>'P5 (140x215 mm ; 5.51x8.46 in)','P6'=>'P6 (107x140 mm ; 4.21x5.51 in)',),'North American Architectural Sizes'=>array('ARCH_E'=>'ARCH_E (914x1219 mm ; 36.00x48.00 in)','ARCH_E1'=>'ARCH_E1 (762x1067 mm ; 30.00x42.00 in)','ARCH_D'=>'ARCH_D (610x914 mm ; 23.00x36.00 in)','ARCH_C, BROADSHEET'=>'ARCH_C, BROADSHEET (457x610 mm ; 18.00x23.00 in)','ARCH_B'=>'ARCH_B (305x457 mm ; 12.00x18.00 in)','ARCH_A'=>'ARCH_A (229x305 mm ; 9.00x12.00 in)',),'Announcement Envelopes'=>array('ANNENV_A2'=>'ANNENV_A2 (111x146 mm ; 4.37x5.75 in)','ANNENV_A6'=>'ANNENV_A6 (121x165 mm ; 4.75x6.50 in)','ANNENV_A7'=>'ANNENV_A7 (133x184 mm ; 5.25x7.25 in)','ANNENV_A8'=>'ANNENV_A8 (140x206 mm ; 5.50x8.12 in)','ANNENV_A10'=>'ANNENV_A10 (159x244 mm ; 6.25x9.62 in)','ANNENV_SLIM'=>'ANNENV_SLIM (98x225 mm ; 3.87x8.87 in)',),'Commercial Envelopes'=>array('COMMENV_N6_1/4'=>'COMMENV_N6_1/4 (89x152 mm ; 3.50x6.00 in)','COMMENV_N6_3/4'=>'COMMENV_N6_3/4 (92x165 mm ; 3.62x6.50 in)','COMMENV_N8'=>'COMMENV_N8 (98x191 mm ; 3.87x7.50 in)','COMMENV_N9'=>'COMMENV_N9 (98x225 mm ; 3.87x8.87 in)','COMMENV_N10'=>'COMMENV_N10 (105x241 mm ; 4.12x9.50 in)','COMMENV_N11'=>'COMMENV_N11 (114x263 mm ; 4.50x10.37 in)','COMMENV_N12'=>'COMMENV_N12 (121x279 mm ; 4.75x11.00 in)','COMMENV_N14'=>'COMMENV_N14 (127x292 mm ; 5.00x11.50 in)',),'Catalogue Envelopes'=>array('CATENV_N1'=>'CATENV_N1 (152x229 mm ; 6.00x9.00 in)','CATENV_N1_3/4'=>'CATENV_N1_3/4 (165x241 mm ; 6.50x9.50 in)','CATENV_N2'=>'CATENV_N2 (165x254 mm ; 6.50x10.00 in)','CATENV_N3'=>'CATENV_N3 (178x254 mm ; 7.00x10.00 in)','CATENV_N6'=>'CATENV_N6 (191x267 mm ; 7.50x10.50 in)','CATENV_N7'=>'CATENV_N7 (203x279 mm ; 8.00x11.00 in)','CATENV_N8'=>'CATENV_N8 (210x286 mm ; 8.25x11.25 in)','CATENV_N9_1/2'=>'CATENV_N9_1/2 (216x267 mm ; 8.50x10.50 in)','CATENV_N9_3/4'=>'CATENV_N9_3/4 (222x286 mm ; 8.75x11.25 in)','CATENV_N10_1/2'=>'CATENV_N10_1/2 (229x305 mm ; 9.00x12.00 in)','CATENV_N12_1/2'=>'CATENV_N12_1/2 (241x318 mm ; 9.50x12.50 in)','CATENV_N13_1/2'=>'CATENV_N13_1/2 (254x330 mm ; 10.00x13.00 in)','CATENV_N14_1/4'=>'CATENV_N14_1/4 (286x311 mm ; 11.25x12.25 in)','CATENV_N14_1/2'=>'CATENV_N14_1/2 (292x368 mm ; 11.50x14.50 in)','Japanese'=>'Japanese (JIS P 0138-61) Standard B-Series','JIS_B0'=>'JIS_B0 (1030x1456 mm ; 40.55x57.32 in)','JIS_B1'=>'JIS_B1 (728x1030 mm ; 28.66x40.55 in)','JIS_B2'=>'JIS_B2 (515x728 mm ; 20.28x28.66 in)','JIS_B3'=>'JIS_B3 (364x515 mm ; 14.33x20.28 in)','JIS_B4'=>'JIS_B4 (257x364 mm ; 10.12x14.33 in)','JIS_B5'=>'JIS_B5 (182x257 mm ; 7.17x10.12 in)','JIS_B6'=>'JIS_B6 (128x182 mm ; 5.04x7.17 in)','JIS_B7'=>'JIS_B7 (91x128 mm ; 3.58x5.04 in)','JIS_B8'=>'JIS_B8 (64x91 mm ; 2.52x3.58 in)','JIS_B9'=>'JIS_B9 (45x64 mm ; 1.77x2.52 in)','JIS_B10'=>'JIS_B10 (32x45 mm ; 1.26x1.77 in)','JIS_B11'=>'JIS_B11 (22x32 mm ; 0.87x1.26 in)','JIS_B12'=>'JIS_B12 (16x22 mm ; 0.63x0.87 in)',),'PA Series'=>array('PA0'=>'PA0 (840x1120 mm ; 33.07x43.09 in)','PA1'=>'PA1 (560x840 mm ; 22.05x33.07 in)','PA2'=>'PA2 (420x560 mm ; 16.54x22.05 in)','PA3'=>'PA3 (280x420 mm ; 11.02x16.54 in)','PA4'=>'PA4 (210x280 mm ; 8.27x11.02 in)','PA5'=>'PA5 (140x210 mm ; 5.51x8.27 in)','PA6'=>'PA6 (105x140 mm ; 4.13x5.51 in)','PA7'=>'PA7 (70x105 mm ; 2.76x4.13 in)','PA8'=>'PA8 (52x70 mm ; 2.05x2.76 in)','PA9'=>'PA9 (35x52 mm ; 1.38x2.05 in)','PA10'=>'PA10 (26x35 mm ; 1.02x1.38 in)',),'Standard Photographic Print Sizes'=>array('PASSPORT_PHOTO'=>'PASSPORT_PHOTO (35x45 mm ; 1.38x1.77 in)','E'=>'E (82x120 mm ; 3.25x4.72 in)','3R, L'=>'3R, L (89x127 mm ; 3.50x5.00 in)','4R, KG'=>'4R, KG (102x152 mm ; 3.02x5.98 in)','4D'=>'4D (120x152 mm ; 4.72x5.98 in)','5R, 2L'=>'5R, 2L (127x178 mm ; 5.00x7.01 in)','6R, 8P'=>'6R, 8P (152x203 mm ; 5.98x7.99 in)','8R, 6P'=>'8R, 6P (203x254 mm ; 7.99x10.00 in)','S8R, 6PW'=>'S8R, 6PW (203x305 mm ; 7.99x12.01 in)','10R, 4P'=>'10R, 4P (254x305 mm ; 10.00x12.01 in)','S10R, 4PW'=>'S10R, 4PW (254x381 mm ; 10.00x15.00 in)','11R'=>'11R (279x356 mm ; 10.98x13.02 in)','S11R'=>'S11R (279x432 mm ; 10.98x17.01 in)','12R'=>'12R (305x381 mm ; 12.01x15.00 in)','S12R'=>'S12R (305x456 mm ; 12.01x17.95 in)',),'Common Newspaper Sizes'=>array('NEWSPAPER_BROADSHEET'=>'NEWSPAPER_BROADSHEET (750x600 mm ; 29.53x23.62 in)','NEWSPAPER_BERLINER'=>'NEWSPAPER_BERLINER (470x315 mm ; 18.50x12.40 in)','NEWSPAPER_COMPACT, NEWSPAPER_TABLOID'=>'NEWSPAPER_COMPACT, NEWSPAPER_TABLOID (430x280 mm ; 16.93x11.02 in)',),'Business Cards'=>array('CREDIT_CARD, BUSINESS_CARD, BUSINESS_CARD_ISO7810'=>'CREDIT_CARD, BUSINESS_CARD, BUSINESS_CARD_ISO7810 (54x86 mm ; 2.13x3.37 in)','BUSINESS_CARD_ISO216'=>'BUSINESS_CARD_ISO216 (52x74 mm ; 2.05x2.91 in)','BUSINESS_CARD_IT, BUSINESS_CARD_UK, BUSINESS_CARD_FR, BUSINESS_CARD_DE, BUSINESS_CARD_ES'=>'BUSINESS_CARD_IT, BUSINESS_CARD_UK, BUSINESS_CARD_FR, BUSINESS_CARD_DE, BUSINESS_CARD_ES (55x85 mm ; 2.17x3.35 in)','BUSINESS_CARD_US, BUSINESS_CARD_CA'=>'BUSINESS_CARD_US, BUSINESS_CARD_CA (51x89 mm ; 2.01x3.50 in)','BUSINESS_CARD_JP'=>'BUSINESS_CARD_JP (55x91 mm ; 2.17x3.58 in)','BUSINESS_CARD_HK'=>'BUSINESS_CARD_HK (54x90 mm ; 2.13x3.54 in)','BUSINESS_CARD_AU, BUSINESS_CARD_DK, BUSINESS_CARD_SE'=>'BUSINESS_CARD_AU, BUSINESS_CARD_DK, BUSINESS_CARD_SE (55x90 mm ; 2.17x3.54 in)','BUSINESS_CARD_RU, BUSINESS_CARD_CZ, BUSINESS_CARD_FI, BUSINESS_CARD_HU, BUSINESS_CARD_IL'=>'BUSINESS_CARD_RU, BUSINESS_CARD_CZ, BUSINESS_CARD_FI, BUSINESS_CARD_HU, BUSINESS_CARD_IL (50x90 mm ; 1.97x3.54 in)',),'Billboards'=>array('4SHEET'=>'4SHEET (1016x1524 mm ; 40.00x60.00 in)','6SHEET'=>'6SHEET (1200x1800 mm ; 47.24x70.87 in)','12SHEET'=>'12SHEET (3048x1524 mm ; 120.00x60.00 in)','16SHEET'=>'16SHEET (2032x3048 mm ; 80.00x120.00 in)','32SHEET'=>'32SHEET (4064x3048 mm ; 160.00x120.00 in)','48SHEET'=>'48SHEET (6096x3048 mm ; 240.00x120.00 in)','64SHEET'=>'64SHEET (8128x3048 mm ; 320.00x120.00 in)','96SHEET'=>'96SHEET (12192x3048 mm ; 480.00x120.00 in)','Old Imperial English'=>'Old Imperial English (some are still used in USA)','EN_EMPEROR'=>'EN_EMPEROR (1219x1829 mm ; 48.00x72.00 in)','EN_ANTIQUARIAN'=>'EN_ANTIQUARIAN (787x1346 mm ; 31.00x53.00 in)','EN_GRAND_EAGLE'=>'EN_GRAND_EAGLE (730x1067 mm ; 28.75x42.00 in)','EN_DOUBLE_ELEPHANT'=>'EN_DOUBLE_ELEPHANT (679x1016 mm ; 26.75x40.00 in)','EN_ATLAS'=>'EN_ATLAS (660x864 mm ; 26.00x33.00 in)','EN_COLOMBIER'=>'EN_COLOMBIER (597x876 mm ; 23.50x34.50 in)','EN_ELEPHANT'=>'EN_ELEPHANT (584x711 mm ; 23.00x28.00 in)','EN_DOUBLE_DEMY'=>'EN_DOUBLE_DEMY (572x902 mm ; 22.50x35.50 in)','EN_IMPERIAL'=>'EN_IMPERIAL (559x762 mm ; 22.00x30.00 in)','EN_PRINCESS'=>'EN_PRINCESS (546x711 mm ; 21.50x28.00 in)','EN_CARTRIDGE'=>'EN_CARTRIDGE (533x660 mm ; 21.00x26.00 in)','EN_DOUBLE_LARGE_POST'=>'EN_DOUBLE_LARGE_POST (533x838 mm ; 21.00x33.00 in)','EN_ROYAL'=>'EN_ROYAL (508x635 mm ; 20.00x25.00 in)','EN_SHEET, EN_HALF_POST'=>'EN_SHEET, EN_HALF_POST (495x597 mm ; 19.50x23.50 in)','EN_SUPER_ROYAL'=>'EN_SUPER_ROYAL (483x686 mm ; 19.00x27.00 in)','EN_DOUBLE_POST'=>'EN_DOUBLE_POST (483x775 mm ; 19.00x30.50 in)','EN_MEDIUM'=>'EN_MEDIUM (445x584 mm ; 17.50x23.00 in)','EN_DEMY'=>'EN_DEMY (445x572 mm ; 17.50x22.50 in)','EN_LARGE_POST'=>'EN_LARGE_POST (419x533 mm ; 16.50x21.00 in)','EN_COPY_DRAUGHT'=>'EN_COPY_DRAUGHT (406x508 mm ; 16.00x20.00 in)','EN_POST'=>'EN_POST (394x489 mm ; 15.50x19.25 in)','EN_CROWN'=>'EN_CROWN (381x508 mm ; 15.00x20.00 in)','EN_PINCHED_POST'=>'EN_PINCHED_POST (375x470 mm ; 14.75x18.50 in)','EN_BRIEF'=>'EN_BRIEF (343x406 mm ; 13.50x16.00 in)','EN_FOOLSCAP'=>'EN_FOOLSCAP (343x432 mm ; 13.50x17.00 in)','EN_SMALL_FOOLSCAP'=>'EN_SMALL_FOOLSCAP (337x419 mm ; 13.25x16.50 in)','EN_POTT'=>'EN_POTT (318x381 mm ; 12.50x15.00 in)',),'Old Imperial Belgian'=>array('BE_GRAND_AIGLE'=>'BE_GRAND_AIGLE (700x1040 mm ; 27.56x40.94 in)','BE_COLOMBIER'=>'BE_COLOMBIER (620x850 mm ; 24.41x33.46 in)','BE_DOUBLE_CARRE'=>'BE_DOUBLE_CARRE (620x920 mm ; 24.41x36.22 in)','BE_ELEPHANT'=>'BE_ELEPHANT (616x770 mm ; 24.25x30.31 in)','BE_PETIT_AIGLE'=>'BE_PETIT_AIGLE (600x840 mm ; 23.62x33.07 in)','BE_GRAND_JESUS'=>'BE_GRAND_JESUS (550x730 mm ; 21.65x28.74 in)','BE_JESUS'=>'BE_JESUS (540x730 mm ; 21.26x28.74 in)','BE_RAISIN'=>'BE_RAISIN (500x650 mm ; 19.69x25.59 in)','BE_GRAND_MEDIAN'=>'BE_GRAND_MEDIAN (460x605 mm ; 18.11x23.82 in)','BE_DOUBLE_POSTE'=>'BE_DOUBLE_POSTE (435x565 mm ; 17.13x22.24 in)','BE_COQUILLE'=>'BE_COQUILLE (430x560 mm ; 16.93x22.05 in)','BE_PETIT_MEDIAN'=>'BE_PETIT_MEDIAN (415x530 mm ; 16.34x20.87 in)','BE_RUCHE'=>'BE_RUCHE (360x460 mm ; 14.17x18.11 in)','BE_PROPATRIA'=>'BE_PROPATRIA (345x430 mm ; 13.58x16.93 in)','BE_LYS'=>'BE_LYS (317x397 mm ; 12.48x15.63 in)','BE_POT'=>'BE_POT (307x384 mm ; 12.09x15.12 in)','BE_ROSETTE'=>'BE_ROSETTE (270x347 mm ; 10.63x13.66 in)',),'Old Imperial French'=>array('FR_UNIVERS'=>'FR_UNIVERS (1000x1300 mm ; 39.37x51.18 in)','FR_DOUBLE_COLOMBIER'=>'FR_DOUBLE_COLOMBIER (900x1260 mm ; 35.43x49.61 in)','FR_GRANDE_MONDE'=>'FR_GRANDE_MONDE (900x1260 mm ; 35.43x49.61 in)','FR_DOUBLE_SOLEIL'=>'FR_DOUBLE_SOLEIL (800x1200 mm ; 31.50x47.24 in)','FR_DOUBLE_JESUS'=>'FR_DOUBLE_JESUS (760x1120 mm ; 29.92x43.09 in)','FR_GRAND_AIGLE'=>'FR_GRAND_AIGLE (750x1060 mm ; 29.53x41.73 in)','FR_PETIT_AIGLE'=>'FR_PETIT_AIGLE (700x940 mm ; 27.56x37.01 in)','FR_DOUBLE_RAISIN'=>'FR_DOUBLE_RAISIN (650x1000 mm ; 25.59x39.37 in)','FR_JOURNAL'=>'FR_JOURNAL (650x940 mm ; 25.59x37.01 in)','FR_COLOMBIER_AFFICHE'=>'FR_COLOMBIER_AFFICHE (630x900 mm ; 24.80x35.43 in)','FR_DOUBLE_CAVALIER'=>'FR_DOUBLE_CAVALIER (620x920 mm ; 24.41x36.22 in)','FR_CLOCHE'=>'FR_CLOCHE (600x800 mm ; 23.62x31.50 in)','FR_SOLEIL'=>'FR_SOLEIL (600x800 mm ; 23.62x31.50 in)','FR_DOUBLE_CARRE'=>'FR_DOUBLE_CARRE (560x900 mm ; 22.05x35.43 in)','FR_DOUBLE_COQUILLE'=>'FR_DOUBLE_COQUILLE (560x880 mm ; 22.05x34.65 in)','FR_JESUS'=>'FR_JESUS (560x760 mm ; 22.05x29.92 in)','FR_RAISIN'=>'FR_RAISIN (500x650 mm ; 19.69x25.59 in)','FR_CAVALIER'=>'FR_CAVALIER (460x620 mm ; 18.11x24.41 in)','FR_DOUBLE_COURONNE'=>'FR_DOUBLE_COURONNE (460x720 mm ; 18.11x28.35 in)','FR_CARRE'=>'FR_CARRE (450x560 mm ; 17.72x22.05 in)','FR_COQUILLE'=>'FR_COQUILLE (440x560 mm ; 17.32x22.05 in)','FR_DOUBLE_TELLIERE'=>'FR_DOUBLE_TELLIERE (440x680 mm ; 17.32x26.77 in)','FR_DOUBLE_CLOCHE'=>'FR_DOUBLE_CLOCHE (400x600 mm ; 15.75x23.62 in)','FR_DOUBLE_POT'=>'FR_DOUBLE_POT (400x620 mm ; 15.75x24.41 in)','FR_ECU'=>'FR_ECU (400x520 mm ; 15.75x20.47 in)','FR_COURONNE'=>'FR_COURONNE (360x460 mm ; 14.17x18.11 in)','FR_TELLIERE'=>'FR_TELLIERE (340x440 mm ; 13.39x17.32 in)','FR_POT'=>'FR_POT (310x400 mm ; 12.20x15.75 in)',)),
            'class' => 'chosen_select_nostd',
            'css' => 'min-width:300px;',
            'desc_tip' => true,
        );
        
        $pdf_margin_top_bottom = array(
            'title' => __('PDF Margin Top & Bottom', 'wpd'),
            'id' => 'wpc-output-options[pdf-margin-tb]',
            'type' => 'text',
            'default' => '20',
            'class' => 'chosen_select_nostd',
            'css' => 'min-width:300px;',
            'desc_tip' => true,
        );
        
        $pdf_margin_left_right = array(
            'title' => __('PDF Margin Left & Right', 'wpd'),
            'id' => 'wpc-output-options[pdf-margin-lr]',
            'type' => 'text',
            'default' => '20',
            'class' => 'chosen_select_nostd',
            'css' => 'min-width:300px;',
            'desc_tip' => true,
        );
        
        $pdf_orientation = array(
            'title' => __('PDF Orientation', 'wpd'),
            'id' => 'wpc-output-options[pdf-orientation]',
            'default' => 'P',
            'type' => 'select',
            'options' => array(
                'P' => __('Portrait', 'wpd'),
                'L' => __('Landscape', 'wpd')
            )
        );
        
        $output_options_begin = array('type' => 'title',
            'id' => 'wpc_output',
            'title' => __('Output Settings')
        );

        $output_options_end = array('type' => 'sectionend',
            'id' => 'wpc_output'
        );

        array_push($options, $output_options_begin);
        $options = array_merge($options, $output_formats);
        array_push($options, $pdf_format);
        array_push($options, $pdf_orientation);
        array_push($options, $pdf_margin_top_bottom);
        array_push($options, $pdf_margin_left_right);
        array_push($options, $output_options_end);
        return $options;
    }

    /**
     * Builds the text settings options
     * @global array $wpc_options_settings
     * @return array Settings
     */
    private function get_text_settings() {
        GLOBAL $wpc_options_settings;
        $options = array();
        $text_options = $wpc_options_settings['wpc-texts-options'];

        $text_options_begin = array('title' => '',
            'type' => 'title',
            'title' => __('Text Settings')
        );
        $text_options_end = array('type' => 'sectionend'
        );
        $text_tab_visible = array(
            'title' => __('Active controls', 'wpd'),
            'desc' => __('Show this tab', 'wpd'),
            'id' => 'wpc-texts-options[visible-tab]',
            'type' => 'checkbox',
            'default' => 'yes',
            'class' => 'checkbox-text'
        );
        $text_all_options = array(
            array(
                'desc' => __('Underline', 'wpd'),
                'id' => 'wpc-texts-options[underline]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => 'start'
            ),
            array(
                'desc' => __('Bold', 'wpd'),
                'id' => 'wpc-texts-options[bold]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Italic', 'wpd'),
                'id' => 'wpc-texts-options[italic]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Text Color', 'wpd'),
                'id' => 'wpc-texts-options[text-color]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Background Color', 'wpd'),
                'id' => 'wpc-texts-options[background-color]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Outline', 'wpd'),
                'id' => 'wpc-texts-options[outline]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Curved', 'wpd'),
                'id' => 'wpc-texts-options[curved]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Font Family', 'wpd'),
                'id' => 'wpc-texts-options[font-family]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Font Size', 'wpd'),
                'id' => 'wpc-texts-options[font-size]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Outline Width', 'wpd'),
                'id' => 'wpc-texts-options[outline-width]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Opacity', 'wpd'),
                'id' => 'wpc-texts-options[opacity]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Alignment', 'wpd'),
                'id' => 'wpc-texts-options[text-alignment]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Strikethrough', 'wpd'),
                'id' => 'wpc-texts-options[text-strikethrough]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Overline', 'wpd'),
                'id' => 'wpc-texts-options[text-overline]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => 'end'
            ),
        );
        array_push($options, $text_options_begin);
        array_push($options, $text_tab_visible);
        $options = array_merge($options, $text_all_options);
        // array_push($options,$this->get_upload_icon_field(__("Tab icon","wpd"),"icon_text","wpc-texts-options"));
        // array_push($options, $text_tab_color);   
        // array_push($options, $text_tab_selected_color);        
        array_push($options, $text_options_end);
        return $options;
    }

    /**
     * Builds the shapes settings options
     * @global array $wpc_options_settings
     * @return array Settings
     */
    private function get_shapes_settings() {
        GLOBAL $wpc_options_settings;
        $options = array();
        $shapes_options = $wpc_options_settings['wpc-shapes-options'];
        $default_tab_color = $this->get_proper_value($shapes_options, 'tab-color', "");
        $default_selected_color = $this->get_proper_value($shapes_options, 'selected-color', "");

        $shapes_options_begin = array('type' => 'title',
            'title' => __('Shapes Settings'));
        $shapes_tab_color = array(
            'title' => __('Normal color', 'wpd'),
            'id' => 'wpc-shapes-options[tab-color]',
            'class' => 'wpc-color',
            'css' => "background-color:" . $default_tab_color,
            'type' => 'text',
        );
        $shapes_tab_selected_color = array(
            'title' => __('Active Color', 'wpd'),
            'id' => 'wpc-shapes-options[selected-color]',
            'class' => 'wpc-color',
            'css' => "background-color:" . $default_selected_color,
            'type' => 'text',
        );
        $shapes_options_end = array('type' => 'sectionend'
        );
        $shapes_tab_visible = array(
            'title' => __('Active controls', 'wpd'),
            'desc' => __('Show this tab', 'wpd'),
            'id' => 'wpc-shapes-options[visible-tab]',
            'type' => 'checkbox',
            'default' => 'yes',
        );
        $shapes_all_options = array(
            array(
                'desc' => __('Square', 'wpd'),
                'id' => 'wpc-shapes-options[square]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => 'start'
            ),
            array(
                'desc' => __('Rounded square', 'wpd'),
                'id' => 'wpc-shapes-options[r-square]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Circle', 'wpd'),
                'id' => 'wpc-shapes-options[circle]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Triangle', 'wpd'),
                'id' => 'wpc-shapes-options[triangle]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Polygon', 'wpd'),
                'id' => 'wpc-shapes-options[polygon]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Star', 'wpd'),
                'id' => 'wpc-shapes-options[star]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Heart', 'wpd'),
                'id' => 'wpc-shapes-options[heart]',
                'type' => 'checkbox',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Outline', 'wpd'),
                'id' => 'wpc-shapes-options[outline]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Background Color', 'wpd'),
                'id' => 'wpc-shapes-options[background-color]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Outline Width', 'wpd'),
                'id' => 'wpc-shapes-options[outline-width]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Opacity', 'wpd'),
                'id' => 'wpc-shapes-options[opacity]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => 'end'
            )
        );
        array_push($options, $shapes_options_begin);
        array_push($options, $shapes_tab_visible);
        $options = array_merge($options, $shapes_all_options);
        // array_push($options,$this->get_upload_icon_field(__("Tab icon","wpd"),"icon_shape","wpc-shapes-options"));
        // array_push($options,$shapes_tab_color);    
        //array_push($options,$shapes_tab_selected_color);      
        array_push($options, $shapes_options_end);
        return $options;
    }

    /**
     * Builds the images settings options
     * @global array $wpc_options_settings
     * @return array Settings
     */
    private function get_images_settings() {
        GLOBAL $wpc_options_settings;
        $options = array();
        $images_options = $wpc_options_settings['wpc-images-options'];
        $default_tab_color = $this->get_proper_value($images_options, 'tab-color', "");
        $default_selected_color = $this->get_proper_value($images_options, 'selected-color', "");

        $images_options_begin = array('title' => '',
            'type' => 'title',
            'title' => __('Image Settings')
        );
        $images_tab_color = array(
            'title' => __('Normal color', 'wpd'),
            'id' => 'wpc-images-options[tab-color]',
            'class' => 'wpc-color',
            'default' => $default_tab_color,
            'css' => "background-color:" . $default_tab_color,
            'type' => 'text',
        );
        $images_tab_selected_color = array(
            'title' => __('Active color', 'wpd'),
            'id' => 'wpc-images-options[selected-color]',
            'class' => 'wpc-color',
            'default' => $default_selected_color,
            'css' => "background-color:" . $default_selected_color,
            'type' => 'text',
        );
        $images_options_end = array('type' => 'sectionend'
        );
        $images_tab_visible = array(
            'title' => __('Active controls', 'wpd'),
            'desc' => __('Show this tab', 'wpd'),
            'id' => 'wpc-images-options[visible-tab]',
            'default' => 'yes',
            'type' => 'checkbox'
        );
        $images_all_options = array(
            array(
                'desc' => __('Grayscale', 'wpd'),
                'id' => 'wpc-images-options[grayscale]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => 'start'
            ),
            array(
                'desc' => __('Invert', 'wpd'),
                'id' => 'wpc-images-options[invert]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Sepia1', 'wpd'),
                'id' => 'wpc-images-options[sepia1]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Sepia2', 'wpd'),
                'id' => 'wpc-images-options[sepia2]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Blur', 'wpd'),
                'id' => 'wpc-images-options[blur]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'desc' => __('Sharpen', 'wpd'),
                'id' => 'wpc-images-options[sharpen]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Opacity', 'wpd'),
                'id' => 'wpc-images-options[opacity]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'desc' => __('Emboss', 'wpd'),
                'id' => 'wpc-images-options[emboss]',
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => ''
            ),
        );
        array_push($options, $images_options_begin);
        array_push($options, $images_tab_visible);
        $options = array_merge($options, $images_all_options);
        //   array_push($options,$this->get_upload_icon_field(__("Images tab icon","wpd"),"icon_image","wpc-images-options"));
        //  array_push($options, $images_tab_color);
        //  array_push($options, $images_tab_selected_color);         
        array_push($options, $images_options_end);
        return $options;
    }

    /**
     * Builds the settings page
     */
    function get_settings_page() {
        if (isset($_POST) && !empty($_POST))
        {
            $this->save_wpc_tab_options();
            GLOBAL $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
        ?>
        <form method="POST">
            <div id="wpc-settings">
                <div class="wrap">
                    <h2>WPC Settings</h2>
                </div>
                <div id="TabbedPanels1" class="TabbedPanels">
                    <ul class="TabbedPanelsTabGroup ">
                        <li class="TabbedPanelsTab " tabindex="1"><span><?php _e('General', 'wpd'); ?></span> </li>
                        <li class="TabbedPanelsTab" tabindex="2"><span><?php _e('Uploads', 'wpd'); ?> </span></li>
                        <li class="TabbedPanelsTab" tabindex="4"><span><?php _e('Output', 'wpd'); ?></span></li>
                        <li class="TabbedPanelsTab" tabindex="6"><span><?php _e('Text', 'wpd'); ?></span></li>
                        <li class="TabbedPanelsTab" tabindex="7"><span><?php _e('Shapes', 'wpd'); ?></span></li>
                        <li class="TabbedPanelsTab" tabindex="8"><span><?php _e('Cliparts', 'wpd'); ?></span></li>
                    </ul>

                    <div class="TabbedPanelsContentGroup">
                        <div class="TabbedPanelsContent">
                            <div class='wpc-grid wpc-grid-pad'>
        <?php
        $general_settings = $this->get_general_settings();
        $this->admin_fields($general_settings);
        ?>                              
                            </div>
                        </div>
                        <div class="TabbedPanelsContent">
                            <div class='wpc-grid wpc-grid-pad'>
        <?php
        $uploads_settings = $this->get_uploads_settings();
        $this->admin_fields($uploads_settings);
        ?>
                            </div>
                        </div>
                        <div class="TabbedPanelsContent">
                            <div class="wpc-grid wpc-grid-pad">
                                <?php
                                $output_settings = $this->get_output_settings();
                                $this->admin_fields($output_settings);
                                ?>
                            </div> 
                        </div>
                        <div class="TabbedPanelsContent">
                            <div class="wpc-grid wpc-grid-pad">
                                <?php
                                $text_settings = $this->get_text_settings();
                                $this->admin_fields($text_settings);
                                ?>
                            </div> 
                        </div>
                        <div class="TabbedPanelsContent">
                            <div class="wpc-grid wpc-grid-pad">
                                <?php
                                $shapes_settings = $this->get_shapes_settings();
                                $this->admin_fields($shapes_settings);
                                ?>
                            </div> 
                        </div>
                        <div class="TabbedPanelsContent">
                            <div class="wpc-grid wpc-grid-pad">
                                <?php
                                $images_settings = $this->get_images_settings();
                                $this->admin_fields($images_settings);
                                ?>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
            <input type="submit" value="<?php _e("Save", "wpd"); ?>" class="button button-primary button-large mg-top-10-i">
        </form>
                                <?php
                            }

                            /**
                             * Save the settings
                             */
                            private function save_wpc_tab_options() {
                                if (isset($_POST) && !empty($_POST)) {
                                    $checkboxes_map = array(
                                        "wpc-output-options" => array("wpc-generate-pdf", "wpc-generate-zip"),
                                        "wpc-texts-options" => array("visible-tab", "underline", "text-spacing", "bold", "italic", "text-color", "background-color", "outline", "curved", "font-family", "font-size", "outline-width", "opacity", "text-alignment", "text-strikethrough", "text-overline"),
                                        "wpc-shapes-options" => array("visible-tab", "square", "r-square", "circle", "triangle", "polygon", "star", "heart", "background-color", "outline", "outline-width", "opacity"),
                                        "wpc-images-options" => array("visible-tab", "emboss", "opacity", "sharpen", "blur", "sepia1", "sepia2", "invert", "grayscale"),
                                        "wpc-designs-options" => array("visible-tab", "saved", "orders"),
                                        "wpc-upload-options" => array("visible-tab", "grayscale", "invert", "sepia1", "sepia2", "blur", "emboss", "opacity", "sharpen")
                                    );
                                    foreach ($checkboxes_map as $key_map => $values) {
                                        if (isset($_POST[$key_map])) {
                                            $this->transform_checkbox_value($key_map, $checkboxes_map[$key_map]);
                                        } else {
                                            foreach ($checkboxes_map[$key_map] as $option) {
                                                $_POST[$key_map][$option] = 'no';
                                            }
                                        }
                                    }
                                    foreach ($_POST as $key => $values)
                                        update_option($key, $_POST[$key]);

                                    $this->init_globals();
                                    ?>
            <div id="message" class="updated below-h2"><p><?php echo __("Settings successfully saved.", "wpd"); ?></p></div>
            <?php
        }
    }
    
    private function get_custom_palette(){
        GLOBAL $wpc_options_settings;
        $colors_options = $wpc_options_settings['wpc-colors-options'];
        $wpc_palette_type = WPD_Admin::get_proper_value($colors_options, 'wpc-color-palette', "");
        $palette_style = "";
        if (isset($wpc_palette_type) && $wpc_palette_type != "custom")
            $palette_style = "style='display:none;'";
        $palette = WPD_Admin::get_proper_value($colors_options, 'wpc-custom-palette', "");
        $custom_palette = '<table class="form-table" id="wpd-predefined-colors-options" ' . $palette_style . '>
                <tbody>
                    <tr valign="top">
                <th scope="row" class="titledesc"></th>
                    <td class="forminp">
                    <div class="wpc-colors">';
        if (isset($palette) && is_array($palette)) {
            foreach ($palette as $color) {
                $custom_palette.='<div>
                                    <input type="text" name="wpc-colors-options[wpc-custom-palette][]"style="background-color: ' . $color . '" value="' . $color . '" class="wpc-color">
                                        <button class="button wpc-remove-color">Remove</button>
                                </div>';
            }
        }
        $custom_palette.='</div>
                        <button class="button mg-top-10" id="wpc-add-color">Add color</button>
                    </td>
                    </tr>
                </tbody>
   </table>';
        return $custom_palette;
    }

    /**
     * Format the checkbox option in the settings
     * @param type $option_name
     * @param type $option_array
     */
    private function transform_checkbox_value($option_name, $option_array) {
        foreach ($option_array as $option) {
            if (isset($_POST[$option_name][$option]) && $_POST[$option_name][$option] == '1')
                $_POST[$option_name][$option] = 'yes';
            else
                $_POST[$option_name][$option] = 'no';
        }
    }

    /**
     * Alerts the administrator if the customization page is missing
     * @global array $wpc_options_settings
     */
    function notify_customization_page_missing() {
        GLOBAL $wpc_options_settings;
        $options = $wpc_options_settings['wpc-general-options'];
        $wpc_page_id = $options['wpc_page_id'];
        $settings_url = get_bloginfo("url") . '/wp-admin/admin.php?page=wpc-manage-settings';
        if (empty($wpc_page_id))
            echo '<div class="error">
                   <p><b>Woocommerce product Designer: </b>The customizer page is not defined. Please configure it in <a href="' . $settings_url . '">woocommerce page settings</a>: .</p>
                </div>';
        if (!extension_loaded('zip'))
            echo '<div class="error">
                   <p><b>Woocommerce product Designer: </b>ZIP extension not loaded on this server. You won\'t be able to generate zip outputs.</p>
                </div>';
    }

    /**
     * Alerts the administrator if the minimum requirements are not met
     */
    function notify_minmimum_required_parameters() {
//        GLOBAL $wpc_options_settings;
//        $options = $wpc_options_settings['wpc-general-options'];
        $message = "";
        $minimum_required_parameters = array(
            "memory_limit" => array(128, "M"),
            "max_file_uploads" => array(100, ""),
            "max_input_vars" => array(5000, ""),
            "post_max_size" => array(128, "M"),
            "upload_max_filesize" => array(128, "M"),
        );
        foreach ($minimum_required_parameters as $key => $min_arr) {
            $defined_value = ini_get($key);
            $defined_value_int = str_replace($min_arr[1], "", $defined_value);
            if ($defined_value_int < $min_arr[0])
                $message.="Your PHP setting <b>$key</b> is currently set to <b>$defined_value</b>. We recommand to set this value at least to <b>" . implode("", $min_arr) . "</b> to avoid any issue with our plugin.<br>";
        }

        if (!empty($message))
            echo '<div class="error">
                   <p><b>Woocommerce Product Designer: </b><br>' . $message . '</p>
                </div>';
    }

    /**
     * Checks if the database needs to be upgraded
     */
    function run_wpc_db_updates_requirements() {
        //Checks db structure for v3.0
        if ($this->get_meta_count('customizable-product') > 0 || $this->get_meta_count('wpc-templates-page') > 0 || $this->get_meta_count('wpc-upload-design') > 0) {
            ?>
            <div class="updated" id="wpc-updater-container">
                <strong><?php echo _e("Woocommerce Product Designer database update required", "wpd"); ?></strong>
                <div>
            <?php echo _e("Hi! This version of the Woocommerce Product Designer made some changes in the way it's data are stored. So in order to work properly, we just need you to click on the \"Run Updater\" button to move your old settings to the new structure. ", "wpd"); ?><br>
                    <input type="button" value="<?php echo _e("Run the updater", "wpd"); ?>" id="wpc-run-updater" class="button button-primary"/>
                    <div class="loading" style="display:none;"></div>
                </div>
            </div>
            <style>
                #wpc-updater-container
                {
                    padding: 3px 17px;
                    font-size: 15px;
                    line-height: 36px;
                    margin-left: 0px;
                    border-left: 5px solid #e14d43 !important;
                }
                #wpc-updater-container.done
                {
                    border-color: #7ad03a !important;
                }
                #wpc-run-updater {
                    background: #e14d43;
                    border-color: #d02a21;
                    color: #fff;
                    -webkit-box-shadow: inset 0 1px 0 #ec8a85,0 1px 0 rgba(0,0,0,.15);
                    box-shadow: inset 0 1px 0 #ec8a85,0 1px 0 rgba(0,0,0,.15);
                }

                #wpc-run-updater:focus, #wpc-run-updater:hover {
                    background: #dd362d;
                    border-color: #ba251e;
                    color: #fff;
                    -webkit-box-shadow: inset 0 1px 0 #e8756f;
                    box-shadow: inset 0 1px 0 #e8756f;
                }
                .loading
                {
                    background: url("<?php echo WPD_URL; ?>/admin/images/spinner.gif") 10% 10% no-repeat transparent;
                    background-size: 111%;
                    width: 32px;
                    height: 40px;
                    display: inline-block;
                }
            </style>
            <script>
                //jQuery('.loading').hide();
                jQuery('#wpc-run-updater').click('click', function () {
                    var ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
                    if (confirm("It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now")) {
                        jQuery('.loading').show();
                        jQuery.post(
                                ajax_url,
                                {
                                    action: 'run_updater'
                                },
                        function (data) {
                            jQuery('.loading').hide();
                            jQuery('#wpc-updater-container').html(data);
                            jQuery('#wpc-updater-container').addClass("done");
                        }
                        );
                    }

                });
            </script>
            <?php
        }
    }

    /**
     * Returns the number of occurences corresponding to a post meta key
     * @global type $wpdb Database object
     * @param type $meta_key Meta Key to check
     * @return int Number of occurences
     */
    private function get_meta_count($meta_key) {
        global $wpdb;
        $sql_result = $wpdb->get_var(
                "
                            SELECT count(*)
                            FROM $wpdb->posts p
                            JOIN $wpdb->postmeta pm on pm.post_id = p.id
                            WHERE p.post_type = 'product'
                            AND pm.meta_key = '" . $meta_key . "'
                      ");
        return $sql_result;
    }

    /**
     * Runs the database upgrade
     */
    function run_wpc_updater() {
        $message = $this->update_db_for_v2_0();
        if (!$message) {
            $message = __("Something went wrong...", "wpd");
        } else {
            $message = $this->update_db_for_v3_0();
            if (!$message) {
                $message = __("Something went wrong...", "wpd");
            } else
                $message = __("Your database has been successfully updated.", "wpd");
        }
        echo $message;
        die();
    }

    private function map_part_datas($metas, $product_id) {
        $wpc_metas = $metas;
        $product = wc_get_product($product_id);
        $canvas_width_arr = get_post_meta($product_id, "wpc-canvas-w", true);
        $canvas_height_arr = get_post_meta($product_id, "wpc-canvas-h", true);
        $parts = get_option("wpc-parts");
        if ($product->product_type == "variable") {
            $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                $variation_id = $variation['variation_id'];
                $wpc_metas[$variation_id]['canvas-w'] = WPD_Admin::get_proper_value($canvas_width_arr, $variation_id, "");
                $wpc_metas[$variation_id]['canvas-h'] = WPD_Admin::get_proper_value($canvas_height_arr, $variation_id, "");
                $wpc_metas = $this->get_part_datas($wpc_metas, $parts, $variation_id);
            }
        } else {
            $wpc_metas[$product_id]['canvas-w'] = WPD_Admin::get_proper_value($canvas_width_arr, $product_id, "");
            $wpc_metas[$product_id]['canvas-h'] = WPD_Admin::get_proper_value($canvas_height_arr, $product_id, "");
            $wpc_metas = $this->get_part_datas($wpc_metas, $parts, $product_id);
        }
        delete_post_meta($product_id, "wpc-canvas-w");
        delete_post_meta($product_id, "wpc-canvas-h");
        return $wpc_metas;
    }

    /**
     * Runs the products meta migrations if needed
     * @return bool
     */
    function run_products_metas_migration_if_needed() {
        $args = array(
            'post_type' => 'product',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'customizable-product'
                ),
                array(
                    'key' => 'wpc-templates-page'
                ),
                array(
                    'key' => 'wpc-upload-design'
                ),
                array(
                    'key' => 'wpc-design-from-blank'
                )
            )
        );
        $the_query = new WP_Query($args);
        $result = true;
        $bounding_box = array(
            "width" => "clip_width",
            "height" => "clip_height",
            "x" => "clip_x",
            "y" => "clip_y",
            "radius" => "clip_radius",
            "r_radius" => "clip_radius_rect",
            "type" => "clip_type",
            "border_color" => "clip_b_color"
        );
        if ($the_query->have_posts()) :
            while ($the_query->have_posts()) : $the_query->the_post();
                $keys_map = array(
                    "is-customizable" => "customizable-product",
                    "templates-page" => "wpc-templates-page",
                    "can-design-from-blank" => "wpc-design-from-blank",
                    "pricing-rules" => "wpc-pricing",
                    "canvas-w" => "wpc-canvas-w",
                    "canvas-h" => "wpc-canvas-h",
                    "ninja-form-options" => "wpc-options-ninja-fom",
                    "output-settings" => "wpc-output-product-settings"
                );
                $product_id = get_the_ID();
                $metas = get_post_meta($product_id, 'wpc-metas', true);
                if (!is_array($metas))
                    $metas = array();
                foreach ($keys_map as $new_key => $old_key) {
                    $metas[$new_key] = get_post_meta($product_id, $old_key, true);
                    $result = delete_post_meta($product_id, $old_key);
                }
                foreach ($bounding_box as $new_box_meta => $old_box_key) {
                    $metas['bounding_box'][$new_box_meta] = get_post_meta($product_id, $old_box_key, true);
                    $result = delete_post_meta($product_id, $old_box_key);
                }
                $wpc_metas = $this->map_part_datas($metas, $product_id);
                $result = update_post_meta($product_id, "wpc-metas", $wpc_metas);
                if (!$result)
                    return $result;
            endwhile;
        endif;
    }

    /**
     * Runs the general options updates if needed
     * @param type $general_options
     * @return bool
     */
    private function run_general_options_migration($general_options) {
        $general_options['canvas-w'] = $general_options['wpc-canvas-w'];
        unset($general_options['wpc-canvas-w']);

        $general_options['canvas-h'] = $general_options['wpc-canvas-h'];
        unset($general_options['wpc-canvas-h']);
        $result = update_option("wpc-general-options", $general_options);
        return $result;
    }

    /**
     * Runs the database upgrade for V3.0
     * @return boolean
     */
    private function update_db_for_v3_0() {
        $this->run_products_metas_migration_if_needed();
        $general_options = get_option('wpc-general-options');
        if (isset($general_options['wpc-canvas-w']) || isset($general_options['wpc-canvas-h'])) {
            $result = $this->run_general_options_migration($general_options);
            return $result;
        }
        return true;
    }

    private function get_part_datas($wpc_metas, $parts, $variation_id) {
        $global_array = $wpc_metas;
        if (is_array($parts)) {
            foreach ($parts as $part) {
                $part_key = sanitize_title($part);
                $global_array[$variation_id]['parts'][$part_key]['bg-inc'] = get_post_meta($variation_id, "wpc_$part_key", true);
                // delete_post_meta($variation_id,"wpc_$part_key");
                $global_array[$variation_id]['parts'][$part_key]['bg-not-inc'] = get_post_meta($variation_id, "wpc_bg-$part_key", true);
                //delete_post_meta($variation_id,"wpc_bg-$part_key");
                $global_array[$variation_id]['parts'][$part_key]['ov']['img'] = get_post_meta($variation_id, "wpc_ov-$part_key", true);
                //delete_post_meta($variation_id,"wpc_ov-$part_key");
                $global_array[$variation_id]['parts'][$part_key]['ov']['inc'] = get_post_meta($variation_id, "wpc_ovni-$part_key", true);
            }
        }
        return $global_array;
    }

    /**
     * Outputs the settings fields
     * @param array $options Settings to output
     */
    public static function admin_fields($options,$metas=false) {
        foreach ($options as $value) {
            if (!isset($value['type']))
                continue;
            if (!isset($value['id']))
                $value['id'] = '';
            if (!isset($value['name']))
                $value['name'] = $value['id'];
            if (!isset($value['title']))
                $value['title'] = isset($value['name']) ? $value['name'] : '';
            if (!isset($value['class']))
                $value['class'] = '';
            if (!isset($value['css']))
                $value['css'] = '';
            if (!isset($value['default']))
                $value['default'] = '';
            if (!isset($value['desc']))
                $value['desc'] = '';
            if (!isset($value['desc_tip']))
                $value['desc_tip'] = false;

            // Custom attribute handling
            $custom_attributes = array();

            if (!empty($value['custom_attributes']) && is_array($value['custom_attributes']))
                foreach ($value['custom_attributes'] as $attribute => $attribute_value)
                    $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';

            // Description handling
            if ($value['desc_tip'] === true) {
                $description = '';
                $tip = $value['desc'];
            } elseif (!empty($value['desc_tip'])) {
                $description = $value['desc'];
                $tip = $value['desc_tip'];
            } elseif (!empty($value['desc'])) {
                $description = $value['desc'];
                $tip = '';
            } else {
                $description = $tip = '';
            }

            if ($description && in_array($value['type'], array('textarea', 'radio'))) {
                $description = '<p style="margin-top:0">' . wp_kses_post($description) . '</p>';
            } elseif ($description && in_array($value['type'], array('checkbox'))) {
                $description = wp_kses_post($description);
            } elseif ($description) {
                $description = '<span class="description">' . wp_kses_post($description) . '</span>';
            }

            if ($tip && in_array($value['type'], array('checkbox'))) {

                $tip = '<p class="description">' . $tip . '</p>';
            } elseif ($tip) {

                $tip = '<img class="help_tip" data-tip="' . esc_attr($tip) . '" src="' . WPD_URL . 'admin/images/help.png" height="16" width="16" />';
            }

             // Metas attributes
            if($metas){
                    $wpc_metas=get_post_meta(get_the_ID(), 'wpc-metas', true);
                    $variations_metas=WPD_Admin::get_proper_value($wpc_metas,$value['data-id'], array());
                    $wpc_output_product_settings=WPD_Admin::get_proper_value($variations_metas,$value['data-option'], array());
                    $option_value=WPD_Admin::get_proper_value($wpc_output_product_settings,$value['data-field'],$value['default']);
            }
            else
                    $option_value = self::get_option($value['id'], $value['default']);
                  
                        
            // Switch based on type
            switch ($value['type']) {

                // Section Titles
                case 'title':
                    if (!empty($value['title'])) {
                        echo '<h3>' . esc_html($value['title']) . '</h3>';
                    }
                    if (!empty($value['desc'])) {
                        echo wpautop(wptexturize(wp_kses_post($value['desc'])));
                    }
                    echo '<table class="form-table">' . "\n\n";
                    if (!empty($value['id'])) {
                        do_action('wpc_settings_' . sanitize_title($value['id']));
                    }
                    break;

                // Section Ends
                case 'sectionend':
                    if (!empty($value['id'])) {
                        do_action('wpc_settings_' . sanitize_title($value['id']) . '_end');
                    }
                    echo '</table>';
                    if (!empty($value['id'])) {
                        do_action('wpc_settings_' . sanitize_title($value['id']) . '_after');
                    }
                    break;

                // Standard text inputs and subtypes like 'number'
                case 'text':
                case 'email':
                case 'number':
                case 'color' :
                case 'password' :

                    $type = $value['type'];
                    $class = '';
//                    $option_value = self::wpc_get_option($value['id'], $value['default']);

                    if ($value['type'] == 'color') {
                        $type = 'text';
                        $value['class'] .= 'colorpick';
                        $description .= '<div id="colorPickerDiv_' . esc_attr($value['id']) . '" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>';
                    }
                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
                    <?php echo $tip; ?>
                        </th>
                        <td class="forminp forminp-<?php echo sanitize_title($value['type']) ?>">
                            <input
                                name="<?php echo esc_attr($value['name']); ?>"
                                id="<?php echo esc_attr($value['id']); ?>"
                                type="<?php echo esc_attr($type); ?>"
                                style="<?php echo esc_attr($value['css']); ?>"
                                value="<?php echo esc_attr($option_value); ?>"
                                class="<?php echo esc_attr($value['class']); ?>"
                    <?php echo implode(' ', $custom_attributes); ?>
                                /> <?php echo $description; ?>
                        </td>
                    </tr><?php
                    break;

                // Textarea
                case 'textarea':

//                    $option_value = wpc_get_option($value['id'], $value['default']);
                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
                    <?php echo $tip; ?>
                        </th>
                        <td class="forminp forminp-<?php echo sanitize_title($value['type']) ?>">
                    <?php echo $description; ?>

                            <textarea
                                name="<?php echo esc_attr($value['id']); ?>"
                                id="<?php echo esc_attr($value['id']); ?>"
                                style="<?php echo esc_attr($value['css']); ?>"
                                class="<?php echo esc_attr($value['class']); ?>"
                    <?php echo implode(' ', $custom_attributes); ?>
                                ><?php echo esc_textarea($option_value); ?></textarea>
                        </td>
                    </tr><?php
                    break;

                // Select boxes
                case 'select' :
                case 'multiselect' :

//                    $option_value = self::wpc_get_option($value['id'], $value['default']);
//                    if($metas)
//                        $option_value=WPD_Admin::get_proper_value($wpc_output_product_settings,$value['data-field'],$value['default']);
                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
                    <?php echo $tip; ?>
                        </th>
                        <td class="forminp forminp-<?php echo sanitize_title($value['type']) ?>">
                            <select
                                name="<?php echo esc_attr($value['id']); ?><?php if ($value['type'] == 'multiselect') echo '[]'; ?>"
                                id="<?php echo esc_attr($value['id']); ?>"
                                style="<?php echo esc_attr($value['css']); ?>"
                                class="<?php echo esc_attr($value['class']); ?>"
                            <?php echo implode(' ', $custom_attributes); ?>
                    <?php if ($value['type'] == 'multiselect') echo 'multiple="multiple"'; ?>
                                >
                    <?php
                    foreach ($value['options'] as $key => $val) {
                        ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php
                                    if (is_array($option_value))
                                        selected(in_array($key, $option_value), true);
                                    else
                                        selected($option_value, $key);
                                    ?>><?php echo $val ?></option>
                        <?php
                    }
                    ?>
                            </select> <?php echo $description; ?>
                        </td>
                    </tr><?php
                    break;
                case 'groupedselect' :

//                    $option_value = self::wpc_get_option($value['id'], $value['default']);
//                     if($metas)
//                        $option_value=WPD_Admin::get_proper_value($wpc_output_product_settings,$value['data-field'],$value['default']);
                           
                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
                    <?php echo $tip; ?>
                        </th>
                        <td class="forminp forminp-<?php echo sanitize_title($value['type']) ?>">
                            <select
                                name="<?php echo esc_attr($value['id']); ?><?php if ($value['type'] == 'multiselect') echo '[]'; ?>"
                                id="<?php echo esc_attr($value['id']); ?>"
                                style="<?php echo esc_attr($value['css']); ?>"
                                class="<?php echo esc_attr($value['class']); ?>"
                            <?php echo implode(' ', $custom_attributes); ?>
                    <?php if ($value['type'] == 'multiselect') echo 'multiple="multiple"'; ?>
                                >
                    <?php
                    foreach ($value['options'] as $group=>$group_values) {
                        ?><optgroup label="<?php echo $group;?>"><?php
                            foreach ($group_values as $key => $val) {
                            ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php
                                        if (is_array($option_value))
                                            selected(in_array($key, $option_value), true);
                                        else
                                            selected($option_value, $key);
                                        ?>><?php echo $val ?></option>
                            <?php
                            }
                        ?></optgroup><?php
                    }
                    ?>
                            </select> <?php echo $description; ?>
                        </td>
                    </tr><?php
                    break;

                // Radio inputs
                case 'radio' :

//                    $option_value = self::wpc_get_option($value['id'], $value['default']);
//                    if($metas)
//                        $option_value=WPD_Admin::get_proper_value($wpc_output_product_settings,$value['data-field'],"");
                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
                    <?php echo $tip; ?>
                        </th>
                        <td class="forminp forminp-<?php echo sanitize_title($value['type']) ?>">
                            <fieldset>
                                <?php echo $description; ?>
                                <ul>
                                    <?php
                                    foreach ($value['options'] as $key => $val) {
                                        ?>
                                        <li>
                                            <label><input
                                                    name="<?php echo esc_attr($value['id']); ?>"
                                                    value="<?php echo $key; ?>"
                                                    type="radio"
                                                    style="<?php echo esc_attr($value['css']); ?>"
                                                    class="<?php echo esc_attr($value['class']); ?>"
                                            <?php echo implode(' ', $custom_attributes); ?>
                                            <?php checked($key, $option_value); ?>
                                                    /> <?php echo $val ?></label>
                                        </li>
                        <?php
                    }
                    ?>
                                </ul>
                            </fieldset>
                        </td>
                    </tr><?php
                    break;

                // Checkbox input
                case 'checkbox' :
                    $visbility_class = array();

                    if (!isset($value['hide_if_checked'])) {
                        $value['hide_if_checked'] = false;
                    }
                    if (!isset($value['show_if_checked'])) {
                        $value['show_if_checked'] = false;
                    }
                    if ($value['hide_if_checked'] == 'yes' || $value['show_if_checked'] == 'yes') {
                        $visbility_class[] = 'hidden_option';
                    }
                    if ($value['hide_if_checked'] == 'option') {
                        $visbility_class[] = 'hide_options_if_checked';
                    }
                    if ($value['show_if_checked'] == 'option') {
                        $visbility_class[] = 'show_options_if_checked';
                    }

                    if (!isset($value['checkboxgroup']) || 'start' == $value['checkboxgroup']) {
                        ?>
                        <tr valign="top" class="<?php echo esc_attr(implode(' ', $visbility_class)); ?>">
                            <th scope="row" class="titledesc"><?php echo esc_html($value['title']) ?></th>
                            <td class="forminp forminp-checkbox">
                                <fieldset>
                        <?php
                    } else {
                        ?>
                                    <fieldset class="<?php echo esc_attr(implode(' ', $visbility_class)); ?>">
                        <?php
                    }

                    if (!empty($value['title'])) {
                        ?>
                                        <legend class="screen-reader-text"><span><?php echo esc_html($value['title']) ?></span></legend>
                        <?php
                    }
                    ?>
                                    <label for="<?php echo $value['id'] ?>">
                                        <input
                                            name="<?php echo esc_attr($value['id']); ?>"
                                            id="<?php echo esc_attr($value['id']); ?>"
                                            type="checkbox"
                                            value="1"
                    <?php checked($option_value, 'yes'); ?>
                    <?php echo implode(' ', $custom_attributes); ?>
                                            /> <?php echo $description ?>
                                    </label> <?php echo $tip; ?>
                    <?php
                    if (!isset($value['checkboxgroup']) || 'end' == $value['checkboxgroup']) {
                        ?>
                                    </fieldset>
                            </td>
                        </tr>
                        <?php
                    } else {
                        ?>
                        </fieldset>
                                    <?php
                                }
                                break;

                            // Image width settings
                            case 'image_width' :

                                $width = $this->wpc_get_option($value['id'] . '[width]', $value['default']['width']);
                                $height = $this->wpc_get_option($value['id'] . '[height]', $value['default']['height']);
                                $crop = checked(1, $this->wpc_get_option($value['id'] . '[crop]', $value['default']['crop']), false);
                                ?><tr valign="top">
                        <th scope="row" class="titledesc"><?php echo esc_html($value['title']) ?> <?php echo $tip; ?></th>
                        <td class="forminp image_width_settings">

                            <input name="<?php echo esc_attr($value['id']); ?>[width]" id="<?php echo esc_attr($value['id']); ?>-width" type="text" size="3" value="<?php echo $width; ?>" /> &times; <input name="<?php echo esc_attr($value['id']); ?>[height]" id="<?php echo esc_attr($value['id']); ?>-height" type="text" size="3" value="<?php echo $height; ?>" />px

                            <label><input name="<?php echo esc_attr($value['id']); ?>[crop]" id="<?php echo esc_attr($value['id']); ?>-crop" type="checkbox" <?php echo $crop; ?> /> <?php _e('Hard Crop?', 'wpd'); ?></label>

                        </td>
                    </tr><?php
                                            break;

                                        // Single page selects
                                        case 'single_select_page' :

                                            $args = array('name' => $value['id'],
                                                'id' => $value['id'],
                                                'sort_column' => 'menu_order',
                                                'sort_order' => 'ASC',
                                                'show_option_none' => ' ',
                                                'class' => $value['class'],
                                                'echo' => false,
                                                'selected' => absint(self::get_option($value['id']))
                                            );

                                            if (isset($value['args']))
                                                $args = wp_parse_args($value['args'], $args);
                                            ?><tr valign="top" class="single_select_page">
                        <th scope="row" class="titledesc"><?php echo esc_html($value['title']) ?> <?php echo $tip; ?></th>
                        <td class="forminp">
                    <?php echo str_replace(' id=', " data-placeholder='" . __('Select a page&hellip;', 'wpd') . "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages($args)); ?> <?php echo $description; ?>
                        </td>
                    </tr><?php
                    break;

                /* // Single country selects
                  case 'single_select_country' :
                  $country_setting = (string) $this->wpc_get_option( $value['id'] );
                  $countries       = WC()->countries->countries;

                  if ( strstr( $country_setting, ':' ) ) {
                  $country_setting = explode( ':', $country_setting );
                  $country         = current( $country_setting );
                  $state           = end( $country_setting );
                  } else {
                  $country = $country_setting;
                  $state   = '*';
                  }
                  ?><tr valign="top">
                  <th scope="row" class="titledesc">
                  <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                  <?php echo $tip; ?>
                  </th>
                  <td class="forminp"><select name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" data-placeholder="<?php _e( 'Choose a country&hellip;', 'wpd' ); ?>" title="Country" class="chosen_select">
                  <?php WC()->countries->country_dropdown_options( $country, $state ); ?>
                  </select> <?php echo $description; ?>
                  </td>
                  </tr><?php
                  break;

                  // Country multiselects
                  case 'multi_select_countries' :

                  $selections = (array) $this->wpc_get_option( $value['id'] );

                  if ( ! empty( $value['options'] ) )
                  $countries = $value['options'];
                  else
                  $countries = WC()->countries->countries;

                  asort( $countries );
                  ?><tr valign="top">
                  <th scope="row" class="titledesc">
                  <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                  <?php echo $tip; ?>
                  </th>
                  <td class="forminp">
                  <select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" style="width:350px" data-placeholder="<?php _e( 'Choose countries&hellip;', 'wpd' ); ?>" title="Country" class="chosen_select">
                  <?php
                  if ( $countries )
                  foreach ( $countries as $key => $val )
                  echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $selections ), true, false ).'>' . $val . '</option>';
                  ?>
                  </select> <?php if ( $description ) echo $description; ?> </br><a class="select_all button" href="#"><?php _e( 'Select all', 'wpd' ); ?></a> <a class="select_none button" href="#"><?php _e( 'Select none', 'wpd' ); ?></a>
                  </td>
                  </tr><?php
                  break; */

                // Default: run an action
                default:
                    do_action('wpc_admin_field_' . $value['type'], $value);
                    break;
            }
        }
    }

    /**
     * Retrieve an option while building the settings page
     * @param type $option_name
     * @param type $default
     * @return type
     */
    private static function get_option($option_name, $default = '') {
        // Array value
        if (strstr($option_name, '[')) {

            parse_str($option_name, $option_array);

            // Option name is first key
            $option_name = current(array_keys($option_array));

            // Get value
            $option_values = get_option($option_name, '');

            $key = key($option_array[$option_name]);

            if (isset($option_values[$key]))
                $option_value = $option_values[$key];
            else
                $option_value = null;

            // Single value
        } else {
            $option_value = get_option($option_name, null);
        }

        if (is_array($option_value))
            $option_value = array_map('stripslashes', $option_value);
        elseif (!is_null($option_value))
            $option_value = stripslashes($option_value);

        return $option_value === null ? $default : $option_value;
    }

    function wpc_add_custom_mime_types($mimes) {
        return array_merge($mimes, array(
            'svg' => 'image/svg+xml'
        ));
    }

    /**
     * Runs the products options migrations if needed
     * @return bool
     */
    function run_products_options_migration_if_needed() {
        $page_id = get_option('wpc_page_id');
        //if(!(!get_option('wpc_page_id')))
        $result = true;
        if ($page_id) {
            $general_options = get_option('wpc-general-options');
            $upload_options = get_option('wpc-upload-options');
            $colors_options = get_option('wpc-colors-options');
            $output_options = get_option('wpc-output-options');
            $general_options_datas = array('wpc_page_id', 'wpc-content-filter', 'wpc-parts-position-cart', 'wpc-download-btn', 'wpc-user-account-download-btn', 'wpc-send-design-mail',
                'wpc-preview-btn', 'wpc-save-btn', 'wpc-cart-btn', 'wpc-redirect-after-cart');
            $upload_options_datas = array("wpc-min-upload-width", "wpc-min-upload-height", "wpc-upl-extensions", "wpc-custom-designs-extensions", "wpc-uploader");
            $output_options_datas = array("wpc-outputpdf-img-number", "wpc-outputpdf-img-col-number",
                "wpc-generate-pdf", "wpc-generate-zip");

            if (empty($general_options)) {
                foreach ($general_options_datas as $general_option_data) {
                    $general_options[$general_option_data] = get_option($general_option_data);
                    delete_option($general_option_data);
                }
                $result = update_option("wpc-general-options", $general_options);
            }
            if ($result && empty($upload_options)) {
                foreach ($upload_options_datas as $upload_option_data) {
                    $upload_options[$upload_option_data] = get_option($upload_option_data);
                    delete_option($upload_option_data);
                }
                $result = update_option("wpc-upload-options", $upload_options);
            }
            if ($result && empty($colors_options)) {
                $colors_options['wpc-svg-colorization'] = get_option("wpc-svg-colorization");
                delete_option("wpc-svg-colorization");
                $result = update_option("wpc-colors-options", $colors_options);
            }
            if ($result && empty($output_options)) {
                foreach ($output_options_datas as $output_option_data) {
                    $output_options[$output_option_data] = get_option($output_option_data);
                    delete_option($output_option_data);
                }
                update_option("wpc-output-options", $output_options);
            }
            if ($result)
                $this->init_globals();
        }
        return $result;
    }

    /**
     * Runs the database upgrade for V2.0
     * @return boolean
     */
    private function update_db_for_v2_0() {
        $message = $this->run_products_options_migration_if_needed();
        return $message;
    }
    
    function get_ad_messages()
    {
        global $pagenow;
        $messages=array(
            "Create unique designs your clients can customize and buy.",
            "Configure the canvas dimensions.",
            "Let your clients save and re-use their work anytime.",
            "Define the minimum uploads dimensions.",
            "Import and edit SVG files right into the canvas.",
            "Define your own color palette.",
            "Let your clients extract and use pictures from social networks.",
            "Let your clients upload and send you their own designs.",
            "Define a price for each element used in a design.",
            "Generate larger files at your convenience."
        );
        $random_message_key=  array_rand($messages);
        if($pagenow=="post-new.php"
            ||$pagenow=="post.php"
            ||(isset($_GET["post_type"])&&$_GET["post_type"]=="wpc-cliparts")
            ||(isset($_GET["post_type"])&&$_GET["post_type"]=="product")
            ||(isset($_GET["page"])&&$_GET["page"]=="wpc-manage-parts")
            ||(isset($_GET["page"])&&$_GET["page"]=="wpc-manage-settings")
        )
        {
            echo '<div class="wpc-info">
               <p>'.$messages[$random_message_key].' <a href="'. admin_url("admin.php?page=wpd-advanced-features").'" class="button">Click here to unlock that feature</a></p>
            </div>';
        }

    }

}
