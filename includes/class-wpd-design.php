<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Contains all methods and hooks callbacks related to the user design
 *
 * @author HL
 */
class WPD_Design {

    function delete_saved_design_ajax() {
        $design_index = $_GET['design_index'];
        $variation_id = $_GET['variation_id'];
        global $current_user;
        $user_designs = get_user_meta($current_user->ID, 'wpc_saved_designs');
        unset($user_designs[$design_index]);
        delete_user_meta($current_user->ID, "wpc_saved_designs");
        foreach ($user_designs as $index => $design) {
            $result = add_user_meta($current_user->ID, "wpc_saved_designs", $design);
            if (!$result)
                break;
        }
        $url = WPD_Product::get_url($variation_id);

        echo json_encode(array(
            "success" => $result,
            "url" => $url,
            "message" => __('An error occured. Please try again later', 'wpd')
        ));
        die();
    }

    function add_custom_design_to_cart_ajax() {
        global $woocommerce;
        $cart_url = $woocommerce->cart->get_cart_url();
        $final_canvas_parts = $_POST["final_canvas_parts"];
        $variation_id = $_POST["variation_id"];
        $quantity = $_POST["quantity"];
        $cart_item_key = $_POST["cart_item_key"];
        $newly_added_cart_item_key = false;

        $tmp_dir = uniqid();
        $upload_dir = wp_upload_dir();
        $generation_path = $upload_dir["basedir"] . "/WPC/$tmp_dir";
        $generation_url = $upload_dir["baseurl"] . "/WPC/$tmp_dir";
        if (wp_mkdir_p($generation_path)) {
            $generation_url = $upload_dir["baseurl"] . "/WPC/$tmp_dir";
            $zip_name = uniqid("wpc_") . ".zip";
            $result = $this->export_data_to_files($generation_path, $final_canvas_parts, $variation_id, $zip_name);
            if (!empty($result) && is_array($result)) {
                $final_canvas_parts["output"]["files"] = $result;
                $final_canvas_parts["output"]["working_dir"] = $tmp_dir;
                $final_canvas_parts["output"]["zip"] = $zip_name;

                $newly_added_cart_item_key = true;
                if ($cart_item_key) {
                    $_SESSION["wpc_generated_data"][$variation_id][$cart_item_key] = $final_canvas_parts;
                    $message = "<div class='wpc_notification success f-right'>" . __("Item successfully updated.", "wpd") . " <a href='$cart_url'>" . __("View Cart", "wpd") . "</a></div>";
                } else {
                    $variable_product = get_product($variation_id);
                    $variation = array();
                    if ($variable_product->product_type == "simple")
                        $product_id = $variation_id;
                    else {
                        $variation = $variable_product->variation_data;
                        $product_id = $variable_product->parent->id;
                    }
                    $newly_added_cart_item_key = $woocommerce->cart->add_to_cart($product_id, $quantity, $variation_id, $variation, $final_canvas_parts);
                    if (method_exists($woocommerce->cart, "maybe_set_cart_cookies"))
                        $woocommerce->cart->maybe_set_cart_cookies();
                    if ($newly_added_cart_item_key) {
                        if (!isset($_SESSION["wpc_generated_data"][$variation_id]))
                            $_SESSION["wpc_generated_data"][$variation_id] = array();
                        $_SESSION["wpc_generated_data"][$variation_id][$newly_added_cart_item_key] = $final_canvas_parts;

                        $message = "<div class='wpc_notification success f-right'>" . __("Product successfully added to basket.", "wpd") . " <a href='$cart_url'>View Cart</a></div>";
                    } else
                        $message = "<div class='wpc_notification failure f-right'>" . __("A problem occured. Please try again.", "wpd") . "</div>";
                }
            } else
                $message = "<div class='wpc_notification failure f-right'>" . __("A problem occured. Please try again.", "wpd") . "</div>";
        }

        echo json_encode(array("success" => $newly_added_cart_item_key,
            "message" => $message,
            "url" => $cart_url
        ));
        die();
    }
    
    private function merge_pictures($base_layer, $top_layer,$final_img){
        $base = imagecreatefrompng($base_layer);
        imagealphablending($base, true);
        list($base_w, $base_h, $type, $attr) = getimagesize($base_layer);
        $top = imagecreatefrompng($top_layer);         
        imagealphablending($top, true);
        list($top_w, $top_h, $type, $attr) = getimagesize($top_layer);
        
        $src_x=($base_w-$top_w)/2;
        $src_y=($base_h-$top_h)/2;

        imagecopyresampled($base, $top, $src_x, $src_y, 0, 0, $top_w, $top_h, $top_w, $top_h);
        imagedestroy($top);

        imagecolortransparent($base, imagecolorallocatealpha($base, 0, 0, 0, 127));

        imagealphablending($base, false);
        imagesavealpha($base, true);

        imagepng($base, $final_img);
    }
    
    function get_watermarked_preview()
    {
        $watermark=  get_attached_file($_POST["watermark"]);
        $upload_dir = wp_upload_dir();
        $preview_filename=  uniqid("preview_").".png";
        $output_file_path = $upload_dir["basedir"]."/".$preview_filename;
        
        if(move_uploaded_file($_FILES["image"]['tmp_name'], $output_file_path))
        {
            $url=$upload_dir["baseurl"]."/".$preview_filename;
            $this->merge_pictures($output_file_path, $watermark, $output_file_path);            
            echo json_encode(array("url" =>$url));
        }
        else
            echo "<div class='wpc_notification failure'>" . __("An error has occured. Please try again later or contact the administrator.", "wpd");
        die();
    }

