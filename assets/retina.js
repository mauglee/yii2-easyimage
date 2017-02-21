(function ($) {
    if (window.devicePixelRatio > 1) {
        $('img[data-rjs]').each(function () {
            var i = $(this);
            var s = i.attr('src');
            var r = i.attr('data-rjs');
            var src = s.substr(0, s.lastIndexOf(".")) + '@' + r + 'x' + s.substr(s.lastIndexOf("."));
            i.attr( 'src', src );
        });
    }
})(jQuery);
