/**
 * jQuery scrollTopTop function
 */
(function($) {
    $.fn.scrollToTop = function(options) {
        var config = {
            "speed" : 800
        };

        if (options) {
            $.extend(config, {
                "speed" : options
            });
        }

        return this.each(function() {

            var $this = $(this);

            $(window).scroll(function() {
                if ($(this).scrollTop() > 100) {
                    $this.fadeIn();
                } else {
                    $this.fadeOut();
                }
            });

            $this.click(function(e) {
                e.preventDefault();
                $("body, html").animate({
                    scrollTop : 0
                }, config.speed);
            });

        });
    };
})(jQuery);

$(function() {
    $("#toTop").scrollToTop();
});

var core = {

    /**
     * Scroll to the tag with given id.
     *
     * @param id
     */
    scrollToTag : function(id)
    {
        var tag = $("#" + id);
        if (typeof (tag.offset()) != 'undefined') {
            $("html,body").animate({scrollTop: tag.offset().top }, 800);
        }
    },

    /**
     * Checks for the current screen device size (xs, sm, md, lg).
     *
     * @param string alias Device type (xs, sm, md, lg)
     * @returns boolean
     */
    isBreakpoint : function ( alias )
    {
        return $('.device-' + alias).is(':visible');
    },

    /**
     * Check for touch device.
     *
     * @returns boolean
     */
    isTouchDevice : function ()
    {
        return !!('ontouchstart' in window);
    },

    /**
     * Sets cookie.
     *
     * @param name
     * @param value
     * @param days
     */
    setCookie : function ( name, value, days )
    {
        var d = new Date();
        d.setTime(d.getTime() + (days*24*60*60*1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + value + "; " + expires;
    }
};