    function save_custom_design_for_later_ajax() {
        $final_canvas_parts = $_POST["final_canvas_parts"];
        $variation_id = $_POST["variation_id"];
        $design_index = $_POST["design_index"];
        $cart_item_key = "";
        if (isset($_POST["cart_item_key"]))
            $cart_item_key = $_POST["cart_item_key"];
        $is_logged = 0;
        $result = 0;
        $message = "";
        $customization_url = WPD_Product::get_url($variation_id);
        $url = wp_login_url($customization_url);
        if (is_user_logged_in()) {
            global $current_user;
            get_currentuserinfo();
            $message = $current_user->ID;
            $is_logged = 1;
            $today = date("Y-m-d H:i:s");
            $tmp_dir = uniqid();
            $upload_dir = wp_upload_dir();
            $generation_path = $upload_dir["basedir"] . "/WPC/$tmp_dir";
            $generation_url = $upload_dir["baseurl"] . "/WPC/$tmp_dir";
            if (wp_mkdir_p($generation_path)) {
                $generation_url = $upload_dir["baseurl"] . "/WPC/$tmp_dir";
                $zip_name = uniqid("wpc_") . ".zip";
                $export_result = $this->export_data_to_files($generation_path, $final_canvas_parts, $variation_id, $zip_name);
                if (!empty($export_result) && is_array($export_result)) {
                    $final_canvas_parts["output"]["files"] = $export_result;
                    $final_canvas_parts["output"]["working_dir"] = $tmp_dir;
                    $final_canvas_parts["output"]["zip"] = $zip_name;
                    $to_save = array($variation_id, $today, $final_canvas_parts);
                    $user_designs = get_user_meta($current_user->ID, 'wpc_saved_designs');
                    if ($design_index != -1) {

                        $user_designs[$design_index] = $to_save;
                    } else
                        array_push($user_designs, $to_save);
                    delete_user_meta($current_user->ID, "wpc_saved_designs");
                    foreach ($user_designs as $index => $design) {
                        $result = add_user_meta($current_user->ID, "wpc_saved_designs", $design);
                        if (!$result)
                            break;
                    }

                    if ($result) {
                        $result = 1;
                        $message = "<div class='wpc_notification success'>" . __("The design has successfully been saved to your account.", "wpd") . "</div>";
                        //$user_designs=get_user_meta($current_user->ID, 'wpc_saved_designs');
                        if ($design_index == -1)
                            $design_index = count($user_designs) - 1;
                        $url = WPD_Product::get_url($variation_id, $design_index);
                    }
                    else {
                        $result = 0;
                        $message = "<div class='wpc_notification failure'>" . __("An error has occured. Please try again later or contact the administrator.", "wpd") . "</div>";
                    }
                }
            }
        } else {
            if (!isset($_SESSION['wpc_designs_to_save']))
                $_SESSION['wpc_designs_to_save'] = array();
            if (!isset($_SESSION['wpc_designs_to_save'][$variation_id]))
                $_SESSION['wpc_designs_to_save'][$variation_id] = array();

            array_push($_SESSION['wpc_designs_to_save'][$variation_id], $final_canvas_parts);
        }
        echo json_encode(array("is_logged" => $is_logged,
            "success" => $result,
            "message" => $message,
            "url" => $url
                )
        );
        die();
    }

    function save_canvas_to_session_ajax() {
        $final_canvas_parts = $_POST["final_canvas_parts"];
        $template_object = get_post_type_object("wpc-template");
        $can_manage_templates = current_user_can($template_object->cap->edit_posts);
        if ($can_manage_templates) {
            $_SESSION["to_save"] = $final_canvas_parts;
        }
        die();
    }

    function generate_downloadable_file() {
        GLOBAL $wpc_options_settings;
        $wpc_output_options = $wpc_options_settings['wpc-output-options'];
        $final_canvas_parts = $_POST["final_canvas_parts"];
        $tmp_dir = uniqid();
        $upload_dir = wp_upload_dir();
        $generation_path = $upload_dir["basedir"] . "/WPC/$tmp_dir";
        $generation_url = $upload_dir["baseurl"] . "/WPC/$tmp_dir";
        $variation_id = $_POST["variation_id"];
        if (isset($wpc_output_options['wpc-generate-zip']))
            $generate_zip = ($wpc_output_options['wpc-generate-zip'] === "yes") ? true : false;
        if (wp_mkdir_p($generation_path)) {
            $generation_url = $upload_dir["baseurl"] . "/WPC/$tmp_dir";

            $zip_name = uniqid("wpc_") . ".zip";
            $result = $this->export_data_to_files($generation_path, $final_canvas_parts, $variation_id, $zip_name, true);
            if (!empty($result) && is_array($result)) {
                $output_msg = "";
                if ($generate_zip)
                    $output_msg = "<div>" . __("The generation has been successfully completed. Please click ", "wpd") . "<a href='$generation_url/" . $zip_name . "' download='" . $zip_name . "'>" . __("here", "wpd") . "</a> " . __("to download your design", "wpd") . ".</div>";
                else {
                    foreach ($result as $part_key => $part_file_arr) {
                        $part_file = $part_file_arr["preview"];
                        if (strpos($part_file, ".pdf"))
                            $output_msg.="<div>" . ucfirst($part_key) . __(": please click ", "wpd") . "<a href='$generation_url/$part_key/$part_key.pdf' class='print_pdf'>" . __("here", "wpd") . "</a> " . __("to download", "wpd") . ".</div>";
                        else
                            $output_msg.="<div>" . ucfirst($part_key) . __(": please click ", "wpd") . "<a href='$generation_url/$part_key/$part_file' download='$part_file'>" . __("here", "wpd") . "</a> " . __("to download", "wpd") . ".</div>";
                    }
                }
                echo json_encode(array(
                    "success" => 1,
                    "message" => "<div class='wpc-success'>" . $output_msg . "</div>",
                        )
                );
            } else
                echo json_encode(array(
                    "success" => 0,
                    "message" => "<div class='wpc-failure'>" . __("An error occured in the generation process. Please try again later.", "wpd") . "</div>",
                        )
                );
        } else
            echo json_encode(array(
                "success" => 0,
                "message" => "<div class='wpc-failure'>" . __("Can't create a generation directory...", "wpd") . "</div>",
                    )
            );
        die();
    }

