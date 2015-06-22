(function ($) {
    'use strict';

    $(document).ready(function () {
        $(".edit-php.post-type-wpc-template .wrap h2 .add-new-h2").show();

        $(document).on("click", ".wpc_img_upload", function (e) {
            e.preventDefault();
            var selector = $(this).attr('data-selector');
            var uploader = wp.media({
                title: 'Please set the picture',
                button: {
                    text: "Set Image"
                },
                multiple: false
            })
                    .on('select', function () {
                        var selection = uploader.state().get('selection');
                        selection.map(
                                function (attachment) {
                                    attachment = attachment.toJSON();
                                    $("#" + selector).attr('value', attachment.id);
                                    $("#" + selector + "_preview").html("<img src='" + attachment.url + "'>");
                                }
                        )
                    })
                    .open();
        });

        //Cliparts add image
        $(document).on("click", "#wpc-add-clipart", function (e) {
            e.preventDefault();
            var selector = $(this).attr('data-selector');
            var trigger = $(this);
            var uploader = wp.media({
                title: 'Please set the picture',
                button: {
                    text: "Set Image"
                },
                multiple: true
            })
                    .on('select', function () {
                        var selection = uploader.state().get('selection');
                        selection.map(
                                function (attachment) {
                                    attachment = attachment.toJSON();
                                    var code = "<input type='hidden' value='" + attachment.id + "' name='selected-cliparts[]'>";
                                    code = code + "<span class='wpc-clipart-holder'><img src='" + attachment.url + "'>";
                                    code = code + "<label>Price: <input type='text' value='0' name='wpc-cliparts-prices[]'></label>";
                                    code = code + "<a href='#' class='button wpc-remove-clipart' data-id='" + attachment.id + "'>Remove</a></span>";
                                    $("#cliparts-container").prepend(code);
                                }
                        )
                    })
                    .open();
        });

        $(document).on("click", ".wpc-remove-clipart", function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            $('#cliparts-form > input[value="' + id + '"]').remove();
            $(this).parent().remove();
        });
        
        $(document).on("click",".o-add-media",function(e){
            e.preventDefault();
           var trigger=$(this);
           var uploader=wp.media({
               title:'Please set the picture',
               button:{
                        text:"Select picture(s)"
                        },
                multiple:false
           })
           .on('select',function(){
                var selection=uploader.state().get('selection');
                selection.map(
                        function(attachment){
                                attachment=attachment.toJSON();
                                trigger.parent().find("input[type=hidden]").val(attachment.id);
                                trigger.parent().find(".media-preview").html("<img src='"+attachment.url+"'>");
    //                            var code="<span class='acd-media-holder'><input type='hidden' value='"+attachment.id+"' name='selected-medias[]'><img src='"+attachment.url+"'><a href='#' class='button acd-remove-media' data-id='"+attachment.id+"'>Remove</a></span>";
    //                            $("#medias-container").prepend(code);
                        }
                )
            })
            .open();
       });

       $(document).on("click",".o-remove-media",function(e){
                e.preventDefault();
                $(this).parent().find(".media-preview").html("");
                $(this).parent().find("input[type=hidden]").val("");
        });

        $(".wpc_order_item").each(function () {
            var item_id = $(this).attr("data-item");
            //       WC<2.2
            $(this).insertBefore($("#order_items_list .item[data-order_item_id='" + item_id + "'] td.name table"));
            //        >=WC2.2
            $(this).insertBefore($("#order_line_items .item[data-order_item_id='" + item_id + "'] td.name table"));
        });

        $(document).on("click", "#wpc-customizer button", function (e) {
            e.preventDefault();
        });

        $(document).on("change", ".wpc-activate-part-cb", function (e) {
            var is_checked = $(this).is(":checked");
            var selector = $(this).attr('data-selector');
            var output_area = selector + "_preview";
            if (is_checked)
                $("#" + selector).attr('value', 0);
            else
                $("#" + selector).attr('value', '');
            $("#" + output_area).html("");

        });

        $(document).on("change", ".wpc-ovni-cb", function (e) {
            var is_checked = $(this).is(":checked");
            var selector = $(this).parent().find("input[type=hidden]");
            if (is_checked)
                selector.val(1);
            else
                selector.val(-1);

        });

        $(document).on("click", ".wpc_img_remove", function (e) {
            e.preventDefault();
            var is_active = $(this).siblings(".wpc-activate-part-cb").is(":checked");
            var selector = $(this).attr('data-selector');
            var output_area = selector + "_preview";
            if (is_active)
                $("#" + selector).attr('value', 0);
            else
                $("#" + selector).attr('value', "");

            $("#" + output_area).html("");
        });

        $('#wpc_output_setting_tab_data').on('show', function () {
            products_tab_data("#wpc_output_setting_tab_data", "get_output_setting_tab_data_content");
        });

        $('#wpc_parts_tab_data').on('show', function () {
            products_tab_data("#wpc_parts_tab_data", "get_product_tab_data_content");
        });

        function products_tab_data(part_tab_id, action_name) {
            var post_id = $("#post_ID").val();
            var post_type = $("#product-type").val();
            var variations_arr = new Object();
            $.each($(".woocommerce_variation h3"), function () {
                var elements = $(this).find("[name^='attribute_']");
                var attributes_arr = [];
                var variation_id = $(this).find('.remove_variation').first().attr("rel");
                $.each(elements, function () {
                    attributes_arr.push($(this).val());
                });
                variations_arr[variation_id] = attributes_arr;
            });

            $.post(
                    ajax_object.ajax_url,
                    {
                        action: action_name,
                        product_id: post_id,
                        post_type: post_type,
                        variations: variations_arr
                    },
            function (data) {
                $(part_tab_id).html(data);
            }
            );
        }

        $('a[href*="post-new.php?post_type=wpc-template"]').click(function (e)
        {
            e.preventDefault();
            $('#wpc-products-selector-modal').modal("show");
        });

        $("#wpc-select-template").click(function (e) {
            var selected_product = $('input[name=template_base_pdt]:checked').val();
            if (typeof selected_product == 'undefined')
                alert("Please select a product first");
            else
            {
                var url = $('a[href$="post-new.php?post_type=wpc-template"]').first().attr("href");
                $(location).attr('href', url + "&base-product=" + selected_product);
            }
        });

        $("#wpc-settings .help_tip").each(function (i, e) {
            var tip = $(e).data("tip");
            $(e).tooltip({title: tip});
        });

        $("#wpc-settings [name='wpc-colors-options[wpc-color-palette]']").change(function () {
            var palette = $(this).val();
            if (palette == "custom")
                $("#wpd-predefined-colors-options").show();
            else
                $("#wpd-predefined-colors-options").hide();
        });

        $(document).on("keyup", "#wpc-settings [name='wpc-colors-options[wpc-custom-palette][]']", function (e) {
            var color = $(this).val();
            $(this).css("background-color", color);
        });

        $("#wpc-settings #wpc-add-color").click(function (e) {
            e.preventDefault();
            var new_color = '<div><input type="text" name="wpc-colors-options[wpc-custom-palette][]" class="wpc-color"><button class="button wpc-remove-color">Remove</button></div>';
            $("#wpc-settings .wpc-colors").append(new_color);
            load_colorpicker();
        });

        $(document).on("click", "#wpc-settings .wpc-remove-color", function (e) {
            e.preventDefault();
            $(this).parent().remove();
        });

        $(document).on("click", ".wpc-add-rule", function (e)
        {
            var new_rule_index = $(".wpc-rules-table tr").length;
            var group_index = $(this).data("group");
            var raw_tpl = $("#wpc-rule-tpl").val();
            var tpl1 = raw_tpl.replace(/{rule-group}/g, group_index);
            var tpl2 = tpl1.replace(/{rule-index}/g, new_rule_index);
            $(this).parents(".wpc-rules-table").find("tbody").append(tpl2);
            $(this).parents(".wpc-rules-table").find(".a_price").attr("rowspan", new_rule_index + 1);
        });

        $(document).on("click", ".wpc-add-group", function (e)
        {
            var new_rule_index = 0;
            var group_index = $(".wpc-rules-table").length;
            var raw_tpl = $("#wpc-first-rule-tpl").val();
            var tpl1 = raw_tpl.replace(/{rule-group}/g, group_index);
            var tpl2 = tpl1.replace(/{rule-index}/g, new_rule_index);
            var html = '<table class="wpc-rules-table widefat"><tbody>' + tpl2 + '</tbody></table>';
            $(".wpc-rules-table-container").append(html);
        });

        $(document).on("click", ".wpc-remove-rule", function (e)
        {
            var nb_rules = $(".wpc-rules-table tr").length;
            $(this).parents(".wpc-rules-table").find(".a_price").attr("rowspan", nb_rules - 1);
            $(this).parents("tr").remove();

        });

        $(document).on("change", "#wpc-settings tr:first-child input[name^='wpc-texts-options']", function (e) {
            var checked = $(this).is(":checked");
            $("#wpc-settings tr:not(:first-child) label[for^='wpc-texts-options'] input[type='checkbox']").prop("checked", checked);
        });

        $(document).on("change", "#wpc-settings tr:first-child input[name^='wpc-shapes-options']", function (e) {
            var checked = $(this).is(":checked");
            $("#wpc-settings tr:not(:first-child) label[for^='wpc-shapes-options'] input[type='checkbox']").prop("checked", checked);
        });

        $(document).on("change", "#wpc-settings tr:first-child input[name^='wpc-images-options']", function (e) {
            var checked = $(this).is(":checked");
            $("#wpc-settings tr:not(:first-child) label[for^='wpc-images-options'] input[type='checkbox']").prop("checked", checked);
        });

        $(document).on("change", "#wpc-settings tr:first-child input[name^='wpc-upload-options']", function (e) {
            var checked = $(this).is(":checked");
            $("#wpc-settings tr:not(:first-child) label[for^='wpc-upload-options'] input[type='checkbox']").prop("checked", checked);
        });
        
        $(document).on("change", "#wpc-settings tr:first-child input[name^='wpc-designs-options']", function (e) {
            var checked = $(this).is(":checked");
            $("#wpc-settings tr:not(:first-child) label[for^='wpc-designs-options'] input[type='checkbox']").prop("checked", checked);
           /* if (checked)
                $("#wpc-settings tr:not(:first-child) label[for^='wpc-designs-options'] input[type='checkbox']").removeAttr("disabled");
            else
                $("#wpc-settings tr:not(:first-child) label[for^='wpc-designs-options'] input[type='checkbox']").attr("disabled", true);*/
        });

        $(document).on("keyup", ".color_field", function (e) {
            var color = $(this).val();
            $(this).css("background-color", color);
        });
        
        $(document).on("click", ".add_icon", function (e) {
            e.preventDefault();
            var data_tab = $(this).data('tab');
            var name = $(this).data('name');
            var uploader = wp.media({
                title: 'Please set the picture',
                button: {
                    text: "Set Image"
                },
                multiple: true
            })
                    .on('select', function () {
                        var selection = uploader.state().get('selection');
                        selection.map(
                                function (attachment) {
                                    attachment = attachment.toJSON();
                                    var code = "<div><input type='hidden' value='" + attachment.id + "' name='" + data_tab + "[" + name + "]'><img src='" + attachment.url + "'><a href='#' class='add_icon button' title='Add Icon'>Add Icon</a></div>";
                                    code = code + "<div class='remove_button'><a href='#' class='button wpc-remove-icon' data-id=''>Remove</a></div>";
                                    $('#' + name + '_icon').html(code);
                                }
                        )
                    }).open();
        });
        
        $(document).on("click", ".wpc-remove-icon", function (e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            //        $("#"+tab+"_icon img").attr("src","");
            //        $("#"+tab+"_icon input").val("");
            $(this).parents(".wpc_images_data").find("img").attr("src", "");
            $(this).parents(".wpc_images_data").find("input[type=hidden]").val("");
        });
        
        load_colorpicker();
        function load_colorpicker()
        {
            $('.wpc-color').each(function (index, element)
            {
                var e = $(this);
                var initial_color = e.val();
                e.css("background-color", initial_color);
                $(this).ColorPicker({
                    color: initial_color,
                    onShow: function (colpkr) {
                        $(colpkr).fadeIn(500);
                        return false;
                    },
                    //                onHide: function (colpkr) {
                    //                        $(colpkr).fadeOut(500);
                    //                        var selected_object=canvas.getActiveObject();
                    //                        if((selected_object!=null))
                    //                        {
                    //                            save_canvas();
                    //                        }
                    //                        return false;
                    //                },
                    onChange: function (hsb, hex, rgb) {
                        e.css("background-color", "#" + hex);
                        e.val("#" + hex);
                    }
                });
            });
        }

        $(".TabbedPanels").each(function ()
        {
            var cookie_id = 'tabbedpanels_' + $(this).attr("id");
            var defaultTab = ($.cookie(cookie_id) ? parseInt($.cookie(cookie_id)) : 0);
//            console.log(defaultTab);
            new Spry.Widget.TabbedPanels($(this).attr("id"),{defaultTab: defaultTab -1 });
        });
//            var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
        $('.TabbedPanelsTab').click(function(event) {
            var cookie_id = 'tabbedpanels_' + $(this).parent().parent('.TabbedPanels').attr('id');
            $.cookie(cookie_id, parseInt($(this).attr('tabindex')));
//            console.log( $.cookie(cookie_id));
    });
    });

})(jQuery);
