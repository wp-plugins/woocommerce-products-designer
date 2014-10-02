<?php

/**
 * Appends the editor at the end of the selected page
 * @param type $content
 * @return string
 */
add_shortcode("wpc-editor", "get_product_editor");
function get_product_editor($content)
{
//    $post_max_value_length=ini_get("suhosin.post.max_value_length");
//    var_dump($post_max_value_length);
    $wpc_page_id = get_option( 'wpc_page_id' );
    $current_page_id=  get_the_ID();
    if ( $wpc_page_id !=$current_page_id)
        return $content;
        
    $product_id=$_GET["product_id"];
    $product=get_product($product_id);
    ob_start();
    
    $is_customizable=0;
    if($product_id)
    {
        if($product->product_type=="variation")
            $is_customizable=  get_post_meta($product->parent->id,"customizable-product",true);        
        else
            $is_customizable=  get_post_meta($product_id,"customizable-product",true);        
    }
    if(!isset($product_id))
    {
        echo __("You have to select a customizable product first.","wpc");
    }
    else if(!$is_customizable)
    {
        echo __("This product is not customizable.","wpc");
    }
    else 
    {
        get_wpc_product_customizer($product_id);
    }
    
    $output=  ob_get_contents();
    ob_end_clean();
    return $content.$output;
}

