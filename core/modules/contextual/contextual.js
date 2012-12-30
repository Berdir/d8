/**
 * @file
 * Attaches behaviors for the Contextual module.
 */

(function ($) {

"use strict";

Drupal.contextualLinks = Drupal.contextualLinks || {};

/**
 * Attaches outline behavior for regions associated with contextual links.
 */
Drupal.behaviors.contextualLinks = {
  attach: function (context) {
    $(context).find('div.contextual').once('contextual-links', function () {
      var $wrapper = $(this);
      var $region = $wrapper.closest('.contextual-region');
      var $links = $wrapper.find('ul');
      var $trigger = $('<a class="trigger" href="#" />').text(Drupal.t('Configure')).click(
        function (e) {
          e.preventDefault();
          e.stopPropagation();
          $links.stop(true, true).slideToggle(100);
          $wrapper.toggleClass('contextual-active');
        }
      );
      // Attach hover behavior to trigger and ul.contextual-links, for non touch devices only.
      if(!Modernizr.touch) {
        $trigger.add($links).hover(
          function () { $region.addClass('contextual-region-active'); },
          function () { $region.removeClass('contextual-region-active'); }
        );
      }
      // Hide the contextual links when user clicks a link or rolls out of the .contextual-region.
      $region.bind('mouseleave click', Drupal.contextualLinks.mouseleave);
      $region.hover(
        function() { $trigger.addClass('contextual-links-trigger-active'); },
        function() { $trigger.removeClass('contextual-links-trigger-active'); }
      );
      // Prepend the trigger.
      $wrapper.prepend($trigger);
    });
  }
};

/**
 * Disables outline for the region contextual links are associated with.
 */
Drupal.contextualLinks.mouseleave = function () {
  $(this)
    .find('.contextual-active').removeClass('contextual-active')
    .find('.contextual-links').hide();
};

})(jQuery);
