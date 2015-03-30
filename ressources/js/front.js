jQuery(document).ready(function ($) {
    var cliparts_accordion = new Spry.Widget.Accordion("img-cliparts-accordion", { useFixedPanelHeights: false, defaultPanel: -1 });
    $("#wpc-top-bar > span").click(function(){
        $("#wpc-top-bar > span").removeClass("selected");
        $(this).addClass("selected");
       $("#wpc-tools-bar > div").hide();
       var data_id=$(this).attr("data-id");
       $(data_id).show();
    });
    
    $(".noUiSlider").each(function()
    {
        var min=$(this).attr("data-min");
        if(min==null)
            min=0;
        
        var max=$(this).attr("data-max");
        if(max==null)
            max=1;
        
        var step=$(this).attr("data-step");
        if(step==null)
            step=0.1;
        
        var start=$(this).attr("data-start");
        if(start==null)
            step=1;
        
        $(this).wpcnoUiSlider({
            start: [parseInt(start)],
            step: parseFloat(step),
            range: {
                    'min': parseInt(min),
                    'max': parseInt(max)
            }

        });
    });
    
    function roundRect(ctx, x, y, width, height, radius, fill, stroke) {
        if (typeof stroke == "undefined" ) {
          stroke = false;
        }
        if (typeof radius === "undefined") {
          radius = 5;
        }
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + width - radius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        ctx.lineTo(x + width, y + height - radius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        ctx.lineTo(x + radius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
        if (stroke) {
        ctx.strokeStyle=stroke;
          ctx.stroke();
        }
        if (fill) {
          ctx.fill();
        }        
      }
    
    
    
    var canvas_width=$("#wpc-editor-container").width();
    var canvas_height=$("#wpc-editor-container").height();
    var clip_w=$("#wpc-editor-container").data("clip_w");
    var clip_h=$("#wpc-editor-container").data("clip_h");
    var clip_r=$("#wpc-editor-container").data("clip_r");
    var clip_rr=$("#wpc-editor-container").data("clip_rr");
    var clip_x_main=$("#wpc-editor-container").data("clip_x");
    var clip_y_main=$("#wpc-editor-container").data("clip_y");
    var clip_type=$("#wpc-editor-container").data("clip_type");
    var clip_border=$("#wpc-editor-container").data("clip_border");
    var output_loop_delay=$("#wpc-editor-container").data("output_delay");
    var palette_type=$("#wpc-editor-container").data("palette_type");
    var canvas = new fabric.Canvas('wpc-editor', { width: canvas_width, height: canvas_height });
    canvas.setWidth(canvas_width);
    canvas.setHeight(canvas_height);
    canvas.backgroundImageStretch=false;
    if(clip_w && clip_h && clip_w>0 && clip_h>0&&clip_type=="rect")
    {
        var clip_x=(canvas_width-clip_w)/2;
        if(clip_x_main)
            clip_x=clip_x_main;
        var clip_y=(canvas_height-clip_h)/2;
        if(clip_y_main)
            clip_y=clip_y_main;
        canvas.clipTo = function(ctx) {
            if(clip_type=="rect"||clip_type=="")
            {
                if(clip_rr>0)
                    roundRect(ctx, clip_x, clip_y, clip_w, clip_h, clip_rr, "", clip_border);
                else
                {
                    ctx.rect(clip_x, clip_y,clip_w , clip_h);
                    if(clip_border)
                    {
                        ctx.strokeStyle=clip_border;
                        ctx.stroke();
                    }
                }
                
            }
        };
    }
    else if(clip_r && clip_r>0&&clip_type=="arc")
    {
        var clip_x=canvas_width/2;
        if(clip_x_main)
            clip_x=clip_x_main;
        var clip_y=canvas_height-clip_h/2;
        if(clip_y_main)
            clip_y=clip_y_main;
        
        canvas.clipTo = function(ctx) {
                ctx.arc(clip_x, clip_y, clip_r, 0, 2*Math.PI);

            if(clip_border)
            {
                ctx.strokeStyle=clip_border;
                ctx.stroke();
            }
        };
    }
    canvas.renderAll();
    
    var serialized_parts=[];
    var final_canvas_parts={};
    var selected_part=-1;
    var canvasManipulationsPosition=[];
    var arr_filters = ['grayscale', 'invert', 'remove-white', 'sepia', 'sepia2',
                    'brightness', 'noise', 'gradient-transparency', 'pixelate',
                    'blur', 'convolute'];
    
    load_canvas_listeners();
    
    function load_canvas_listeners()
    {
        canvas.on('object:selected', function(options) {
            if (options.target) {
                var objectType=options.target.type;
                var arr_shapes=["rect","circle","triangle","polygon"];
                if(objectType=="text")
                {
                        $(".text-btn").click();
                        $('#font-family-selector').val(options.target.get("fontFamily"));
                        $('#font-size-slider').val(options.target.get("fontSize"));
                        $('#txt-color-selector').css("background-color",options.target.get("fill"));
                        $('#txt-bg-color-selector').css("background-color",options.target.get("backgroundColor"));
                        $('#new-text').val(options.target.get("text"));

                        var text_decoration=options.target.get("textDecoration");
                        if(text_decoration=="underline")
                            $("#underline-cb").attr('checked','checked');
                        else
                            $("#underline-cb").removeAttr('checked');

                        var fontWeight=options.target.get("fontWeight");
                        if(fontWeight=="bold")
                            $("#bold-cb").attr('checked','checked');
                        else
                            $("#bold-cb").removeAttr('checked');

                        var fontStyle=$("#selected_object").get("fontStyle");
                        if(fontStyle=="italic")
                            $("#italic-cb").attr('checked','checked');
                        else
                            $("#italic-cb").removeAttr('checked');
                         if (options.target.get("stroke")!=false&&options.target.getStroke()!=null)
                         {
                            $('#txt-outline-color-selector').css("background-color",options.target.get("stroke"));
                            $('#o-thickness-slider').val(options.target.get("strokeWidth"));
                         }
                         else
                         {
                             $('#o-thickness-slider').val(0);
                         }
                         
                         var txt_opacity=options.target.opacity;
                         $("#opacity-slider").val(txt_opacity);
    
                }
                else if(jQuery.inArray(objectType, arr_shapes)>=0)
                {
                    var shape_opacity=options.target.opacity;
                    $("#shape-opacity-slider").val(shape_opacity);
                    $('#shape-bg-color-selector').css("background-color",options.target.get("fill"));                    
                    $('#shape-outline-color-selector').css("background-color",options.target.get("stroke"));
                    $("#shape-thickness-slider").val(options.target.get("strokeWidth"));
                }
                else if(objectType=="image")
                {
                    $(".images-btn").click();
                    var filters=options.target.filters;
                    $("#img-effects input:checkbox").removeAttr('checked');
                    $.each( filters, function( index, value ) {                        
                        if(value)
                        {
                            var filter=value.type;
                            var matrix=value.matrix;
                            var blur_matrix=[ 1/9, 1/9, 1/9, 1/9, 1/9, 1/9, 1/9, 1/9, 1/9 ];
                            var sharpen_maxtrix=[  0, -1,  0, -1,  5, -1, 0, -1,  0 ];
                            var emboss_matrix=[ 1,   1,  1, 1, 0.7, -1, -1,  -1, -1 ];
                            if(filter=="Grayscale")
                                $("#grayscale").attr('checked','checked');
                            else if(filter=="Invert")
                                $("#invert").attr('checked','checked');
                            else if(filter=="Sepia")
                                $("#sepia").attr('checked','checked');
                            else if(filter=="Sepia2")
                                $("#sepia2").attr('checked','checked');
                            else if(filter=="Convolute")
                            {
                                if(($(matrix).not(blur_matrix).length == 0 && $(blur_matrix).not(matrix).length == 0))
                                    $("#blur").attr('checked','checked');
                                
                                else if(($(matrix).not(sharpen_maxtrix).length == 0 && $(sharpen_maxtrix).not(matrix).length == 0))
                                    $("#sharpen").attr('checked','checked');
                                
                                else if(($(matrix).not(emboss_matrix).length == 0 && $(emboss_matrix).not(matrix).length == 0))
                                    $("#emboss").attr('checked','checked');
                            }
                            
                            else
                                console.log(filter, matrix);
                            
                        }
                       
                        
//                        var filter_index=jQuery.inArray(filter.type.toLowerCase(), arr_filters);
//                        console.log(filters[key]);
                    });
//                    var filter_index=jQuery.inArray(filters, arr_filters);
                }
                else if((objectType=="path"||objectType=="path-group"))
                {
                    $(".images-btn").click();
//                    $('#clipart-bg-color-selector').css("background-color",options.target.get("fill"));
                    $("#clipart-bg-color-container").html("");
                    if (options.target.isSameColor && options.target.isSameColor() || !options.target.paths) {
                        var color_picker_id='clipart-bg-'+1+'-color-selector';
                        var colorpicker_tpl='<span id="'+color_picker_id+'" class="svg-color-selector" data-placement="top" data-original-title="Background color (SVG files only)" style="background-color:'+options.target.get("fill")+'"></span>';
                        $("#clipart-bg-color-container").append(colorpicker_tpl);
                        $("[data-original-title]").tooltip();
                        load_svg_color_picker(color_picker_id);
                    }
                    else if (options.target.paths) {                        
                        var used_colors=[];
                        var picker_index=0;
                        for (var i = 0; i < options.target.paths.length; i++) {
                            var color_picker_id='clipart-bg-'+picker_index+'-color-selector';
                            var current_color=options.target.paths[i].fill;
                            var colorpicker_tpl='<span id="'+color_picker_id+'" class="svg-color-selector" data-placement="top" data-original-title="Background color (SVG files only)" style="background-color:'+current_color+'" data-index="'+i+'"></span>';
                                $("#clipart-bg-color-container").append(colorpicker_tpl);
                                $("[data-original-title]").tooltip();
                                load_svg_color_picker(color_picker_id);
                                picker_index++;
                            
                            
                            
//                            if(i==14)
//                            {
//                                alert("Color limit reached.");
//                                break;
//                            }
                       }
                    }
                        
                }
            }
        });
        
        canvas.on('object:added', function(options) {
            if (options.target) {
                canvas.calcOffset();
                canvas.renderAll();
                options.target.setCoords();
                var objectType=options.target.type;
                if(objectType=="text")
                {
                    reset_text_palette();
                }
                canvas.calcOffset();
            }
        });
        
        canvas.on('object:modified', function(options) {
            canvas.calcOffset();
            canvas.renderAll();
            options.target.setCoords();            
            save_canvas();
        });
    }
    
    function change_item_color(id, hex)
    {
        $('#'+id).css('background-color', '#' + hex);
        var selected_object=canvas.getActiveObject();
        if((selected_object!=null))
        {
                if((id=="txt-color-selector")||(id=="shape-bg-color-selector")||id=="clipart-bg-color-selector")
                    selected_object.set("fill", '#' + hex);
                else if(id=="txt-bg-color-selector")
                    selected_object.set("backgroundColor", '#' + hex);
                else if(id=="txt-outline-color-selector"||id=="shape-outline-color-selector")
                    selected_object.set("stroke", '#' + hex);                                    
                else
                    console.log("unknow color selector :#"+id);

                canvas.renderAll();

        }
    }
    
    function change_svg_color(id, hex, index)
    {
        $('#'+id).css('background-color', '#' + hex);

        var selected_object=canvas.getActiveObject();
        if((selected_object!=null)&&(selected_object.type=="path"||selected_object.type=="path-group"))
        {
            {
                if (selected_object.isSameColor && selected_object.isSameColor() || !selected_object.paths) {
                    selected_object.set("fill", '#' + hex);
                }
                else if (selected_object.paths) {
                    if(svg_colorization=="by-colors")
                    {
                        index=$("#"+id).attr("data-index");
                        var indexes=index.split(',');
                        $.each( indexes, function( key, value )
                        {
                            selected_object.paths[value].setFill('#' + hex);
                        });
                    }
                    else
                        selected_object.paths[index].setFill('#' + hex);
                }
            }
            canvas.renderAll();

        }
    }
    
    $('[id$="color-selector"]').each(function()
    {
            var id=$(this).attr("id");
            var initial_color=$(this).css("background-color");
            if(!initial_color)
                initial_color="#0000ff";
            if(palette_type=="custom")
            {
                $('#'+id).qtip({
                    content: "<div class='wpc-custom-colors-container' data-id='"+id+"'>"+palette_tpl+"</div>",
                    position: {
                        corner: {
                           target: 'middleRight',
                           tooltip: 'leftTop'
                        }
                     },
                     style: { 
                        width: 200,
                        padding: 5,
                        background: 'white',
                        color: 'black',
//                        textAlign: 'center',
                        border: {
                           width: 1,
                           radius: 1,
                           color: '#08AED6'
                        }
                    },
                    tip: 'bottomLeft',
                    show: 'click',
                    hide: { when: { event: 'unfocus' } }
                 });
            }
            else
            {
                $('#'+id).ColorPicker({
                    color: initial_color,
                    onShow: function (colpkr) {
                            $(colpkr).fadeIn(500);
                            return false;
                    },
                    onHide: function (colpkr) {
                            $(colpkr).fadeOut(500);
                            var selected_object=canvas.getActiveObject();
                            if((selected_object!=null))
                            {
                                save_canvas();
                            }
                            return false;
                    },
                    onChange: function (hsb, hex, rgb) {
                            change_item_color(id, hex);
                        }
                });
            }
            
    });
    
    $(document).on("click",".wpc-custom-colors-container span",function(e){
        var id=$(this).parent().data("id");
        var hex=$(this).data("color");
        change_item_color(id, hex);
    });
    
    $(document).on("click",".wpc-custom-svg-colors-container span",function(e){
        var id=$(this).parent().data("id");
        var index=$(this).parent().data("index");
        var hex=$(this).data("color");
        change_svg_color(id, hex, index);
    });
    
    function load_svg_color_picker(id)
    {
        var selector=$('#'+id);
        var index=selector.data("index");
        var initial_color=selector.css("background-color");
        if(!initial_color)
            initial_color="#0000ff";
            selector.ColorPicker({
                color: initial_color,
                onShow: function (colpkr) {
                        $(colpkr).fadeIn(500);
                        return false;
                },
                onHide: function (colpkr) {
                        $(colpkr).fadeOut(500);
                        var selected_object=canvas.getActiveObject();
                        if((selected_object!=null))
                        {
                            save_canvas();
                        }
                        return false;
                },
                onChange: function (hsb, hex, rgb) {
                    change_svg_color(id, hex, index);
                }
            });
    }
    
    function apply_filter(filter, toApply) {
    
        var selected_object=canvas.getActiveObject();
        
        var filter_index=jQuery.inArray(filter.type.toLowerCase(), arr_filters);
        if((selected_object!=null)&&(selected_object.type=="image"))
        {
            if(toApply)
                selected_object.filters[filter_index] = filter;
            else
                selected_object.filters[filter_index] = false;
            
            selected_object.applyFilters(canvas.renderAll.bind(canvas));
            save_canvas();
        }

    }
    
    function load_fonts()
    {
        var blankObj = new fabric.Text("Loading fonts...", { 
        left: 200,
        top: 125,
        fontSize: 1,
        fontWeight: "bold",
        fontStyle:"italic",
        textDecoration: "underline",
        useNative: true
        });

       canvas.add(blankObj);

      $("#font-family-selector > option").each(function() {
              blankObj.set('fontFamily', $(this).val());
              canvas.renderAll();
            });

      canvas.remove(blankObj);
    }
    
    function reset_text_palette()
    {
        $("#new-text").val("");
        
        $("#underline-cb").removeAttr('checked');
        $("#bold-cb").removeAttr('checked');
        $("#italic-cb").removeAttr('checked');
        
        $("#font-family-selector").val($("#font-family-selector option:first").val());
        $("#font-family-selector").val($("#font-family-selector option:first").val());
        $("#o-thickness-slider").val($("#o-thickness-slider option:first").val());
        $("#opacity-slider").val(1);
    }
    
    function applyFilterValue(index, prop, value) {
      var obj = canvas.getActiveObject();
      if (obj.filters[index]) {
        obj.filters[index][prop] = value;
        obj.applyFilters(canvas.renderAll.bind(canvas));
      }
    }
    
    function create_text_elmt(txt)
    {
        var strokeWidth=$("#o-thickness-slider").val();
    	var fontWeight="normal";
    	var textDecoration="";
    	var fontStyle="";
    	var font_color=$("#txt-color-selector").css('background-color');
        var fontFamily=$("#font-family-selector").val();
        var font_size=parseInt($("#font-size-slider").val());
        var opacity=$("#opacity-slider").val();
        var strokeColor=$("#txt-outline-color-selector").css('background-color');
        var bgColor=$("#txt-bg-color-selector").css('background-color');
        
        var is_bold=$("#bold-cb").is(":checked");
        var is_underlined=$("#underline-cb").is(":checked");
        var is_italic=$("#italic-cb").is(":checked");
        
         if(is_bold)
            fontWeight="bold";
        if(is_underlined)
           textDecoration="underline";        
        if(is_italic)
           fontStyle="italic";
      
      
     var text = new fabric.Text(txt, { 
        	left: 30, 
        	top: 70 ,
        	fontFamily: fontFamily,
        	fontSize: font_size,
        	fontWeight: fontWeight,
        	fontStyle:fontStyle,
        	textDecoration: textDecoration,
        	selectable:true,
                fill :font_color
 	 
        });
        
        if(strokeWidth>0)
        {
            text.set("stroke",strokeColor);
            text.set("strokeWidth",parseInt(strokeWidth));            
        }
        
        return text;
    }
    
    function add_text (txt, left, top){
        var text=create_text_elmt(txt);
        canvas.add(text);
        if(left)
        {
            text.set("left",left);
            text.set("top",top);
        }
        else
        {
            canvas.centerObjectH(text);
            canvas.centerObjectV(text);
        }
        canvas.renderAll();
        text.setCoords();
        
        $("#new-text").val("");
        save_canvas();
    }
    
    function cloneObject(object, render_after)
    {
        var new_object=fabric.util.object.clone(object);
        new_object.set("top", new_object.top+5);
        new_object.set("left", new_object.left+5);
        canvas.add(new_object);
        if(render_after)
        {
            canvas.renderAll();
            save_canvas();
        }
        
    }

    $('#grayscale').change(function() 
    {
        apply_filter(new fabric.Image.filters.Grayscale(), $(this).is(':checked'));

    });

    $('#invert').change(function() {
        apply_filter(new fabric.Image.filters.Invert(), $(this).is(':checked'));  
    });

    $('#sepia').change(function() {
        apply_filter(new fabric.Image.filters.Sepia(), $(this).is(':checked'));  
    });

    $('#sepia2').change(function() {
        apply_filter(new fabric.Image.filters.Sepia2(), $(this).is(':checked'));  
    });

    $('#blur').change(function() {
        if($(this).is(':checked'))
            $("#sharpen, #emboss").removeAttr('checked');
        
        apply_filter(new fabric.Image.filters.Convolute({
                    matrix: [ 1/9, 1/9, 1/9,
                              1/9, 1/9, 1/9,
                              1/9, 1/9, 1/9 ]
                  }),
                  $(this).is(':checked'));  
    });
   
    
    $('#sharpen').change(function() {
        if($(this).is(':checked'))
            $("#blur, #emboss").removeAttr('checked');
        
        apply_filter(new fabric.Image.filters.Convolute({
                matrix: [  0, -1,  0,
                          -1,  5, -1,
                           0, -1,  0 ]
              }),
              $(this).is(':checked'));  
    });

    $('#emboss').change(function() {
        if($(this).is(':checked'))
            $("#sharpen, #blur").removeAttr('checked');
        
        apply_filter(new fabric.Image.filters.Convolute({
            matrix: [ 1,   1,  1,
                      1, 0.7, -1,
                     -1,  -1, -1 ]
          }), $(this).is(':checked'));  
    });
    
    $('#new-text').keyup(function() 
    {
        var selected_object=canvas.getActiveObject();
        var new_text=$('#new-text').val();
        if((selected_object!=null)&&(selected_object.type=="text"))
        {
            selected_object.set("text",new_text);
            save_canvas();
            canvas.renderAll();
        }
    });
    
    $("#shape-thickness-slider").change(function()
    {
        var selected_object=canvas.getActiveObject();
        if(selected_object!=null)
        {
            var arr_shapes=["rect","circle","triangle","polygon"];
            if(jQuery.inArray(selected_object.type, arr_shapes)>=0)
            {
                var outline_color=$("#shape-outline-color-selector").css("background-color");
                var outline_width=$("#shape-thickness-slider").val();
                if(selected_object!=null)
                {        
                    if(outline_width>0)        
                    {
                        selected_object.set("strokeWidth",parseInt(outline_width));
                        selected_object.set("stroke",outline_color);
                    }
                    else
                        selected_object.set("stroke",false);
                }
                canvas.renderAll();
            }
        }
    });
    
//    $('#bg_color').iris({
//            width: 240,
//            hide: false,
//            change: function(event, ui) {
//                canvas.setBackgroundColor ( ui.color.toString());
//                canvas.renderAll();
//        }
//    });

    $(".noUiSlider").each(function()
    {
        var min=$(this).attr("data-min");
        if(min==null)
            min=0;
        
        var max=$(this).attr("data-max");
        if(max==null)
            max=1;
        
        var step=$(this).attr("data-step");
        if(step==null)
            step=0.1;
        
        var start=$(this).attr("data-start");
        if(start==null)
            start=1;
        var start_array=[start];
    });
    
    $("#add-text-btn").click(function()
    {
        var new_text=$("#new-text").val();
        if(new_text.length==0)
            alert("Please enter the text to add.");
        else
            add_text(new_text, false, false);
        
    });
    
    $("#delete_btn").click(function(){
        var selected_object=canvas.getActiveObject();
        var selected_group=canvas.getActiveGroup();
        var is_confirmed;
        if(selected_object!=null)
        {
            if (selected_object['lockDeletion']) {
                alert(deletion_error_msg);
            }
            else
            {
                is_confirmed=confirm('Do you really want to delete the selected items?');
                if(is_confirmed)
                {
                    canvas.remove(selected_object);
    //                selected_object.remove();
                    canvas.calcOffset();
                    canvas.renderAll();
                    save_canvas();
                }            
            };           
        }
        else if(selected_group!=null)
        {
            if (selected_group['lockDeletion']) {
                alert(deletion_error_msg);
            }
            else
            {
            is_confirmed=confirm('Do you really want to delete the selected items?');
            if(is_confirmed)
            {
                selected_group.forEachObject(function(a) {
                    canvas.remove(a);
                  });
                canvas.discardActiveGroup();
                canvas.calcOffset();
                canvas.renderAll();
                save_canvas();
            }            

            };           
        }
    });
    
    $("#copy_paste_btn").click(function(){  
        var selected_object=canvas.getActiveObject();
        var selected_group=canvas.getActiveGroup();
        if(selected_group!=null)
        {
            var new_group=new fabric.Group();
            canvas.discardActiveGroup();
            canvas.renderAll();
            var objects=canvas.getObjects();
            $.each( objects, function( key, current_item ) 
            {
                if(selected_group.contains(current_item))
                {
                    cloneObject(current_item, false);
                }
            });
            canvas.renderAll();
            save_canvas();
        }
        else if(selected_object!=null)
        {
            canvas.discardActiveObject();
            cloneObject(selected_object, true);
            save_canvas();
        }
    });
    
    $("#clear_all_btn").click(function(){  
        var is_confirmed=confirm('Do you really want to delete all items in the design area?');
        if(is_confirmed)
        {
            canvas.clear();
            save_canvas();
        }
    });
    var global_zoom=1;
    $("#zoom-in-btn").click(function(){  
        global_zoom+=0.2;
        canvas.setZoom(global_zoom);
    });
    
    $("#zoom-out-btn").click(function(){  
        global_zoom-=0.2;
        canvas.setZoom(global_zoom);
    });
    
    $("#grid-btn").click(function(){
       $("#wpc-editor-container") .toggleClass("wpc-canvas-grid");
    });
    
    $("#bring_to_front_btn").click(function(){
        var selected_object=canvas.getActiveObject();
        var selected_group=canvas.getActiveGroup();
        if(selected_object!=null)
        {
            canvas.bringForward(selected_object);
            canvas.renderAll();
            save_canvas();
            
        }
        else if(selected_group!=null)
        {
            selected_group.forEachObject(function(a) {
                    canvas.bringForward(a);
                  });
            canvas.discardActiveGroup();
            canvas.renderAll();
            save_canvas();
        }
    });
    
    $("#send_to_back_btn").click(function(){
        var selected_object=canvas.getActiveObject();
        var selected_group=canvas.getActiveGroup();
        if(selected_object!=null)
        {
            canvas.sendBackwards(selected_object);
            canvas.renderAll();
            save_canvas();
        }
        else if(selected_group!=null)
        {
            selected_group.forEachObject(function(a) {
                    canvas.sendBackwards(a);
                  });
            canvas.discardActiveGroup();
            canvas.renderAll();
            save_canvas();
                 
        }
    });
    
    $("#align_h_btn").click(function(){
        var selected_object=canvas.getActiveObject();
        var selected_group=canvas.getActiveGroup();
        if(selected_object!=null)
        {
            canvas.centerObjectH(selected_object);
            canvas.renderAll();
            selected_object.setCoords();
            save_canvas();
        }
        else if(selected_group!=null)
        {       
            canvas.centerObjectH(selected_group);
            canvas.renderAll();
            selected_group.setCoords();
            save_canvas();
        }
    });
    
    $("#align_v_btn").click(function(){
        var selected_object=canvas.getActiveObject();
        var selected_group=canvas.getActiveGroup();
        if(selected_object!=null)
        {
            canvas.centerObjectV(selected_object);
            canvas.renderAll();
            selected_object.setCoords();
            save_canvas();
        }
        else if(selected_group!=null)
        {       
            canvas.centerObjectV(selected_group);
            canvas.renderAll();
            selected_group.setCoords();
            save_canvas();
        }
        
        
    });
    
    $("#flip_h_btn").click(function(){
        var selected_object=canvas.getActiveObject();
        var selected_group=canvas.getActiveGroup();
        if(selected_object!=null)
        {
            if(selected_object.get("flipX")==true)
                selected_object.set("flipX", false);
            else
                selected_object.set("flipX", true);
            canvas.renderAll();
            save_canvas();
        }
        else if(selected_group!=null)
        {
            if(selected_group.get("flipX")==true)
                selected_group.set("flipX", false);
            else
                selected_group.set("flipX", true);
            canvas.renderAll();
            save_canvas();
        }
    });
    
    $("#flip_v_btn").click(function(){
        var selected_object=canvas.getActiveObject();
        var selected_group=canvas.getActiveGroup();
        if(selected_object!=null)
        {
            if(selected_object.get("flipY")==true)
                selected_object.set("flipY", false);
            else
                selected_object.set("flipY", true)
            canvas.renderAll();
            save_canvas();
        }
        else if(selected_group!=null)
        {
            if(selected_group.get("flipY")==true)
                selected_group.set("flipY", false);
            else
                selected_group.set("flipY", true)
            canvas.renderAll();
            save_canvas();
        }
        
    });
    
    $.fn.add_shape= function(classname){
        var opacity=$("#shape-opacity-slider").val();
        
        var style="background-color:#ed8a46;";
        style+="opacity:"+opacity+";";
        style+="left:0px;top:0px;"
        
        var output="<div class='draggable resizable "+classname+"' style='"+style+"'></div>";
        this.append(output);
        $.load_design_area_features();
        $.reset_palette();
    };
    
    $("#square-btn").click(function()
    {
        var bg_color=$("#shape-bg-color-selector").css("background-color");
        var outline_color=$("#shape-outline-color-selector").css("background-color");
        var opacity=$("#shape-opacity-slider").val();
        
        var rect = new fabric.Rect({
            left: 100,
            top: 50,
            fill: bg_color,
            opacity:opacity,
            width: 50,
            height: 50
        });
        
        var outline_width=$("#shape-thickness-slider").val();
        if(outline_width>0)        
        {
            rect.set("strokeWidth",parseInt(outline_width));
            rect.set("stroke",outline_color);
        }
        
        canvas.add(rect);
        canvas.centerObjectH(rect);
        canvas.centerObjectV(rect);
        canvas.renderAll();
        rect.setCoords();
        save_canvas();
    });
    
    $("#r-square-btn").click(function()
    {
        var bg_color=$("#shape-bg-color-selector").css("background-color");
        var outline_color=$("#shape-outline-color-selector").css("background-color");
        var opacity=$("#shape-opacity-slider").val();
        
        var rect = new fabric.Rect({
            left: 100,
            top: 50,
            fill: bg_color,
            opacity:opacity,
            width: 50,
            height: 50,
            rx:10,
            ry:10,
            selectable:true
        });
        
        var outline_width=$("#shape-thickness-slider").val();
        if(outline_width>0)        
        {
            rect.set("strokeWidth",parseInt(outline_width));
            rect.set("stroke",outline_color);
        }
        
        canvas.add(rect);
        canvas.centerObjectH(rect);
        canvas.centerObjectV(rect);
        canvas.renderAll();
        rect.setCoords();
        save_canvas();
    });
    
    $("#circle-btn").click(function()
    {
        var bg_color=$("#shape-bg-color-selector").css("background-color");
        var outline_color=$("#shape-outline-color-selector").css("background-color");
        var opacity=$("#shape-opacity-slider").val();        
        
        var circle = new fabric.Circle({
            left: 100,
            top: 50,
            fill: bg_color,
            opacity:opacity,
            radius:25,
            selectable:true
        });
        
        var outline_width=$("#shape-thickness-slider").val();
        if(outline_width>0)        
        {
            circle.set("strokeWidth",parseInt(outline_width));
            circle.set("stroke",outline_color);
        }
        
        canvas.add(circle);
        canvas.centerObjectH(circle);
        canvas.centerObjectV(circle);
        canvas.renderAll();
        circle.setCoords();
        save_canvas();
    });
    
    $("#triangle-btn").click(function()
    {
        var bg_color=$("#shape-bg-color-selector").css("background-color");
        var outline_color=$("#shape-outline-color-selector").css("background-color");
        var opacity=$("#shape-opacity-slider").val();
        
        var triangle = new fabric.Triangle({
            left: 100,
            top: 50,
            fill: bg_color,
            opacity:opacity,
            width: 50,
            height: 50,
            selectable:true
        });
        
        var outline_width=$("#shape-thickness-slider").val();
        if(outline_width>0)        
        {
            triangle.set("strokeWidth",parseInt(outline_width));
            triangle.set("stroke",outline_color);
        }
        
        canvas.add(triangle);
        canvas.centerObjectH(triangle);
        canvas.centerObjectV(triangle);
        canvas.renderAll();
        triangle.setCoords();
        save_canvas();
    });
    
    $("#polygon-btn").click(function()
    {
        var bg_color=$("#shape-bg-color-selector").css("background-color");
        var outline_color=$("#shape-outline-color-selector").css("background-color");
        var opacity=$("#shape-opacity-slider").val();
        var nb_points=$("#polygon-nb-points").val();
        var startPoints = [];
        if(nb_points==5)
        {
            startPoints = [
            {x: 0, y: 50},
            {x: 45, y: 80},
            {x: 85, y: 50},
            {x: 70, y: 0},
            {x: 17, y: 0}
          ];


        }
        else if(nb_points==6)
        {
            startPoints = [
                {x: 45, y: 90},
                {x: 90, y: 70},
                {x: 90, y: 20},
                {x: 45, y: 0},
                {x: 0, y: 20},
                {x: 0, y: 70}
            ];


        }
        else if(nb_points==7)
        {
            startPoints = [
                {x: 26, y: 90},
                {x: 65, y: 90},
                {x: 88, y: 57},
                {x: 81, y: 18},
                {x: 45, y: 0},
                {x: 12, y: 18},
                {x: 0, y: 58}
            ];
        }
        else if(nb_points==8)
        {
            startPoints = [
                {x: 28, y: 90},
                {x: 63, y: 90},
                {x: 90, y: 63},
                {x: 90, y: 27},
                {x: 63, y: 0},
                {x: 28, y: 0},
                {x: 0, y: 27},
                {x: 0, y: 63}
            ];


        }
        else if(nb_points==9)
        {
            startPoints = [
                {x: 45, y: 90},
                {x: 75, y: 80},
                {x: 90, y: 52},
                {x: 85, y: 20},
                {x: 60, y: 0},
                {x: 30, y: 0},
                {x: 8, y: 20},
                {x: 0, y: 53},
                {x: 17, y: 78}
            ];

        }
        else if(nb_points==10)
        {
            startPoints = [
                {x: 35, y: 90},
                {x: 63, y: 90},
                {x: 86, y: 74},
                {x: 95, y: 47},
                {x: 86, y: 19},
                {x: 63, y: 0},
                {x: 35, y: 0},
                {x: 11, y: 19},
                {x: 0, y: 45},
                {x: 11, y: 72}
            ];
        }


          var clonedStartPoints = startPoints.map(function(o){
            return fabric.util.object.clone(o);
          });

          var polygon = new fabric.Polygon(clonedStartPoints, {
            left: 100,
            top: 50,
            fill: bg_color,
            opacity:opacity,
            selectable:true
          });
          
        var outline_width=$("#shape-thickness-slider").val();
        if(outline_width>0)        
        {
            polygon.set("strokeWidth",parseInt(outline_width));
            polygon.set("stroke",outline_color);
        }
        
        canvas.add(polygon);
        canvas.centerObjectH(polygon);
        canvas.centerObjectV(polygon);
        canvas.renderAll();
        polygon.setCoords();
        save_canvas();
    });
    
    $("#star-btn").click(function()
    {
        var bg_color=$("#shape-bg-color-selector").css("background-color");
        var outline_color=$("#shape-outline-color-selector").css("background-color");
        var opacity=$("#shape-opacity-slider").val();
        var nb_points=$("#star-nb-points").val();
        var startPoints = [];
        if(nb_points==5)
        {
            startPoints = [
            {x: 46, y: 90},
            {x: 58, y: 56},
            {x: 93, y: 55},
            {x: 65, y: 35},
            {x: 77, y: 0},
            {x: 48, y: 22},
            {x: 19, y: 0},
            {x: 30, y: 35},
            {x: 0, y: 56},
            {x: 37, y: 56}
          ];


        }
        else if(nb_points==6)
        {
            startPoints = [
                {x: 40, y: 90},
                {x: 54, y: 68},
                {x: 79, y: 68},
                {x: 66, y: 45},
                {x: 79, y: 23},
                {x: 53, y: 23},
                {x: 40, y: 0},
                {x: 26, y: 23},
                {x: 0, y: 23},
                {x: 14, y: 45},
                {x: 0, y: 68},
                {x: 26, y: 68}
            ];


        }
        else if(nb_points==7)
        {
            startPoints = [
                {x: 49, y: 90},
                {x: 57, y: 60},
                {x: 87, y: 74},
                {x: 64, y: 47},
                {x: 91, y: 34},
                {x: 64, y: 34},
                {x: 71, y: 0},
                {x: 47, y: 26},
                {x: 25, y: 0},
                {x: 31, y: 32},
                {x: 0, y: 32},
                {x: 31, y: 47},
                {x: 7, y: 74},
                {x: 39, y: 60}
            ];
        }
        else if(nb_points==8)
        {
            startPoints = [
                {x: 46, y: 90},
                {x: 52, y: 63},
                {x: 77, y: 78},
                {x: 61, y: 53},
                {x: 89, y: 46},
                {x: 61, y: 40},
                {x: 77, y: 14},
                {x: 52, y: 30},
                {x: 46, y: 0},
                {x: 37, y: 30},
                {x: 14, y: 14},
                {x: 27, y: 39},
                {x: 0, y: 46},
                {x: 27, y: 53},
                {x: 13, y: 77},
                {x: 37, y: 62}
            ];


        }
        else if(nb_points==9)
        {
            startPoints = [
                {x: 45, y: 90},
                {x: 56, y: 73},
                {x: 74, y: 69},
                {x: 71, y: 59},
                {x: 88, y: 52},
                {x: 74, y: 39},
                {x: 84, y: 21},
                {x: 65, y: 21},
                {x: 61, y: 0},
                {x: 45, y: 14},
                {x: 30, y: 0},
                {x: 26, y: 21},
                {x: 21, y: 6},
                {x: 16, y: 39},
                {x: 0, y: 51},
                {x: 18, y: 59},
                {x: 16, y: 79},
                {x: 34, y: 73}
            ];


        }
        else if(nb_points==10)
        {
            startPoints = [
                {x: 35, y: 90},
                {x: 50, y: 81},
                {x: 63, y: 90},
                {x: 69, y: 73},
                {x: 88, y: 73},
                {x: 82, y: 56},
                {x: 96, y: 46},
                {x: 82, y: 36},
                {x: 87, y: 18},
                {x: 70, y: 18},
                {x: 63, y: 0},
                {x: 49, y: 12},
                {x: 35, y: 0},
                {x: 28, y: 18},
                {x: 11, y: 18},
                {x: 17, y: 35},
                {x: 0, y: 46},
                {x: 17, y: 56},
                {x: 11, y: 73},
                {x: 28, y: 73}
            ];
        }


          var clonedStartPoints = startPoints.map(function(o){
            return fabric.util.object.clone(o);
          });

          var star = new fabric.Polygon(clonedStartPoints, {
            left: 100,
            top: 50,
            fill: bg_color,
            opacity:opacity,
            selectable:true
          });
          
        var outline_width=$("#shape-thickness-slider").val();
        if(outline_width>0)        
        {
            star.set("strokeWidth",parseInt(outline_width));
            star.set("stroke",outline_color);
        }
        
        canvas.add(star);
        canvas.centerObjectH(star);
        canvas.centerObjectV(star);
        canvas.renderAll();
        star.setCoords();
        save_canvas();
    });
    
    $("#left_arrow").click(function()
    {
        canvas.deactivateAll();
        var canvas_width=canvas.getWidth();
        var items = canvas.getObjects();
        var pas = 20;
        $.each( items, function( key, current_item ) 
        { 
            var x_coordinate=parseInt(current_item.get("left"));
            var longueur=0-parseInt(canvas_width)+(current_item.get("width")/2);
            var new_x_coordinate;
           
            if(x_coordinate>=longueur)
                new_x_coordinate=(x_coordinate-pas);
            else
            {
                new_x_coordinate= canvas_width-(longueur-x_coordinate-current_item.get("width")/2);
            }
            current_item.set("left", parseInt(new_x_coordinate));
            current_item.setCoords();
        });
        canvas.calcOffset();
        
        
        canvas.renderAll();
    });
    
    $("#right_arrow").click(function()
    {
        var canvas_width=canvas.getWidth();
        var items = canvas.getObjects();
        var pas = 40;
        $.each( items, function( key, current_item ) 
        { 
            var longueur=parseInt(canvas_width)+(current_item.get("width")/2);
            var x_coordinate=parseInt(current_item.get("left"));
            if(x_coordinate<longueur)
            {
                current_item.set("left", x_coordinate+pas);
            }
            else
            {
                var diff=x_coordinate-longueur;
                current_item.set("left", (0-longueur)+diff+current_item.get("width"));
            }       
        });
        
        canvas.renderAll();
    });
    
    $("#underline-cb").change(function()
    {
        var selected_object=canvas.getActiveObject();        
        
        if((selected_object!=null)&&(selected_object.type=="text"))
        {
            var is_underlined=$("#underline-cb").is(":checked");
            if(is_underlined)
                selected_object.set("textDecoration","underline");
            else
                selected_object.set("textDecoration","none");            
            canvas.renderAll();
        }        
    });
    
    $("#bold-cb").change(function()
    {
        var selected_object=canvas.getActiveObject();
        var is_bold=$("#bold-cb").is(":checked");
        if((selected_object!=null)&&(selected_object.type=="text"))
        {
            if(is_bold)
                selected_object.set("fontWeight","bold");
            else
                selected_object.set("fontWeight","normal");
            canvas.renderAll();
        }
        else if((selected_object!=null)&&(selected_object.type=="group"))
        {
            selected_object.forEachObject(function(a) {
                if(is_bold)
                    a.set("fontWeight","bold");
                else
                    a.set("fontWeight","normal");
                canvas.renderAll();
            });
        }
    });
    
    $("#italic-cb").change(function()
    {
        var selected_object=canvas.getActiveObject();        
        var is_italic=$("#italic-cb").is(":checked");
        if((selected_object!=null)&&(selected_object.type=="text"))
        {
            if(is_italic)
                selected_object.set("fontStyle","italic");
            else
                selected_object.set("fontStyle","normal");
            canvas.renderAll();
        }
        else if((selected_object!=null)&&(selected_object.type=="group"))
        {
            selected_object.forEachObject(function(a) {
                if(is_italic)
                    a.set("fontStyle","italic");
                else
                    a.set("fontStyle","normal");
                canvas.renderAll();
            });
        }
    });
    
    $("#font-family-selector").change(function()
    {
        var selected_object=canvas.getActiveObject();        
        var font_size=parseInt($("#font-size-slider").val());
        var font_family=$(this).val();
        if((selected_object!=null)&&(selected_object.type=="text"))
        {
            selected_object.set('fontFamily',font_family );
            selected_object.setFontSize(parseInt(font_size));
            canvas.renderAll();
        }
        else if((selected_object!=null)&&(selected_object.type=="group"))
        {
            selected_object.forEachObject(function(a) {
                a.set('fontFamily', font_family);
                a.setFontSize(parseInt(font_size));
                canvas.renderAll();
            });
        }
    });
    
    $("#font-size-slider").change(function()
    {
        var selected_object=canvas.getActiveObject();        
        var font_size=parseInt($("#font-size-slider").val());
        if((selected_object!=null)&&(selected_object.type=="text"))
        {
            selected_object.setFontSize(parseInt(font_size));
            canvas.renderAll();
        }
    });
    
    $("#o-thickness-slider").change(function()
    {
        var selected_object=canvas.getActiveObject();        
        if((selected_object!=null)&&(selected_object.type=="text"))
        {
            if($(this).val()>0)
            {
                var stroke=$("#txt-outline-color-selector").css('background-color');
                selected_object.set("strokeWidth",parseInt($(this).val()));
                selected_object.set("stroke",stroke);
        }
            else
                selected_object.set("stroke",false);
            canvas.renderAll();
        }
    });
    
    $("[id$='opacity-slider']").change(function()
    {
        var selected_object=canvas.getActiveObject();        
        if(selected_object!=null)
        {
            selected_object.set("opacity",$(this).val());
            canvas.renderAll();
            save_canvas();
        }
    });
    
    function optimize_img_width(obj)
    {
        var displayable_area_width=canvas.getWidth();
        var displayable_area_height=canvas.getHeight();
        if(clip_w && clip_h && clip_w>0 && clip_h>0&&clip_type=="rect")
        {
            displayable_area_width=clip_w;
            displayable_area_height=clip_h;
        }
        else if(clip_r && clip_r>0&&clip_type=="arc")
        {
            displayable_area_width=clip_r;
            displayable_area_height=clip_r;
        }
        var dimensions=get_img_best_fit_dimensions(obj, displayable_area_width,displayable_area_height );
        var scaleW=displayable_area_width/dimensions[0];
        var scaleH=displayable_area_height/dimensions[1];
        if(scaleW>scaleH)
            obj.scaleToWidth(dimensions[0]);
        else
            obj.scaleToHeight(dimensions[1]);
//        console.log(displayable_area_width/dimensions[0]);
//        console.log(displayable_area_height/dimensions[1]);
//        obj.set("width", dimensions[0]);
//        obj.set("height", dimensions[1]);
//        obj.set("width", dimensions[0]);
    }
    
    function add_img_on_editor(url)
    {
        var ext = url.split('.').pop();
        if(ext=="svg")
        {
            fabric.loadSVGFromURL(url, function(objects, options) {
             var obj = fabric.util.groupSVGElements(objects, options);
             optimize_img_width(obj);
             canvas.add(obj).centerObject(obj).calcOffset().renderAll();
             obj.setCoords();             
             save_canvas();
          }); 
        }
        else
        {
            fabric.Image.fromURL(url, function(img) 
                {
                    optimize_img_width(img);
                    canvas.add(img.set(
                                            { 
                                                    left: 100, 
                                                    top: 100, 
                                                    angle: 0 
                                            })
                                ).centerObject(img).renderAll();
                        img.setCoords();
                        save_canvas();
                });
        }
    }
    
   $("#img-cliparts-container").on('click', 'img', function()
     {
            var medium_url=$(this).attr("src");	
            add_img_on_editor(medium_url);
    });
    
    $(document).on("click","#wpc-add-img",function(e){
        e.preventDefault();
       var selector=$(this).attr('data-selector');
       var trigger=$(this);
       var uploader=wp.media({
           title:'Add image on the design area',
           button:{
                    text:"Add image"
                    },
            multiple:false
       })
       .on('select',function(){
            var selection=uploader.state().get('selection');
            selection.map(
                    function(attachment){
                            attachment=attachment.toJSON();
                            add_img_on_editor(attachment.url);
                    }
            )
        })
        .open();
   });
    

    $('#grayscale').change(function() 
    {
        apply_filter(new fabric.Image.filters.Grayscale(), $(this).is(':checked'));

    });

    $('#invert').change(function() {
        apply_filter(new fabric.Image.filters.Invert(), $(this).is(':checked'));  
    });

    $('#sepia').change(function() {
        apply_filter(new fabric.Image.filters.Sepia(), $(this).is(':checked'));  
    });

    $('#sepia2').change(function() {
        apply_filter(new fabric.Image.filters.Sepia2(), $(this).is(':checked'));  
    });

    $('#blur').change(function() {
        apply_filter(new fabric.Image.filters.Convolute({
                    matrix: [ 1/9, 1/9, 1/9,
                              1/9, 1/9, 1/9,
                              1/9, 1/9, 1/9 ]
                  }),
                  $(this).is(':checked'));  
    });

    $('#sharpen').change(function() {
        apply_filter(new fabric.Image.filters.Convolute({
                matrix: [  0, -1,  0,
                          -1,  5, -1,
                           0, -1,  0 ]
              }),
              $(this).is(':checked'));  
    });

    $('#emboss').change(function() {
        apply_filter(new fabric.Image.filters.Convolute({
            matrix: [ 1,   1,  1,
                      1, 0.7, -1,
                     -1,  -1, -1 ]
          }), $(this).is(':checked'));  
    });
    
    $(document).keydown(function(e) {
        var selected_object=canvas.getActiveObject();
        var selected_group=canvas.getActiveGroup();
        
        if(e.which==46) //Delete button
            $("#delete_btn").click();
        else if(e.which==37) //Left button
        {
            if(selected_group!=null)
            {
                selected_group.set("left", selected_group.left-1);
                canvas.renderAll();
                save_canvas();
            }            
            else if((selected_object!=null))
            {
                selected_object.set("left", selected_object.left-1);
                canvas.renderAll();
                save_canvas();
            }
            
        }
        else if(e.which==39) //Right button
        {    
            if(selected_group!=null)
            {
                selected_group.set("left", selected_group.left+1);
                canvas.renderAll();
                save_canvas();
            }
            else if((selected_object!=null))
            {
                selected_object.set("left", selected_object.left+1);
                canvas.renderAll();
                save_canvas();
            }
        }
        else if(e.which==38) //Top button
        {            
            
            if(selected_group!=null)
            {
                e.preventDefault();
                selected_group.set("top", selected_group.top-1);
                canvas.renderAll();
                save_canvas();
            }
            else if((selected_object!=null))
            {
                e.preventDefault();
                selected_object.set("top", selected_object.top-1);
                canvas.renderAll();
                save_canvas();
            }
        }
        else if(e.which==40) //Bottom button
        {            
            if(selected_group!=null)
            {
                e.preventDefault();
                selected_group.set("top", selected_group.top+1);
                canvas.renderAll();
                save_canvas();
            }
            else if((selected_object!=null))
            {
                e.preventDefault();
                selected_object.set("top", selected_object.top+1);
                canvas.renderAll();
                save_canvas();
            }
            
        }
        else if(e.keyCode == 67 && e.ctrlKey)//ctrl+c
        {
            $("#copy_paste_btn").click();
        }
//        else if(e.keyCode == 86 && e.ctrlKey)//ctrl+v
//        {
//            $("#copy_paste_btn").click();
//        }
        else if(e.keyCode == 90 && e.ctrlKey)//ctrl+z
        {
            $("#undo-btn").click();
        }
        else if(e.keyCode == 89 && e.ctrlKey)//ctrl+y
        {
            $("#redo-btn").click();
        }
    });
    
    load_fonts();

    setTimeout(function() {
        canvas.calcOffset();
    }, 100);
    
    //Woo
    var serialized_parts=[];
    var final_canvas_parts=new Object();
    var selected_part=-1;
    var canvasManipulationsPosition=[];
    var arr_filters = ['grayscale', 'invert', 'remove-white', 'sepia', 'sepia2',
                    'brightness', 'noise', 'gradient-transparency', 'pixelate',
                    'blur', 'convolute'];
                
    $(document).on("touchstart click",".wpc-customize-product",function() 
    {
        var variation_id=0;
        var type=$(this).data("type");
        if(type=="simple")
            variation_id=$(this).data("id");
        else if(type=="variable")
            variation_id=$("input[name='variation_id']").val();
        
        if(!variation_id)
        {
            alert("Select the product options first");
            return;
        }
        else
        {
            $.get( 
                    ajax_object.ajax_url,
                    {
                        action: "get_customizer_url", 
                        variation_id:variation_id

                    },
                    function(data) {
                        if(data.url)
                            $(location).attr('href',data.url);
                    },
                    "json"
                );
        }
    });
    
    $(document).on("touchstart click",".wpc-upload-product-design",function(e) 
    {
        e.preventDefault();
        var variation_id=0;
        var type=$(this).data("type");
        if(type=="simple")
            variation_id=$(this).data("id");
        else if(type=="variable")
            variation_id=$("input[name='variation_id']").val();
        
        if(!variation_id)
        {
            alert("Select the product options first");
            return;
        }
        else
        {
            $(".wpc-uploaded-design-container").show();
            $("#wpc-product-id-upl").val(variation_id);
            $(this).hide();
        }
    });
    
    function GetURLParameter(sParam)
    {
        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++) 
        {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == sParam) 
            {
                return sParameterName[1];
            }
        }
    }
    
    function generate_final_canvas_part(part_index)
    {
        generate_canvas_part(part_index, false);
    }
    
    function generate_canvas_part(part_index, preview)
    {
        selected_part=part_index;
        preview = typeof preview !== 'undefined' ? preview : true;
        var data_id=$("#wpc-parts-bar > span:eq("+part_index+")").attr("data-id");
        var data_part_img=$("#wpc-parts-bar > span:eq("+part_index+")").attr("data-url");
        canvas.clear();
        canvas.loadFromJSON(serialized_parts[data_id][canvasManipulationsPosition[data_id]],
        function(){
                    load_bg_ov_if_needed(selected_part, function(){
                        var image=canvas.toDataURL({format:'png'});
//                        var svg=canvas.toSVG();
                        if(preview)
                        {
                            var modal_content="";
                            if(data_part_img)
                                modal_content="<div style='background-image:url("+data_part_img+");'><img src='"+image+"'></div>";
                            else
                                modal_content="<div><img src='"+image+"'></div>";
                            $("#wpc-modal .modal-body").append(modal_content);
                        }
                        else
                        {
                            var canvas_obj=$.parseJSON(serialized_parts[data_id][canvasManipulationsPosition[data_id]]);
                            final_canvas_parts[data_id]={ json:serialized_parts[data_id][canvasManipulationsPosition[data_id]], image:image, original_part_img:data_part_img};
                        }
                    });
            });   
    }
    
    function preload_canvas(data)
    {
        if(typeof data=="object")
        {
            var is_first=true;
                            $.each( data, function( index, value ) {
                                $.each( value, function( index1, value1 ) {
                                    if(index1=="json")
                                    {
                                        serialized_parts[index]=[];
                                        canvasManipulationsPosition[index]=0;
                                        var json_value=value1;//.replace(/\\/g, '');
                                        serialized_parts[index].push(json_value);
                                        if(is_first)
                                        {
                                            selected_part=0;
                                            canvas.loadFromJSON(json_value,function(){
                                            canvas.renderAll.bind(canvas);
                                            });
                                            canvas.calcOffset();
                                            load_first_part_img();
                                        }
    //                                        console.log(serialized_parts);
                                    }

                                });
                                is_first=false;
                            });
        }
    }
    
    $(document).on("touchstart click","#preview-order", function() 
    {
        $("#wpc-modal .modal-body").html("");
        //Make sure the last modification is handled
        save_canvas();
        loop_through_parts(output_loop_delay,
                    generate_canvas_part,
                    function(){
                        $("#wpc-parts-bar > span").first().click();
                        $('#wpc-modal').modal("show");
                        $.unblockUI();
                    }
                );
    });
    
    $(document).on("touchstart click", "#add-to-cart", function() 
    {
        $("#debug").html("");
        //Make sure the last modification is handled
        save_canvas();
        loop_through_parts(output_loop_delay,
                    generate_final_canvas_part,
                    function(){
                            if(jQuery.isEmptyObject( final_canvas_parts ))
                            {
                                $("#debug").html("<div class='wpc-failure'>"+empty_object_msg+"</div>");
                                $.unblockUI();
                            }
                            else
                            {
                                var quantity=$("#wpc-qty").val();
                                var variation_id=global_variation_id;
                                var cart_item_key=GetURLParameter("edit");
                                if(typeof cart_item_key == 'undefined')
                                    cart_item_key="";
                                $.post( 
                                ajax_object.ajax_url,
                                {
                                    action: "add_custom_design_to_cart", 
                                    variation_id:variation_id,
                                    cart_item_key:cart_item_key,
                                    final_canvas_parts:final_canvas_parts,
                                    quantity:quantity

                                },
                                function(data) {
                                    if($("#wpc-parts-bar > span").length>1)
                                        $("#wpc-parts-bar > span").first().click();
                                    else
                                        reload_first_part_data();
                                    $("#debug").html(data.message);
                                    $.unblockUI();
                                }
                        ,"json"
                            );                        
                        }
                    }
                );
    });
    
    function reload_first_part_data()
    {
        var data_id=$("#wpc-parts-bar > span:eq(0)").attr("data-id");
        canvas.clear();
        canvas.loadFromJSON(serialized_parts[data_id][canvasManipulationsPosition[data_id]],function(){
            canvas.renderAll.bind(canvas); 
        });
    }
    
    $(document).on("touchstart click", "#download-design", function() 
    {
        $("#debug").html("");
        loop_through_parts(output_loop_delay,
                    generate_final_canvas_part,
                    function(){
                            if(jQuery.isEmptyObject( final_canvas_parts ))
                            {
                                $("#debug").html("<div class='wpc-failure'>"+empty_object_msg+"</div>");
                                $.unblockUI();
                            }
                            else
                            {
                                    var variation_id=global_variation_id;

                                    $.post( 
                                    ajax_object.ajax_url,
                                    {
                                        action: "generate_downloadable_file",
                                        final_canvas_parts:final_canvas_parts, 
                                        variation_id:variation_id

                                    },
                                    function(data) {
                                        if($("#wpc-parts-bar > span").length>1)
                                            $("#wpc-parts-bar > span").first().click();
                                        else
                                            reload_first_part_data();

                                        $("#debug").html(data.message);
                                        $.unblockUI();
                                    }
                                    ,"json"
                                );
                            }
                    }
                );
    });
    
    $(document).on("touchstart click","#td-tools-container ul>li",function(){
            $("#td-tools-container ul>li").removeClass("selected");
            $(this).addClass("selected");
        });

    $(document).on("touchstart click","#add-text",function(){
        $("#tools > div").hide();
        $("#text-tools").show();
    });

    $(document).on("touchstart click","#saved-designs",function(){
        $("#tools > div").hide();
        $("#saved-designs-tools").show();
    });
        
        
    function refresh_undo_redo_status()
    {
        var data_id=$("#wpc-parts-bar > span:eq("+selected_part+")").attr("data-id");
        if((serialized_parts[data_id].length==1)||(canvasManipulationsPosition[data_id]==0))
            $("#undo-btn").addClass("disabled");
        else
            $("#undo-btn").removeClass("disabled");

        if((serialized_parts[data_id].length>0)&&(canvasManipulationsPosition[data_id]<serialized_parts[data_id].length-1))
            $("#redo-btn").removeClass("disabled");
        else
            $("#redo-btn").addClass("disabled");
    }

    function save_canvas()
    {   
        var data_id=$("#wpc-parts-bar > span:eq("+selected_part+")").attr("data-id");
        for (i=canvasManipulationsPosition[data_id]; i<=serialized_parts[data_id].length-2;i++)
        {
            serialized_parts[data_id].pop();
        }

        canvasManipulationsPosition[data_id]++;
        var json=JSON.stringify(canvas.toJSON());
        serialized_parts[data_id].push( json);
        refresh_undo_redo_status();
    }

    $(document).on("touchstart click","#undo-btn",function()
    {
        var current_data_id=$("#wpc-parts-bar > span:eq("+selected_part+")").attr("data-id");
        if(!$(this).hasClass("disabled")&&canvasManipulationsPosition[current_data_id]>0)
        {
            canvas.clear();
            canvasManipulationsPosition[current_data_id]--;
            canvas.loadFromJSON(serialized_parts[current_data_id][canvasManipulationsPosition[current_data_id]]);        
            refresh_undo_redo_status();
        }
    });
    
    $(document).on("touchstart click","#redo-btn",function()
    {
        var current_data_id=$("#wpc-parts-bar > span:eq("+selected_part+")").attr("data-id");
        if(!$(this).hasClass("disabled"))
        {
            canvas.clear();
            canvasManipulationsPosition[current_data_id]++;
            canvas.loadFromJSON(serialized_parts[current_data_id][canvasManipulationsPosition[current_data_id]]);        
            refresh_undo_redo_status();
        }
    });
    
    $("[data-original-title]").tooltip();
    
    

    $(document).on("change",'#wpc-qty',function() 
    {
        var qty=$(this).val();
        var unit_price=$(this).attr("uprice");
        if(!$.isNumeric(qty))
        {
            $(this).val(1);
            $("#total_order").html(unit_price);
            return;
        }

        var total=unit_price*qty;
        $("#total_order").html(total);

    });


    function upload_image_callback(responseText, statusText, xhr, form)
    {
        var response=$.parseJSON(responseText);
        
        if(response.success)
        {
            if($("#uploads-accordion .AccordionPanelContent").text()=="Empty")
                $("#uploads-accordion .AccordionPanelContent").text("");
            $("#uploads-accordion .AccordionPanelContent").append(response.message);
            var nb_uploads=$("#uploads-accordion .AccordionPanelContent img").length;
            $("#uploads-accordion .AccordionPanelTab").text("Uploads ("+nb_uploads+")");
        }
        else
            alert(response.message);
        $("#userfile").val("");
    }
    
    $(".native-uploader #userfile").change(function(){
        var file=$(this).val().toLowerCase();
        if(file!="")
        {
                $("#userfile_upload_form").ajaxForm({
                    success:upload_image_callback
                }).submit();
        }
    });
    
    function upload_custom_design_callback(responseText, statusText, xhr, form)
    {
        var response=$.parseJSON(responseText);
        
        if(response.success)
        {
            $("#wpc-uploaded-file").html(response.message);
        }
        else
            alert(response.message);
        $("#user-custom-design").val("");
    }
    
    $(".native-uploader #user-custom-design").change(function(){
        var file=$(this).val().toLowerCase();
        if(file!="")
        {
                $("#custom-upload-form").ajaxForm({
                    success:upload_custom_design_callback
                }).submit();
        }
    });
    var ul = $('#userfile_upload_form.custom-uploader ul');
    
    if($('#custom-upload-form.custom-uploader').length)
    {
        // Initialize the jQuery File Upload plugin
        $('#custom-upload-form.custom-uploader').fileupload({
        url: ajax_object.ajax_url,
        // This element will accept file drag/drop uploading
        dropZone: $('#drop'),

        // This function is called when a file is added to the queue;
        // either via the browse button, or via drag/drop:
        add: function (e, data) {
            var tpl = $('<li class="working"><input type="text" value="0" data-width="48" data-height="48"'+
                ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /><p></p><span></span></li>');

            // Append the file name and file size
            tpl.find('p').text(data.files[0].name).append('<i>' + formatFileSize(data.files[0].size) + '</i>');

            // Add the HTML to the UL element
            data.context = tpl.appendTo(ul);

            // Initialize the knob plugin
            tpl.find('input').knob();

            // Listen for clicks on the cancel icon
            tpl.find('span').click(function(){

                if(tpl.hasClass('working')){
                    jqXHR.abort();
                }

                tpl.fadeOut(function(){
                    tpl.remove();
                });

            });
		
            // Automatically upload the file once it is added to the queue
            var jqXHR = data.submit();
        },

        progress: function(e, data){

            // Calculate the completion percentage of the upload
            var progress = parseInt(data.loaded / data.total * 100, 10);

            // Update the hidden input field and trigger a change
            // so that the jQuery knob plugin knows to update the dial
            data.context.find('input').val(progress).change();

            if(progress == 100){
                data.context.removeClass('working');
            }
        },

        fail:function(e, data){
            // Something has gone wrong!
            data.context.addClass('error');
        },
		 done: function (e, data) {
                        upload_custom_design_callback(data.result, false, false, false);		
		}
    });
    }
    

    $('#drop a').click(function(){
        // Simulate a click on the file input button
        // to show the file browser dialog
        $(this).parent().find('input').click();
    });

    if($('#userfile_upload_form.custom-uploader').length)
    {
        // Initialize the jQuery File Upload plugin
        $('#userfile_upload_form.custom-uploader').fileupload({
            url: ajax_object.ajax_url,
            // This element will accept file drag/drop uploading
            dropZone: $('#drop'),

            // This function is called when a file is added to the queue;
            // either via the browse button, or via drag/drop:
            add: function (e, data) {
                var tpl = $('<li class="working"><input type="text" value="0" data-width="48" data-height="48"'+
                    ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /><p></p><span></span></li>');

                // Append the file name and file size
                tpl.find('p').text(data.files[0].name).append('<i>' + formatFileSize(data.files[0].size) + '</i>');

                // Add the HTML to the UL element
                data.context = tpl.appendTo(ul);

                // Initialize the knob plugin
                tpl.find('input').knob();

                // Listen for clicks on the cancel icon
                tpl.find('span').click(function(){

                    if(tpl.hasClass('working')){
                        jqXHR.abort();
                    }

                    tpl.fadeOut(function(){
                        tpl.remove();
                    });

                });

                // Automatically upload the file once it is added to the queue
                var jqXHR = data.submit();



            },

            progress: function(e, data){

                // Calculate the completion percentage of the upload
                var progress = parseInt(data.loaded / data.total * 100, 10);

                // Update the hidden input field and trigger a change
                // so that the jQuery knob plugin knows to update the dial
                data.context.find('input').val(progress).change();

                if(progress == 100){
                    data.context.removeClass('working');
                }
            },

            fail:function(e, data){
                // Something has gone wrong!
                data.context.addClass('error');
            },
                     done: function (e, data) {
                            var name=data.files[0].name;
                            upload_image_callback(data.result, false, false, false);		
                    }
        });
    }


    // Prevent the default action when a file is dropped on the window
    $(document).on('drop dragover', function (e) {
        e.preventDefault();
    });
	
    function in_array(needle, haystack){
            var found = 0;
            for (var i=0, len=haystack.length;i<len;i++) {
                    if (haystack[i] == needle) return i;
                            found++;
            }
            return -1;
    }


    // Helper function that formats the file sizes
    function formatFileSize(bytes) {
        if (typeof bytes !== 'number') {
            return '';
        }

        if (bytes >= 1000000000) {
            return (bytes / 1000000000).toFixed(2) + ' GB';
        }

        if (bytes >= 1000000) {
            return (bytes / 1000000).toFixed(2) + ' MB';
        }

        return (bytes / 1000).toFixed(2) + ' KB';
    }
    
    function get_img_best_fit_dimensions(img, max_width, max_height)
    {
        var w=img.width;
        var h=img.height;
        var ratio = w / h;
        w = max_width;
        h = max_width / ratio;

        if (h > max_height)
        {
            h = max_height;
            w = max_height * ratio;
        }
        return [w,h];
    }
    
    function load_bg_ov_if_needed(index, callback)
    {
        var selector=$("#wpc-parts-bar > span:eq("+index+")");
        var canvas_bg=selector.data("bg");
        if(canvas_bg=="")
            canvas_bg=null;
        var canvas_ov=selector.data("ov");
        if(canvas_ov=="")
            canvas_ov=null;
        
//        canvas.setBackgroundImage(canvas_bg,canvas.renderAll.bind(canvas), {
//            left: canvas_width/2,
//            top: canvas_height/2,
//            originX: 'center',
//            originY: 'center',
//            scaleY:.35,
//            scaleX:.35
//          });
        
        var bg_img = new Image();
        bg_img.onload = function(){
            var dimensions=get_img_best_fit_dimensions(bg_img, canvas_width,canvas_height );
           canvas.setBackgroundImage(bg_img.src, canvas.renderAll.bind(canvas), {
                left: canvas_width/2,
                top: canvas_height/2,
                originX: 'center',
                originY: 'center',
                width: dimensions[0],
                height: dimensions[1]
            });
        };
        if(canvas_bg!=null)
            bg_img.src = canvas_bg;
        
        var ov_img = new Image();
        ov_img.onload = function(){
            var dimensions=get_img_best_fit_dimensions(ov_img, canvas_width,canvas_height );
           canvas.setOverlayImage(ov_img.src, canvas.renderAll.bind(canvas), {
                left: canvas_width/2,
                top: canvas_height/2,
                originX: 'center',
                originY: 'center',
                width: dimensions[0],
                height: dimensions[1]
            });
        };
        if(canvas_ov!=null)
            ov_img.src = canvas_ov;
        
        
//        canvas.setOverlayImage(canvas_ov,canvas.renderAll.bind(canvas), {
//            left: canvas_width/2,
//            top: canvas_height/2,
//            originX: 'center',
//            originY: 'center'
//          });
          
          if($.isFunction(callback))
                setTimeout(function(){
                        callback(index);
                    },200);
    }
    
    $(document).on("click","#wpc-parts-bar > span",function(e){
        var img_src=$(this).attr("data-url");
        if(selected_part==$(this).index())
        {
            return;
        }
        else
        {
            load_bg_ov_if_needed($(this).index());
            $("#wpc-parts-bar > span").removeClass("active");
            $(this).addClass("active");
            if(selected_part>=0)
            {
                save_canvas();
                canvas.clear();
            }
            selected_part=$(this).index();
            if(img_src)
            {
                var bg_code="url('"+img_src+"') no-repeat center center";
                $("#wpc-editor-container").css("background",bg_code);
            }
        }

        var data_id=$(this).attr("data-id");
        if(typeof serialized_parts[data_id]=="undefined")//Fixe les parts non chargés lorsque le to_load est défini
        {
            serialized_parts[data_id]=[];
            canvasManipulationsPosition[data_id]=-1;
        }
        if(serialized_parts[data_id][canvasManipulationsPosition[data_id]])
        {
            canvas.loadFromJSON(serialized_parts[data_id][canvasManipulationsPosition[data_id]]);
        }
        refresh_undo_redo_status();
    });

    function loop_through_parts(delay, loop_callback, end_callback)
    {
        $.blockUI({ message: loading_msg });
        var nb_parts=$("#wpc-parts-bar > span").length;
        var current_part=0;
        var loopKey=setInterval(function(){
            if($.isFunction(loop_callback))
                loop_callback(current_part);
            if(current_part==nb_parts-1)
            {
                window.clearInterval(loopKey);
                if($.isFunction(end_callback))
                {
                    setTimeout(function(){
                        end_callback();
//                        $.unblockUI();
                    },delay);
                }
                else
                    $.unblockUI();


            }
            else
                current_part++;
        }, delay);
    }

    function click_on_part(part_index)
    {
        $("#wpc-parts-bar > span:eq("+part_index+")").click();
    }
    
    function load_first_part_img()
    {
        var img_src=$("#wpc-parts-bar > span").first().attr("data-url");
        var bg_code="url('"+img_src+"') no-repeat center center";
        $("#wpc-editor-container").css("background",bg_code);
    }

    var order_item_id=GetURLParameter("oid");
    if(typeof to_load == 'undefined')
    {
        $("#wpc-parts-bar > span").each(function(key){
        var data_id=$(this).attr("data-id");
        serialized_parts[data_id]=[];
        canvasManipulationsPosition[data_id]=-1;
        var nb_parts=$("#wpc-parts-bar > span").length;
        if(key==nb_parts-1)
        {
            loop_through_parts(output_loop_delay, click_on_part,
                function(){
                    $("#wpc-parts-bar > span").first().click();
                    canvas.renderAll();
                    $.unblockUI();
                });
        }
    });
    }
    
    function update_image_tools_scroller()
    {
        setTimeout(function() {
            $("#img-cliparts-container").perfectScrollbar("update");
        }, 100);
    }
    
    $('#image-tools-container').on('show', function() {
        update_image_tools_scroller();
    });
    
    $("#image-tools-container .AccordionPanelTab").click(function(){
        update_image_tools_scroller();
    });
    
    $(".scrollable").each(function() {
            $(this).perfectScrollbar({
                wheelSpeed:100
            });        
    });
    
    if(typeof to_load!== 'undefined')
    setTimeout(function() {
        preload_canvas(to_load);
    }, 500);
    
    $("#lock-mvt-x, #lock-mvt-y, #lock-scl-x, #lock-scl-y, #lock-Deletion").change(function(e)
    {
        var property=$(this).data("property");
        var selected_object=canvas.getActiveObject();
        var selected_group=canvas.getActiveGroup();
        if(selected_object!=null)
        {
            if($(this).is(':checked'))
                selected_object[property] = true;
            else
                selected_object[property] = false;
            save_canvas();
        }
        else if(selected_group!=null)
        {
            if($(this).is(':checked'))
                selected_group[property] = true;
            else
                selected_group[property] = false;
            save_canvas();
        }
    });
    
    $( document ).on( 'click', '#wpc-qty-container .plus, #wpc-qty-container .minus', function() {

		// Get values
		var $qty		= $( "#wpc-qty" ),
			currentVal	= parseFloat( $qty.val() ),
			max			= parseFloat( $qty.attr( 'max' ) ),
			min			= parseFloat( $qty.attr( 'min' ) ),
			step		= $qty.attr( 'step' );

		// Format values
		if ( ! currentVal || currentVal === '' || currentVal === 'NaN' ) currentVal = 0;
		if ( max === '' || max === 'NaN' ) max = '';
		if ( min === '' || min === 'NaN' ) min = 0;
		if ( step === 'any' || step === '' || step === undefined || parseFloat( step ) === 'NaN' ) step = 1;

		// Change the value
		if ( $( this ).is( '.plus' ) ) {

			if ( max && ( max == currentVal || currentVal > max ) ) {
				$qty.val( max );
			} else {
				$qty.val( currentVal + parseFloat( step ) );
			}

		} else {

			if ( min && ( min == currentVal || currentVal < min ) ) {
				$qty.val( min );
			} else if ( currentVal > 0 ) {
				$qty.val( currentVal - parseFloat( step ) );
			}

		}

		// Trigger change event
		$qty.trigger( 'change' );
    });
        
    $(document).on("click",".print_pdf",function(e){
        e.preventDefault();
        var href=$(this).attr("href");
        var w = window.open(href);
//        w.addEventListener('load', w.print, true);
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