(function ($) {
    'use strict';

    var TSShowcaseHandler = function ($scope, $) {
        var $container = $scope.find('.ts-hover-showcase-container');
        if (!$container.length) {
            return;
        }

        var $navItems = $container.find('.ts-cat-item');
        var $panels   = $container.find('.ts-product-panel');

        // Trigger switch on hover (mouseenter) and touch/click for mobile
        $navItems.on('mouseenter click', function (e) {
            var $this = $(this);
            var targetPanelId = $this.data('target-panel');

            if ($this.hasClass('ts-active')) {
                return;
            }

            // Update active state in sidebar navigation
            $navItems.removeClass('ts-active');
            $this.addClass('ts-active');

            // Switch visible product grid
            $panels.removeClass('ts-active');
            $('#' + targetPanelId).addClass('ts-active');
        });
    };

    // Initialize with Elementor frontend hooks
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/ts_woo_category_hover_showcase.default', TSShowcaseHandler);
    });

})(jQuery);