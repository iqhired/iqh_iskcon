(function($) {
    $('.login input.input').each(function() {
       label = $(this).parent().children("label").text();
       $(this).attr("placeholder", label);
       $(this).insertBefore($(this).parent());
       $(this).next().remove();
    });

    $('.user-pass-wrap').each(function() {
     label = $(this).children("label").text();
     $(this).children("input").attr("placeholder", label);
     $(this).children("label").remove();
    });

    var msgbox = $('p.message');
    msgbox.detach();
    msgbox.appendTo('.alter-form-container');
    msgbox.wrap('<div class="alter-form-message"></div>');

})(jQuery);
