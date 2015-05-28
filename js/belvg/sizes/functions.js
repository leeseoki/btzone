
function checkDisable(n)
{
    if (jQblvg('#attribute'+n).is(':disabled')) {
        jQblvg('#sizesButtonLink').hide();
    } else {
        jQblvg('#sizesButtonLink').show();
    }
}

function refreshSizepopup()
{
    jQblvg('#sizesPopup').css('width', parseInt(jQblvg(window).width()*0.9) + 'px');
    if ((jQblvg('#sizes_form').outerWidth(true) + jQblvg('#sizes_result').outerWidth(true)) > jQblvg(window).width()*0.9) {
        jQblvg('#sizesPopup').addClass('one-column');
        jQblvg('.sizes_image').hide();
    } else {
        jQblvg('#sizesPopup').removeClass('one-column');
        jQblvg('.sizes_image').show();
    }
}
