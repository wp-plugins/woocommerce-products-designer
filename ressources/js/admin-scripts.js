jQuery(document).ready(function ($) {
    
    $(document).on("click",".wpc_img_upload",function(e){
        e.preventDefault();
       var selector=$(this).attr('data-selector');
       var uploader=wp.media({
           title:'Please set the picture',
           button:{
                    text:"Set Image"
                    },
            multiple:false
       })
       .on('select',function(){
            var selection=uploader.state().get('selection');
            selection.map(
                    function(attachment){
                            attachment=attachment.toJSON();
                            $("#"+selector).attr('value',attachment.id);
                            $("#"+selector+"_preview").html("<img src='"+attachment.url+"'>");
                    }
            )
        })
        .open();
   });
   
   //Cliparts add image
   $(document).on("click","#wpc-add-clipart",function(e){
        e.preventDefault();
       var selector=$(this).attr('data-selector');
       var trigger=$(this);
       var uploader=wp.media({
           title:'Please set the picture',
           button:{
                    text:"Set Image"
                    },
            multiple:true
       })
       .on('select',function(){
            var selection=uploader.state().get('selection');
            selection.map(
                    function(attachment){
                            attachment=attachment.toJSON();
                            var code="<input type='hidden' value='"+attachment.id+"' name='selected-cliparts[]'>";
                            code=code+"<span class='wpc-clipart-holder'><img src='"+attachment.url+"'>";
                            code=code+"<a href='#' class='button wpc-remove-clipart' data-id='"+attachment.id+"'>Remove</a></span>";
                            $("#cliparts-container").prepend(code);
                    }
            )
        })
        .open();
   });
   
   $(document).on("click",".wpc-remove-clipart",function(e){
       e.preventDefault();
      var id=$(this).data("id");
      $('#cliparts-form > input[value="'+id+'"]').remove();
      $(this).parent().remove();
   });
   
   $(".wpc_order_item").each(function(){
       var item_id=$(this).attr("data-item");
       $(this).insertBefore($("#order_items_list .item[data-order_item_id='"+item_id+"'] td.name table"));
   });
   
   $(document).on("click","#wpc-customizer button",function(e){
       e.preventDefault();
   });
   
   $(document).on("change",".wpc-activate-part-cb",function(e){
       var is_checked=$(this).is(":checked");
       var selector=$(this).attr('data-selector');
       var output_area=selector+"_preview";
       if(is_checked)
           $("#"+selector).attr('value',0);
       else
           $("#"+selector).attr('value','');
       $("#"+output_area).html("");
           
   });
   
   $(document).on("click",".wpc_img_remove",function(e){
       e.preventDefault();
       var is_active=$(this).siblings(".wpc-activate-part-cb").is(":checked");
       var selector=$(this).attr('data-selector');
       var output_area=selector+"_preview";
       if(is_active)
           $("#"+selector).attr('value',0);
        else
           $("#"+selector).attr('value',"");
       
        $("#"+output_area).html("");
   });
   
   $('#wpc_parts_tab_data').on('show', function() {
      var post_id=$("#post_ID").val();
      var post_type=$("#product-type").val();      
      var variations_arr=new Object();
      $.each($(".woocommerce_variation h3"),function(){
         var elements=$(this).find("[name^='attribute_']");
         var attributes_arr=[];
         var variation_id=$(this).find('.remove_variation').first().attr("rel");
         $.each(elements,function(){
            attributes_arr.push($(this).val());
         });
         variations_arr[variation_id]=attributes_arr;                
      });
      
      $.post( 
                ajax_object.ajax_url,
                {
                    action: "get_wpc_product_tab_data_content",
                    product_id:post_id,
                    post_type:post_type,
                    variations:variations_arr
                },
                function(data) {
                    $("#wpc_parts_tab_data").html(data);
                }
            );
                
    });
    
    $("#wpc-settings .help_tip").each(function(i,e){
        var tip=$(e).data("tip");
       $(e).tooltip({title:tip});
    });
});

//Triggers callbacks on hide/show
(function ($) {
    $.each(['show', 'hide'], function (i, ev) {
      var el = $.fn[ev];
      $.fn[ev] = function () {
        this.trigger(ev);
        return el.apply(this, arguments);
      };
    });
})(jQuery);