function wpc_starts_with($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function wpc_check_rule($objects, $rule)
{
    $param=$rule["param"];
    $value=$rule["value"];
    $operator=$rule["operator"];
    $results=array();
    foreach ($objects as $object)
    {
        $to_eval="if($object[$param] $operator $value) return true; else return false;";
        $evaluation=  eval($to_eval);
        array_push($results, $evaluation);
    }
    
    return $results;
}

/**
 * Builds the editor for a simple product or variations in the front
 * @global object $wpdb
 * @global object $current_user
 * @param type $product_id Product or Variation ID
 * @return string
 */
function get_wpc_product_customizer($product_id)
{
    $product=get_product($product_id);
//    ob_start();
    
    if(!has_part($product_id))
    {
        _e( 'Error: No active part defined for this product. A customizable product should have at least one part defined.', 'wpc' );
        return;
    }
    global $wpdb;
    $product_price=$product->price;
    $shop_currency_symbol=get_woocommerce_currency_symbol();
    $generate_layers=  (get_option("wpc-generate-layers")==="yes")?true:false;
    
    if($product->product_type=="variation")
        $normal_product_id=$product->parent->id;
    else
        $normal_product_id=  $product_id;
    
    $clip_w=  get_post_meta($normal_product_id,"clip_width",true);
    $canvas_w=800;
    $canvas_h=500;
    $clip_h=  get_post_meta($normal_product_id,"clip_height",true);
    $clip_x=  get_post_meta($normal_product_id,"clip_x",true);
    $clip_y=  get_post_meta($normal_product_id,"clip_y",true);
    $clip_radius=  get_post_meta($normal_product_id,"clip_radius",true);
    $clip_radius_rect=  get_post_meta($normal_product_id,"clip_radius_rect",true);
    if(empty($clip_radius_rect))
        $clip_radius_rect=0;
    $clip_type=  get_post_meta($normal_product_id,"clip_type",true);
    $clip_border=  get_post_meta($normal_product_id,"clip_b_color",true);
    $output_w=  get_option("wpc-min-output-width");
    $output_loop_delay=  get_option("wpc-output-loop-delay");
    if(!$output_loop_delay)
        $output_loop_delay=1000;
    $svg_colorization=  get_option("wpc-svg-colorization");
    $wpc_palette_type=  get_option("wpc-color-palette");
    $palette= get_option("wpc-custom-palette");
    $palette_tpl="";
    $redirect_after=  get_option("wpc-redirect-after-cart");
    if(empty($redirect_after))
        $redirect_after=0;
    if(is_array($palette)&&!empty($palette))
    {
        foreach ($palette as $color)
        {
            $hex=str_replace("#", "", $color);
            $palette_tpl.='<span style="background-color: '.$color.'" data-color="'.$hex.'" class="wpc-custom-color"></span>';
        }
    }
    if(isset($_GET["edit"]))
    {
        $variation_id=$_GET["product_id"];
        $cart_item_key=$_GET["edit"];
        $data=$_SESSION["wpc_generated_data"][$variation_id][$cart_item_key];
    }
    else if(isset($_GET["design_index"]))
    {
        global $current_user;
        $design_index=$_GET["design_index"];
        $user_designs=get_user_meta($current_user->ID, 'wpc_saved_designs');
        $data=$user_designs[$design_index][2];
    }
    else if(isset($_GET["oid"]))
    {
        $order_item_id=$_GET["oid"];
        $sql="select meta_value FROM ".$wpdb->prefix."woocommerce_order_itemmeta where order_item_id=$order_item_id and meta_key='wpc_data'";
        //echo $sql;
        $wpc_data=$wpdb->get_var($sql);
        $data=  unserialize($wpc_data);
    }
    
    if(isset($data)&&!empty($data))
    {        
        //Useful when editing cart item
        $data=  stripslashes_deep($data);
        ?>
        <script>
            var to_load=<?php echo json_encode($data);?>;
        </script>
        <?php
    }
    ?>
    <script>
        var palette_tpl='<?php echo $palette_tpl;?>';
        var loading_msg='<?php _e("Just a moment","wpc");?>';
        var deletion_error_msg='<?php _e("The deletion of this object is not allowed","wpc");?>';
        var empty_object_msg='<?php _e("The edition area is empty.","wpc");?>';
        var global_variation_id=<?php echo $product_id;?>;
    </script>
    <div id="wpc-customizer">
        <div id="wpc-top-bar">
            <span class="text-btn selected" data-id="#txt-tools-container"><?php echo __("Text","wpc");?></span>
            <span class="shapes-btn" data-id="#shape-tools-container"><?php echo __("Shapes","wpc");?></span>
            <span class="images-btn" data-id="#image-tools-container"><?php echo __("Images / Cliparts","wpc");?></span>
        </div>
        <div id="wpc-edition-area">
            <div id="wpc-tools-bar">
                <?php get_tools_bar();?>
            </div>
            <div id="wpc-editor-container" style="width: <?php echo $canvas_w;?>px;height:<?php echo $canvas_h;?>px;" data-clip_w="<?php echo $clip_w;?>" data-clip_h="<?php echo $clip_h;?>" data-clip_x="<?php echo $clip_x;?>" data-clip_r="<?php echo $clip_radius;?>" data-clip_rr="<?php echo $clip_radius_rect;?>" data-clip_y="<?php echo $clip_y;?>" data-clip_type="<?php echo $clip_type;?>" data-clip_border="<?php echo $clip_border;?>" data-output_w="<?php echo $output_w;?>" data-output_delay="<?php echo $output_loop_delay;?>" data-svg_colorization="<?php echo $svg_colorization;?>" data-palette_type="<?php echo $wpc_palette_type;?>" data-print_layers="<?php echo $generate_layers;?>">
                <canvas id="wpc-editor" ></canvas>
            </div>
            <div id="wpc-buttons-bar">
                <?php get_buttons_bar();?>
            </div>
            <div id="wpc-parts-bar">
                <?php get_parts($product_id);?>
            </div>
        </div>
        <?php
                $download_btn=  get_option("wpc-download-btn");
                $preview_btn=  get_option("wpc-preview-btn");
                $cart_btn=  get_option("wpc-cart-btn");
            ?>
        <div id="action-btn-bar">
            <?php
            if($preview_btn!=="0")
            {
            ?>
            <a id="preview-order" class="wpc-button blue"><?php echo __("Preview","wpc");?></a>
            <?php
            }
            
            if($download_btn!=="0")
            {
            ?>
            <a id="download-design" class="wpc-button blue"><?php echo __("Download","wpc");?></a>
            <?php
            }
            
            if($cart_btn!=="0")
            {
            ?>
            <div class='d-iblk f-right'>
                <div id="wpc-qty-container" class="d-iblk p-relative">
                    <input type="button" value="-" class="minus">
                    <input id="wpc-qty" type="number" step="1" value="1" class="input-text qty text" min="1" dntmesecondfocus="true" uprice="<?php echo "$product_price";?>">
                    <input type="button" value="+" class="plus">
                </div>
                <span class='d-iblk v-align-t' id='wpc-price'>
                    <span id="currency"><?php echo "$shop_currency_symbol";?></span>
                    <span id="total_order"><?php echo "$product_price";?></span>
                </span>
                <a id='add-to-cart'  class="wpc-button blue v-align-t" data-id="<?php echo $product_id;?>"><?php _e("Add to cart","wpc");?></a>
            </div>
            <?php
            }
            ?>
            
        </div>
        <div id="debug" class="mg-top-10 w-100 d-iblk"></div>
    </div>
    <?php
    $modal='<div class="modal fade wpc-modal" id="wpc-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title" id="myModalLabel">Preview</h4>
                </div>
                <div class="modal-body">
                </div>
              </div>
            </div>
        </div>';
    array_push(wpc_retarded_actions::$code,$modal);
    add_action( 'wp_footer', array( 'wpc_retarded_actions', 'display_code' ), 10, 1 );
}

add_action('wp_head','get_js_variables');
function get_js_variables() {
    ?>
        <script type="text/javascript">
            var jquery_calls_url = '<?php echo WPC_URL; ?>jquery_calls.php';
        </script>
        <?php
}

/**
 * Checks if a product contains at least one active part
 * @param type $product_id Product ID
 * @return boolean
 */
function has_part($product_id)
{
    $parts=  get_option("wpc-parts");
    $wc_product=get_product($product_id);
    if($wc_product->product_type=="variable")
    {
        $variations=$wc_product->get_available_variations();
        foreach ($variations as $variation)
        {
            $variation_id=$variation['variation_id'];
            foreach ($parts as $part) {
                $part_key= sanitize_title($part);
                $part_media_id=  get_post_meta($variation_id, "wpc_$part_key", true);
                if($part_media_id||$part_media_id=="0")
                    return true;
            }
        }
    }
    else
    {
        foreach ($parts as $part) {
            $part_key= sanitize_title($part);
            $part_media_id=  get_post_meta($product_id, "wpc_$part_key", true);
            if($part_media_id||$part_media_id=="0")
                return true;
        }
    }
    return false;
}

/**
 * Create the parts bar at the bottom of the editor
 * @param type $product_id Product ID
 */
function get_parts($product_id)
{
    $parts=  get_option("wpc-parts");
    $is_first=true;
    foreach ($parts as $part) {
        $part_key= sanitize_title($part);
        $part_media_id=  get_post_meta($product_id, "wpc_$part_key", true);
        if(!($part_media_id||$part_media_id=="0"))
            continue;
        $class="";
        if($is_first)
            $class="class='active'";
        $is_first=false;
        $img_ov_src="";
        $part_ov_img=get_post_meta($product_id,"wpc_ov-$part_key",true);
        if(isset($part_ov_img))
        {
            $img_ov_src=  wp_get_attachment_url($part_ov_img);
        }
        $part_bg_img=get_post_meta($product_id,"wpc_bg-$part_key",true);
        $img_bg_src="";
        if(isset($part_bg_img))
            $img_bg_src=  wp_get_attachment_url($part_bg_img);
                
        if($part_media_id=="0")
        {
            ?>
            <span data-id="<?php echo $part_key;?>" data-url="" <?php echo $class;?> data-placement="top" data-original-title="<?php echo $part;?>" data-bg="<?php echo $img_bg_src;?>" data-ov="<?php echo $img_ov_src;?>">
                <?php echo $part;?>
            </span>
            <?php            
        }
        else
        {
            $part_img=wp_get_attachment_image_src($part_media_id,"full");        
            $part_img_url=$part_img[0];
            $final_img_url=$part_img_url;
            ?>
            <span data-id="<?php echo $part_key;?>" data-url="<?php echo $final_img_url;?>" <?php echo $class;?> data-placement="top" data-original-title="<?php echo $part;?>" data-bg="<?php echo $img_bg_src;?>" data-ov="<?php echo $img_ov_src;?>">
                <img src="<?php echo $part_img_url;?>">
            </span>
            <?php
        }
        
        
        
    }
}

/**
 * Creates the editor buttons bar (Preview, Download, Save for later, Add to cart)
 */
function get_buttons_bar()
{
    ?>
<!--        <button id="zoom-in-btn" data-placement="top" data-original-title="Zoom in"></button>
        <button id="zoom-out-btn" data-placement="top" data-original-title="Zoom out"></button>-->
        <button id="grid-btn" data-placement="top" data-original-title="Grid"></button>
        <button id="clear_all_btn" data-placement="top" data-original-title="<?php _e("Clear all","wpc");?>"></button>
        <button id="delete_btn" data-placement="top" data-original-title="<?php _e("Delete","wpc");?>"></button>
        <button id="copy_paste_btn" data-placement="top" data-original-title="<?php _e("Duplicate","wpc");?>"></button>
        <button id="send_to_back_btn" data-placement="top" data-original-title="<?php _e("Send to back","wpc");?>"></button>
        <button id="bring_to_front_btn" data-placement="top" data-original-title="<?php _e("Bring to front","wpc");?>"></button>
        <button id="flip_h_btn" data-placement="top" data-original-title="<?php _e("Flip horizontally","wpc");?>"></button>
        <button id="flip_v_btn" data-placement="top" data-original-title="<?php _e("Flip vertically","wpc");?>"></button>
        <button id="align_h_btn" data-placement="top" data-original-title="<?php _e("Center horizontally","wpc");?>"></button>
        <button id="align_v_btn" data-placement="top" data-original-title="<?php _e("Center vertically","wpc");?>"></button>
        <button id="undo-btn" data-placement="top" data-original-title="<?php _e("Undo","wpc");?>"></button>
        <button id="redo-btn" data-placement="top" data-original-title="<?php _e("Redo","wpc");?>"></button>
    <?php
}

/**
 * Create the tools group (text, shape, images and user saved designs)
 */
function get_tools_bar()
{
    get_text_tools();
    get_shape_tools();
    get_image_tools();
}

/**
 * Creates text elements tools
 */
function get_text_tools()
{
    $fonts=get_option("wpc-fonts");
    if(empty($fonts))
        $fonts=  get_default_fonts ();
    ?>
    <div id="txt-tools-container" style="display: block;">
        <div class="d-iblk" style="width: 200px;">
            <textarea id="new-text" placeholder="<?php _e("Enter your texte here...","wpc");?>"></textarea>
            <a id="add-text-btn" class="wpc-button blue"><?php _e("Add","wpc");?></a>
        </div>
        <div class="d-iblk">
            <input type="checkbox" id="underline-cb" class="custom-cb">            
            <label for="underline-cb" data-placement="top" data-original-title="<?php _e("Underline","wpc");?>"></label>
            
            <input type="checkbox" id="bold-cb" class="custom-cb">
            <label for="bold-cb" data-placement="top" data-original-title="<?php _e("Bold","wpc");?>"></label>
            
            <span id="txt-outline-color-selector" class="color-selector border-color-selector" data-placement="top" data-original-title="<?php _e("Outline color","wpc");?>"></span>
            <br>
            <input type="checkbox" id="italic-cb" class="custom-cb">
            <label for="italic-cb" data-placement="top" data-original-title="<?php _e("Italic","wpc");?>"></label>
            
            <span id="txt-color-selector" style="background-color: #C6C4C4;" data-placement="top" data-original-title="<?php _e("Text color","wpc");?>"></span>
            <span id="txt-bg-color-selector" class="bg-color-selector" data-placement="top" data-original-title="<?php _e("Background color","wpc");?>"></span>
            
        </div>
        <div class="d-iblk">
            <div>
                <?php _e("Font family: ","wpc");?>
                <select id="font-family-selector">
                    <?php 
                        foreach ($fonts as $font)
                        {
                            $font_label=$font[0];
                            echo "<option>$font_label</option>";
                        }
                    ?>
                    
                </select>
            </div>
            <div class="mg-top-10">
                <?php _e("Font size: ","wpc");?>
                <div id="font-size-slider" class="noUiSlider horizontal" data-min="10" data-max="80" data-step="1" data-start="30"></div>
            </div>
            
        </div>
        <div class="d-iblk">
            <div>
                <?php _e("Outline width: ","wpc");?>
                <div id="o-thickness-slider" class="noUiSlider horizontal" data-min="0" data-max="10" data-step="1" data-start="0"></div>
            </div>
            <div class="mg-top-20">
                <?php _e("Opacity: ","wpc");?>
                <div id="opacity-slider" class="noUiSlider horizontal" data-min="0" data-max="1" data-step="0.1" data-start="1"></div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Creates shapes elements tools
 */
function get_shape_tools()
{
    ?>
    <div id="shape-tools-container" style="display: none;">
        <div class="d-iblk">
            <div>
                <a class="square" id="square-btn"></a>
                <a class="r-square" id="r-square-btn"></a>
                <a class="circle" id="circle-btn"></a>
                <a class="triangle" id="triangle-btn"></a>
                
            </div>
            <div>
                <div class="d-iblk">
                    <a class="polygon" id="polygon-btn"></a>
                    <select id="polygon-nb-points" class="mg-top-5 v-align-t">
                        <option>5</option>
                        <option>6</option>
                        <option>7</option>
                        <option>8</option>
                        <option>9</option>
                        <option>10</option>
                    </select>
                    <a class="star" id="star-btn"></a>
                        <select id="star-nb-points" class="mg-top-5 v-align-t">
                            <option>5</option>
                            <option>6</option>
                            <option>7</option>
                            <option>8</option>
                            <option>9</option>
                            <option>10</option>
                        </select>
                </div>
            </div>
        </div>
        <div class="d-iblk v-align-t mg-left-15">
            <span id="shape-outline-color-selector" class="color-selector border-color-selector mg-top-5" data-placement="top" data-original-title="<?php _e("Outline color","wpc");?>"></span>
            <br>
            <span id="shape-bg-color-selector" class="bg-color-selector mg-top-10" data-placement="top" data-original-title="<?php _e("Background color","wpc");?>"></span>
        </div>
        <div class="d-iblk v-align-t mg-left-15">
            <div class="mg-top-5">
                <?php _e("Outline width:","wpc");?>
                <div id="shape-thickness-slider" class="noUiSlider horizontal" data-min="0" data-max="10" data-step="1" data-start="0"></div>
            </div>
            <div class="mg-top-20">
                <?php _e("Opacity:","wpc");?>
                <div id="shape-opacity-slider" class="noUiSlider horizontal" data-min="0" data-max="1" data-step="0.1" data-start="1"></div>          
            </div>
        </div>
    </div>
    <?php
}

/**
 * Creates images/vectors elements tools
 */
function get_image_tools()
{
    ?>
    <div id="image-tools-container" style="display: none;">
        <div id="img-effects-container">
                <div>
                     <input type="checkbox" id="grayscale" class="custom-cb">
                     <label for="grayscale" data-placement="top" data-original-title="Grayscale"><?php _e("Grayscale","wpc");?></label>
                     <input type="checkbox" id="invert" class="custom-cb">
                     <label for="invert" data-placement="top" data-original-title="Invert"><?php _e("Invert","wpc");?></label>
                     <input type="checkbox" id="sepia" class="custom-cb">
                     <label for="sepia" data-placement="top" data-original-title="Sepia 1"><?php _e("Sepia 1","wpc");?></label>                     
                     <input type="checkbox" id="sepia2" class="custom-cb">
                     <label for="sepia2" data-placement="top" data-original-title="Sepia 2"><?php _e("Sepia 2","wpc");?></label>
                     <br>
                     <input type="checkbox" id="blur" class="custom-cb">                     
                     <label for="blur" data-placement="top" data-original-title="Blur"><?php _e("Blur","wpc");?></label>
                     <input type="checkbox" id="sharpen" class="custom-cb">
                     <label for="sharpen" data-placement="top" data-original-title="Sharpen"><?php _e("Sharpen","wpc");?></label>
                     <input type="checkbox" id="emboss" class="custom-cb">
                     <label for="emboss" data-placement="top" data-original-title="Emboss"><?php _e("Emboss","wpc");?></label>
                     <div id="clipart-bg-color-container">
                         <!--<span id="clipart-bg-color-selector" class="svg-color-selector" data-placement="top" data-original-title="Background color (SVG files only)"></span>-->
                     </div>
                </div>
                <div>
                    Opacity:
                    <div id="img-opacity-slider" class="noUiSlider horizontal" data-min="0" data-max="1" data-step="0.1" data-start="1"></div>
                </div>
        </div>
        <div id="img-cliparts-container" class="scrollable">
            <div class="scroll-container">
            <?php
            $args=array(
                'numberposts' => -1,
                'post_type'        => 'wpc-cliparts'
                );
            $cliparts_groups= get_posts($args);
//            if(!empty($cliparts_groups))
//            {
                echo '<div id="img-cliparts-accordion" class="Accordion" tabindex="0">';
                echo '<div class="AccordionPanel AccordionPanelOpen" id="uploads-accordion">
                                    <div class="AccordionPanelTab">'.__("Uploads","wpc").' (0)</div>
                                    <div class="AccordionPanelContent" style="height: auto; display: block;">Empty</div>
                            </div>';
                if(isset($_SESSION["wpc-facebook-images"]))
                {
                    echo '<div class="AccordionPanel AccordionPanelOpen" id="facebook-accordion">
                                    <div class="AccordionPanelTab">Facebook images ('.count($_SESSION["wpc-facebook-images"]).')</div>
                                    <div class="AccordionPanelContent" style="height: auto; display: block;">';
                                    foreach ($_SESSION["wpc-facebook-images"] as $facebook_img)
                                    {
                                        echo "<span class='clipart-img'><img src='$facebook_img'></span>";
                                    }
                                echo '</div>
                            </div>';
                }
                if(isset($_SESSION["wpc-instagram-images"]))
                {
                    echo '<div class="AccordionPanel AccordionPanelOpen" id="instagram-accordion">
                                    <div class="AccordionPanelTab">Instagram images ('.count($_SESSION["wpc-instagram-images"]).')</div>
                                    <div class="AccordionPanelContent" style="height: auto; display: block;">';
                                    foreach ($_SESSION["wpc-instagram-images"] as $instagram_img)
                                    {
                                        echo "<span class='clipart-img'><img src='$instagram_img'></span>";
                                    }
                                echo '</div>
                            </div>';
                }
                foreach($cliparts_groups as $cliparts_group)
                {
                    $cliparts= get_post_meta($cliparts_group->ID, "wpc-cliparts", true);
                    $cliparts_prices= get_post_meta($cliparts_group->ID, "wpc-cliparts-prices", true);
                    if(!empty($cliparts))
                    {
                            echo '<div class="AccordionPanel">
                                    <div class="AccordionPanelTab">'.$cliparts_group->post_title.' ('.count($cliparts).')</div>
                                    <div class="AccordionPanelContent">';
                        
                        foreach($cliparts as $i=>$clipart_id)
                        {
                            $attachment=wp_get_attachment_image_src($clipart_id,"full");        
                            $attachment_url=$attachment[0];
                            $price=0;
                            if(isset($cliparts_prices[$i]))
                                $price=$cliparts_prices[$i];
                            echo "<span class='clipart-img'><img src='$attachment_url' data-price='$price'></span>";
                        }
                        echo '</div>
                            </div>';
                    }
                }
                echo '</div>';
//            }
            

            ?>
            </div>
        </div>
        <?php 
            //Create a conflict for admin post page so we disable it
            if(!is_admin())
                get_upload_image_tools();
            else
                echo "<a id='wpc-add-img' class='button'>Add image</a>";
        ?>
    </div>
    <?php
}

/**
 * Creates uploads images tools
 */
function get_upload_image_tools()
{
    $facebook_app_id=  get_option("wpc-facebook-app-id");
    $instagram_app_id=  get_option("wpc-instagram-app-id");
    $uploader=  get_option("wpc-uploader");
    $form_class="custom-uploader";
    if($uploader=="native")
        $form_class="native-uploader"
    ?>
    <form id="userfile_upload_form" class="<?php echo $form_class;?>" method="post" action="<?php echo WPC_URL;?>jquery_calls.php" enctype="multipart/form-data">
        <?php
        if($uploader=="native")
        {
        ?>
            <input type="file" name="userfile" id="userfile">
            <input type="hidden" name="action" value="handle_picture_upload">
        <?php
        }
        else
        {
        ?>        
            <div id="drop">
               <?php echo __("Drop your file here or","wpc");?>
               <a><?php echo __("Select file","wpc");?></a>
               <input type="file" name="userfile" />
            </div>
            <ul>
                        <!-- The file uploads will be shown here --></ul>
        <?php
        }
        ?>
    </form>
    <?php
}

/**
 * Returns user ordered designs
 * @global object $wpdb
 * @param type $user_id
 * @return array
 */
function get_user_orders_designs($user_id)
{
    global $wpdb;
    $designs=array();
    $args = array(
                    'numberposts' => -1,
                    'meta_key' => '_customer_user',
                    'meta_value' => $user_id,
                    'post_type' => 'shop_order',
                    'post_status' => 'publish'
                );

        $orders=get_posts($args);
        foreach ($orders as $order)
        {
            $sql_1="select distinct order_item_id FROM ".$wpdb->prefix."woocommerce_order_items where order_id=$order->ID";
            $order_items_id=$wpdb->get_col($sql_1);
            foreach ($order_items_id as $order_item_id) {
                $sql_2="select meta_key, meta_value FROM ".$wpdb->prefix."woocommerce_order_itemmeta where order_item_id=$order_item_id and meta_key in ('_product_id', '_variation_id', 'wpc_data')";
                $order_item_metas=$wpdb->get_results($sql_2);
                $normalized_item_metas=array();
                foreach ($order_item_metas as $order_item_meta)
                {
                    $normalized_item_metas[$order_item_meta->meta_key]=$order_item_meta->meta_value;
                }
                if(!isset($normalized_item_metas["wpc_data"]))
                    continue;
                
                if($normalized_item_metas["_variation_id"])
                    $product_id=$normalized_item_metas["_variation_id"];
                else
                    $product_id=$normalized_item_metas["_product_id"];
                array_push($designs, array($product_id,$order->post_date,  unserialize($normalized_item_metas["wpc_data"]), $order_item_id));
            }
        }
        return $designs;
}

add_action("init", "set_variable_action_filters");
function set_variable_action_filters()
{
    $woo_version=  wpc_get_woo_version_number();
    if($woo_version<2.1)
        add_filter("woocommerce_in_cart_product_title","get_wpc_variable_attributes",10,2);
    else
        add_filter("woocommerce_cart_item_name","get_wpc_variable_attributes",10,2);
    if(get_option("wpc-parts-position-cart")=="name")
    {
        if($woo_version<2.1)
        {
            //Old WC versions
            add_filter("woocommerce_in_cart_product_title","get_wpc_data",10,3);
            
        }
        else
        {
            //New WC versions
            add_filter("woocommerce_cart_item_name","get_wpc_data",10,3);            
        }
        
    }
    else
    {
        if($woo_version<2.1)
        {
            //Old WC versions
            add_filter("woocommerce_in_cart_product_thumbnail","get_wpc_data",10,3);
        }
        else
        {
            //New WC versions
            add_filter("woocommerce_cart_item_thumbnail","get_wpc_data",10,3);
        }
    }
}

class wpc_retarded_actions
{
    public static $code=array();

    public static function display_code()
    {
        foreach(self::$code as $i=>$current_code)
        {
            echo $current_code;
            unset(self::$code[$i]);
        }
    }
}


function get_wpc_data($thumbnail_code, $values, $cart_item_key)
{
    $variation_id=$values["variation_id"];
    if(isset($_SESSION["wpc_generated_data"][$variation_id][$cart_item_key]))
    {
        $thumbnail_code.="<br>";
        $customization_list=$_SESSION["wpc_generated_data"][$variation_id][$cart_item_key];
        foreach ($customization_list as $customisation_key=>$customization)
        {
            $image=$customization["image"];
            $original_part_img_url=$customization["original_part_img"];
            $modal_id=$variation_id."_".$cart_item_key."$customisation_key-". uniqid();
            
            $thumbnail_code.='<span><a class="button" data-toggle="modal" data-target="#'.$modal_id.'">'.ucfirst($customisation_key).'</a></span>';
            $modal='<div class="modal fade wpc-modal wpc_part" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                  <h4 class="modal-title" id="myModalLabel'.$modal_id.'">Preview</h4>
                                </div>
                                <div class="modal-body">
                                    <div style="background-image:url('.$original_part_img_url.')"><img src="'.$image.'"></div>
                                </div>
                              </div>
                            </div>
                          </div>';
            array_push(wpc_retarded_actions::$code,$modal);
            add_action( 'wp_footer', array( 'wpc_retarded_actions', 'display_code' ), 10, 1 );
        }
        $thumbnail_code.='<a class="button alt" href="'.get_wpc_url($variation_id).'&edit='.$cart_item_key.'">Edit</a>';
        
    }
    else if(isset($_SESSION["wpc-uploaded-designs"][$cart_item_key]))
    {
        $thumbnail_code.="<br>";
        $thumbnail_code.='<span><a class="button" href='.$_SESSION["wpc-uploaded-designs"][$cart_item_key].'>'.__("Custom design","wpc").'</a></span>';
    }
    return $thumbnail_code;
    
}

function get_wpc_variable_attributes($code, $values)
{
    if(isset($values["variation_id"])&&!empty($values["variation_id"])&&$values["variation"]=="wpc_data")
    {
        $product=  get_product($values["variation_id"]);
        $attributes=$product->variation_data;
        if(is_array($attributes)&&!empty($attributes))
        {
            foreach ($attributes as $attribute_key=>$attribute_value)
            {
                $attribute_name=  wpc_get_product_attribute_name($product->id, $attribute_key);
                $code.="<div><strong>$attribute_name</strong>: $attribute_value</div>";
            }
        }
    }
    return $code;
}

//User my account page
add_filter("woocommerce_order_item_quantity_html", "get_user_account_products_meta",11,2);
function get_user_account_products_meta($output, $item)
{
    $download_btn=  get_option("wpc-user-account-download-btn");
    if($download_btn!=="0"&&isset($item["variation_id"])&&!empty($item["variation_id"]))
    {
        $product=  get_product($item["variation_id"]);
        $item_id=  uniqid();
//        var_dump($product);
        ob_start();
        get_order_custom_admin_data($product, $item, $item_id);
        $admin_data=  ob_get_contents();
        ob_end_clean();
        $output.=$admin_data;
    }
    return $output;
}

/**
 * Return the default fonts list
 * @return array
 */
function get_default_fonts()
{
    $default=array(
        array("Shadows Into Light","http://fonts.googleapis.com/css?family=Shadows+Into+Light"),
        array("Droid Sans","http://fonts.googleapis.com/css?family=Droid+Sans:400,700"),
        array("Abril Fatface","http://fonts.googleapis.com/css?family=Abril+Fatface"),
        array("Arvo","http://fonts.googleapis.com/css?family=Arvo:400,700,400italic,700italic"),
        array("Lato","http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic"),
        array("Just Another Hand","http://fonts.googleapis.com/css?family=Just+Another+Hand")
    );
    
    return $default;
}

add_action('wp_enqueue_scripts', 'orion_scripts');
function orion_scripts() {
    wp_enqueue_script('jquery');
    $wpc_page_id = get_option( 'wpc_page_id' );
    if(is_admin())
        $current_page_id=0;
    else
        $current_page_id=  get_the_ID();
    if ( $wpc_page_id ==$current_page_id)
    {
        $fonts=get_option("wpc-fonts");
        if(empty($fonts))
        {
            $fonts=get_default_fonts();
        }

        foreach ($fonts as $font)
        {
            $font_label=$font[0];
            $font_url=$font[1];
            if($font_url)
            {
                $handler=  sanitize_title($font_label)."-css";
                wp_register_style($handler, $font_url, array(), false, 'all');
                wp_enqueue_style($handler);
            }
        }
    }
    
    wp_register_style('simplegrid-css', WPC_URL . 'ressources/css/simplegrid.css', array(), '1.0', 'all');
    wp_enqueue_style('simplegrid-css');
    
    wp_register_script('nouislider-js', WPC_URL . 'ressources/js/noUiSlider/jquery.nouislider.min.js');
    wp_enqueue_script('nouislider-js', array('jquery'), false, false);
    
    wp_register_script('qtip-js', WPC_URL . 'ressources/js/jquery.qtip-1.0.0-rc3.min.js');
    wp_enqueue_script('qtip-js', array('jquery'), false, false);
    
    wp_register_style('nouislider-css', WPC_URL . 'ressources/js/noUiSlider/nouislider.css', array(), '1.0', 'all');
    wp_enqueue_style('nouislider-css');
    
    wp_register_script( 'jquery-blockui-wpc', WPC_URL . 'ressources/js/jquery-blockui/jquery.blockUI.min.js');
    wp_enqueue_script('jquery-blockui-wpc', array('jquery'), false, false);
    
    wp_register_script('SpryAccordion-js', WPC_URL . 'ressources/js/spry/SpryAccordion.js');
    wp_enqueue_script('SpryAccordion-js', array('jquery'), false, false);
    
    wp_register_style('SpryAccordion-css', WPC_URL . 'ressources/js/spry/SpryAccordion.css', array(), '1.0', 'all');
    wp_enqueue_style('SpryAccordion-css');
    
    wp_register_script('scroller-mouse-js', WPC_URL . 'ressources/js/perfectScrollbar/perfect-scrollbar-0.4.10.with-mousewheel.min.js');
    wp_enqueue_script('scroller-mouse-js', array('jquery', 'jquery-ui-mouse'), '1.1', false);
    wp_register_style('scroller-css', WPC_URL . 'ressources/js/perfectScrollbar/perfect-scrollbar-0.4.10.min.css', array(), '1.0', 'all');
    wp_enqueue_style('scroller-css');
    
    wp_register_style('colorpicker-css', WPC_URL . 'ressources/js/colorpicker/css/colorpicker.css');
    wp_enqueue_style('colorpicker-css');
    
    wp_register_script('colorpicker-js', WPC_URL . 'ressources/js/colorpicker/js/colorpicker.js');
    wp_enqueue_script('colorpicker-js', array('jQuery'));
    
    wp_register_script('fabric-js', WPC_URL . 'ressources/js/fabric.all.min.js');
    wp_enqueue_script('fabric-js', array('jquery'), '1.1', false);
    
    $uploader=  get_option("wpc-uploader");
    if($uploader=="native")
    {
        wp_register_script('jquery-form-js', WPC_URL . 'ressources/js/jquery.form.js');
        wp_enqueue_script('jquery-form-js', array('jquery'), '1.1', false);
    }
    else
    {
        wp_register_script('widget', WPC_URL . 'ressources/js/upload/js/jquery.ui.widget.js');
        wp_enqueue_script('widget', array('jquery'), '1.1', false);

        wp_register_style('upload-style', WPC_URL . 'ressources/js/upload/css/style.css');
        wp_enqueue_style('upload-style');

        wp_register_script('fileupload', WPC_URL . 'ressources/js/upload/js/jquery.fileupload.js');
        wp_enqueue_script('fileupload', array('jquery'), '1.1', false);

        wp_register_script('iframe-transport', WPC_URL . 'ressources/js/upload/js/jquery.iframe-transport.js');
        wp_enqueue_script('iframe-transport', array('jquery'), '1.1', false);

        wp_register_script('knob', WPC_URL . 'ressources/js/upload/js/jquery.knob.js');
        wp_enqueue_script('knob', array('jquery'), '1.1', false);
    }
    
    wp_register_script('tooltip-js', WPC_URL . 'ressources/js/tooltip.js');
    wp_enqueue_script('tooltip-js', array('jquery'), '1.1', false);
    
    wp_register_style('modal-css', WPC_URL . 'ressources/js/modal/modal.css');
    wp_enqueue_style('modal-css');
    wp_register_script('modal-js', WPC_URL . 'ressources/js/modal/modal.js');
    wp_enqueue_script('modal-js', array('jquery'));
        
    wp_register_script('front-js', WPC_URL . 'ressources/js/front.js');
    wp_enqueue_script('front-js', array('jquery'), '1.1', false);
    
    wp_localize_script( 'front-js', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );

    wp_register_style('wpc-css', WPC_URL . 'ressources/css/wpc-front.css', array(), '1.0', 'all');
    wp_enqueue_style('wpc-css');
}

add_action( 'woocommerce_after_add_to_cart_button', 'get_customize_btn');
function get_customize_btn(){
    $post_id=get_the_ID();
    $product=  get_product($post_id);
    $is_customizable=  get_post_meta($post_id,"customizable-product",true);
    $design_from_blank = get_post_meta($post_id, 'wpc-design-from-blank', true);
    if($is_customizable)
    {
        if($design_from_blank)
        {
            ?><input type="button" value="<?php _e("Design from blank","wpc");?>" data-id="<?php echo $post_id;?>" data-type="<?php echo $product->product_type;?>" class="mg-top-10 wpc-customize-product"/><?php
        }
    }
}

add_action("woocommerce_add_cart_item","set_custom_upl_cart_item_data",10,2);
function set_custom_upl_cart_item_data($cart_item_data, $cart_item_key )
{
    $product_id=$cart_item_data["product_id"];
    $variation_id=$cart_item_data["variation_id"];
    $element_id=$product_id;
    if(isset($variation_id)&&!empty($variation_id))
        $element_id=$variation_id;
    return $cart_item_data;
}

add_filter("woocommerce_loop_add_to_cart_link","get_customize_btn_loop",10,2);
function get_customize_btn_loop($html,$product)
{
    $is_customizable=  get_post_meta($product->id,"customizable-product",true);
    if($is_customizable)
    {
        $design_from_blank = get_post_meta($product->id, 'wpc-design-from-blank', true);
        if($product->product_type=="simple"&&$design_from_blank)
            $html.='<input type="button" value="'. __("Design from blank","wpc").'" data-id="'.$product->id.'" data-type="'.$product->product_type.'" class="mg-top-10 wpc-customize-product"/>';
    }
    return $html;
}
function save_pdf_output($generation_path, $input_file, $output_file)
{
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf->SetCreator("Woocommerce Products Designer by ORION");
    $pdf->SetAuthor('Woocommerce Products Designer by ORION');
    $pdf->SetTitle('Output');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
    }
    
    $pdf->AddPage();

    // NOTE: Uncomment the following line to rasterize SVG image using the ImageMagick library.
    //$pdf->setRasterizeVectorImages(true);

//    $pdf->ImageSVG($file=$input_file, $x='', $y='', $w='', $h='', $link='', $align='', $palign='', $border=0, $fitonpage=true);
//    $pdf->Image($input_file, $x='', '', '', '', '', '', '', false, 300);
    $pdf->Image($input_file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=true, $alt=false, $altimgs=array());
//    $this->Image($input_file, 'C', 6, '', '', '', false, 'C', false, 300, 'C', false, false, 0, false, false, false);
//    Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false, $alt=false, $altimgs=array())

    $pdf->Output($output_file, 'F');
}

add_action( 'wp_ajax_generate_downloadable_file', 'generate_downloadable_file' );
add_action( 'wp_ajax_nopriv_generate_downloadable_file', 'generate_downloadable_file' );
function generate_downloadable_file()
{
    $final_canvas_parts=$_POST["final_canvas_parts"];
    $tmp_dir=  uniqid();
    $upload_dir=  wp_upload_dir();
    $generation_path = $upload_dir["basedir"]."/WPC/$tmp_dir";
    $generation_url = $upload_dir["baseurl"]."/WPC/$tmp_dir";
    $variation_id=$_POST["variation_id"];
    if(wp_mkdir_p($generation_path))
    {
        $generation_url = $upload_dir["baseurl"]."/WPC/$tmp_dir";

        $result=export_data_to_files($generation_path, $final_canvas_parts, $generation_url, false, $variation_id);
        if($result)
            echo json_encode(array(
                "success"=>1,
                "message"=>"<div class='wpc-success'>".$result."</div>",
                    )
                );
        else
            echo json_encode(array(
                "success"=>0,
                "message"=>"<div class='wpc-failure'>".__( "An error occured in the generation process. Please try again later.","wpc")."</div>",
                    )
                );
    }
    else
        echo json_encode(array(
                "success"=>0,
                "message"=>"<div class='wpc-failure'>".__( "Can't create a generation directory...","wpc")."</div>",
                    )
                );
            die();
}

/**
 * Export data to archive
 * @param string $generation_dir Working directory path
 * @param array $data Data to export
 * @return boolean|string
 */
function export_data_to_files($generation_dir, $data, $generation_url, $force_zip, $variation_id)
{
    $generate_layers=  (get_option("wpc-generate-layers")==="yes")?true:false;
    $generate_pdf=  (get_option("wpc-generate-pdf")==="yes")?true:false;
    $generate_zip=  (get_option("wpc-generate-zip")==="yes")?true:false;
    $output_msg=false;
    $output_arr=array();
    foreach ($data as $part_key=> $part_data)
    {
        $part_dir="$generation_dir/$part_key";
        if(!wp_mkdir_p($part_dir))
        {
            echo "Can't create part directory...";
            continue;
        }
        //Layers
        if($generate_layers)
        {
            $part_layers_dir="$part_dir/layers";
            if(!wp_mkdir_p($part_layers_dir))
            {
                echo "Can't create layers directory...";
                continue;
            }
            if(isset($part_data["layers"]))
                $layers=$part_data["layers"];
            else
                continue;
            
            foreach ($layers as $layer_data)
            {
                $usable_data=substr($layer_data, strpos($layer_data, ",") + 1);
                $decodedData = base64_decode($usable_data);
                $file_name=  uniqid("wpc_layer_");
                $output_file_path=$part_layers_dir."/$file_name.png";
                $fp = fopen($output_file_path, 'wb');
                fwrite($fp, $decodedData);
                fclose($fp);
            }
        }
        
        //Part image
        $usable_data=substr($part_data["image"], strpos($part_data["image"], ",") + 1);
        $decodedData = base64_decode($usable_data);
        $file_name=  $part_key;
        $output_file_path=$part_dir."/$file_name.png";
        $fp = fopen($output_file_path, 'wb');
        fwrite($fp, $decodedData);
        fclose($fp);
        
        if(!$generate_pdf)
            $output_msg.="<div>".ucfirst($part_key).__(": please click ","wpc")."<a href='$generation_url/$part_key/$part_key.png' download='$part_key.png'>".__( "here","wpc")."</a> ".__( "to download","wpc").".</div>";
        
        //Part pdf
        if($generate_pdf)
        {
//            if ($nbCol==''|| $nbCol=='0'||$total_img==''||$total_img=='0') {
//                $nbCol = get_option("wpc-outputpdf-img-col-number");
//                $total_img = get_option("wpc-outputpdf-img-number");
//            }

            $output_pdf_file_path=$part_dir."/$part_key.pdf";
            //save_pdf_output($generation_dir, $output_file_path, $output_pdf_file_path);
            save_pdf_output($generation_dir, $output_file_path, $output_pdf_file_path);
            $download=false;
            if($download)
                $handler="download='$part_key.pdf'";
            else
                $handler="class='print_pdf'";
            $output_msg.="<div>".ucfirst($part_key).__(": please click ","wpc")."<a href='$generation_url/$part_key/$part_key.pdf' $handler>".__( "here","wpc")."</a> ".__( "to download","wpc").".</div>";
        }
        
    }
    
    
    if($force_zip)
    {
        $zip_name=  uniqid("wpc_").".zip";
        $result=  create_zip($generation_dir, "$generation_dir/$zip_name");
        if($result)
            return $zip_name;
        else
            return false;
    }
    else if($generate_zip)
    {
        $zip_name=  uniqid("wpc_").".zip";
        $result=  create_zip($generation_dir, "$generation_dir/$zip_name");
        
        if($result)
            $output_msg="<div>".__("The generation has been successfully completed. Please click ","wpc")."<a href='$generation_url/$zip_name' download='$zip_name'>".__( "here","wpc")."</a> ".__( "to download your design","wpc").".</div>";
        else
            $output_msg=false;
    }
    return $output_msg;
}

/**
 * Creates a compressed zip file
 * @param type $source Input directory path to zip
 * @param type $destination Output file path
 * @return boolean
 */
function create_zip($source, $destination)
{
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $source = str_replace('\\', DIRECTORY_SEPARATOR, realpath($source));

    if (is_dir($source) === true)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file)
        {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);

            // Ignore "." and ".." folders
            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                continue;

            $file = realpath($file);

            if (is_dir($file) === true)
                $zip->addEmptyDir(str_replace($source .DIRECTORY_SEPARATOR, '', $file . DIRECTORY_SEPARATOR));
            else if (is_file($file) === true)
                $zip->addFromString(str_replace($source . DIRECTORY_SEPARATOR, '', $file), file_get_contents($file));
        }
    }
    else if (is_file($source) === true)
    {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}

