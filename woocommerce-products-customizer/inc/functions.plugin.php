<?php
// Function called at the installation of the plugin
function wpc_install() {

}
// Function called at the uninstallation of the plugin
function wpc_uninstall() {

}

add_action('admin_head', 'get_js_variables');


global $pagenow;
//var_dump($pagenow);
//We deactivate unecessary scripts loading to limit interferences
if($pagenow=="post-new.php"
        ||$pagenow=="post.php"
        ||(isset($_GET["post_type"])&&$_GET["post_type"]=="wpc-cliparts")
        ||(isset($_GET["post_type"])&&$_GET["post_type"]=="product")
        ||(isset($_GET["page"])&&$_GET["page"]=="wpc-manage-parts")
        ||(isset($_GET["page"])&&$_GET["page"]=="wpc-advanced-features")
        ||(isset($_GET["page"])&&$_GET["page"]=="wpc-manage-settings")
  )
{
    add_action( 'admin_enqueue_scripts', 'add_customizer_admin_scripts' );
    add_action( 'admin_enqueue_scripts', 'orion_scripts' );
}

function add_customizer_admin_scripts() {
    wp_enqueue_media();
    wp_enqueue_script( 'wpc_admin_js', WPC_URL . 'ressources/js/admin-scripts.js' );
    wp_register_style('wpc-admin-css', WPC_URL . 'ressources/css/wpc-back.css');
    wp_enqueue_style('wpc-admin-css');
    
//    wp_register_style('modal-css', WPC_URL . 'ressources/js/modal/modal.css');
//    wp_enqueue_style('modal-css');
//    wp_register_script('modal-js', WPC_URL . 'ressources/js/modal/modal.js');
//    wp_enqueue_script('modal-js', array('jquery'));
}

add_action('admin_menu', 'add_woo_parts_submenu');
function add_woo_parts_submenu()
{
    $icon=WPC_URL . 'ressources/images/wpc-dashicon.png';
    add_menu_page( 'WPC', 'WPC', 'manage_product_terms', 'wpc-manage-dashboard', 'get_wpc_parts_page', $icon);
    add_submenu_page( 'wpc-manage-dashboard', __('Parts', 'wpc' ), __( 'Parts', 'wpc' ), 'manage_product_terms', 'wpc-manage-parts', 'get_wpc_parts_page');
    add_submenu_page( 'wpc-manage-dashboard', __('Fonts', 'wpc' ), __( 'Fonts', 'wpc' ), 'manage_product_terms', 'wpc-manage-fonts', 'get_wpc_fonts_page');
    add_submenu_page( 'wpc-manage-dashboard', __('Cliparts', 'wpc' ), __( 'Cliparts', 'wpc' ), 'manage_product_terms', 'edit.php?post_type=wpc-cliparts', false);
    add_submenu_page( 'wpc-manage-dashboard', __('Settings', 'wpc' ), __( 'Settings', 'wpc' ), 'manage_product_terms', 'wpc-manage-settings', 'get_wpc_settings_page');
    add_submenu_page( 'wpc-manage-dashboard', __('Advanced features', 'wpc' ), __( 'Advanced features', 'wpc' ), 'manage_product_terms', 'wpc-advanced-features', 'get_wpc_advanced_features_page');
}

function get_wpc_parts_page()
{
    include_once( WPC_DIR.'/inc/woocommerce-add-parts.php' );
    woocommerce_add_parts();
}

function get_wpc_fonts_page()
{
    include_once( WPC_DIR.'/inc/woocommerce-add-fonts.php' );
    woocommerce_add_fonts();
}

