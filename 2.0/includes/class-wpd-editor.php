<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-wpd-editor
 *
 * @author HL
 */
class WPD_Editor {

    private $item_id;
    private $root_item_id;

    public function __construct($item_id) {
        if ($item_id) {
            $this->item_id = $item_id;
            $this->root_item_id = WPD_Product::get_parent($item_id);
        }
    }

    function get_editor() {
        GLOBAL $wpc_options_settings, $wp_query;
        $wpd_query_vars = array();

        ob_start();
        $product = get_product($this->item_id);
        if (!WPD_Product::has_part($this->root_item_id)) {
            _e('Error: No active part defined for this product. A customizable product should have at least one part defined.', 'wpd');
            return;
        }
        $wpc_metas = get_post_meta($this->root_item_id, 'wpc-metas', true);
        $general_options = $wpc_options_settings['wpc-general-options'];
        $product_price = $product->price;
//        $shop_currency_symbol=get_woocommerce_currency_symbol();
        $colors_options = $wpc_options_settings['wpc-colors-options'];
        $wpc_output_options = $wpc_options_settings['wpc-output-options'];
        if (isset($wpc_output_options['wpc-generate-layers']) && $wpc_output_options['wpc-generate-layers'] === "yes")
            $generate_layers = true;
        else
            $generate_layers = false;
        
        $product_metas = WPD_Admin::get_proper_value($wpc_metas, $this->item_id, array());

        $canvas_w = WPD_Product::get_option($product_metas, $general_options, "canvas-w", 800);
        $canvas_h = WPD_Product::get_option($product_metas, $general_options, "canvas-h", 500);
        $watermark= WPD_Admin::get_proper_value($product_metas, "watermark", "");
        
        $bounding_box_array = WPD_Admin::get_proper_value($wpc_metas, 'bounding_box', array());
        $clip_w = WPD_Admin::get_proper_value($bounding_box_array, "width", "");
        $clip_h = WPD_Admin::get_proper_value($bounding_box_array, "height", "");
        $clip_x = WPD_Admin::get_proper_value($bounding_box_array, "x", "");
        $clip_y = WPD_Admin::get_proper_value($bounding_box_array, "y", "");
        $clip_radius = WPD_Admin::get_proper_value($bounding_box_array, "radius", "");
        $clip_radius_rect = WPD_Admin::get_proper_value($bounding_box_array, "r_radius", 0);
        $clip_type = WPD_Admin::get_proper_value($bounding_box_array, "type", "");
        $clip_border = WPD_Admin::get_proper_value($bounding_box_array, "border_color", "");

        $wpc_output_product_settings = WPD_Admin::get_proper_value($product_metas, 'output-settings', array());
        $output_w = WPD_Product::get_option($wpc_output_product_settings, $wpc_output_options, "wpc-min-output-width",$canvas_w);
        $output_loop_delay = WPD_Product::get_option($wpc_output_product_settings, $wpc_output_options, "wpc-output-loop-delay",1000);
       
        $svg_colorization = WPD_Admin::get_proper_value($colors_options, "wpc-svg-colorization", 'by-path');
        $wpc_palette_type = WPD_Admin::get_proper_value($colors_options, 'wpc-color-palette', 'unlimited');
        $palette = WPD_Admin::get_proper_value($colors_options, 'wpc-custom-palette', '');
        $palette_tpl = "";

        if (isset($general_options['wpc-redirect-after-cart']) && !empty($general_options['wpc-redirect-after-cart']))
            $redirect_after = $general_options['wpc-redirect-after-cart'];
        else
            $redirect_after = 0;

        $wpc_img_format = "png";

        if (!empty($palette) && is_array($palette)) {
            foreach ($palette as $color) {
                $hex = str_replace("#", "", $color);
                $palette_tpl.='<span style="background-color: ' . $color . '" data-color="' . $hex . '" class="wpc-custom-color"></span>';
            }
        }
        if (isset($wp_query->query_vars["edit"])) {
            $variation_id = $wp_query->query_vars["product_id"];
            $cart_item_key = $wp_query->query_vars["edit"];
            $wpd_query_vars["edit"] = $cart_item_key;
            $data = $_SESSION["wpc_generated_data"][$variation_id][$cart_item_key];
            //Useful when editing cart item
            $data = stripslashes_deep($data);
        } else if (isset($wp_query->query_vars["oid"])) {
            $order_item_id = $wp_query->query_vars["oid"];
            $wpd_query_vars["oid"] = $order_item_id;
            $sql = "select meta_value FROM " . $wpdb->prefix . "woocommerce_order_itemmeta where order_item_id=$order_item_id and meta_key='wpc_data'";
            //echo $sql;
            $wpc_data = $wpdb->get_var($sql);
            $data = unserialize($wpc_data);
        }

        if (isset($data) && !empty($data)) {
            $design = new WPD_Design();
            $a_price = $design->get_additional_price($this->root_item_id, $data);
            $product_price+=$a_price;
            ?>
            <script>
                var to_load =<?php echo json_encode($data); ?>;
            </script>
            <?php
        }

        $editor_params = array(
            "canvas_w" => $canvas_w,
            "canvas_h" => $canvas_h,
            "watermark" => $watermark,
            "clip_w" => $clip_w,
            "clip_h" => $clip_h,
            "clip_x" => $clip_x,
            "clip_r" => $clip_radius,
            "clip_rr" => $clip_radius_rect,
            "clip_y" => $clip_y,
            "clip_type" => $clip_type,
            "clip_border" => $clip_border,
            "output_w" => $output_w,
            "output_loop_delay" => $output_loop_delay,
            "svg_colorization" => $svg_colorization,
            "palette_type" => $wpc_palette_type,
            "print_layers" => $generate_layers,
            "output_format" => $wpc_img_format,
            "global_variation_id" => $this->item_id,
            "redirect_after" => $redirect_after,
            "palette_tpl" => $palette_tpl,
            "translated_strings" => array(
                "loading_msg" => __("Just a moment", "wpd"),
                "deletion_error_msg" => __("The deletion of this object is not allowed", "wpd"),
                "loading_msg" => __("Just a moment", "wpd"),
                "empty_object_msg" => __("The edition area is empty.", "wpd"),
                "delete_all_msg" => __("Do you really want to delete all items in the design area ?", "wpd"),
                "delete_msg" => __("Do you really want to delete the selected items ?", "wpd"),
                "empty_txt_area_msg" => __("Please enter the text to add.", "wpd"),
            ),
            "query_vars" => $wpd_query_vars,
        );

        $this->register_styles();
        $this->register_scripts();

        $text_options = WPD_Admin::get_proper_value($wpc_options_settings, 'wpc-texts-options', array());
        $shapes_options = WPD_Admin::get_proper_value($wpc_options_settings, 'wpc-shapes-options', array());
        $cliparts_options = WPD_Admin::get_proper_value($wpc_options_settings, 'wpc-images-options', array());
        $uploads_options = WPD_Admin::get_proper_value($wpc_options_settings, 'wpc-upload-options', array());
        $designs_options = WPD_Admin::get_proper_value($wpc_options_settings, 'wpc-designs-options', array());

        $text_tab_visible = WPD_Admin::get_proper_value($text_options, 'visible-tab', 'yes');
        $shape_tab_visible = WPD_Admin::get_proper_value($shapes_options, 'visible-tab', 'yes');
        $clipart_tab_visible = WPD_Admin::get_proper_value($cliparts_options, 'visible-tab', 'yes');
        $design_tab_visible=WPD_Admin::get_proper_value($designs_options,'visible-tab', 'yes');       
        $upload_tab_visible = WPD_Admin::get_proper_value($uploads_options, 'visible-tab', 'yes');
        ?>
        <script>
            var wpd =<?php echo json_encode($editor_params); ?>;
        </script>
        <div class='wpc-container'>
            <?php $this->get_toolbar(); ?>
            <div class="wpc-editor-wrap">
                <div class="wpc-editor-col">
                    <div id="wpc-tools-box-container" class="Accordion" tabindex="0">
                        <?php if ($text_tab_visible == "yes") { ?>
                            <div class="AccordionPanel" id="text-panel">
                                <div id="text-tools" class="AccordionPanelTab"><?php _e("TEXT", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php $this->get_text_tools($text_options); ?>
                                </div>
                            </div>
                            <?php
                        }
                        if ($shape_tab_visible == "yes") {
                            ?>
                            <div class="AccordionPanel" id="shapes-panel">
                                <div id="shapes-tools" class="AccordionPanelTab"><?php _e("SHAPES", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php $this->get_shapes_tools($shapes_options); ?>
                                </div>
                            </div>
                            <?php
                        }
                        if ($upload_tab_visible == "yes") {
//Create a conflict for admin post page so we disable it
//                            if (!is_admin()) {
                            ?>
                            <div class="AccordionPanel" id="uploads-panel">
                                <div id="upload-tools" class="AccordionPanelTab"><?php _e("UPLOADS", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php $this->get_uploads_tools($uploads_options); ?>                                 
                                </div>
                            </div>
                            <?php
//                                    }
                        }
                        if ($clipart_tab_visible == "yes") {
                            ?>
                            <div class="AccordionPanel" id="cliparts-panel">
                                <div id="arts-tools" class="AccordionPanelTab"><?php _e("CLIPARTS", "wpd"); ?></div>
                                <div class="AccordionPanelContent">
                                    <?php $this->get_images_tools($cliparts_options); ?>                                 
                                </div>
                            </div>
                            <?php
                        }
                        
                        if ($design_tab_visible == "yes") {
                        ?>

                        <div class="AccordionPanel" id="user-designs-panel">
                            <div id="designs-tools" class="AccordionPanelTab"><?php _e("MY DESIGNS", "wpd"); ?></div>
                            <div class="AccordionPanelContent">
                                <?php $this->get_user_designs_tools(); ?>
                            </div>
                        </div>
                        <?php
                        }
                        ?>

                    </div>

                </div>
                <div class="wpc-editor-col-2">
                    <div id="wpc-editor-container">
                        <canvas id="wpc-editor" ></canvas>
                    </div>

                    <div id="product-part-container" class="">
                        <?php $this->get_parts(); ?>
                    </div>
                    <?php 
                        if(!is_admin()){
                            WPD_Design::get_option_form($this->root_item_id, $wpc_metas);
                        }
                    ?>
                    <div id="debug"></div>

                </div>
                <?php
                //We don't show the column at all if there is nothing to show inside
        
        if(isset($general_options['wpc-download-btn']))
            $download_btn=$general_options['wpc-download-btn'];
        
        if(isset($general_options['wpc-preview-btn']))
            $preview_btn= $general_options['wpc-preview-btn'];
        
        if(isset($general_options['wpc-cart-btn']))
            $cart_btn=  $general_options['wpc-cart-btn'];
        
        if  (
                (isset($preview_btn) && $preview_btn!=="0") || 
                (isset($download_btn) && $download_btn!=="0") || 
                (isset($save_btn) && $save_btn!=="0") ||
                (isset($cart_btn) && $cart_btn!=="0")
            )
        {
                ?>
                <div class=" wpc-editor-col">
                    <?php
                    $this->get_design_actions_box();

                    if (!is_admin())
                        $this->get_cart_actions_box();
                    ?>                      
                </div>
                <?php
        }
                ?>
            </div>

        </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    private function fix_template_data($tpl_id) {
        GLOBAL $wpdb;
        $sql = "select meta_value from $wpdb->postmeta where post_id='$tpl_id' and meta_key='data'";
//            var_dump($sql);
        $value = $wpdb->get_var($sql);
        //Replace the line breaks (create an issue during the import)
        $value = mb_eregi_replace("\n", "|n", $value);

        $data = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $value);
        $data = unserialize($data);
//            var_dump($data);
        if ($data)
            update_post_meta($tpl_id, "data", stripslashes_deep($data));
        return $data;
    }

    private function get_toolbar() {
        ?>
        <div id="wpc-buttons-bar">
        <!--        <button id="zoom-in-btn" data-placement="top" data-original-title="<?php // _e("Zoom in","wpd"); ?>"></button>
        <button id="zoom-out-btn" data-placement="top" data-original-title="<?php // _e("Zoom out","wpd"); ?>"></button>
        <button id="zoom-reset-btn" data-placement="top" data-original-title="<?php // _e("Zoom reset","wpd"); ?>"></button>-->
            <span id="grid-btn" data-placement="top" data-original-title="<?php _e("grid", "wpd"); ?>"></span>
            <span id="clear_all_btn" data-placement="top" data-original-title="<?php _e("Clear all", "wpd"); ?>"></span>
            <span id="delete_btn" data-placement="top" data-original-title="<?php _e("Delete", "wpd"); ?>"></span>
            <span id="copy_paste_btn" data-placement="top" data-original-title="<?php _e("Duplicate", "wpd"); ?>"></span>
            <span id="send_to_back_btn" data-placement="top" data-original-title="<?php _e("Send to back", "wpd"); ?>"></span>
            <span id="bring_to_front_btn" data-placement="top" data-original-title="<?php _e("Bring to front", "wpd"); ?>"></span>
            <span id="flip_h_btn" data-placement="top" data-original-title="<?php _e("Flip horizontally", "wpd"); ?>"></span>
            <span id="flip_v_btn" data-placement="top" data-original-title="<?php _e("Flip vertically", "wpd"); ?>"></span>
            <span id="align_h_btn" data-placement="top" data-original-title="<?php _e("Center horizontally", "wpd"); ?>"></span>
            <span id="align_v_btn" data-placement="top" data-original-title="<?php _e("Center vertically", "wpd"); ?>"></span>
            <span id="undo-btn" data-placement="top" data-original-title="<?php _e("Undo", "wpd"); ?>"></span>
            <span id="redo-btn" data-placement="top" data-original-title="<?php _e("Redo", "wpd"); ?>"></span>
        </div>
        <?php
    }

    private function get_text_tools($text_options) {
        $font_family = WPD_Admin::get_proper_value($text_options, 'font-family', 'yes');
        $font_size = WPD_Admin::get_proper_value($text_options, 'font-size', 'yes');
        $bold = WPD_Admin::get_proper_value($text_options, 'bold', 'yes');
        $italic = WPD_Admin::get_proper_value($text_options, 'italic', 'yes');
        $text_color = WPD_Admin::get_proper_value($text_options, 'text-color', 'yes');
        $background_color = WPD_Admin::get_proper_value($text_options, 'background-color', 'yes');
        $outline_width = WPD_Admin::get_proper_value($text_options, 'outline-width', 'yes');
        $outline = WPD_Admin::get_proper_value($text_options, 'outline', 'yes');
        $curved = WPD_Admin::get_proper_value($text_options, 'curved', 'yes');
        $text_radius = WPD_Admin::get_proper_value($text_options, 'text-radius', 'yes');
        $text_spacing = WPD_Admin::get_proper_value($text_options, 'text-spacing', 'yes');
        $opacity = WPD_Admin::get_proper_value($text_options, 'opacity', 'yes');
        $text_alignment = WPD_Admin::get_proper_value($text_options, 'text-alignment', 'yes');
        $underline = WPD_Admin::get_proper_value($text_options, 'underline', 'yes');
        $text_strikethrough = WPD_Admin::get_proper_value($text_options, 'text-strikethrough', 'yes');
        $text_overline = WPD_Admin::get_proper_value($text_options, 'text-overline', 'yes');

        //var_dump($text_options);

        $fonts = get_option("wpc-fonts");
        if (empty($fonts)) {
            $fonts = $this->get_default_fonts();
        }
        ?>
        <div class="text-tool-container dspl-table">
            <div >
                <span class="text-label"><?php _e("Text", "wpd"); ?></span>
                <span class="">
                    <textarea id = "new-text" class="text-element-border text-container "></textarea>
                    <button id="wpc-add-text" class="wpc-btn-effect"><?php _e("ADD", "wpd"); ?></button>
                </span>
            </div>
            <?php
            if ($font_family == "yes") {
                ?>
                <div >
                    <span >Font</span>
                    <span class="font-selector-container ">
                        <select id="font-family-selector" class="font-selector text-element-border">
                            <?php
                            foreach ($fonts as $font) {
                                $font_label = $font[0];
                                echo "<option data-font_style = 'font-family:$font_label' value = '$font_label' >$font_label</option>";
                            }
                            ?>
                        </select>
                    </span>
                </div>
                <?php
            }
            if ($font_size == "yes") {
                ?>
                <div >
                    <span><?php _e("Size", "wpd"); ?></span>
                    <span >
                        <!--<input id="font-size-selector" type="number" class="text-element-border size-set" value="14">-->
                        <?php
                        $options = array();
                        for ($i = 8; $i <= 30; $i++) {
                            $options[$i] = $i;
                        }
                        echo $this->get_html_select("font-size-selector", "font-size-selector", "text-element-border text-tools-select", $options, 30);
                        ?>
                    </span>
                </div>
                <?php
            }
            if ($bold == "yes" || $italic == "yes" || $text_color == "yes" || $background_color == "yes") {
                ?>
                <div >
                    <span>
                        <?php _e("Style", "wpd"); ?>
                    </span> 
                    <div class="mg-r-element ">
                        <?php
                        if ($bold == "yes") {
                            ?>
                            <input type="checkbox" id="bold-cb" class="custom-cb">
                            <label for="bold-cb" data-placement="top" data-original-title="<?php _e("Bold", "wpd"); ?>"></label>
                            <?php
                        }
                        if ($italic == "yes") {
                            ?>
                            <input type="checkbox" id="italic-cb" class="custom-cb">
                            <label for="italic-cb" data-placement="top" data-original-title="<?php _e("Italic", "wpd"); ?>"></label>
                            <?php
                        }
                        if ($text_color == "yes") {
                            ?>
                            <span id="txt-color-selector" class=" "  data-placement="top" data-original-title="<?php _e("Text color", "wpd"); ?>" style="background-color: #4f71b9;"></span>
                            <?php
                        }
                        if ($background_color == "yes") {
                            ?>
                            <span id="txt-bg-color-selector" class="bg-color-selector " data-placement="top" data-original-title="<?php _e("Background color", "wpd"); ?>" style="background-color: #4f71b9;"></span>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            if ($outline_width == "yes" || $outline == "yes") {
                ?>
                <div>
                    <span ><?php _e("Outline", "wpd"); ?>
                    </span>
                    <div>
                        <?php
                        if ($outline_width == "yes") {
                            ?>
                            <label  for="o-thickness-slider" class=" width-label"><?php _e("Width", "wpd"); ?></label>
                            <?php
                            $options = array(0 => __("None", "wpd"), 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
                            echo $this->get_html_select("o-thickness-slider", "o-thickness-slider", "text-element-border text-tools-select", $options);
                        }
                        if ($outline == "yes") {
                            ?>
                            <div class="color-container">
                                <label for="color" class=" color-label"><?php _e("Color", "wpd"); ?></label> 
                                <span id="txt-outline-color-selector" class="bg-color-selector " data-placement="top" data-original-title="<?php _e("Background color", "wpd"); ?>" style="background-color: #4f71b9;"></span>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                </div>
                <?php
            }
            if ($curved == "yes") {
                ?>
                <div >
                    <span>Curved</span>
                    <div>
                        <input type="checkbox" id="cb-curved" class="custom-cb checkmark"> 
                        <label for="cb-curved" id="cb-curved-label" ></label>

                        <label for="radius" class="radius-label "><?php _e("Radius", "wpd"); ?></label>
                        <?php
                        $options = array();
                        for ($i = 1; $i <= 20; $i++) {
                            array_push($options, $i);
                        }
                        echo $this->get_html_select("spacing", "curved-txt-spacing-slider", "text-element-border text-tools-select", $options, 9);
                        ?>
                        <div class="spacing-container">
                            <label for="spacing" class="spacing-label "><?php _e("Spacing", "wpd"); ?></label>
                            <?php
                            $options = array();
                            for ($i = 0; $i <= 30; $i++) {
                                $options[$i * 10] = $i * 10;
                            }
                            echo $this->get_html_select("radius", "curved-txt-radius-slider", "text-element-border text-tools-select", $options, 150);
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            if ($opacity == "yes") {
                ?>
                <div>
                    <span ><?php _e("Opacity", "wpd"); ?></span>
                    <span >
                        <?php
                        $this->get_opacity_dropdown("opacity", "opacity-slider", "text-element-border text-tools-select");
                        ?>
                    </span>
                </div>
                <?php
            }
            if ($text_alignment == "yes") {
                ?>
                <div>
                    <span><?php _e("Alignment", "wpd"); ?></span>
                    <div class="mg-r-element">
                        <input type="radio" id="txt-align-left" name="radio" class="txt-align" value="left"/>
                        <label for="txt-align-left" ><span></span></label>

                        <input type="radio" id="txt-align-center" name="radio" class="txt-align" value="center"/>
                        <label for="txt-align-center"><span ></span></label>

                        <input type="radio" id="txt-align-right" name="radio" class="txt-align" value="right"/>
                        <label for="txt-align-right"><span ></span></label>

                    </div>

                </div>
                <?php
            }
            if ($underline == "yes" || $text_strikethrough == "yes" || $text_overline == "yes") {
                ?>
                <div >
                    <span><?php _e("Decoration", "wpd"); ?></span>
                    <div class=" mg-r-element">
                        <?php
                        if ($underline == "yes") {
                            ?>
                            <input type="radio" id="underline-cb" name="txt-decoration" class="txt-decoration" value="underline">
                            <label for="underline-cb" data-placement="top" data-original-title="<?php _e("Underline", "wpd"); ?>"><span></span></label>
                            <?php
                        }
                        if ($text_strikethrough == "yes") {
                            ?>
                            <input type="radio" id="strikethrough-cb" name="txt-decoration" class="txt-decoration" value="line-through">
                            <label for="strikethrough-cb" data-placement="top" data-original-title="<?php _e("Strikethrough", "wpd"); ?>"><span></span></label>
                            <?php
                        }
                        if ($text_overline == "yes") {
                            ?>
                            <input type="radio" id="overline-cb" name="txt-decoration" class="txt-decoration" value="overline">
                            <label for="overline-cb" data-placement="top" data-original-title="<?php _e("Overline", "wpd"); ?>"><span></span></label>
                            <?php
                        }
                        ?>
                        <input type="radio" id="txt-none-cb" name="txt-decoration" class="txt-decoration" value="none">
                        <label for="txt-none-cb" data-placement="top" data-original-title="<?php _e("None", "wpd"); ?>"><span></span></label>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    private function get_shapes_tools($shapes_options) {
        $background_color = WPD_Admin::get_proper_value($shapes_options, 'background-color', 'yes');
        $outline_width = WPD_Admin::get_proper_value($shapes_options, 'outline-width', 'yes');
        $outline = WPD_Admin::get_proper_value($shapes_options, 'outline', 'yes');
        $opacity = WPD_Admin::get_proper_value($shapes_options, 'opacity', 'yes');
        $square = WPD_Admin::get_proper_value($shapes_options, 'square', 'yes');
        $r_square = WPD_Admin::get_proper_value($shapes_options, 'r-square', 'yes');
        $circle = WPD_Admin::get_proper_value($shapes_options, 'circle', 'yes');
        $triangle = WPD_Admin::get_proper_value($shapes_options, 'triangle', 'yes');
        $heart = WPD_Admin::get_proper_value($shapes_options, 'heart', 'yes');
        $polygon = WPD_Admin::get_proper_value($shapes_options, 'polygon', 'yes');
        $star = WPD_Admin::get_proper_value($shapes_options, 'star', 'yes');
        ?>
        <div class="dspl-table">
            <?php
            if ($background_color == "yes") {
                ?>
                <div>
                    <span class="text-label"><?php _e("Background", "wpd"); ?></span>
                    <span class="">
                        <span id="shape-bg-color-selector" class="bg-color-selector " data-placement="top" data-original-title="<?php _e("Background color", "wpd"); ?>" style="background-color: #4f71b9;"></span>
                    </span>
                </div>
                <?php
            }
            if ($outline_width == "yes" || $outline == "yes") {
                ?>
                <div>
                    <span class="text-label"><?php _e("Outline", "wpd"); ?></span>
                    <span class="">
                        <?php if ($outline_width == "yes") { ?>
                            <label class="width-label"><?php _e("Width", "wpd"); ?></label>
                            <?php
                            $options = array(0 => "None", 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
                            echo $this->get_html_select("shape-thickness-slider", "shape-thickness-slider", "text-element-border text-tools-select", $options);
                        }
                        if ($outline == "yes") {
                            ?>
                            <div class="color-container">
                                <label class=" color-label">Color</label> 
                                <span id="shape-outline-color-selector" class="bg-color-selector " data-placement="top" data-original-title="<?php _e("Outline color", "wpd"); ?>" style="background-color: #4f71b9;"></span>
                            </div>
                        <?php } ?>
                    </span>
                </div>
                <?php
            }
            if ($opacity == "yes") {
                ?>
                <div>
                    <span class="text-label"><?php _e("Opacity", "wpd"); ?></span>
                    <span class="">
                        <?php
                        echo $this->get_opacity_dropdown("shape-opacity-slider", "shape-opacity-slider", "");
                        ?>
                    </span>
                </div>
                <?php
            }
            if ($square == "yes" || $r_square == "yes" || $circle == "yes" || $triangle == "yes" || $heart == "yes" || $polygon == "yes" || $star == "yes") {
                ?>
                <div>
                    <span class="text-label">
                        <?php _e("Shapes", "wpd"); ?>
                    </span>
                    <div class="img-container shapes">
                        <?php if ($square == "yes") { ?>
                            <span id="square-btn"></span>
                        <?php }if ($r_square == "yes") { ?>
                            <span id="r-square-btn"></span>
                        <?php }if ($circle == "yes") { ?>
                            <span id="circle-btn"></span>
                        <?php }if ($triangle == "yes") { ?>
                            <span id="triangle-btn"></span>
                        <?php }if ($heart == "yes") { ?>
                            <span id="heart-btn"></span>
                        <?php }if ($polygon == "yes") { ?>
                            <span id="polygon5" class="polygon-btn" data-num="5"></span>
                            <span id="polygon6" class="polygon-btn" data-num="6"></span>
                            <span id="polygon7" class="polygon-btn" data-num="7"></span>
                            <span id="polygon8" class="polygon-btn" data-num="8"></span>
                            <span id="polygon9" class="polygon-btn" data-num="9"></span>
                            <span id="polygon10" class="polygon-btn" data-num="10"></span>
                        <?php }if ($star == "yes") { ?>
                            <span id="star5" class="star-btn" data-num="5"></span>
                            <span id="star6" class="star-btn" data-num="6"></span>
                            <span id="star7" class="star-btn" data-num="7"></span>
                            <span id="star8" class="star-btn" data-num="8"></span>
                            <span id="star9" class="star-btn" data-num="9"></span>
                            <span id="star10" class="star-btn" data-num="10"></span>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    private function get_uploads_tools($options) {
        $opacity = WPD_Admin::get_proper_value($options, 'opacity', 'yes');
        if (isset($options['wpc-uploader']))
            $uploader = $options['wpc-uploader'];
        $form_class = "custom-uploader";
        if ($uploader == "native")
            $form_class = "native-uploader";
        if (!is_admin()) {
            ?>
            <form id="userfile_upload_form" class="<?php echo $form_class; ?>" method="POST" action="<?php echo admin_url('admin-ajax.php'); ?>" enctype="multipart/form-data">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wpc-picture-upload-nonce'); ?>">
                <input type="hidden" name="action" value="handle_picture_upload">
                <?php
                if ($uploader == "native") {
                    ?>
                    <input type="file" name="userfile" id="userfile">
                    <?php
                } else {
                    ?>        
                    <div id="drop">
                        <a><?php _e("Pick a file", "wpd"); ?></a>
                        <label for="userfile"></label>
                        <input type="file" name="userfile" id="userfile"/>
                        <div class="acd-upload-info"></div>
                    </div>
                    <?php
                }
                ?>
            </form>

            <div id="acd-uploaded-img" class="img-container"></div>
            <?php
        } else
            echo "<span class='filter-set-label' style='display: inline-block;'></span><a id='wpc-add-img' class='button' style='margin-bottom: 10px;'>Add image</a>";
        
        $grayscale = WPD_Admin::get_proper_value($options, 'grayscale', 'yes');
        $invert = WPD_Admin::get_proper_value($options, 'invert', 'yes');
        $sepia1 = WPD_Admin::get_proper_value($options, 'sepia1', 'yes');
        $sepia2 = WPD_Admin::get_proper_value($options, 'sepia2', 'yes');
        $blur = WPD_Admin::get_proper_value($options, 'blur', 'yes');
        $sharpen = WPD_Admin::get_proper_value($options, 'sharpen', 'yes');
        $emboss = WPD_Admin::get_proper_value($options, 'emboss', 'yes');
        ?>

        <div class="filter-set-container">
            <?php
            if ($grayscale == "yes" || $grayscale == "yes" || $invert == "yes" || $sepia1 == "yes" || $sepia2 == "yes" || $blur == "yes" || $sharpen == "yes" || $emboss == "yes")
            {
            ?>
            <span class="filter-set-label"><?php _e("Filters", "wpd"); ?></span>
            <span>
                <div class="mg-r-element ">

        <?php $this->get_image_filters(2, $options); ?>

                </div>

            </span>
            <?php
            }
            ?>

        </div>
        <?php if ($opacity == "yes") { ?>
            <div>
                <span ><?php _e("Opacity", "wpd"); ?></span>
                <span >   
            <?php
            $this->get_opacity_dropdown("img-opacity-slider", "img-opacity-slider", "text-element-border text-tools-select");
            ?>
                </span>
            </div>
            <?php
        }
    }

    private function get_opacity_dropdown($name, $id, $class = "") {
        $options = array();
        for ($i = 0; $i <= 10; $i++) {
            $key = $i / 10;
            $value = $i * 10;
            $options["$key"] = "$value%";
        }
        echo $this->get_html_select($name, $id, $class, $options, 1);
    }

    private function get_image_filters($index, $options) {
        $grayscale = WPD_Admin::get_proper_value($options, 'grayscale', 'yes');
        $invert = WPD_Admin::get_proper_value($options, 'invert', 'yes');
        $sepia1 = WPD_Admin::get_proper_value($options, 'sepia1', 'yes');
        $sepia2 = WPD_Admin::get_proper_value($options, 'sepia2', 'yes');
        $blur = WPD_Admin::get_proper_value($options, 'blur', 'yes');
        $sharpen = WPD_Admin::get_proper_value($options, 'sharpen', 'yes');
        $emboss = WPD_Admin::get_proper_value($options, 'emboss', 'yes');

        if ($grayscale == "yes") {
            ?> 
            <input type="checkbox" id="grayscale-<?php echo $index; ?>"  class="custom-cb filter-cb acd-grayscale">
            <label for="grayscale-<?php echo $index; ?>"><?php _e("Grayscale", "wpd"); ?></label>
            <?php
        }
        if ($invert == "yes") {
            ?>
            <input type="checkbox" id="invert-<?php echo $index; ?>" class="custom-cb filter-cb acd-invert">
            <label for="invert-<?php echo $index; ?>"><?php _e("Invert", "wpd"); ?></label>
            <?php
        }
        if ($sepia1 == "yes") {
            ?>
            <input type="checkbox" id="sepia-<?php echo $index; ?>" class="custom-cb filter-cb acd-sepia">
            <label for="sepia-<?php echo $index; ?>"><?php _e("Sepia 1", "wpd"); ?></label>
            <?php
        }
        if ($sepia2 == "yes") {
            ?>
            <input type="checkbox" id="sepia2-<?php echo $index; ?>" class="custom-cb filter-cb acd-sepia2">
            <label for="sepia2-<?php echo $index; ?>"><?php _e("Sepia 2", "wpd"); ?></label>
            <?php
        }
        if ($blur == "yes") {
            ?>
            <input type="checkbox" id="blur-<?php echo $index; ?>" class="custom-cb filter-cb acd-blur">
            <label for="blur-<?php echo $index; ?>"><?php _e("Blur", "wpd"); ?></label>
            <?php
        }
        if ($sharpen == "yes") {
            ?>
            <input type="checkbox" id="sharpen-<?php echo $index; ?>" class="custom-cb filter-cb acd-sharpen">
            <label for="sharpen-<?php echo $index; ?>"><?php _e("Sharpen", "wpd"); ?></label>
            <?php
        }
        if ($emboss == "yes") {
            ?>
            <input type="checkbox" id="emboss-<?php echo $index; ?>" class="custom-cb filter-cb acd-emboss">
            <label for="emboss-<?php echo $index; ?>"><?php _e("Emboss", "wpd"); ?></label>
            <?php
        }
    }

    private function get_images_tools($options) {
        $opacity = WPD_Admin::get_proper_value($options, 'opacity', 'yes');
        ?>
        <!--<div class="">-->
        <?php
        $args = array(
            'numberposts' => -1,
            'post_type' => 'wpc-cliparts'
        );
        $cliparts_groups = get_posts($args);
        echo '<div id="img-cliparts-accordion" class="Accordion minimal" tabindex="0">';
        foreach ($cliparts_groups as $cliparts_group) {
            $cliparts = get_post_meta($cliparts_group->ID, "wpc-cliparts", true);
            $cliparts_prices = get_post_meta($cliparts_group->ID, "wpc-cliparts-prices", true);
            if (!empty($cliparts)) {
                echo '<div class="AccordionPanel">
                                    <div class="AccordionPanelTab">' . $cliparts_group->post_title . ' (' . count($cliparts) . ')</div>
                                    <div class="AccordionPanelContent img-container">';

                foreach ($cliparts as $i => $clipart_id) {
                    $attachment_url = wp_get_attachment_url($clipart_id);
                    $price = 0;
                    if (isset($cliparts_prices[$i]))
                        $price = $cliparts_prices[$i];
                    echo "<span class='clipart-img'><img src='$attachment_url' data-price='$price'></span>";
                }
                echo '</div>
                            </div>';
            }
        }
        echo '</div>';
        
        $grayscale = WPD_Admin::get_proper_value($options, 'grayscale', 'yes');
        $invert = WPD_Admin::get_proper_value($options, 'invert', 'yes');
        $sepia1 = WPD_Admin::get_proper_value($options, 'sepia1', 'yes');
        $sepia2 = WPD_Admin::get_proper_value($options, 'sepia2', 'yes');
        $blur = WPD_Admin::get_proper_value($options, 'blur', 'yes');
        $sharpen = WPD_Admin::get_proper_value($options, 'sharpen', 'yes');
        $emboss = WPD_Admin::get_proper_value($options, 'emboss', 'yes');
        
        ?>

        <div class="filter-set-container">
            <?php
            if ($grayscale == "yes" || $grayscale == "yes" || $invert == "yes" || $sepia1 == "yes" || $sepia2 == "yes" || $blur == "yes" || $sharpen == "yes" || $emboss == "yes")
            {
            ?>
            <span class="filter-set-label"><?php _e("Filter", "wpd"); ?></span>
            <?php
            }
            ?>
            <span>
                <div class="mg-r-element ">
        <?php $this->get_image_filters(1, $options); ?>
                    <div id="clipart-bg-color-container"></div>

                </div>

            </span>

        </div>
        <?php if ($opacity == "yes") { ?>
            <div>
                <span ><?php _e("Opacity", "wpd"); ?></span>
                <span >   
            <?php $this->get_opacity_dropdown("opacity", "txt-opacity-slider", "text-element-border text-tools-select"); ?>
                </span>
            </div>
        <?php } ?>
        <?php
    }

    private function get_user_designs_tools() {
        if(is_user_logged_in())
            {
            GLOBAL $current_user;
            GLOBAL $wpc_options_settings;
            $designs_options = WPD_Admin::get_proper_value($wpc_options_settings, 'wpc-designs-options', array());
            $saved_visible=WPD_Admin::get_proper_value($designs_options,'saved', 'yes');       
            $orders_visible=WPD_Admin::get_proper_value($designs_options,'orders', 'yes');       
            $user_designs=get_user_meta($current_user->ID, 'wpc_saved_designs');
            $user_orders_designs=  $this->get_user_orders_designs($current_user->ID);
        ?>
        <div id="my-designs-accordion" class="Accordion minimal" tabindex="0">
            <?php
            if($saved_visible==="yes")
            {
            ?>
            <div class="AccordionPanel">
                <div class="AccordionPanelTab"><?php _e("Saved Designs","wpd");?></div>
                <div class="AccordionPanelContent">
                    <?php echo $this->get_user_design_output_block($user_designs);?>
                </div>
            </div>
            <?php
            }
            
            if($orders_visible==="yes")
            {
            ?>
            <div class="AccordionPanel">
                <div class="AccordionPanelTab"><?php _e("Past Orders","wpd");?></div>
                <div class="AccordionPanelContent">
                    <?php echo $this->get_user_design_output_block($user_orders_designs);?>
                </div>
            </div>
            <?php
            }
            ?>
        </div>
        <?php
        }
        else
        {
            _e("You need to be logged in before loading your designs.","wpd");
        }
    }
    
    /**
    * Returns user ordered designs
    * @global object $wpdb
    * @param type $user_id
    * @return array
    */
   private function get_user_orders_designs($user_id)
   {
       global $wpdb;
       $designs=array();
       $args = array(
                       'numberposts' => -1,
                       'meta_key' => '_customer_user',
                       'meta_value' => $user_id,
                       'post_type' => 'shop_order',
                       'post_status' => array( 'wc-processing', 'wc-completed' )
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

    
    private function get_user_design_output_block($user_designs)
    {
        $output="";
        foreach ($user_designs as $s_index=>$user_design)
        {
            if(!empty($user_design)){
            $variation_id=$user_design[0];
            $save_time=$user_design[1];
            $design_data=$user_design[2];
            $order_item_id="";
            //Comes from an order
            if(count($user_design)>=4)
                $order_item_id=$user_design[3];
            $output.="<div class='wpc_order_item' data-item='$variation_id'>";
            if(count($user_design)>1)
                $output.="<span data-original-title='$save_time' class='info-icon'></span>";
            if(is_array($design_data))
            {
    //            var_dump($design_data);
                $new_version=false;
                $upload_dir=  wp_upload_dir();
                if(isset($design_data["output"]["files"]))
                {
                    $tmp_dir=$design_data["output"]["working_dir"];
                    $design_data=$design_data["output"]["files"];
                    $new_version=true;

                }
                foreach ($design_data as $data_key=>$data)
                {
                    if(!empty($data))
                    {
                        if($new_version)
                        {
                            $generation_url = $upload_dir["baseurl"]."/WPC/$tmp_dir/$data_key/";
                            $img_src=$generation_url.$data["image"];
                            $original_part_img_url="";
                        }
                        else
                        {
                            if(!isset($data["image"]))
                                continue;
                            $img_src=$data["image"];
                            $original_part_img_url=$data["original_part_img"];
                        }

                        if($order_item_id)
                            $modal_id=$order_item_id."_$variation_id"."_$data_key";
                        else
                            $modal_id=$s_index."_$variation_id"."_$data_key";

                        $output.='<span><a class="wpd-button" data-toggle="modal" data-target="#'.$modal_id.'">'.ucfirst($data_key).'</a></span>';
                        $modal='<div class="modal fade wpc-modal wpc_part" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                        array_push(wpd_retarded_actions::$code,$modal);
                        add_action( 'wp_footer', array( 'wpd_retarded_actions', 'display_code' ), 10, 1 );
                    }
                    
                }
                if($order_item_id)
                    $output.='<a class="wpd-button" href="'.WPD_Product::get_url($variation_id, false, false, $order_item_id).'">'.__("Load","wpc").'</a>';
                else
                {
                    $output.='<a class="wpd-button" href="'.WPD_Product::get_url($variation_id, $s_index).'">'.__("Load","wpc").'</a>';
                    $output.='<a class="wpd-button wpd-delete-design" data-index="'.$s_index.'">'.__("Delete","wpc").'</a>';
                }
            }
            $output.="</div>";
        }
        }
        return $output;
    }

    private function get_parts() {
        $parts = get_option("wpc-parts");
        $is_first = true;
        $wpc_metas = get_post_meta($this->root_item_id, 'wpc-metas', true);
        ?>
        <div id="product-part-container">
            <ul id="wpc-parts-bar">
        <?php
        foreach ($parts as $part) {
            $part_key = sanitize_title($part);
            if (WPD_Admin::get_proper_value($wpc_metas, $this->item_id, array()) && WPD_Admin::get_proper_value($wpc_metas[$this->item_id], 'parts', array()) && WPD_Admin::get_proper_value($wpc_metas[$this->item_id]['parts'], $part_key, array())) {
                $part_media_id = WPD_Admin::get_proper_value($wpc_metas[$this->item_id]['parts'][$part_key], 'bg-inc');
                $part_bg_img = WPD_Admin::get_proper_value($wpc_metas[$this->item_id]['parts'][$part_key], 'bg-not-inc');
                if (WPD_Admin::get_proper_value($wpc_metas[$this->item_id]['parts'][$part_key], 'ov')) {
                    $part_ov_img = WPD_Admin::get_proper_value($wpc_metas[$this->item_id]['parts'][$part_key]['ov'], 'img');
                    $overlay_included = WPD_Admin::get_proper_value($wpc_metas[$this->item_id]['parts'][$part_key]['ov'], 'inc', 1);
                }
            }
            if (!($part_media_id || $part_media_id == "0"))
                continue;
            $class = "";
            if ($is_first)
                $class = "class='active'";
            $is_first = false;
            $img_ov_src = "";

            if (isset($part_ov_img)) {
                $img_ov_src = wp_get_attachment_url($part_ov_img);
            }

            $img_bg_src = "";
            if (!empty($part_bg_img))
                $img_bg_src = wp_get_attachment_url($part_bg_img);

            if ($part_media_id == "0") {
                $final_img_url = "";
                $part_img = $part;
            } else {
                $final_img_url = wp_get_attachment_url($part_media_id);
                $part_img = '<img src="' . $final_img_url . '">';
            }
            ?>
                    <li data-id="<?php echo $part_key; ?>" data-url="<?php echo $final_img_url; ?>" <?php echo $class; ?> data-placement="top" data-original-title="<?php echo $part; ?>" data-bg="<?php echo $img_bg_src; ?>" data-ov="<?php echo $img_ov_src; ?>" data-ovni="<?php echo $overlay_included; ?>">
                    <?php echo $part_img; ?>
                    </li>
                        <?php
                    }
                    ?>
            </ul>
        </div>
        <?php
    }

    private function get_design_actions_box() {
        GLOBAL $wpc_options_settings;
        $general_options=$wpc_options_settings['wpc-general-options'];
        
        if(isset($general_options['wpc-download-btn']))
            $download_btn=$general_options['wpc-download-btn'];
        if(isset($general_options['wpc-preview-btn']))
            $preview_btn= $general_options['wpc-preview-btn'];
        if(isset($general_options['wpc-save-btn']))
            $save_btn=  $general_options['wpc-save-btn'];
        
        $design_index = -1;
        if (isset($_GET["design_index"])) {
            $design_index = $_GET["design_index"];
        }
        //We don't show the box at all if there is nothing to show inside
        if(isset($preview_btn) && $preview_btn==="0" && isset($download_btn) && $download_btn==="0" && isset($save_btn) && $save_btn==="0")
            return;
        ?>
        <div id="wpc-design-btn-box" >
            <div class="title"><?php _e("ACTIONS", "wpd"); ?></div>
            <?php
            if(isset($preview_btn) && $preview_btn!=="0")
            {
            ?>
            <button id="preview-btn" class="wpc-btn-effect"><?php _e("PREVIEW", "wpd"); ?></button>
            <?php
            }
            if(!is_admin())
            {
                if(isset($download_btn) && $download_btn!=="0")
                {
            ?>
            <button id="download-btn" class="wpc-btn-effect"><?php _e("DOWNLOAD", "wpd"); ?></button>
            <?php
                }
            if(isset($save_btn) && $save_btn!=="0")
            {
            ?>
            <button id="save-btn" class="wpc-btn-effect" data-index="<?php echo $design_index; ?>"><?php _e("SAVE", "wpd"); ?></button>
            <?php
            }
            }
            ?>
        </div>
        <?php
        $modal = '<div class="modal fade wpd-modal" id="wpd-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title" id="myModalLabel">' . __('PREVIEW', 'wpd') . '</h4>
                </div>
                <div class="modal-body txt-center">
                </div>
              </div>
            </div>
        </div>';
        array_push(wpd_retarded_actions::$code, $modal);
        add_action('wp_footer', array('wpd_retarded_actions', 'display_code'), 10, 1);
    }

    private function get_cart_actions_box() {
        GLOBAL $wpc_options_settings;
        GLOBAL $wp_query;
        $general_options=$wpc_options_settings['wpc-general-options'];        
        if(isset($general_options['wpc-cart-btn']))
            $cart_btn=  $general_options['wpc-cart-btn'];
        
        $product = get_product($this->item_id);
        $product_price = $product->price;
        $shop_currency_symbol = get_woocommerce_currency_symbol();
        if(isset($cart_btn) && $cart_btn!=="0")
        {
            $quantity=1;
        ?>
        <div id="wpc-cart-box" class="">
            <div class="title">CART</div>
            <div id="wpc-qty-container" class="">
                <?php
            if (!isset($wp_query->query_vars["edit"])) 
            {
                ?>
                <input type="button" value="-" class=" minus wpc-custom-right-quantity-input-set wpc-btn-effect">
                <input id="wpd-qty" type="number" step="1" value="<?php echo $quantity;?>" class="wpc-custom-right-quantity-input" min="1" dntmesecondfocus="true" uprice="<?php echo $product_price; ?>">
                <input type="button" value="+" class=" plus wpc-custom-right-quantity-input-set wpc-btn-effect">
                <?php
            }
            ?>

                <div class="total-price">
                    <span id="total_order"><?php echo "$product_price"; ?></span>
                    <span><?php echo "$shop_currency_symbol"; ?></span>
                </div>
            </div>
            <?php
            if (isset($wp_query->query_vars["edit"])) 
            {
                ?>
                <button id="add-to-cart-btn" class="wpc-btn-effect" data-id="<?php echo $this->item_id ?>"><?php _e("UPDATE CART", "wpd"); ?></button>
                <?php
            }
            else
            {
                ?>
                <button id="add-to-cart-btn" class="wpc-btn-effect" data-id="<?php echo $this->item_id ?>"><?php _e("ADD TO CART", "wpd"); ?></button>
                <?php
            }
            ?>
            
        </div>
        <?php
        }
    }

    private function register_scripts() {
        wp_enqueue_script('wpd-qtip', WPD_URL . 'public/js/jquery.qtip-1.0.0-rc3.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-fabric-js', WPD_URL . 'public/js/fabric.all.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-editor-js', WPD_URL . 'public/js/editor.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-editor-text-controls', WPD_URL . 'public/js/editor.text.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-editor-toolbar-js', WPD_URL . 'public/js/editor.toolbar.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-editor-shapes-js', WPD_URL . 'public/js/editor.shapes.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-accordion-js', WPD_URL . 'public/js/SpryAssets/SpryAccordion.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-block-UI-js', WPD_URL . 'public/js/blockUI/jquery.blockUI.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-fancyselect-js', WPD_URL . 'public/js/fancySelect.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-editor-img-js', WPD_URL . 'public/js/editor.img.min.js', array('jquery'), WPD_VERSION, false);
        self::register_upload_scripts();
    }

    public static function register_upload_scripts() {
        GLOBAL $wpc_options_settings;
        $options = $wpc_options_settings['wpc-upload-options'];
        $uploader = $options['wpc-uploader'];
        if ($uploader == "native") {
            wp_register_script('wpd-jquery-form-js', WPD_URL . 'public/js/jquery.form.min.js');
            wp_enqueue_script('wpd-jquery-form-js', array('jquery'), WPD_VERSION, false);
        } else {
            wp_register_script('wpd-widget', WPD_URL . 'public/js/upload/js/jquery.ui.widget.min.js');
            wp_enqueue_script('wpd-widget', array('jquery'), WPD_VERSION, false);

            wp_register_script('wpd-fileupload', WPD_URL . 'public/js/upload/js/jquery.fileupload.min.js');
            wp_enqueue_script('wpd-fileupload', array('jquery'), WPD_VERSION, false);

            wp_register_script('wpd-iframe-transport', WPD_URL . 'public/js/upload/js/jquery.iframe-transport.min.js');
            wp_enqueue_script('wpd-iframe-transport', array('jquery'), WPD_VERSION, false);

            wp_register_script('wpd-knob', WPD_URL . 'public/js/upload/js/jquery.knob.min.js');
            wp_enqueue_script('wpd-knob', array('jquery'), WPD_VERSION, false);
        }
    }

    private function register_styles() {
        wp_enqueue_style("wpd-SpryAccordion-css", WPD_URL . 'public/js/SpryAssets/SpryAccordion.min.css', array(), WPD_VERSION, 'all');
        wp_enqueue_style("wpd-editor", WPD_URL . 'public/css/editor.min.css', array(), WPD_VERSION, 'all');
        wp_enqueue_style("wpd-fancyselect-css", WPD_URL . 'public/css/fancySelect.min.css', array(), WPD_VERSION, 'all');
        $this->register_fonts();
    }

    private function register_fonts() {
        $fonts = get_option("wpc-fonts");
        if (empty($fonts)) {
            $fonts = $this->get_default_fonts();
        }

        foreach ($fonts as $font) {
            $font_label = $font[0];
            $font_url = str_replace('http://', '//', $font[1]);
            if ($font_url) {
                $handler = sanitize_title($font_label) . "-css";
                wp_register_style($handler, $font_url, array(), false, 'all');
                wp_enqueue_style($handler);
            }
        }
    }

    /**
     * Return the default fonts list
     * @return array
     */
    private function get_default_fonts() {
        $default = array(
            array("Shadows Into Light", "http://fonts.googleapis.com/css?family=Shadows+Into+Light"),
            array("Droid Sans", "http://fonts.googleapis.com/css?family=Droid+Sans:400,700"),
            array("Abril Fatface", "http://fonts.googleapis.com/css?family=Abril+Fatface"),
            array("Arvo", "http://fonts.googleapis.com/css?family=Arvo:400,700,400italic,700italic"),
            array("Lato", "http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic"),
            array("Just Another Hand", "http://fonts.googleapis.com/css?family=Just+Another+Hand")
        );

        return $default;
    }

    /**
     * Builds a select dropdpown
     * @param type $name Name
     * @param type $id ID
     * @param type $class Class
     * @param type $options Options
     * @param type $selected Selected value
     * @param type $multiple Can select multiple values
     * @return string HTML code
     */
    private function get_html_select($name, $id, $class, $options, $selected = '', $multiple = false) {
        ob_start();
        ?>
        <select name="<?php echo $name; ?>" <?php echo ($id) ? "id=\"$id\"" : ""; ?> <?php echo ($class) ? "class=\"$class\"" : ""; ?> <?php echo ($multiple) ? "multiple" : ""; ?> >
        <?php
        if (is_array($options) && !empty($options)) {
            foreach ($options as $name => $label) {
                if (!$multiple && $name == $selected) {
                    ?> <option value="<?php echo $name ?>"  selected="selected" > <?php echo $label; ?></option> <?php
                    } else if ($multiple && in_array($name, $selected)) {
                        ?> <option value="<?php echo $name ?>"  selected="selected" > <?php echo $label; ?></option> <?php
                    } else {
                        ?> <option value="<?php echo $name ?>"> <?php echo $label; ?></option> <?php
                    }
                }
            }
            ?>
        </select>
            <?php
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

    }
    