add_shortcode('wpc-products','get_wpc_products_display');
function get_wpc_products_display($atts){
    extract( shortcode_atts( array(
		'cat' => '',
                'products' => '',
                'cols' => '3'
	), $atts, 'wpc-products' ) );
    $args = array( 
            'posts_per_page' => 12, 
            'post_type'=> 'product',
            'product_cat'=>$cat,
            'meta_query'=> array(
                                    array(
                                        'key' => 'customizable-product',
                                        'value' => '1'
                                        )
                                )
            );
                $product_arr=array();
            if(!empty($products))
            {
                $product_arr=  explode(",", $products);
                $args["post__in"]= $product_arr;
            }
    $products = get_posts( $args );
    
    ob_start();
        
?>
	<div class='container wp-products-container wpc-grid wpc-grid-pad'>
<?php
	
	$shop_currency_symbol=get_woocommerce_currency_symbol();

	//var_dump($categories); wpc_custom
		foreach ($products as $product) {
			$prod=get_product($product->ID);
                        $design_from_blank = get_post_meta($product->ID, 'wpc-design-from-blank', true);
?>
				<div class='wpc-col-1-<?php echo $cols;?> cat-item-ctn'>
					<div class='cat-item'>
						<h3><?php  echo $product->post_title; ?> <span><?php echo $shop_currency_symbol.''.$prod->price ?></span></h3>
						<?php  echo get_the_post_thumbnail($product->ID, 'medium'); ?>
						<hr>
                                                <?php
                                                    
                                                    if($design_from_blank)
                                                    {
                                                        ?><a href="<?php echo get_wpc_url($product->ID) ?>" class='btn-choose'> Design from blank</a><?php
                                                    }
                                                ?>
					</div>
				</div>
<?php 
			}
            ?>
	</div>
        <?php
        $output=  ob_get_contents();
        ob_end_clean();
        return $output;
}