function get_wpc_settings_page()
{
    ?>
    <div id="wpc-settings">
        <div class="wrap">
            <h2>WPC Settings</h2>
        <?php
        if(isset($_POST))
            save_wpc_tab_options();
        ?>
        <form method="POST">
            <?php
            get_wpc_options();
            $wpc_palette_type=  get_option("wpc-color-palette");
            $palette_style="";
            if($wpc_palette_type!="custom")
                $palette_style="style='display:none;'";
            $palette= get_option("wpc-custom-palette");
            ?>
            <table class="form-table" id="wpc-color-palette" <?php echo $palette_style;?>>
                <tbody>
                    <tr valign="top">
                    <th scope="row" class="titledesc">
                            
                    </th>
                    <td class="forminp">
                        <div class="wpc-colors">
                            <?php
                            if(is_array($palette))
                            {
                                foreach ($palette as $color)
                                {
                                    ?>
                                    <div>
                                        <input type="text" name="wpc-custom-palette[]"style="background-color: <?php echo $color;?>" value="<?php echo $color;?>">
                                        <button class="button wpc-remove-color">Remove</button>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            
                        </div>
                        <button class="button mg-top-10" id="wpc-add-color">Add color</button>
                    </td>
                    </tr>
                </tbody>
            </table>
            
            
            <input type="submit" value="<?php _e("Save","wpc");?>" class="button">
        </form>
        </div>
    </div>
    <?php
}

function get_wpc_advanced_features_page()
{
    ?>
    <div id="wpc-advanced-features">
        <div class="wrap">
            <h2><a href="http://codecanyon.net/item/woocommerce-products-designer/7818029" target="blank">Need more features? Let's go pro!</a></h2>
            <div>
                <a href="http://codecanyon.net/item/woocommerce-products-designer/7818029" target="blank">
                <img src="<?php echo WPC_URL."ressources/images/go-pro.png"?>">
                </a>
            </div>
        </div>
    </div>
    <?php
}
function wpc_get_woo_version_number() {
        // If get_plugins() isn't available, require it
	if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
        // Create the plugins folder and file variables
	$plugin_folder = get_plugins( '/' . 'woocommerce' );
	$plugin_file = 'woocommerce.php';
	
	// If the plugin version number is set, return it 
	if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
		return $plugin_folder[$plugin_file]['Version'];

	} else {
	// Otherwise return null
		return NULL;
	}
}

//add_filter( 'woocommerce_settings_tabs_array', 'add_wpc_settings_tab', 999 );
//function add_wpc_settings_tab($tabs) {
//    $tabs ['wpc'] = __( 'Customizer', 'wpc' );
//    return $tabs;
//}

//add_action("woocommerce_settings_tabs_wpc", "set_wpc_options");
function get_wpc_options()
{
    $options=array();
    
     $customizer_page=array(
                'title' => __( 'Customizer Page', 'wpc' ),
                'desc' 		=> __( 'This setting allows the plugin to locate the page where customizations are made.', 'wpc' ),
                'id' 		=> 'wpc_page_id',
                'type' 		=> 'single_select_page',
                'default'	=> '',
                'class'		=> 'chosen_select_nostd',
                'css' 		=> 'min-width:300px;',
                'desc_tip'	=>  true,
        );
     
     
     
    $customizer_cart_display=array(
               'title' => __( 'Parts position in cart', 'wpc' ),
               'id' 		=> 'wpc-parts-position-cart',
               'default'	=> 'thumbnail',
               'type' 		=> 'radio',
               'desc_tip'	=>  __( 'This option allows you to set where to show your customized products parts on the cart page', 'wpc' ),
               'options'	=> array(
                       'thumbnail' => __( 'Thumbnail column', 'wpc' ),
                       'name' => __( 'Name column', 'wpc' )
               )
        );
     $download_button=array(
		'title' => __( 'Download design', 'wpc' ),
		'id' 		=> 'wpc-download-btn',
		'default'	=> '1',
		'type' 		=> 'radio',
		'desc_tip'	=>  __( 'This option allows you to show/hide the download button on the customization page', 'wpc' ),
		'options'	=> array(
			'1' => __( 'Yes', 'wpc' ),
			'0' => __( 'No', 'wpc' )
		)
         );
     $user_account_download_button=array(
		'title' => __( 'Download design from user account page', 'wpc' ),
		'id' 		=> 'wpc-user-account-download-btn',
		'default'	=> '1',
		'type' 		=> 'radio',
		'desc_tip'	=>  __( 'This option allows you to show/hide the download button on user account page', 'wpc' ),
		'options'	=> array(
			'1' => __( 'Yes', 'wpc' ),
			'0' => __( 'No', 'wpc' )
		)
         );
     
     $preview_button=array(
		'title' => __( 'Preview design', 'wpc' ),
		'id' 		=> 'wpc-preview-btn',
		'default'	=> '1',
		'type' 		=> 'radio',
		'desc_tip'	=>  __( 'This option allows you to show/hide the preview button on the customization page', 'wpc' ),
		'options'	=> array(
			'1' => __( 'Yes', 'wpc' ),
			'0' => __( 'No', 'wpc' )
		)
         );
     
     $cart_button=array(
		'title' => __( 'Add to cart', 'wpc' ),
		'id' 		=> 'wpc-cart-btn',
		'default'	=> '1',
		'type' 		=> 'radio',
		'desc_tip'	=>  __( 'This option allows you to show/hide the cart button on the customization page', 'wpc' ),
		'options'	=> array(
			'1' => __( 'Yes', 'wpc' ),
			'0' => __( 'No', 'wpc' )
		)
         );
     
     $uploader_type=array(
		'title' => __( 'File upload script', 'wpc' ),
		'id' 		=> 'wpc-uploader',
		'default'	=> 'thumbnail',
		'type' 		=> 'radio',
		'desc_tip'	=>  __( 'This option allows you to set which file upload script you would like to use', 'wpc' ),
		'options'	=> array(
			'custom' => __( 'Custom with graphical enhancements', 'wpc' ),
			'native' => __( 'Normal', 'wpc' )
		)
         );
     
     
     $upl_extensions=array(
		'title' => __( 'Allowed uploads extensions', 'wpc' ),
		'id' 		=> 'wpc-upl-extensions',
		'default'	=> array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'),
		'type' 		=> 'multiselect',
		'desc_tip'	=>  __( 'Allowed extensions for uploads', 'wpc' ),
		'options'	=> array(
			'jpg' => __( 'jpg', 'wpc' ),
			'jpeg' => __( 'jpeg', 'wpc' ),
                        'png' => __( 'png', 'wpc' ),
                        'gif' => __( 'gif', 'wpc' ),
                        'bmp' => __( 'bmp', 'wpc' ),
                        'svg' => __( 'svg', 'wpc' )
		)
     );
     $output_loop_delay=array(
                'title' => __( 'Output loop delay (milliseconds)', 'wpc' ),
                'desc' 		=> __( 'Delay to go through each part. Should be increased when the plugin tries to handle high resolution files.', 'wpc' ),
                'id' 		=> 'wpc-output-loop-delay',
                'type' 		=> 'text',
                'default'	=> '1000',
                'class'		=> 'chosen_select_nostd',
                'css' 		=> 'min-width:300px;',
                'desc_tip'	=>  true,
        );
     $output_formats=array(
                        array(
				'desc' 	=> __( 'PDF', 'wpc' ),
				'id' 		=> 'wpc-generate-pdf',
//				'default'	=> 'yes',
				'type' 		=> 'checkbox',
				'checkboxgroup'		=> '',
			),
                        array(
				'desc' 	=> __( 'Zip output folder', 'wpc' ),
				'id' 		=> 'wpc-generate-zip',
//				'default'	=> 'yes',
				'type' 		=> 'checkbox',
				'checkboxgroup'		=> 'end'
			),
         );
    $general_options_begin=array( 'title' => __( 'General Settings', 'wpc' ), 
                           'type' => 'title',
                            'id' => 'wpc_page_options'
                        ); 
     
     $general_options_end=array( 'type' => 'sectionend', 
                         'id' => 'wpc_page_options' 
                       );
     $upload_settings_begin=array( 'title' => __( 'Uploads', 'wpc' ), 
                           'type' => 'title',
                           'id' => 'wpc_uploads'
                        );
     $upload_settings_end=array(
                           'type' => 'sectionend',
                           'id' => 'wpc_uploads'
                        );
     
     $output_options_begin=array( 'title' => __( 'Output', 'wpc' ), 
                           'type' => 'title',
                            'id' => 'wpc_output'
                        ); 
     
     $output_options_end=array( 'type' => 'sectionend', 
                         'id' => 'wpc_output' 
                       );
     
     
    array_push($options, $general_options_begin);
    array_push($options, $customizer_page);
    array_push($options, $customizer_cart_display);
    array_push($options, $preview_button);
    array_push($options, $download_button);
    array_push($options, $user_account_download_button);
    array_push($options, $cart_button);
    array_push($options, $general_options_end);
    array_push($options, $upload_settings_begin);
    array_push($options, $uploader_type);
    array_push($options, $upl_extensions);
    array_push($options, $upload_settings_end);
    array_push($options, $output_options_begin);
    array_push($options, $output_loop_delay);
    $options=array_merge($options, $output_formats);
    array_push($options, $output_options_end);
    
    woocommerce_admin_fields( $options );
}

//add_action( 'admin_init', 'save_wpc_tab_options' );
function save_wpc_tab_options()
{
    if(isset($_POST)&&!empty($_POST))
    {
//        var_dump($_POST);
        $var_names_arr=array("wpc_page_id",
                            "wpc-upl-extensions",
                            "wpc-output-loop-delay", "wpc-generate-pdf", "wpc-generate-zip",
                            "wpc-uploader", "wpc-parts-position-cart",
                            "wpc-download-btn", "wpc-user-account-download-btn", "wpc-preview-btn", "wpc-save-btn", "wpc-cart-btn");
        $checkboxes=array("wpc-generate-pdf","wpc-generate-zip");
        foreach ($var_names_arr as $var_name)
        {
            //Special treatment for checkboxes
            if(in_array($var_name, $checkboxes))
            {
                if(isset($_POST[$var_name]))
                    update_option($var_name, "yes");
                else
                    update_option($var_name, "no");
            }
            else
            {
                if(isset($_POST[$var_name]))
                    update_option($var_name, $_POST[$var_name]);
                else
                    delete_option($var_name);
            }
        }
        ?>
        <div id="message" class="updated below-h2"><p><?php echo __("Options saved","wpc");?></p></div>
        <?php
    }
}

add_action('admin_notices', 'notify_customization_page_missing');
function notify_customization_page_missing(){
     $wpc_page_id = get_option( 'wpc_page_id' );
     $settings_url=get_bloginfo("url").'/wp-admin/admin.php?page=wpc-manage-settings';
     if(empty($wpc_page_id))
        echo '<div class="error">
           <p><b>Product Customizer: </b>The customizer page is not defined. Please configure it in <a href="'.$settings_url.'">woocommerce page settings</a>: .</p>
        </div>';
}

add_action('admin_init', 'register_product_assets', 1);
function register_product_assets() {
    add_meta_box('customizable-product', __('Customizable Product'), 'add_customizable_meta_box', 'product', 'side', 'default');
}   

function add_customizable_meta_box($product) {
    $customizable = get_post_meta($product->ID, 'customizable-product', true);
    $design_from_blank = get_post_meta($product->ID, 'wpc-design-from-blank', true);
    if ($customizable == 1)
        $is_checked = "checked='checked'";
    else
        $is_checked = "";
    echo "<label for='customizable-product'>";
    echo "<input type='checkbox' name='customizable-product' id='customizable-product' value='1' $is_checked />Customizable product</label><br>";
    
    if ($design_from_blank == 1)
        $is_checked = "checked='checked'";
    else
        $is_checked = "";
    echo "<label for='wpc-design-from-blank'>";
    echo "<input type='checkbox' name='wpc-design-from-blank' id='wpc-design-from-blank' value='1' $is_checked />The clients can design from blank</label><br>";
}

add_action( 'admin_notices', 'get_customizable_product_errors' );
function get_customizable_product_errors() {
    $post_type=  get_post_type();
    if($post_type=="product")
    {
        $product_id=  get_the_ID();
        $is_customizable=  get_post_meta($product_id,"customizable-product",true);  
        if($is_customizable)
        {
            $parts=  get_option("wpc-parts");
            if(empty($parts))
            {
                delete_post_meta($product_id, "customizable-product");
                ?>
                <div class="error">
                    <p><?php _e( 'Error: empty product parts list. At least one part is required to create a customizable product.', 'wpc' ); ?></p>
                </div>
                <?php
            }
            else if(!has_part($product_id))
            {
                delete_post_meta($product_id, "customizable-product");
                ?>
                <div class="error">
                    <p><?php _e( 'Error: No active part defined for this product. A customizable product should have at least one part defined.', 'wpc' ); ?></p>
                </div>
                <?php
            }
        
        }
    }
}

add_action('save_post_product', 'save_customizable_meta');
function save_customizable_meta($product_id) {
    $var_names_arr=array(
        "customizable-product","wpc-upload-design", "wpc-design-from-blank",
        "clip_width","clip_height", "clip_x","clip_y", "clip_radius", "clip_radius_rect", "clip_type", "clip_b_color", "wpc-pricing", "wpc-outputpdf-img-number-pp", "wpc-outputpdf-img-col-pp",
        "wpc-canvas-w", "wpc-canvas-h");
//    var_dump($_POST["wpc-pricing"]);
    foreach ($var_names_arr as $var_name)
    {
        if (isset($_POST[$var_name]))
            update_post_meta($product_id, $var_name, $_POST[$var_name]);
        else
            delete_post_meta($product_id, $var_name);
    }
    
}

 add_action("save_post_product","save_wpc_variations_attr",10,1);
 function save_wpc_variations_attr($post_id)
 {
     if(isset($_POST)&&!empty($_POST))
     {
//         var_dump($_POST);
//         exit();
        $parts=  get_option("wpc-parts");
        if(empty($parts))
            return;
        foreach ($parts as $part) {
            $attribut_key=  sanitize_title($part);
            if(isset($_POST["wpc_$attribut_key"]))
            {
                $variable_custom_field = $_POST["wpc_$attribut_key"];
                if(is_array($variable_custom_field))
                {
                    foreach ($variable_custom_field as $variation_id=>$part_img_id)
                    {
                        if ( isset( $part_img_id ) )
                            update_post_meta( $variation_id, "wpc_$attribut_key", $part_img_id );
                        else
                            delete_post_meta( $variation_id, "wpc_$attribut_key");
                    }
                }
            }
        }
        
        $additional_settings=array("wpc_bg","wpc_ov");
        foreach ($additional_settings as $setting)
        {
            if(isset($_POST[$setting])&&  is_array($_POST[$setting]))
            {
                foreach ($_POST[$setting] as $variation_id=>$settings_parts)
                {
                    foreach ($settings_parts as $part_key=>$media_id)
                    {
                        $meta_key="$setting-$part_key";
                        delete_post_meta($variation_id, $meta_key);
                        if(!empty($media_id))
                            add_post_meta($variation_id, $meta_key, $media_id);
                        
                    }
                    
                }
            }
        }
        
//        $additional_settings2=array("wpc-canvas-w", "wpc-canvas-h");
//        foreach ($additional_settings2 as $setting)
//        {
//            if(isset($_POST[$setting]))
//                update_post_meta($post_id, $setting, $_POST[$setting]);
//            else
//                delete_post_meta($post_id, $meta_key);
//        }
//        exit();
     
     }
		
 }
 
 function get_wpc_variable_order_item_attributes($_product)
 {
     $output="";
    if(isset($_product->variation_id))
    {
        $attributes=$_product->variation_data;
        foreach ($attributes as $attribute_key=>$attribute_value)
        {
            $attribute_name= ucfirst(str_replace("attribute_", "", $attribute_key));
            $output.="<div><strong>$attribute_name</strong>: $attribute_value</div>";
        }
    }
    
    return $output;
 }

add_action("woocommerce_admin_order_item_values","get_order_custom_admin_data",10,3);
function get_order_custom_admin_data($_product, $item, $item_id )
{
    $output="";
    if(isset($item["wpc_data"]))
    {
        foreach ($item["item_meta"]["wpc_data"] as $s_index=>$serialized_data)
        {
            $output.="<div class='wpc_order_item' data-item='$item_id'>";
            $output.=get_wpc_variable_order_item_attributes($_product);
            $unserialized_data=unserialize($serialized_data);
            if(count($item["item_meta"]["wpc_data"])>1)
                $output.=($s_index+1)."-";
            foreach ($unserialized_data as $data_key=>$data)
            {                
                $img_src=$data["image"];
                $original_part_img_url=$data["original_part_img"];
                $modal_id=$s_index."_$item_id"."_$data_key";
                $output.='<span><a class="button" data-toggle="modal" data-target="#'.$modal_id.'">'.ucfirst($data_key).'</a></span>';
                $output.='<div class="modal fade wpc_part" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                  <h4 class="modal-title" id="myModalLabel'.$modal_id.'">Preview</h4>
                                </div>
                                <div class="modal-body">
                                    <div style="background-image:url('.$original_part_img_url.')"><img src="'.$img_src.'"></div>
                                </div>
                              </div>
                            </div>
                          </div>';
            }
            //Deb
//            var_dump($unserialized_data);
            //wc_delete_order_item_meta($item_id, "wpc_data_zip");
            $zip_file=wc_get_order_item_meta( $item_id, "wpc_data_zip", $single = true );
            if(!empty($zip_file)&& is_array($zip_file))
            {
                $output.="<a class='button' href='".$zip_file["url"]."' download='".basename($zip_file["url"])."'>".__( "Download design","wpc")."</a> ";
            }
            else
            {
                $output.=wpc_generate_order_item_zip($item_id, $unserialized_data, false, $_product->id);
            }
            
            //End
            $output.="</div>";
        }
    }
    else if(isset($item["wpc_data_upl"]))
    {        
        $output.="<div class='wpc_order_item' data-item='$item_id'>";
        $output.=get_wpc_variable_order_item_attributes($_product);
        $output.="<a class='button' href='".$item["wpc_data_upl"]."' download='".basename($item["wpc_data_upl"])."'>".__( "Download custom design","wpc")."</a> ";
        $output.="</div>";
    }
    
    echo $output;
    
}

function wpc_generate_order_item_zip($item_id, $unserialized_data, $return_meta, $variation_id)
{
    $tmp_dir=  uniqid();
    $upload_dir=  wp_upload_dir();
    $generation_path = $upload_dir["basedir"]."/WPC/$tmp_dir";
    if(wp_mkdir_p($generation_path))
    {
        $generation_url = $upload_dir["baseurl"]."/WPC/$tmp_dir";

        $result=export_data_to_files($generation_path, $unserialized_data, $generation_url, true, $variation_id);
        if($result)
        {
            $output="<a class='button' href='$generation_url/$result' download='$result'>".__( "Download design","wpc")."</a> ";
            $meta=array("path"=>json_encode("$generation_path/$result"),"url"=>"$generation_url/$result");
            wc_add_order_item_meta( $item_id, 'wpc_data_zip', $meta ,true);
            if($return_meta)
                return $meta;
            else
                return $output;
        }
    }
    return false;
}

//Allow us to hide the wpc_data_upl meta from the meta list in the order details page
add_filter("woocommerce_hidden_order_itemmeta","unset_wpc_data_upl_meta");
function unset_wpc_data_upl_meta($hidden_meta)
{
    array_push($hidden_meta, "wpc_data_upl");
    return $hidden_meta;
}

add_action( 'wp_ajax_get_customizer_url', 'get_wpc_url_ajax' );
add_action( 'wp_ajax_nopriv_get_customizer_url', 'get_wpc_url_ajax' );
function get_wpc_url_ajax()
{
    $variation_id=$_GET['variation_id'];
    $wpc_page_url=  get_wpc_url($variation_id);
    echo json_encode(array("url"=>$wpc_page_url));
    die();
}

function get_wpc_url($variation_id)
{
    $wpc_page_id = get_option( 'wpc_page_id' );
    $wpc_page_url="";
    if ( $wpc_page_id ) {
      $wpc_page_url = get_permalink( $wpc_page_id );
      $query = parse_url($wpc_page_url, PHP_URL_QUERY);
        // Returns a string if the URL has parameters or NULL if not
        if( $query )
            $wpc_page_url .= '&product_id='.$variation_id;
        else
            $wpc_page_url .= '?product_id='.$variation_id;
    }
    
    return $wpc_page_url;
}

add_action( 'wp_login', 'save_user_designs_wrapper', 10, 2 );
function save_user_designs_wrapper($user_login, $user)
{
    save_user_designs( $user->ID );    
}

add_action( 'user_register', 'save_user_designs', 10, 1 );
function save_user_designs( $user_id ) {

    if ( isset( $_SESSION['wpc_designs_to_save'] ) )
    {
        foreach ($_SESSION['wpc_designs_to_save'] as $variation_id => $design_array) {
            foreach ($design_array as $key => $design) {
                $today = date("Y-m-d H:i:s");   
                add_user_meta($user_id, 'wpc_saved_designs',  array($variation_id, $today,$design));                
            }
            unset($_SESSION['wpc_designs_to_save'][$variation_id]);
            
        }
        unset($_SESSION['wpc_designs_to_save']);
    }

}

add_action("woocommerce_add_to_cart","set_customizations_data",10,6);
function set_customizations_data($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
{
    if($variation=="wpc_data")
    {
        if(!isset($_SESSION["wpc_generated_data"][$variation_id]))
            $_SESSION["wpc_generated_data"][$variation_id]=array();
        $_SESSION["wpc_generated_data"][$variation_id][$cart_item_key]=$cart_item_data;
    }
    
}

function wpc_get_product_attribute_name($product_id, $attribute_key)
{
//    var_dump($attribute_key);
    $original_product_attributes=get_post_meta($product_id,"_product_attributes",true);
    $queriable_attribute_key= str_replace("attribute_", "", $attribute_key);
    $attribute_name=$original_product_attributes[$queriable_attribute_key]["name"];
    return wc_attribute_label($attribute_name);
}

//add_filter("woocommerce_get_item_data","get_customized_item_variation_attributes",10,2);
function get_customized_item_variation_attributes($data,$cart_item)
{
    $product=  get_product($cart_item["variation_id"]);
    if($cart_item["variation"]=="wpc_data"&&$product->product_type=="variation")
    {
        if(!$cart_item["data"]->product_type=="variable")
            return;
        $output="";
        foreach ( $cart_item["data"]->variation_data as $name => $value ) {

			if ( ! $value )
				continue;

            // If this is a term slug, get the term's nice name
            if ( taxonomy_exists( esc_attr( str_replace( 'attribute_', '', $name ) ) ) ) {
            	$term = get_term_by( 'slug', $value, esc_attr( str_replace( 'attribute_', '', $name ) ) );
            	if ( ! is_wp_error( $term ) && $term->name )
            		$value = $term->name;
                        $output.="<dt>".wc_attribute_label(str_replace('attribute_', '', $name)).':</dt> <dd>'.$value.'</dd>';
            }
            else
            var_dump($name);
        }
        echo '<dl class="variation">'.$output.'</dl>';
    }
    return $data;
    
}

add_action("woocommerce_before_cart_item_quantity_zero","remove_wpc_customization");
function remove_wpc_customization($cart_item_key)
{
        foreach ($_SESSION["wpc_generated_data"] as $variation_id => $variation_customizations) {
            if(isset($_SESSION["wpc_generated_data"][$variation_id][$cart_item_key]))
            {
                unset($_SESSION["wpc_generated_data"][$variation_id][$cart_item_key]);
                if(empty($_SESSION["wpc_generated_data"][$variation_id]))
                    unset($_SESSION["wpc_generated_data"][$variation_id]);
                break;
            }
            else if(isset($_SESSION["wpc-uploaded-designs"]))
                unset($_SESSION["wpc-uploaded-designs"][$cart_item_key]);
        }
}

add_action("woocommerce_add_order_item_meta","save_customized_item_meta",10,3);
function save_customized_item_meta($item_id, $values, $cart_item_key)
{
    $variation_id=$values["variation_id"];
    if(isset($_SESSION["wpc_generated_data"][$variation_id]))
    {
        $variation_customizations=$_SESSION["wpc_generated_data"][$variation_id];
        foreach ($variation_customizations as $cart_item_key_i => $customizations) {
            wc_add_order_item_meta( $item_id, 'wpc_data', $customizations ); 
            unset($_SESSION["wpc_generated_data"][$variation_id][$cart_item_key_i]);
            break;
        }
        if(empty($_SESSION["wpc_generated_data"][$variation_id]))
            unset($_SESSION["wpc_generated_data"][$variation_id]);
    }
    else if(isset($_SESSION["wpc-uploaded-designs"][$cart_item_key]))
    {
        wc_add_order_item_meta( $item_id, 'wpc_data_upl', $_SESSION["wpc-uploaded-designs"][$cart_item_key] ); 
        unset($_SESSION["wpc-uploaded-designs"][$cart_item_key]);
    }
    
}

add_action("woocommerce_product_write_panel_tabs","get_wpc_product_tab_label");
function get_wpc_product_tab_label()
{
    ?>
        <li class="wpc_parts_tab"><a href="#wpc_parts_tab_data"><?php _e( 'Product parts', 'wpc' ); ?></a></li>
    <?php
}

add_action("woocommerce_product_write_panels","get_wpc_product_tab_data");
function get_wpc_product_tab_data()
{
    ?>
        <div id="wpc_parts_tab_data" class="panel woocommerce_options_panel">
            <?php 
                get_wpc_product_tab_data_content();
            ?>
        </div>
    <?php
}

function get_wpc_product_tab_data_content_line($variation_id, $attributes_str, $product_id)
{
    $canvas_width="";
    $canvas_height="";
    $canvas_width_arr=  get_post_meta($product_id, "wpc-canvas-w",true);
    if(isset($canvas_width_arr[$variation_id]))
        $canvas_width=$canvas_width_arr[$variation_id];
    $canvas_height_arr=  get_post_meta($product_id, "wpc-canvas-h",true);
    if(isset($canvas_height_arr[$variation_id]))
        $canvas_height=$canvas_height_arr[$variation_id];
    ?>
        <div class="panel wc-metaboxes-wrapper">
            <div class=" wc-metaboxes ui-sortable">
                    <div class="wc-metabox open">
                            <h3>
                                    <div class="handlediv" title="Click to toggle"></div>
                                    <strong><?php echo "#$variation_id — $attributes_str";?></strong>
                            </h3>
                            <table cellpadding="0" cellspacing="0" class="wc-metabox-content" style="display: table;">
                                <tbody>
                                    <?php
                                        $parts=  get_option("wpc-parts");
//                                        var_dump(get_post_meta($variation_id));
                                        if(is_array($parts))
                                        {
                                        foreach ($parts as $part) {
                                            $part_key= sanitize_title($part);
                                            $selector="wpc_".$part_key."_".$variation_id;
                                            $canvas_bg_selector="wpc_bg_".$part_key."_".$variation_id;
                                            $canvas_ov_selector="wpc_ov_".$part_key."_".$variation_id;
                                            $part_img=get_post_meta($variation_id,"wpc_$part_key",true);
                                            $part_bg_img=get_post_meta($variation_id,"wpc_bg-$part_key",true);
//                                            var_dump($part_bg_img);
                                            $part_ov_img=get_post_meta($variation_id,"wpc_ov-$part_key",true);
//                                            var_dump($part_ov_img);
                                            $cb_status="";
                                            if($part_img||$part_img=="0")
                                                $cb_status='checked="checked"';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="wpc-part-block">
                                                        <label><?php echo $part; ?></label>
                                                        <input type="checkbox" class="wpc-activate-part-cb" data-selector="<?php echo $selector;?>" <?php echo $cb_status;?>>
                                                        <div class="wpc-part-img-block">
                                                                <div>
                                                                    Background image (not included in design)
                                                                </div>
                                                                <div>
                                                                <button class="button wpc_img_upload" data-selector="<?php echo $selector;?>">Set image</button>
                                                                <button class="button wpc_img_remove" data-key="<?php echo $part_key;?>" data-id="<?php echo $part_key;?>" data-selector="<?php echo $selector;?>">Remove image</button>
                                                                <input type="hidden" id="<?php echo $selector;?>" name="wpc_<?php echo $part_key;?>[<?php echo $variation_id; ?>]" value="<?php echo $part_img; ?>"/>
                                                                <div id="<?php echo $selector;?>_preview" class="wpc_preview">
                                                                    <?php
                                                                        if(isset($part_img))
                                                                        {
                                                                            $img_src=  wp_get_attachment_url($part_img);
                                                                            echo "<img src='$img_src'>";
                                                                        }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="wpc-part-img-block">
                                                            <div>
                                                                Background image (included in design)
                                                            </div>
                                                            <div>
                                                                <button class="button wpc_img_upload" data-selector="<?php echo $canvas_bg_selector;?>">Set image</button>
                                                                <button class="button wpc_img_remove" data-key="<?php echo $part_key;?>" data-id="<?php echo $part_key;?>" data-selector="<?php echo $canvas_bg_selector;?>">Remove image</button>
                                                                <input type="hidden" id="<?php echo $canvas_bg_selector;?>" name="wpc_bg[<?php echo $variation_id; ?>][<?php echo $part_key;?>]" value="<?php echo $part_bg_img; ?>"/>
                                                                <div id="<?php echo $canvas_bg_selector;?>_preview" class="wpc_preview">
                                                                    <?php
                                                                        if(isset($part_bg_img))
                                                                        {
                                                                            $img_src=  wp_get_attachment_url($part_bg_img);
                                                                            echo "<img src='$img_src'>";
                                                                        }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="wpc-part-img-block">
                                                            <div>
                                                                Overlay image (included in design)
                                                            </div>
                                                            <div>
                                                                <button class="button wpc_img_upload" data-selector="<?php echo $canvas_ov_selector;?>">Set image</button>
                                                                <button class="button wpc_img_remove" data-key="<?php echo $part_key;?>" data-id="<?php echo $part_key;?>" data-selector="<?php echo $canvas_ov_selector;?>">Remove image</button>
                                                                <input type="hidden" id="<?php echo $canvas_ov_selector;?>" name="wpc_ov[<?php echo $variation_id; ?>][<?php echo $part_key;?>]" value="<?php echo $part_ov_img; ?>"/>
                                                                <div id="<?php echo $canvas_ov_selector;?>_preview" class="wpc_preview">
                                                                    <?php
                                                                        if(isset($part_ov_img))
                                                                        {
                                                                            $img_src=  wp_get_attachment_url($part_ov_img);
                                                                            echo "<img src='$img_src'>";
                                                                        }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php        
                                        }
                                        }
                                        ?>
                                </tbody>
                            </table>
                    </div>
            </div>
        </div>
    <?php
}

add_action( 'wp_ajax_get_wpc_product_tab_data_content', 'get_wpc_product_tab_data_content_ajx' );
function get_wpc_product_tab_data_content_ajx()
{
    $product_id=$_POST["product_id"];
    $post_type=$_POST["post_type"];
    $variations_arr=array();
    if(isset($_POST["variations"]))
        $variations_arr=$_POST["variations"];
    
    $parts=  get_option("wpc-parts");
    if(empty($parts))
    {
        echo __( 'Error: empty product parts list. At least one is required to create a customizable product.', 'wpc' ); 
        return;
    }
    if($post_type=="variable")
    {
        if(!is_array($variations_arr)||empty($variations_arr))
        {
            echo "<div style='margin:10px; color:red;'>Please setup the products variations first .</div>";
            return;
        }
        foreach ($variations_arr as $variation_id => $attributes)
        {
            if(!is_array($attributes))
                continue;
            $attributes_str="";
            foreach($attributes as $attribute)
            {
                $attributes_str.=" ".ucfirst($attribute);
            }
            get_wpc_product_tab_data_content_line($variation_id, $attributes_str, $product_id);
        }
    }
    else if($post_type=="simple")
    {
        get_wpc_product_tab_data_content_line($product_id, "Simple product", $product_id);
    }
    else
        echo "<div style='margin:10px; color:red;'>We don't currently support $post_type products.</div>";
    die();
    
}

function get_wpc_product_tab_data_content()
{
    $parts=  get_option("wpc-parts");
    if(empty($parts))
    {
        echo __( 'Error: empty product parts list. At least one is required to create a customizable product.', 'wpc' ); 
        return;
    }
    $product=get_product();
    if($product->product_type=="variable")
    {
        $variations=$product->get_available_variations();
        foreach ($variations as $variation)
        {
            $variation_id=$variation['variation_id'];
            $attributes=$variation["attributes"];
            $attributes_str="";
            foreach($attributes as $attribute)
            {
                $attributes_str.=" ".ucfirst($attribute);
            }
            get_wpc_product_tab_data_content_line($variation_id, $attributes_str, $product->id);
            ?>

            <?php
        }
    }
    else if($product->product_type=="simple")
    {
        get_wpc_product_tab_data_content_line($product->id, "Simple product", $product->id);
    }
    else
        echo "<div style='margin:10px; color:red;'>We don't currently support $product->product_type products.</div>";
    
}

add_action( 'woocommerce_product_options_general_product_data', "get_canvas_clip_dimensions_fields" );
function get_canvas_clip_dimensions_fields()
{
    echo "<div class='mg-top-10 mg-left-10'><strong>".__( 'BOUNDING BOX PARAMETERS', 'wpc' ) .": </strong></div>";
    echo "<div class='mg-top-10 mg-left-10'>".__( 'If the coordinates (X,Y) are not set, they will automatically be determined from the product center.', 'wpc' )."</strong></div>";
    woocommerce_wp_text_input( array( 'id' => 'clip_x', 'label' => __( 'X', 'wpc' ) . ' (px)', 'description' => __( 'Bounding box coordinate X on the product', 'wpc' ),'desc_tip' => 'true' ) );
    woocommerce_wp_text_input( array( 'id' => 'clip_y', 'label' => __( 'Y', 'wpc' ) . ' (px)', 'description' => __( 'Bounding box coordinate Y on the product', 'wpc' ),'desc_tip' => 'true' ) );
    
    echo "<div class='mg-top-10 mg-left-10'>".__( 'The dimensions are required to apply a bounding box on the product.', 'wpc' )." </strong></div>";
    woocommerce_wp_text_input( array( 'id' => 'clip_width', 'label' => __( 'Width', 'wpc' ) . ' (px)', 'description' => __( 'Bounding box width on the product', 'wpc' ),'desc_tip' => 'true' ) );
    woocommerce_wp_text_input( array( 'id' => 'clip_height', 'label' => __( 'Height', 'wpc' ) . ' (px)', 'description' => __( 'Bounding box height on the product', 'wpc' ),'desc_tip' => 'true' ) );
    woocommerce_wp_text_input( array( 'id' => 'clip_radius_rect', 'label' => __( 'Radius (rect)', 'wpc' ) . ' (px)', 'description' => __( 'Bounding box radius on the product (used for rectangle)', 'wpc' ),'desc_tip' => 'true' ) );
    woocommerce_wp_text_input( array( 'id' => 'clip_radius', 'label' => __( 'Radius (circle)', 'wpc' ) . ' (px)', 'description' => __( 'Bounding box radius on the product (used for circle)', 'wpc' ),'desc_tip' => 'true' ) );
    woocommerce_wp_select(array( 'id' => 'clip_type', 'label' => __( 'Type', 'wpc' ) . ' (px)', 'description' => __( 'Bounding box type', 'wpc' ),'desc_tip' => 'true', 'options'=>array("rect"=>"Rectangle", "arc"=>"Circle") ));
    woocommerce_wp_text_input( array( 'id' => 'clip_b_color', 'label' => __( 'Border color', 'wpc' ) , 'description' => __( 'Bounding box border color', 'wpc' ),'desc_tip' => 'true' ) );    

}

add_filter('upload_mimes','wpc_add_custom_mime_types');
function wpc_add_custom_mime_types($mimes){
        return array_merge($mimes,array (
                'svg' => 'image/svg+xml'
        ));
}

add_filter('manage_edit-product_columns', 'get_wpc_product_columns');
function get_wpc_product_columns($defaults) {
    $defaults['is_customizable'] =__('Custom','wpc');
    return $defaults;
}

add_action('manage_product_posts_custom_column', 'get_wpc_products_columns_values', 5, 2);
function get_wpc_products_columns_values($column_name, $id) {
    if ($column_name === 'is_customizable') {

        $is_customizable=  get_post_meta($id,"customizable-product",true);
        if($is_customizable)
            _e ("Yes","wpc");
        else
            _e ("No","wpc");
    }
}

add_action( 'admin_notices', 'get_ad_messages' );
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
           <p>'.$messages[$random_message_key].' <a href="'. admin_url("admin.php?page=wpc-advanced-features").'">Click here to unlock that feature</a></p>
        </div>';
    }
    
}

?>