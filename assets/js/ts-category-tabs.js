(function ($) {
    'use strict';

    /**
     * TS Category Product Tabs Handler
     *
     * @param {jQuery} $scope
     * @param {jQuery} $
     */
    var TSCategoryTabsHandler = function ($scope, $) {
        var $wrapper = $scope.find('.ts-product-tabs-wrapper');
        if (!$wrapper.length) {
            return;
        }

        var $tabs = $wrapper.find('.ts-product-tabs-nav .ts-tab-item');
        var $panels = $wrapper.find('.ts-product-tabs-content .ts-tab-panel');

        // Unbind any previous events to prevent double binding on elementor re-render
        $tabs.off('click.tsTabs keydown.tsTabs');

        $tabs.on('click.tsTabs keydown.tsTabs', function (e) {
            // Handle keyboard accessibility (Enter or Space)
            if (e.type === 'keydown') {
                if (e.which !== 13 && e.which !== 32) {
                    return;
                }
                e.preventDefault();
            }

            var $this = $(this);

            if ($this.hasClass('is-active')) {
                return;
            }

            var targetSelector = $this.data('target');
            var $targetPanel = $wrapper.find(targetSelector);

            // Update Tab Nav States
            $tabs.removeClass('is-active').attr('aria-selected', 'false').attr('tabindex', '-1');
            $this.addClass('is-active').attr('aria-selected', 'true').attr('tabindex', '0');

            // Smoothly switch matching tab pane
            $panels.stop(true, true).removeClass('is-active').hide();

            if ($targetPanel.length) {
                $targetPanel.css({ display: 'none', opacity: 0 }).addClass('is-active').fadeTo(220, 1, function () {
                    // Trigger window resize so any inner loop sliders, swipers or masonry adjust smoothly
                    $(window).trigger('resize');
                });
            }
        });
    };

    // Elementor frontend hook
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/ts_category_product_tabs.default',
            TSCategoryTabsHandler
        );
    });

    // Fallback for regular document ready if not inside Elementor frontend runtime
    $(document).ready(function () {
        if (!window.elementorFrontend) {
            $('.elementor-widget-ts_category_product_tabs').each(function () {
                TSCategoryTabsHandler($(this), $);
            });
        }
    });

})(jQuery);
