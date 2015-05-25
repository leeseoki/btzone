/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var i;
mm = jQuery.noConflict();
var isMobile = 	navigator.userAgent.match(/iPhone|iPod|ipad/i)||navigator.userAgent.match(/Android/i)
        ||navigator.userAgent.match(/BlackBerry/i)||navigator.userAgent.match(/Opera Mini/i)
        ||navigator.userAgent.match(/IEMobile/i);
mm(document).ready(function(mm) {
    if (isMobile) {
        mm('.level0').click(function() {
            // alert(content_id);
            var li_id = mm(this).attr('id');
            var $this = mm(this);
            if ($this.hasClass('active')) {
                $this.removeClass('active');
            } else {
                $this.addClass('active');
            }
            modifyClass(li_id);
            var content_id = 'menu-' + $this.attr('id');
            if (mm('#' + content_id).hasClass('active')) {
                otherHide(content_id, true)
                mm('#' + content_id).hide();
                mm('#' + content_id).removeClass('active');
            } else {
                otherHide(content_id, true)
                mm('#' + content_id).fadeIn(500);
                mm('#' + content_id).addClass('active');
            }
        });
        mm('.grid-categories-megamenu').parent().parent().click(function() {
            height1 = mm(this).attr('id');
            var max_height_title = 0;
            mm.each(mm('#' + height1 + ' .level1-title'), function(key, e) {
                if (max_height_title < mm(this).height()) {
                    max_height_title = mm(this).height();
                }
            });
            mm('#' + height1 + ' .level1-title').height(max_height_title);
            var max_height1 = 0;
            mm.each(mm('#' + height1 + ' .level1-megamenu'), function(key, e) {
                if (max_height1 < mm(this).height()) {
                    max_height1 = mm(this).height();
                }
            });
            mm('#' + height1 + ' .level1-megamenu').height(max_height1);

        });
        mm('.list-categories-megamenu').parent().parent().click(function() {
            height2 = mm(this).attr('id');
            var max_height2 = 0;
            mm.each(mm('#' + height2 + ' .level1-megamenu'), function(key, e) {
                if (max_height2 < mm(this).height()) {
                    max_height2 = mm(this).height();
                }
            });
            mm('#' + height2 + ' .level1-megamenu').height(max_height2);
        });

        mm('.group-category').parent().parent().parent().click(function() {
            height3 = mm(this).attr('id');
            var max_height3 = 0;
            mm.each(mm('#' + height3 + ' .level1-megamenu'), function() {
                if (max_height3 < mm(this).height()) {
                    max_height3 = mm(this).height();
                }
            });
            mm('#' + height3 + ' .level1-megamenu').height(max_height3);
        });
        mm('.products-megamenu-grid').parent().parent().click(function() {
            height4 = mm(this).attr('id');
            var max_height_name = 0;
            mm.each(mm('#' + height4 + ' .product-name'), function(key, e) {
                if (max_height_name < mm(this).height()) {
                    max_height_name = mm(this).height();
                }
            });
            mm('#' + height4 + ' .product-name').height(max_height_name);
            mm('#' + height4 + ' .item').height('auto');
        });
    } else {
        mm('.level0').hover(function() {
            var li_id = mm(this).attr('id'); 
            var $this = mm(this);
            if ($this.hasClass('active')) {
                $this.removeClass('active');
            } else {
                $this.addClass('active');
            }
            modifyClass(li_id);
//            if(mm(this).hasClass('anchor_text')) {
//                window.location.href = mm(this).children('.megamenu-lable').attr('href');
//            }
            if(mm(this).hasClass('group-menu')) {
                window.location.href = mm(this).children('.megamenu-lable').attr('href');

            }
            var content_id = 'menu-' + $this.attr('id');
            if (mm('#' + content_id).hasClass('active')) {
                otherHide(content_id, true)
                mm('#' + content_id).hide();
                mm('#' + content_id).removeClass('active');
            } else {
                otherHide(content_id, true)
                mm('#' + content_id).fadeIn(500);
                mm('#' + content_id).addClass('active');
            }
        });
        mm('.grid-categories-megamenu').parent().parent().hover(function() {
            height1 = mm(this).attr('id');
            var max_height_title = 0;
            mm.each(mm('#' + height1 + ' .level1-title'), function(key, e) {
                if (max_height_title < mm(this).height()) {
                    max_height_title = mm(this).height();
                }
            });
            mm('#' + height1 + ' .level1-title').height(max_height_title);
            var max_height1 = 0;
            mm.each(mm('#' + height1 + ' .level1-megamenu'), function(key, e) {
                if (max_height1 < mm(this).height()) {
                    max_height1 = mm(this).height();
                }
            });
            mm('#' + height1 + ' .level1-megamenu').height(max_height1);

        });
        mm('.list-categories-megamenu').parent().parent().hover(function() {
            height2 = mm(this).attr('id');
            var max_height2 = 0;
            mm.each(mm('#' + height2 + ' .level1-megamenu'), function(key, e) {
                if (max_height2 < mm(this).height()) {
                    max_height2 = mm(this).height();
                }
            });
            mm('#' + height2 + ' .level1-megamenu').height(max_height2);
        });

        mm('.group-category').parent().parent().parent().hover(function() {
            height3 = mm(this).attr('id');
            var max_height3 = 0;
            mm.each(mm('#' + height3 + ' .level1-megamenu'), function() {
                if (max_height3 < mm(this).height()) {
                    max_height3 = mm(this).height();
                }
            });
            mm('#' + height3 + ' .level1-megamenu').height(max_height3);
        });
        mm('.products-megamenu-grid').parent().parent().hover(function() {
            height4 = mm(this).attr('id');
            var max_height_name = 0;
            mm.each(mm('#' + height4 + ' .product-name'), function(key, e) {
                if (max_height_name < mm(this).height()) {
                    max_height_name = mm(this).height();
                }
            });
            mm('#' + height4 + ' .product-name').height(max_height_name);
            mm('#' + height4 + ' .item').height('auto');
        });
    }
    
    function otherHide(id, flag) {
        mm.each(mm('.magestore-megamenu'), function(key, e) {
            if (mm(this).attr('id') != id) {
                if (flag)
                    mm(this).hide();
                else
                    mm(this).hide();
                mm(this).removeClass('active');
                mm(this).parent().css('margin-bottom', 0);
            }
        });
    }

    function modifyClass(id) {
        mm.each(mm('.level0'), function(key, e) {
            if (mm(this).attr('id') != id) {
                if (mm(this).hasClass('active'))
                    mm(this).removeClass('active');
            }
        });
    }

});