    function get_user_account_products_meta($output, $item) {
        GLOBAL $wpc_options_settings;
        $options = $wpc_options_settings['wpc-general-options'];
        $download_btn = WPD_Admin::get_proper_value($options, 'wpc-user-account-download-btn', "");
        if ($download_btn !== "0" && isset($item["variation_id"]) && (!empty($item["variation_id"]) || $item["variation_id"] == "0")) {
            $product = get_product($item["variation_id"]);
            $item_id = uniqid();
            //        var_dump($product);
            ob_start();
            $this->get_order_custom_admin_data($product, $item, $item_id);
            $admin_data = ob_get_contents();
            ob_end_clean();
            $output.=$admin_data;
        }
        return $output;
    }

    function save_customized_item_meta($item_id, $values, $cart_item_key) {
        $variation_id = $values["variation_id"];
        if (isset($values["output"])) {
            if (isset($_SESSION["wpc_generated_data"][$variation_id][$cart_item_key])) {
                wc_add_order_item_meta($item_id, 'wpc_data', $_SESSION["wpc_generated_data"][$variation_id][$cart_item_key]);
                unset($_SESSION["wpc_generated_data"][$variation_id][$cart_item_key]);
            }
            if (empty($_SESSION["wpc_generated_data"][$variation_id]))
                unset($_SESSION["wpc_generated_data"][$variation_id]);
        }
        else if (isset($_SESSION["wpc-uploaded-designs"][$cart_item_key])) {
            wc_add_order_item_meta($item_id, 'wpc_data_upl', $_SESSION["wpc-uploaded-designs"][$cart_item_key]);
            unset($_SESSION["wpc-uploaded-designs"][$cart_item_key]);
        }
        if (isset($_SESSION["wpc_design_pricing_options"][$cart_item_key]) && !empty($_SESSION["wpc_design_pricing_options"][$cart_item_key])) {
            $wpc_design_pricing_options_data = $this->get_design_pricing_options_data($_SESSION["wpc_design_pricing_options"][$cart_item_key]);
            wc_add_order_item_meta($item_id, '_wpc_design_pricing_options', $wpc_design_pricing_options_data);
            unset($_SESSION["wpc_design_pricing_options"][$cart_item_key]);
        }
    }

    function remove_wpc_customization($cart_item_key) {
        foreach ($_SESSION["wpc_generated_data"] as $variation_id => $variation_customizations) {
            if (isset($_SESSION["wpc_generated_data"][$variation_id][$cart_item_key])) {
                unset($_SESSION["wpc_generated_data"][$variation_id][$cart_item_key]);
                if (empty($_SESSION["wpc_generated_data"][$variation_id]))
                    unset($_SESSION["wpc_generated_data"][$variation_id]);
                break;
            }
            else if (isset($_SESSION["wpc-uploaded-designs"]))
                unset($_SESSION["wpc-uploaded-designs"][$cart_item_key]);
        }
    }

    function get_order_custom_admin_data($_product, $item, $item_id) {
        $output = "";
        if (isset($item["wpc_data"])) {
            $upload_dir = wp_upload_dir();
            foreach ($item["item_meta"]["wpc_data"] as $s_index => $serialized_data) {
                $output.="<div class='wpc_order_item' data-item='$item_id'>";
                //            $output.=get_variable_order_item_attributes($_product);
                $unserialized_data = unserialize($serialized_data);
//                $old_version=false;
                //Previous version compatibility
//                if(!isset($unserialized_data["output"]["files"]))
//                {
//                    $old_version=true;
//                    $design_data=$unserialized_data;
//                }
//                else
                $design_data = $unserialized_data["output"]["files"];

                if (count($item["item_meta"]["wpc_data"]) > 1)
                    $output.=($s_index + 1) . "-";
                foreach ($design_data as $data_key => $data) {
//                    if(!$old_version)
//                    {
                    $tmp_dir = $unserialized_data["output"]["working_dir"];
                    $generation_url = $upload_dir["baseurl"] . "/WPC/$tmp_dir/$data_key/";
                    if(is_admin())
                        $img_src = $generation_url . $data["image"];
                    else
                    {
                        if(isset($data["preview"]))
                            $img_src = $generation_url . $data["preview"];
                        else
                            $img_src = $generation_url . $data["image"];
                    }
//                    }
//                    else
//                        $img_src=$data["image"];
                    $original_part_img_url = $unserialized_data[$data_key]["original_part_img"];
                    $modal_id = $s_index . "_$item_id" . "_$data_key";
                    $output.='<span><a class="button" data-toggle="modal" data-target="#' . $modal_id . '">' . ucfirst($data_key) . '</a></span>';
                    $output.='<div class="modal fade wpc_part" id="' . $modal_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                  <div class="modal-content">
                                    <div class="modal-header">
                                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                      <h4 class="modal-title" id="myModalLabel' . $modal_id . '">Preview</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div style="background-image:url(' . $original_part_img_url . ')"><img src="' . $img_src . '"></div>
                                    </div>
                                  </div>
                                </div>
                              </div>';
                }
                //Deb
//                if($old_version)
//                {
//                    $zip_file=wc_get_order_item_meta( $item_id, "wpc_data_zip", $single = true );
//                    if(!empty($zip_file)&& is_array($zip_file))
//                    {
//                        $output.="<a class='button' href='".$zip_file["url"]."' download='".basename($zip_file["url"])."'>".__( "Download design","wpd")."</a> ";
//                    }
//                    else
//                    {
//                        $output.=$this->wpc_generate_order_item_zip($item_id, $unserialized_data, false, $_product->id);
//                    }
//                }
//                else
//                {
                $zip_file = $unserialized_data["output"]["zip"];
                if (!empty($zip_file))
                    $output.="<a class='button' href='" . $upload_dir["baseurl"] . "/WPC/$tmp_dir/$zip_file' download='" . basename($zip_file) . "'>" . __("Download design", "wpd") . "</a> ";
//                }
                if (isset($item["wpc_design_pricing_options"])) {
                    $output.=$item["wpc_design_pricing_options"];
                }
                //End
                $output.="</div>";
            }
        } else if (isset($item["wpc_data_upl"])) {
            $output.="<div class='wpc_order_item' data-item='$item_id'>";
            //Looks like the structure changed for latest versions of WC (tested on 2.3.7)
            $design_url = $item["item_meta"]["wpc_data_upl"][0];
            if(is_serialized($design_url))
            {
                $unserialized_urls=  unserialize($design_url);
                foreach ($unserialized_urls as $design_url)
                {
                    $output.="<a class='button' href='" . $design_url . "' download='" . basename($design_url) . "'>" . __("Download custom design", "wpd") . "</a> ";
                }
            }
            else
            {
            //        $output.=get_variable_order_item_attributes($_product);
                $output.="<a class='button' href='" . $design_url . "' download='" . basename($design_url) . "'>" . __("Download custom design", "wpd") . "</a> ";
            }
            if (isset($item["wpc_design_pricing_options"])) {
                $output.=$item["wpc_design_pricing_options"];
            }
            $output.="</div>";
        }

        echo $output;
    }