add_action( 'wp_ajax_add_custom_design_to_cart', 'add_custom_design_to_cart_ajax' );
add_action( 'wp_ajax_nopriv_add_custom_design_to_cart', 'add_custom_design_to_cart_ajax' );
function add_custom_design_to_cart_ajax()
{
    global $woocommerce;
    $cart_url = $woocommerce->cart->get_cart_url();
    $final_canvas_parts=$_POST["final_canvas_parts"];
    $variation_id=$_POST["variation_id"];
    $quantity=$_POST["quantity"];
    $cart_item_key=$_POST["cart_item_key"];
    if($cart_item_key)
    {
        $_SESSION["wpc_generated_data"][$variation_id][$cart_item_key]=$final_canvas_parts;
        $result=true;
        $message="<div class='wpc_notification success f-right'>Item successfully updated. <a href='$cart_url'>View Cart</a></div>";
    }
    else
    {
        $variable_product=get_product($variation_id);
        if($variable_product->product_type=="simple")
            $product_id=$variation_id;
        else
            $product_id=$variable_product->parent->id;
        $result=$woocommerce->cart->add_to_cart( $product_id, $quantity, $variation_id, "wpc_data", $final_canvas_parts);
        if(method_exists($woocommerce->cart,"maybe_set_cart_cookies"))
            $woocommerce->cart->maybe_set_cart_cookies();
        if($result)
            $message="<div class='wpc_notification success f-right'>".__( "Product successfully added to basket.","wpc")." <a href='$cart_url'>View Cart</a></div>";
        else
            $message="<div class='wpc_notification failure f-right'>".__( "A problem occured. Please try again.","wpc")."</div>";
    }
    echo json_encode(array("success"=>$result,
                           "message"=>$message,
                            "url"=>$cart_url
                            ));
    die();
}

