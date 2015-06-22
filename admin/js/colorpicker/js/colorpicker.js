/**
 *
 * Color picker
 * Author: Stefan Petre www.eyecon.ro
 *
 * Dual licensed under the MIT and GPL licenses
 *
 */
 (function($) {
    var ColorPicker = function() {
        var ids = {},
        inAction,
        charMin = 65,
        visible,
        tpl = '<div class="wpc-colorpicker"><div class="wpc-colorpicker_color"><div><div></div></div></div><div class="wpc-colorpicker_hue"><div></div></div><div class="wpc-colorpicker_new_color"></div><div class="wpc-colorpicker_current_color"></div><div class="wpc-colorpicker_hex"><input type="text" maxlength="6" size="6" /></div><div class="wpc-colorpicker_rgb_r wpc-colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="wpc-colorpicker_rgb_g wpc-colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="wpc-colorpicker_rgb_b wpc-colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="wpc-colorpicker_hsb_h wpc-colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="wpc-colorpicker_hsb_s wpc-colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="wpc-colorpicker_hsb_b wpc-colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="wpc-colorpicker_submit"></div></div>',
        defaults = {
            eventName: 'click',
            onShow: function() {},
            onBeforeShow: function() {},
            onHide: function() {},
            onChange: function() {},
            onSubmit: function() {},
            color: 'ff0000',
            livePreview: true,
            flat: false
        },
        fillRGBFields = function(hsb, cal) {
            var rgb = HSBToRGB(hsb);
            $(cal).data('wpc-colorpicker').fields.eq(1).val(rgb.r).end().eq(2).val(rgb.g).end().eq(3).val(rgb.b).end();
        },
        fillHSBFields = function(hsb, cal) {
            $(cal).data('wpc-colorpicker').fields.eq(4).val(hsb.h).end().eq(5).val(hsb.s).end().eq(6).val(hsb.b).end();
        },
        fillHexFields = function(hsb, cal) {
            $(cal).data('wpc-colorpicker').fields.eq(0).val(HSBToHex(hsb)).end();
        },
        setSelector = function(hsb, cal) {
            $(cal).data('wpc-colorpicker').selector.css('backgroundColor', '#' + HSBToHex({
                h: hsb.h,
                s: 100,
                b: 100
            }));
            $(cal).data('wpc-colorpicker').selectorIndic.css({
                left: parseInt(150 * hsb.s / 100, 10),
                top: parseInt(150 * (100 - hsb.b) / 100, 10)
                });
        },
        setHue = function(hsb, cal) {
            $(cal).data('wpc-colorpicker').hue.css('top', parseInt(150 - 150 * hsb.h / 360, 10));
        },
        setCurrentColor = function(hsb, cal) {
            $(cal).data('wpc-colorpicker').currentColor.css('backgroundColor', '#' + HSBToHex(hsb));
        },
        setNewColor = function(hsb, cal) {
            $(cal).data('wpc-colorpicker').newColor.css('backgroundColor', '#' + HSBToHex(hsb));
        },
        keyDown = function(ev) {
            var pressedKey = ev.charCode || ev.keyCode || -1;
            if ((pressedKey > charMin && pressedKey <= 90) || pressedKey == 32) {
                return false;
            }
            var cal = $(this).parent().parent();
            if (cal.data('wpc-colorpicker').livePreview === true) {
                change.apply(this);
            }
        },
        change = function(ev) {
            var cal = $(this).parent().parent(),
            col;
            if (this.parentNode.className.indexOf('_hex') > 0) {
                cal.data('wpc-colorpicker').color = col = HexToHSB(fixHex(this.value));
            } else if (this.parentNode.className.indexOf('_hsb') > 0) {
                cal.data('wpc-colorpicker').color = col = fixHSB({
                    h: parseInt(cal.data('wpc-colorpicker').fields.eq(4).val(), 10),
                    s: parseInt(cal.data('wpc-colorpicker').fields.eq(5).val(), 10),
                    b: parseInt(cal.data('wpc-colorpicker').fields.eq(6).val(), 10)
                    });
            } else {
                cal.data('wpc-colorpicker').color = col = RGBToHSB(fixRGB({
                    r: parseInt(cal.data('wpc-colorpicker').fields.eq(1).val(), 10),
                    g: parseInt(cal.data('wpc-colorpicker').fields.eq(2).val(), 10),
                    b: parseInt(cal.data('wpc-colorpicker').fields.eq(3).val(), 10)
                    }));
            }
            if (ev) {
                fillRGBFields(col, cal.get(0));
                fillHexFields(col, cal.get(0));
                fillHSBFields(col, cal.get(0));
            }
            setSelector(col, cal.get(0));
            setHue(col, cal.get(0));
            setNewColor(col, cal.get(0));
            cal.data('wpc-colorpicker').onChange.apply(cal, [col, HSBToHex(col), HSBToRGB(col)]);
        },
        blur = function(ev) {
            var cal = $(this).parent().parent();
            cal.data('wpc-colorpicker').fields.parent().removeClass('wpc-colorpicker_focus');
        },
        focus = function() {
            charMin = this.parentNode.className.indexOf('_hex') > 0 ? 70: 65;
            $(this).parent().parent().data('wpc-colorpicker').fields.parent().removeClass('wpc-colorpicker_focus');
            $(this).parent().addClass('wpc-colorpicker_focus');
        },
        downIncrement = function(ev) {
            var field = $(this).parent().find('input').focus();
            var current = {
                el: $(this).parent().addClass('wpc-colorpicker_slider'),
                max: this.parentNode.className.indexOf('_hsb_h') > 0 ? 360: (this.parentNode.className.indexOf('_hsb') > 0 ? 100: 255),
                y: ev.pageY,
                field: field,
                val: parseInt(field.val(), 10),
                preview: $(this).parent().parent().data('wpc-colorpicker').livePreview
            };
            $(document).bind('mouseup', current, upIncrement);
            $(document).bind('mousemove', current, moveIncrement);
        },
        moveIncrement = function(ev) {
            ev.data.field.val(Math.max(0, Math.min(ev.data.max, parseInt(ev.data.val + ev.pageY - ev.data.y, 10))));
            if (ev.data.preview) {
                change.apply(ev.data.field.get(0), [true]);
            }
            return false;
        },
        upIncrement = function(ev) {
            change.apply(ev.data.field.get(0), [true]);
            ev.data.el.removeClass('wpc-colorpicker_slider').find('input').focus();
            $(document).unbind('mouseup', upIncrement);
            $(document).unbind('mousemove', moveIncrement);
            return false;
        },
        downHue = function(ev) {
            // prevent android from highlighting the hue selector on click
            ev.preventDefault();

            var moveEvent = ev;
            if (typeof(event) !== "undefined" && event.touches) {
                moveEvent = event.touches[0];
            }

            var current = {
                cal: $(this).parent(),
                y: $(this).offset().top
            };
            current.preview = current.cal.data('wpc-colorpicker').livePreview;
            $(document).bind('mouseup touchend', current, upHue);
            $(document).bind('mousemove touchmove', current, moveHue);

            changeHue(moveEvent, current, current.preview);
            return false;
        },
        changeHue = function(ev, data,  preview) {
            change.apply(data.cal.data('wpc-colorpicker').fields.eq(4).val(parseInt(360 * (150 - Math.max(0, Math.min(150, ev.pageY - data.y))) / 150, 10)).get(0), [preview]);
        },
        moveHue = function(ev) {
            var moveEvent = ev;

            // mobile touch event!
            if (typeof(event) !== "undefined" && event.touches) {
                moveEvent = event.touches[0];
            }
            changeHue(moveEvent, ev.data, ev.data.preview);
            return false;
        },
        upHue = function(ev) {
            fillRGBFields(ev.data.cal.data('wpc-colorpicker').color, ev.data.cal.get(0));
            fillHexFields(ev.data.cal.data('wpc-colorpicker').color, ev.data.cal.get(0));
            $(document).unbind('mouseup touchend', upHue);
            $(document).unbind('mousemove touchmove', moveHue);
            return false;
        },
        downSelector = function(ev) {
            // prevent android from highlighting the selector on click
            ev.preventDefault();
            var current = {
                cal: $(this).parent(),
                pos: $(this).offset()
                };
            current.preview = current.cal.data('wpc-colorpicker').livePreview;
            $(document).bind('mouseup touchend', current, upSelector);
            $(document).bind('mousemove touchmove', current, moveSelector);

            $(".wpc-colorpicker_color", current.cal).one('click', current, moveSelector);
            ev.data = current;
            moveSelector(ev);
        },
        moveSelector = function(ev) {
            var moveEvent = ev;

            // mobile touch event!
            if (typeof(event) !== "undefined" && event.touches) {
                moveEvent = event.touches[0];
            }

            change.apply(ev.data.cal.data('wpc-colorpicker').fields.eq(6).val(parseInt(100 * (150 - Math.max(0, Math.min(150, (moveEvent.pageY - ev.data.pos.top)))) / 150, 10)).end().eq(5).val(parseInt(100 * (Math.max(0, Math.min(150, (moveEvent.pageX - ev.data.pos.left)))) / 150, 10)).get(0), [ev.data.preview]);
            return false;
        },
        upSelector = function(ev) {
            fillRGBFields(ev.data.cal.data('wpc-colorpicker').color, ev.data.cal.get(0));
            fillHexFields(ev.data.cal.data('wpc-colorpicker').color, ev.data.cal.get(0));
            $(document).unbind('mouseup touchend', upSelector);
            $(document).unbind('mousemove touchmove', moveSelector);
            return false;
        },
        enterSubmit = function(ev) {
            $(this).addClass('wpc-colorpicker_focus');
        },
        leaveSubmit = function(ev) {
            $(this).removeClass('wpc-colorpicker_focus');
        },
        clickSubmit = function(ev) {
            var cal = $(this).parent();
            var col = cal.data('wpc-colorpicker').color;
            cal.data('wpc-colorpicker').origColor = col;
            setCurrentColor(col, cal.get(0));
            cal.data('wpc-colorpicker').onSubmit(col, HSBToHex(col), HSBToRGB(col), cal.data('wpc-colorpicker').el);
        },
        toggle = function(ev) {
          var $body = $('body'),
              current = $body.data('wpc-colorpickerId'),
              me = $(this).data('wpc-colorpickerId');
          if ( current && current === me ) {
            $(document).trigger('mousedown');
            $body.data('wpc-colorpickerId', null );
          } else {
            show.call(this, ev);
            $body.data('wpc-colorpickerId', me );
          }
        },
        show = function(ev) {
            var cal = $('#' + $(this).data('wpc-colorpickerId'));
            cal.data('wpc-colorpicker').onBeforeShow.apply(this, [cal.get(0)]);
            var pos = $(this).offset();
            var viewPort = getViewport();
            var top = pos.top + this.offsetHeight;
            var left = pos.left;
            if (top + 176 > viewPort.t + viewPort.h) {
                top -= this.offsetHeight + 176;
            }
            if (left + 356 > viewPort.l + viewPort.w) {
                left = Math.max(0, left - 356);
            }
            cal.css({
                left: left + 'px',
                top: top + 'px'
            });
            if (cal.data('wpc-colorpicker').onShow.apply(this, [cal.get(0)]) != false) {
                cal.show();
            }
            $(document).bind('mousedown', {
                cal: cal
            }, hide);
            return false;
        },
        hide = function(ev) {
            if (!isChildOf(ev.data.cal.get(0), ev.target, ev.data.cal.get(0))) {
                if (ev.data.cal.data('wpc-colorpicker').onHide.apply(this, [ev.data.cal.get(0)]) != false) {
                    ev.data.cal.hide();
                }
                if (!isChildOf($(ev.data.cal[0]).data("wpc-colorpicker")["el"], ev.target)) {
                    $('body').data('wpc-colorpickerId', null);
                }
                $(document).unbind('mousedown', hide);
            }
        },
        isChildOf = function(parentEl, el, container) {
            if (parentEl == el) {
                return true;
            }
            if (parentEl.contains) {
                return parentEl.contains(el);
            }
            if (parentEl.compareDocumentPosition) {
                return !! (parentEl.compareDocumentPosition(el) & 16);
            }
            var prEl = el.parentNode;
            while (prEl && prEl != container) {
                if (prEl == parentEl)
                    return true;
                prEl = prEl.parentNode;
            }
            return false;
        },
        getViewport = function() {
            var m = document.compatMode == 'CSS1Compat';
            return {
                l: window.pageXOffset || (m ? document.documentElement.scrollLeft: document.body.scrollLeft),
                t: window.pageYOffset || (m ? document.documentElement.scrollTop: document.body.scrollTop),
                w: window.innerWidth || (m ? document.documentElement.clientWidth: document.body.clientWidth),
                h: window.innerHeight || (m ? document.documentElement.clientHeight: document.body.clientHeight)
                };
        },
        fixHSB = function(hsb) {
            return {
                h: Math.round( Math.min(360, Math.max(0, hsb.h)) ),
                s: Math.round( Math.min(100, Math.max(0, hsb.s)) ),
                b: Math.round( Math.min(100, Math.max(0, hsb.b)) )
                };
        },
        fixRGB = function(rgb) {
            return {
                r: Math.min(255, Math.max(0, rgb.r)),
                g: Math.min(255, Math.max(0, rgb.g)),
                b: Math.min(255, Math.max(0, rgb.b))
                };
        },
        fixHex = function(hex) {
            var len = 6 - hex.length;
            if (len > 0) {
                var o = [];
                for (var i = 0; i < len; i++) {
                    o.push('0');
                }
                o.push(hex);
                hex = o.join('');
            }
            return hex;
        },
        HexToRGB = function(hex) {
            var hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
            return {
                r: hex >> 16,
                g: (hex & 0x00FF00) >> 8,
                b: (hex & 0x0000FF)
                };
        },
        HexToHSB = function(hex) {
            return RGBToHSB(HexToRGB(hex));
        },
        RGBToHSB = function(rgb) {
            var hsb = {
                h: 0,
                s: 0,
                b: 0
            };
            var min = Math.min(rgb.r, rgb.g, rgb.b);
            var max = Math.max(rgb.r, rgb.g, rgb.b);
            var delta = max - min;
            hsb.b = max;
            if (max != 0) {
}
            hsb.s = max != 0 ? 255 * delta / max: 0;
            if (hsb.s != 0) {
                if (rgb.r == max) {
                    hsb.h = (rgb.g - rgb.b) / delta;
                } else if (rgb.g == max) {
                    hsb.h = 2 + (rgb.b - rgb.r) / delta;
                } else {
                    hsb.h = 4 + (rgb.r - rgb.g) / delta;
                }
            } else {
                hsb.h = -1;
            }
            hsb.h *= 60;
            if (hsb.h < 0) {
                hsb.h += 360;
            }
            hsb.s *= 100 / 255;
            hsb.b *= 100 / 255;

            hsb.h = Math.round ( hsb.h );
            hsb.s = Math.round ( hsb.s );
            hsb.b = Math.round ( hsb.b );
            return hsb;
        },
        HSBToRGB = function(hsb) {
            var rgb = {};
            var h = Math.round(hsb.h);
            var s = Math.round(hsb.s * 255 / 100);
            var v = Math.round(hsb.b * 255 / 100);
            if (s == 0) {
                rgb.r = rgb.g = rgb.b = v;
            } else {
                var t1 = v;
                var t2 = (255 - s) * v / 255;
                var t3 = (t1 - t2) * (h % 60) / 60;
                if (h == 360)
                    h = 0;
                if (h < 60) {
                    rgb.r = t1;
                    rgb.b = t2;
                    rgb.g = t2 + t3
                } else if (h < 120) {
                    rgb.g = t1;
                    rgb.b = t2;
                    rgb.r = t1 - t3
                } else if (h < 180) {
                    rgb.g = t1;
                    rgb.r = t2;
                    rgb.b = t2 + t3
                } else if (h < 240) {
                    rgb.b = t1;
                    rgb.r = t2;
                    rgb.g = t1 - t3
                } else if (h < 300) {
                    rgb.b = t1;
                    rgb.g = t2;
                    rgb.r = t2 + t3
                } else if (h < 360) {
                    rgb.r = t1;
                    rgb.g = t2;
                    rgb.b = t1 - t3
                } else {
                    rgb.r = 0;
                    rgb.g = 0;
                    rgb.b = 0
                }
            }
            return {
                r: Math.round(rgb.r),
                g: Math.round(rgb.g),
                b: Math.round(rgb.b)
                };
        },
        RGBToHex = function(rgb) {
            var hex = [rgb.r.toString(16), rgb.g.toString(16), rgb.b.toString(16)];
            $.each(hex, function(nr, val) {
                if (val.length == 1) {
                    hex[nr] = '0' + val;
                }
            });
            return hex.join('');
        },
        HSBToHex = function(hsb) {
            return RGBToHex(HSBToRGB(hsb));
        },
        restoreOriginal = function() {
            var cal = $(this).parent();
            var col = cal.data('wpc-colorpicker').origColor;
            cal.data('wpc-colorpicker').color = col;
            fillRGBFields(col, cal.get(0));
            fillHexFields(col, cal.get(0));
            fillHSBFields(col, cal.get(0));
            setSelector(col, cal.get(0));
            setHue(col, cal.get(0));
            setNewColor(col, cal.get(0));
        };
        return {
            init: function(opt) {
                opt = $.extend({}, defaults, opt || {});
                if (typeof opt.color == 'string') {
                    opt.color = HexToHSB(opt.color);
                } else if (opt.color.r != undefined && opt.color.g != undefined && opt.color.b != undefined) {
                    opt.color = RGBToHSB(opt.color);
                } else if (opt.color.h != undefined && opt.color.s != undefined && opt.color.b != undefined) {
                    opt.color = fixHSB(opt.color);
                } else {
                    return this;
                }
                return this.each(function() {
                    if (!$(this).data('wpc-colorpickerId')) {
                        var options = $.extend({}, opt);
                        options.origColor = opt.color;
                        var id = 'wpc-colorpicker_' + ($(this).attr('id') || parseInt(Math.random() * 1000));
                        $(this).data('wpc-colorpickerId', id);
                        var cal = $(tpl).attr('id', id);
                        if (options.flat) {
                            cal.appendTo(this).show();
                        } else {
                            cal.appendTo(document.body);
                        }
                        options.fields = cal.find('input').bind('keyup', keyDown).bind('change', change).bind('blur', blur).bind('focus', focus);
                        cal.find('span').bind('mousedown touchstart', downIncrement).end().find('>div.wpc-colorpicker_current_color').bind('click', restoreOriginal);
                        options.selector = cal.find('div.wpc-colorpicker_color').bind('touchstart mousedown', downSelector);
                        options.selectorIndic = options.selector.find('div div');
                        options.el = this;
                        options.hue = cal.find('div.wpc-colorpicker_hue div');
                        cal.find('div.wpc-colorpicker_hue').bind('mousedown touchstart', downHue);
                        options.newColor = cal.find('div.wpc-colorpicker_new_color');
                        options.currentColor = cal.find('div.wpc-colorpicker_current_color');
                        cal.data('wpc-colorpicker', options);
                        cal.find('div.wpc-colorpicker_submit').bind('mouseenter touchstart', enterSubmit).bind('mouseleave touchend', leaveSubmit).bind('click', clickSubmit);
                        fillRGBFields(options.color, cal.get(0));
                        fillHSBFields(options.color, cal.get(0));
                        fillHexFields(options.color, cal.get(0));
                        setHue(options.color, cal.get(0));
                        setSelector(options.color, cal.get(0));
                        setCurrentColor(options.color, cal.get(0));
                        setNewColor(options.color, cal.get(0));
                        if (options.flat) {
                            cal.css({
                                position: 'relative',
                                display: 'block'
                            });
                        } else {
                            $(this).bind(options.eventName, toggle);
                        }
                    }
                });
            },
            showPicker: function() {
                return this.each(function() {
                    if ($(this).data('wpc-colorpickerId')) {
                        show.apply(this);
                    }
                });
            },
            hidePicker: function() {
                return this.each(function() {
                    if ($(this).data('wpc-colorpickerId')) {
                        $('#' + $(this).data('wpc-colorpickerId')).hide();
                    }
                });
            },
            setColor: function(col) {
                if (typeof col == 'string') {
                    col = HexToHSB(col);
                } else if (col.r != undefined && col.g != undefined && col.b != undefined) {
                    col = RGBToHSB(col);
                } else if (col.h != undefined && col.s != undefined && col.b != undefined) {
                    col = fixHSB(col);
                } else {
                    return this;
                }
                return this.each(function() {
                    if ($(this).data('wpc-colorpickerId')) {
                        var cal = $('#' + $(this).data('wpc-colorpickerId'));
                        cal.data('wpc-colorpicker').color = col;
                        cal.data('wpc-colorpicker').origColor = col;
                        fillRGBFields(col, cal.get(0));
                        fillHSBFields(col, cal.get(0));
                        fillHexFields(col, cal.get(0));
                        setHue(col, cal.get(0));
                        setSelector(col, cal.get(0));
                        setCurrentColor(col, cal.get(0));
                        setNewColor(col, cal.get(0));
                    }
                });
            }
        };
    } ();
    $.fn.extend({
        ColorPicker: ColorPicker.init,
        ColorPickerHide: ColorPicker.hidePicker,
        ColorPickerShow: ColorPicker.showPicker,
        ColorPickerSetColor: ColorPicker.setColor
    });
})(jQuery)