    function handle_custom_design_upload() {
        $nonce = $_POST['nonce'];
        if (!wp_verify_nonce($nonce, 'wpc-custom-upload-nonce')) {
            $busted = __("Cheating huh?", "wpd");
            die($busted);
        }

        $upload_dir = wp_upload_dir();
        $product_id = $_POST["wpc-product-id-upl"];
        $generation_path = $upload_dir["basedir"];
        $generation_url = $upload_dir["baseurl"];
        $file_name = uniqid();
        $valid_formats = array();
        $options = get_option('wpc-upload-options');
        $valid_formats_raw = $options['wpc-custom-designs-extensions'];
        if (!empty($valid_formats_raw)) {
            $valid_formats = array_map('trim', explode(',', $valid_formats_raw));
        }
        $name = $_FILES['user-custom-design']['name'];
        $size = $_FILES['user-custom-design']['size'];

        if (isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST") {

            if (strlen($name)) {
                if (!isset($_SESSION["wpc-user-uploaded-designs"]))
                    $_SESSION["wpc-user-uploaded-designs"] = array();

                if (isset($_SESSION["wpc-user-uploaded-designs"][$product_id]))
                    unset($_SESSION["wpc-user-uploaded-designs"][$product_id]);

                list($txt, $ext) = explode(".", $name);
                $ext = strtolower($ext);
                if (in_array($ext, $valid_formats) || empty($valid_formats)) {
                    //            var_dump($_FILES);
                    $tmp = $_FILES['user-custom-design']['tmp_name'];
                    $success = 0;
                    $message = "";
                    if (move_uploaded_file($tmp, $generation_path . "/" . $file_name . ".$ext")) {
                        $success = 1;
                        $_SESSION["wpc-user-uploaded-designs"][$product_id] = "$generation_url/$file_name.$ext";
                        $message = $_FILES['user-custom-design']['name'] . " successfully uploaded. Click on the Add to cart button to add this product and your design to the cart.";
                        $valid_formats_for_thumb = array("psd", "eps");
                        if (in_array($ext, $valid_formats_for_thumb)) {
                            $output_thumb = uniqid() . ".png";
                            $thumb_generation_success = $this->generate_adobe_thumb($generation_path, $file_name . ".$ext", $output_thumb);
                            if ($thumb_generation_success)
                                $message.="<div class='wpc-file-preview'><b>Preview</b><br><img src='$generation_url/$output_thumb'></div>";
                        }
                    }
                    else {
                        $success = 0;
                        $message = __('An error occured during the upload. Please try again later', 'wpd');
                    }
                } else if (!in_array($ext, $valid_formats)) {
                    $success = 0;
                    $message = __('Incorrect file extension. Allowed extensions: ', 'wpd') . implode(", ", $valid_formats);
                }
                echo json_encode(
                        array(
                            "success" => $success,
                            "message" => $message,
                        )
                );
            }
        }
        die();
    }