add_action( 'wp_ajax_save_custom_design_for_later', 'save_custom_design_for_later_ajax' );
add_action( 'wp_ajax_nopriv_save_custom_design_for_later', 'save_custom_design_for_later_ajax' );
function save_custom_design_for_later_ajax()
{
    $final_canvas_parts=$_POST["final_canvas_parts"];
    $variation_id=$_POST["variation_id"];
    $cart_item_key="";
    if(isset($_POST["cart_item_key"]))
        $cart_item_key=$_POST["cart_item_key"];
    $is_logged=0;
    $result=0;
    $message="";
    $url=wp_login_url(get_wpc_url($variation_id));
    if(is_user_logged_in())
    {
        global $current_user;
        get_currentuserinfo();
        $message=$current_user->ID;
        $is_logged=1;
        $today = date("Y-m-d H:i:s");     
        $result=add_user_meta($current_user->ID, "wpc_saved_designs", array($variation_id,$today,$final_canvas_parts));
        if($result)
        {
            $result=1;
            $message="<div class='wpc_notification success'>".__( "The design has successfully been saved to your account.","wpc")."</div>";
            $user_designs=get_user_meta($current_user->ID, 'wpc_saved_designs');
            $newly_added_index=  count($user_designs)-1;
            $url=get_wpc_url($variation_id)."&design_index=".$newly_added_index;
        }
        else
        {
            $result=0;
            $message="<div class='wpc_notification failure'>".__( "An error has occured. Please try again later or contact the administrator.","wpc")."</div>";
        }
    }
    else
    {
        if(!isset($_SESSION['wpc_designs_to_save']))
            $_SESSION['wpc_designs_to_save']=array();
        if(!isset($_SESSION['wpc_designs_to_save'][$variation_id]))
            $_SESSION['wpc_designs_to_save'][$variation_id]=array();
        
        array_push($_SESSION['wpc_designs_to_save'][$variation_id], $final_canvas_parts);
    }
    echo json_encode(array( "is_logged"=>$is_logged,
                            "success"=>$result,
                            "message"=>$message,
                            "url"=>$url
                            )
                    );
    die();
}

?>