    private function wpc_starts_with($haystack, $needle) {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    private function wpc_check_rule($objects, $rule) {
        $param = $rule["param"];
        $value = $rule["value"];
        $operator = $rule["operator"];
        $results = array();
        foreach ($objects as $object) {
            $to_eval = "if($object[$param] $operator $value) return true; else return false;";
            $evaluation = eval($to_eval);
            array_push($results, $evaluation);
        }

        return $results;
    }

    private function get_group_valid_items_count($group_results) {
        $group_count = false;
        foreach ($group_results as $group_type => $type_results) {
            if (count($type_results) === 1)
                $intersection = current($type_results);
            else
                $intersection = call_user_func_array('array_intersect', $type_results);
            $group_type_count = count(array_filter($intersection));

            //If at least one rule is not valid for any item, the group is not valid
            if (!$group_type_count)
                return 0;
            else if ($group_count)
                $group_count = min(array($group_count, $group_type_count));
            else
                $group_count = $group_type_count;
        }

        return $group_count;
    }

    private function get_group_results($priceable_elements, $rules) {
        $group_results = array();
        //For each rule in the group
        foreach ($rules as $rule_arr) {
            //We skip invalid rules
            if (!$rule_arr["param"] || !$rule_arr["operator"] || !$rule_arr["value"])
                continue;
            //If it's a i-text rule
            if ($this->wpc_starts_with($rule_arr["param"], "txt")) {
                if (isset($priceable_elements["i-text"]))
                    $results_arr = $this->wpc_check_rule($priceable_elements["i-text"], $rule_arr);
                else
                    $results_arr = array(false);
                if (!isset($group_results["i-text"]))
                    $group_results["i-text"] = array();
                array_push($group_results["i-text"], $results_arr);
            }
            //else if it's an image rule
            else if ($this->wpc_starts_with($rule_arr["param"], "img")) {
                if (isset($priceable_elements["image"]))
                    $results_arr = $this->wpc_check_rule($priceable_elements["image"], $rule_arr);
                else
                    $results_arr = array(false);
                if (!isset($group_results["image"]))
                    $group_results["image"] = array();
                array_push($group_results["image"], $results_arr);
            }
            //else if it's a vector rule
            else if ($this->wpc_starts_with($rule_arr["param"], "path")) {
                if (isset($priceable_elements["path"]))
                    $results_arr = $this->wpc_check_rule($priceable_elements["path"], $rule_arr);
                else
                    $results_arr = array(false);
                if (!isset($group_results["path"]))
                    $group_results["path"] = array();
                //            var_dump($results_arr);
                array_push($group_results["path"], $results_arr);
            }
        }
        return $group_results;
    }

    private function wpd_exec($cmd) {
        $output = array();
        exec("$cmd 2>&1", $output);
        return $output;
    }

    private function get_design_options_prices($json_wpc_design_options) {
        $wpc_design_options_prices = 0;
        if (!empty($json_wpc_design_options)) {
            $json = $json_wpc_design_options;
            $json = str_replace("\n", "|n", $json);
            $unslashed_json = stripslashes_deep($json);
            $decoded_json = json_decode($unslashed_json);
            //var_dump($decoded_json);
            if (is_object($decoded_json) && property_exists($decoded_json, 'opt_price')) {
                $wpc_design_options_prices = $decoded_json->opt_price;
            }
        }
        return $wpc_design_options_prices;
    }

    public static function get_design_pricing_options_data($wpc_design_pricing_options) {
        $wpc_design_pricing_options_data = '';
        //    var_dump($wpc_design_pricing_options);
        if (!empty($wpc_design_pricing_options) && function_exists('ninja_forms_get_field_by_id')) {
            //        $json=$wpc_design_pricing_options;
            //        $json=  str_replace("\n", "|n", $json);
            //        $unslashed_json=  stripslashes_deep($json);
            //        $decoded_json=  json_decode($unslashed_json);
            //var_dump($decoded_json);
            $decoded_json = self::wpc_json_decode($wpc_design_pricing_options);
            if (is_object($decoded_json)) {
                $wpc_ninja_form_fields_to_hide_name = array('_wpnonce', '_ninja_forms_display_submit', '_form_id', '_wp_http_referer');
                $wpc_ninja_form_fields_type_to_hide = array('_calc', '_honeypot');
                $wpc_ninja_form_id = '';
                $wpc_ninja_form_id = $decoded_json->wpc_design_opt_list->_form_id;
                $wpc_design_pricing_options_data .= '<div class = "wpc_cart_item_form_data_wrap mg-bot-10">';
                foreach ($decoded_json->wpc_design_opt_list as $ninja_forms_field_id => $ninja_forms_field_value) {
                    if (!in_array($ninja_forms_field_id, $wpc_ninja_form_fields_to_hide_name)) {
                        //var_dump($ninja_forms_field_id);
                        $wpc_get_ninjaform_field_arg = array(
                            'id' => str_replace('ninja_forms_field_', '', $ninja_forms_field_id),
                            'form_id' => $wpc_ninja_form_id
                        );
                        $wpc_ninjaform_field = ninja_forms_get_field_by_id($wpc_get_ninjaform_field_arg);
                        //var_dump($wpc_ninjaform_field);
                        if (!in_array($wpc_ninjaform_field["type"], $wpc_ninja_form_fields_type_to_hide)) {
                            //                        if (empty($ninja_forms_field_value)){
                            //                            $wpc_ninja_form_field_value = __(' ', 'wpd');
                            //                        }else{
                            $wpc_ninja_form_field_value = $ninja_forms_field_value;
                            //                        }   
                            $wpc_design_pricing_options_data .= '<b>' . $wpc_ninjaform_field["data"]["label"] . '</b>: ' . $wpc_ninja_form_field_value . '<br />';
                        }
                    }
                }
                $wpc_design_pricing_options_data .= '<div class = "wpc_cart_item_form_data_wrap">';
            }
        }
        return $wpc_design_pricing_options_data;
    }

    public static function get_option_form($product_id, $wpc_metas) {
        if (function_exists('ninja_forms_display_form')) {
            global $woocommerce;
            $product = get_product($product_id);
            if ($product->product_type == "variation")
                $normal_product_id = $product->parent->id;
            else
                $normal_product_id = $product_id;

            if ($wpc_metas['ninja-form-options'])
                $form_id = $wpc_metas['ninja-form-options'];
            if (!empty($form_id)) {
                global $woocommerce;
                $currency_symbol = get_woocommerce_currency_symbol();
                $product_regular_price = get_post_meta(get_the_ID(), '_regular_price', true);

                //Fill the form in cart item edition case
                add_filter('ninja_forms_field', 'WPD_Design::wpc_fill_option_form', 10, 2);
                echo '<div class = "wpd-design-opt" data-currency_symbol = "' . $currency_symbol . '" data-regular_price = "' . $product_regular_price . '" >';
                ninja_forms_display_form($form_id);
                echo '</div>';
            }
        }
    }

    public static function wpc_json_decode($json) {
        $decoded_json = '';
        if (!empty($json)) {
            $json = str_replace("\n", "|n", $json);
            $unslashed_json = stripslashes_deep($json);
            $decoded_json = json_decode($unslashed_json);
        }
        return $decoded_json;
    }

    public static function wpc_fill_option_form($data, $field_id) {
        // $data will contain all of the field settings that have been saved for this field.
        // Let's change the default value of the field if in cart item edition case
        GLOBAL $wp_query;
        if (isset($wp_query->query_vars["edit"]) && isset($_SESSION["wpc_design_pricing_options"])) {
            $cart_item_key = $wp_query->query_vars["edit"];
            if (isset($_SESSION["wpc_design_pricing_options"][$cart_item_key])) {
                $wpc_json_ninja_form_fields = $_SESSION["wpc_design_pricing_options"][$cart_item_key];
                $wpc_ninja_form_fields = self::wpc_json_decode($wpc_json_ninja_form_fields);
                if (is_object($wpc_ninja_form_fields)) {
                    $wpc_design_opt_list = $wpc_ninja_form_fields->wpc_design_opt_list;
                    $wpc_ninja_form_id = $wpc_design_opt_list->_form_id;
                    $wpc_get_ninjaform_field_arg = array(
                        'id' => $field_id,
                        'form_id' => $wpc_ninja_form_id
                    );
                    $wpc_ninjaform_field = ninja_forms_get_field_by_id($wpc_get_ninjaform_field_arg);


                    if (property_exists($wpc_design_opt_list, 'ninja_forms_field_' . $field_id)) {     // if it is a single field
                        $ninja_forms_field_id = 'ninja_forms_field_' . $field_id;
                        if ($wpc_ninjaform_field["type"] == '_checkbox') {
                            $default_value = '';
                            $checkbox = trim($wpc_design_opt_list->$ninja_forms_field_id);
                            if ($checkbox == 'checked') {
                                $default_value = $checkbox;
                            }
                            $data['default_value'] = $default_value;
                        } else {
                            $data['default_value'] = $wpc_design_opt_list->$ninja_forms_field_id;
                        }
                    } elseif (property_exists($wpc_design_opt_list, 'ninja_forms_field_' . $field_id . '[]')) {      //if it is a list of field
                        $ninja_forms_field_id = 'ninja_forms_field_' . $field_id . '[]';
                        if ($wpc_ninjaform_field['data']['list_type'] == 'checkbox') {
                            $checkbox_list = explode(';', $wpc_design_opt_list->$ninja_forms_field_id);
                            $default_value = array();
                            foreach ($checkbox_list as $checkbox) {
                                $checkbox = explode(':', $checkbox);
                                if (isset($checkbox[1]) && trim($checkbox[1]) == 'checked') {
                                    $default_value[] = trim($checkbox[0]);
                                }
                            }
                            $data['default_value'] = $default_value;
                        } elseif ($wpc_ninjaform_field['data']['list_type'] == 'multi') {
                            $multi_list = explode('|', $wpc_design_opt_list->$ninja_forms_field_id);
                            $default_value = array();
                            foreach ($multi_list as $value) {
                                $default_value[] = trim($value);
                            }
                            $data['default_value'] = $default_value;
                        }
                    }
                }
            }
        }
        return $data;
    }

    private function save_pdf_output_new($variation_id, $input_file, $output_file) {
        GLOBAL $wpc_options_settings;
        $product_id = WPD_Product::get_parent($variation_id);
        $wpc_metas = get_post_meta($product_id, 'wpc-metas', true);
        $product_metas=  WPD_Admin::get_proper_value($wpc_metas, $variation_id, array());
        $variation_output_settings=WPD_Admin::get_proper_value($product_metas, "output-settings", array());
        $global_output_settings = $wpc_options_settings['wpc-output-options'];
        $pdf_format=WPD_Product::get_option($variation_output_settings, $global_output_settings, "pdf-format", "A0");//WPD_Admin::get_proper_value($wpc_output_options, "pdf-format", "A0");
        $pdf_orientation=WPD_Product::get_option($variation_output_settings, $global_output_settings, "pdf-orientation", "P");//WPD_Admin::get_proper_value($wpc_output_options, "pdf-orientation", "P");
        $pdf_margin_lr=WPD_Product::get_option($variation_output_settings, $global_output_settings, "pdf-margin-lr", 20);//WPD_Admin::get_proper_value($wpc_output_options, "pdf-margin-lr", 20);
        $pdf_margin_tb=WPD_Product::get_option($variation_output_settings, $global_output_settings, "pdf-margin-tb", 20);//WPD_Admin::get_proper_value($wpc_output_options, "pdf-margin-tb", 20);
        /*if ($nbCol <= 0 || $total <= 0) {
            $nbCol = 1;
            $total = 1;
        }*/
        $pdf = new TCPDF($pdf_orientation, PDF_UNIT, $pdf_format, true, 'UTF-8', false);

        $pdf->SetCreator("Woocommerce Products Designer by ORION");
        $pdf->SetAuthor('Woocommerce Products Designer by ORION');
        $pdf->SetTitle('Output');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins($pdf_margin_lr, $pdf_margin_tb, -1, true);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        $pdf->AddPage();

//        $defaultImageSize = getimagesize($input_file);
//        $pageWidth = $pdf->getPageWidth();
//        $pagHeight = $pdf->getPageHeight();

        $pdf->Image($input_file, '', '', '', '', '', false, 'C', false, 300, 'C', false, false, 0, false, false, false);
        /*$nblign = $total / $nbCol;
        $marge = 10;

        //Set width and height 
        $w = ($pageWidth - $marge * ($nbCol + 1)) / $nbCol;
        $h = ($defaultImageSize[1] * $w) / $defaultImageSize[0];
        if ($h * ($nblign + 1) + 20 > $pagHeight) {
            $h = ($pagHeight - 2 * ($nblign + 1) - 20) / ($nblign + 1);
            $w = ($defaultImageSize[0] * $h) / $defaultImageSize[1];
            $marge = ($pageWidth - ($w * $nbCol)) / ($nbCol + 1);
        }

        //Print images
        $x = $marge;
        $y = 20;
        $i = 0;
        while ($i < $total) {
            for ($i2 = 0; $i2 < $nbCol; $i2++) {
                if ($i < $total) {
                    $pdf->Image($input_file, $x, $y, $w, $h, '', '', '', true, 300, '', false, false, 0, '', false, false);
                    $x += ($w + $marge);
                    $i ++;
                }
            }
            $x = $marge;
            $y += $h;
        }*/
        $pdf->Output($output_file, 'F');
    }

    private function convert_png_to_jpg($input_path, $remove_input = true) {
        $output_path = str_replace(".png", ".jpg", $input_path);
        // create new imagick object from image.jpg
        $im = new Imagick($input_path);
        $im->setImageBackgroundColor('white');
        $im = $im->flattenImages();
        $im->setImageFormat("jpg");
        $im->writeImage($output_path);
        if ($remove_input)
            unlink($input_path);
        return $output_path;
    }

    /**
     * Export data to archive
     * @param string $generation_dir Working directory path
     * @param array $data Data to export
     * @param int $variation_id Product/Variation ID
     * @return boolean|string
     */
    private function export_data_to_files($generation_dir, $data, $variation_id, $zip_name, $pdf_watermark=false) {
        GLOBAL $wpc_options_settings;
        $wpc_output_options = $wpc_options_settings['wpc-output-options'];
        $generate_layers = false;
        $generate_pdf = false;
        $generate_zip = false;
        
        $product_id = WPD_Product::get_parent($variation_id);
        $wpc_metas = get_post_meta($product_id, 'wpc-metas', true);
        $product_metas=  WPD_Admin::get_proper_value($wpc_metas, $variation_id, array());
        $watermark_id= WPD_Admin::get_proper_value($product_metas, "watermark", false);
        $watermark=false;
        if($watermark_id)
            $watermark=  get_attached_file($watermark_id);
        
        if (isset($wpc_output_options['wpc-generate-layers']))
            $generate_layers = ($wpc_output_options['wpc-generate-layers'] === "yes") ? true : false;
        if (isset($wpc_output_options['wpc-generate-pdf']))
            $generate_pdf = ($wpc_output_options['wpc-generate-pdf'] === "yes") ? true : false;
        if (isset($wpc_output_options['wpc-generate-zip']))
            $generate_zip = ($wpc_output_options['wpc-generate-zip'] === "yes") ? true : false;

        $wpc_img_format = "png";

        $output_arr = array();
        foreach ($data as $part_key => $part_data) {
            $part_dir = "$generation_dir/$part_key";
            if (!wp_mkdir_p($part_dir)) {
                echo "Can't create part directory...";
                continue;
            }
            //Layers
            $layers_array = array();
            if ($generate_layers) {
                $part_layers_dir = "$part_dir/layers";
                if (!wp_mkdir_p($part_layers_dir)) {
                    echo "Can't create layers directory...";
                    continue;
                }
                if (!isset($_FILES["layers"]))
                    continue;
                if (isset($_FILES["layers"]["tmp_name"][$part_key])) {
                    foreach ($_FILES["layers"]["tmp_name"][$part_key] as $layer_data) {
                        $file_name = uniqid("wpc_layer_");
                        $output_file_path = $part_layers_dir . "/$file_name.$wpc_img_format";
                        if (move_uploaded_file($layer_data, $output_file_path)) {
                            if($pdf_watermark&&$watermark_id)
                            {
                                $watermarked_layer_name=  uniqid("watermarked_layer_").".png";
                                $watermarked_layer_path = "$part_layers_dir/".$watermarked_layer_name;
                                $this->merge_pictures($output_file_path, $watermark, $watermarked_layer_path);
                                array_push($layers_array, $watermarked_layer_path);
                            }
                            else
                                array_push($layers_array, $output_file_path);
                        }
                    }
                }
            }

            //Part image
            $output_file_path = $part_dir . "/$part_key.$wpc_img_format";
//            var_dump($_FILES[$part_key]);
            $moved=move_uploaded_file($_FILES[$part_key]['tmp_name']['image'], $output_file_path);
            
            $output_arr[$part_key]["image"] = "$part_key.$wpc_img_format";
            
            //Preview
            if($watermark_id)
            {
                $preview_filename=  uniqid("preview_").".png";
                $preview_file_path = "$part_dir/".$preview_filename;
                $this->merge_pictures($output_file_path, $watermark, $preview_file_path);            
                $output_arr[$part_key]["preview"]=$preview_filename;
                
            }
            else
                $output_arr[$part_key]["preview"] = $output_arr[$part_key]["image"];

            if (!$generate_pdf && !$generate_zip)
                $output_arr[$part_key]["file"] = "$part_key.$wpc_img_format";

//            $product_id = WPD_Product::get_parent($variation_id);
//            $wpc_output_product_settings = get_post_meta($product_id, "wpc_output_product_settings", true);
            $total_img=1;
            $nbCol=1;

            //Part pdf
            if ($generate_pdf && !$generate_layers) {
//                $total_img = WPD_Product::get_option($wpc_output_product_settings, $wpc_output_options, "wpc-outputpdf-img-number", $product_id, $variation_id);
//                $nbCol = WPD_Product::get_option($wpc_output_product_settings, $wpc_output_options, "wpc-outputpdf-img-col", $product_id, $variation_id);

                $output_pdf_file_path = $part_dir . "/$part_key.pdf";
                if($pdf_watermark&&$watermark_id)
                    $this->save_pdf_output_new($variation_id, $preview_file_path, $output_pdf_file_path);
                else
                    $this->save_pdf_output_new($variation_id, $output_file_path, $output_pdf_file_path);
                if (!$generate_zip)
                    $output_arr[$part_key]["file"] = "$part_key.pdf";
            }
            // PDF Layers
            else if ($generate_layers && $generate_pdf) {
                $this->generate_pdf_layers($variation_id, $layers_array, $generation_dir . "/$part_key/$part_key.pdf");
                if (!$generate_zip)
                    $output_arr[$part_key]["file"] = "$part_key.pdf";
            }
        }

        $result = $this->generate_design_archive($generation_dir, "$generation_dir/$zip_name");
        return $output_arr;
    }

    /**
     * Creates a compressed zip file
     * @param type $source Input directory path to zip
     * @param type $destination Output file path
     * @return boolean
     */
    private function generate_design_archive($source, $destination) {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', DIRECTORY_SEPARATOR, realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true)
                    $zip->addEmptyDir(str_replace($source . DIRECTORY_SEPARATOR, '', $file . DIRECTORY_SEPARATOR));
                else if (is_file($file) === true)
                    $zip->addFromString(str_replace($source . DIRECTORY_SEPARATOR, '', $file), file_get_contents($file));
            }
        }
        else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }

    private function generate_pdf_layers($variation_id, $layers_array, $output_file) {
        GLOBAL $wpc_options_settings;
        $product_id = WPD_Product::get_parent($variation_id);
        $wpc_metas = get_post_meta($product_id, 'wpc-metas', true);
        $product_metas=  WPD_Admin::get_proper_value($wpc_metas, $variation_id, array());
        $variation_output_settings=WPD_Admin::get_proper_value($product_metas, "output-settings", array());
        $global_output_settings = $wpc_options_settings['wpc-output-options'];
        $pdf_format=WPD_Product::get_option($variation_output_settings, $global_output_settings, "pdf-format", "A0");//WPD_Admin::get_proper_value($wpc_output_options, "pdf-format", "A0");
        $pdf_orientation=WPD_Product::get_option($variation_output_settings, $global_output_settings, "pdf-orientation", "P");//WPD_Admin::get_proper_value($wpc_output_options, "pdf-orientation", "P");
        $pdf_margin_lr=WPD_Product::get_option($variation_output_settings, $global_output_settings, "pdf-margin-lr", 20);//WPD_Admin::get_proper_value($wpc_output_options, "pdf-margin-lr", 20);
        $pdf_margin_tb=WPD_Product::get_option($variation_output_settings, $global_output_settings, "pdf-margin-tb", 20);//WPD_Admin::get_proper_value($wpc_output_options, "pdf-margin-tb", 20);
        /*if ($nbCol <= 0 || $total <= 0) {
            $nbCol = 1;
            $total = 1;
        }*/
        $pdf = new TCPDF($pdf_orientation, PDF_UNIT, $pdf_format, true, 'UTF-8', false);

        $pdf->SetCreator("Woocommerce Products Designer by ORION");
        $pdf->SetAuthor('Woocommerce Products Designer by ORION');
        $pdf->SetTitle('Output');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins($pdf_margin_lr, $pdf_margin_tb, -1, true);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        $pdf->AddPage();

        //$pdf->Image($input_file, '', '', '', '', '', false, 'C', false, 300, 'C', false, false, 0, false, false, false);

        $layers_id = 0;
                    foreach ($layers_array as $layer) {
                        $pdf->startLayer('layer' . $layers_id, true, true, false);
            $pdf->Image($layer, '', '', '', '', '', '', 'C', true, 300, 'C', false, false, 0, '', false, false);
                        $pdf->endLayer();
                        $layers_id ++;
                    }
//        $defaultImageSize = getimagesize($img_output_file_path);
//        $pageWidth = $pdf->getPageWidth();
//        $pagHeight = $pdf->getPageHeight();
//
//        $nblign = $total / $nbCol;
//        $marge = 10;
//
//        //Set width and height 
//        $w = ($pageWidth - $marge * ($nbCol + 1)) / $nbCol;
//        $h = ($defaultImageSize[1] * $w) / $defaultImageSize[0];
//        if ($h * ($nblign + 1) + 20 > $pagHeight) {
//            $h = ($pagHeight - 2 * ($nblign + 1) - 20) / ($nblign + 1);
//            $w = ($defaultImageSize[0] * $h) / $defaultImageSize[1];
//            $marge = ($pageWidth - ($w * $nbCol)) / ($nbCol + 1);
//        }
//
//        //Print images
//        $x = $marge;
//        $y = 20;
//        $i = 0;
//        $layers_id = 0;
//        while ($i < $total) {
//            for ($i2 = 0; $i2 < $nbCol; $i2++) {
//                if ($i < $total) {
//                    foreach ($layers_array as $layer) {
//                        $pdf->startLayer('layer' . $layers_id, true, true, false);
//                        $pdf->Image($layer, $x, $y, $w, $h, '', '', '', true, 300, '', false, false, 0, '', false, false);
//                        $pdf->endLayer();
//                        $layers_id ++;
//                    }
//
//                    $x += ($w + $marge);
//                    $i ++;
//                }
//            }
//            $x = $marge;
//            $y += $h;
//        }
        $pdf->Output($output_file, 'F');
    }

    function unset_wpc_data_upl_meta($hidden_meta) {
        array_push($hidden_meta, "wpc_data_upl");
        array_push($hidden_meta, "_wpc_design_pricing_options");
        return $hidden_meta;
    }

    function force_individual_cart_items( $cart_item_data, $product_id ){
        if(isset($_SESSION["wpc-user-uploaded-designs"][$product_id])){
            $unique_cart_item_key = md5( microtime().rand() );
            $cart_item_data['unique_key'] = $unique_cart_item_key;
}

        

        return $cart_item_data;

    }   

}
