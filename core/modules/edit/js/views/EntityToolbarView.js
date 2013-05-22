/**
 * @file
 * A Backbone View that provides an entity level toolbar.
 */
(function ($, Backbone, Drupal, debounce) {

"use strict";

Drupal.edit.EntityToolbarView = Backbone.View.extend({

  _loader: null,
  _loaderVisibleStart: 0,
  _fieldToolbarRoot: null,
  _fieldLabelRoot: null,

  events: function () {
    var map = {
      'click.edit button.action-save': 'onClickSave',
      'click.edit button.action-cancel': 'onClickCancel',
      'mouseenter.edit': 'onMouseenter'
    };
    return map;
  },

  /**
   * {@inheritdoc}
   */
  initialize: function (options) {
    var that = this;

    this.appModel = options.appModel;

    this.model.on('change:isActive change:isDirty', this.render, this);
    this.model.on('change:state', this.stateChange, this);
    this.model.on('fieldViewChange', this.fieldViewChangeHandler, this);

    this.appModel.on('change:highlightedEditor', this.render, this);
    this.appModel.on('change:activeEditor', this.render, this);

    $(window).on('resize.edit scroll.edit', debounce($.proxy(this.windowChangeHandler, this), 150));

    // Set the el into its own property. Eventually the el property will be
    // replaced with the rendered toolbar.
    this.$entity = this.$el;

    // Set the toolbar container to this view's el property.
    this.buildToolbarEl();
    this._fieldToolbarRoot = this.$el.find('.edit-toolbar-field').get(0);

    this._loader = null;
    this._loaderVisibleStart = 0;

    this.render();
  },

  /**
   * {@inheritdoc}
   */
  render: function (model, changeValue) {

    if (this.model.get('isActive')) {
      // If the toolbar container doesn't exist, create it.
      if ($('body').children('#edit-entity-toolbar').length === 0) {
        $('body').append(this.$el);
      }

      this.label();

      this.show('ops');
      // If render is being called and the toolbar is already visible, just
      // reposition it.
      this.position();
    }

    var $save = this.$el.find('.edit-button.action-save');
    $save.attr('aria-hidden', !this.model.get('isDirty'));
    // The progress spinner will only be set when the save button is clicked.
    // Remove it on any call to render.
    $save.find('.ajax-progress').remove();
    $save.find('span').text(Drupal.t('Save'));
    $save.removeClass('action-saving');

    return this;
  },

  /**
   *
   */
  windowChangeHandler: function (event) {
    this.position();
  },

  /**
   *
   */
  fieldViewChangeHandler: function (view) {
    this.render(this, view);
  },

  /**
   * Uses the jQuery.ui.position() method to position the entity toolbar.
   */
  position: function (element) {
    clearTimeout(this.timer);
    var that = this;
    // Vary the edge of the positioning according to the direction of language
    // in the document.
    var edge = (document.documentElement.dir === 'rtl') ? 'right' : 'left';
    // If a field in this entity is active, position against it.
    var activeEditor = Drupal.edit.app.model.get('activeEditor');
    var activeEditorView = activeEditor && activeEditor.editorView;
    var activeEditedElement = activeEditorView && activeEditorView.getEditedElement();

    // Label of a highlighted field, if it exists.
    var highlightedEditor = Drupal.edit.app.model.get('highlightedEditor');
    var highlightedEditorView = highlightedEditor && highlightedEditor.editorView;
    var highlightedEditedElement = highlightedEditorView && highlightedEditorView.getEditedElement();
    // Prefer the specified element from the parameters, then the acive field
    // and finally the entity itself to determine the position of the toolbar.
    var of = element || activeEditedElement || highlightedEditedElement || this.$entity;
    // Uses the jQuery.ui.position() method. Use a timeout to move the toolbar
    // only after the user has focused on an editable for 250ms. This prevents
    // the toolbar from jumping around the screen.
    this.timer = setTimeout(function () {
      that.$el
        .position({
          my: edge + ' bottom',
          at: edge + ' top',
          of: of,
          // Eliminate some of the placement jitteriness by flooring the suggested
          // values.
          using: function (suggested, info) {
            info.element.element.css({
              left: Math.floor(suggested.left),
              top: Math.floor(suggested.top)
            });
          }
        })
        .css({
          'max-width': $(of).outerWidth(),
          'width': '100%'
        });
      }, 250);
  },

  /**
   * Determines the actions to take given a change of state.
   *
   * @param Drupal.edit.EntityModel model
   * @param String state
   *   The state of the associated field. One of Drupal.edit.EntityModel.states.
   */
  stateChange: function (model, state, options) {
      var from = model.previous('state');
      var to = state;
      switch (to) {
        case 'opened':
          this.position();
          break;
        case 'activating':
          this.setLoadingIndicator(true);
          break;
        case 'active':
          this.setLoadingIndicator(false);
          break;
        case 'changed':
          this.$el
            .find('button.save')
            .addClass('blue-button')
            .removeClass('gray-button');
          break;
        case 'saving':
          this.setLoadingIndicator(true);
          break;
        case 'saved':
          this.setLoadingIndicator(false);
          break;
        case 'invalid':
          this.setLoadingIndicator(false);
          break;
        default:
          break;
      }
  },

  /**
   * Set the model state to 'saving' when the save button is clicked.
   *
   * @param jQuery event
   */
  onClickSave: function (event) {
    event.stopPropagation();
    event.preventDefault();
    var $target = $(event.target);
    $target = ($target.is('.action-save')) ? $target : $target.closest('.action-save');
    $target.addClass('action-saving');
    $target.find('span')
      .text(Drupal.t('Saving@ellipsis', {'@ellipsis': '...'}))
      .after(Drupal.theme.editThrobber());
    // Save the model.
    this.model.set('state', 'committing');
  },

  /**
   * Sets the model state to candidate when the cancel button is clicked.
   *
   * @param jQuery event
   */
  onClickCancel: function (event) {
    event.preventDefault();
    this.model.set('state', 'deactivating');
  },

  /**
   *
   */
  onMouseenter: function (event) {
    clearTimeout(this.timer);
  },

  /**
   *
   */
  buildToolbarEl: function () {
    var $toolbar;
    $toolbar = $(Drupal.theme('editEntityToolbar', {
      id: 'edit-entity-toolbar'
    }));

    $toolbar
      .find('.edit-toolbar-entity')
      // Append the "ops" toolgroup into the toolbar.
      .prepend(Drupal.theme('editToolgroup', {
        classes: ['ops'],
        buttons: [
          { label: Drupal.t('Save'), type: 'submit', classes: 'action-save edit-button', attributes: {'aria-hidden': true}},
          { label: Drupal.t('Close'), classes: 'action-cancel edit-button' }
        ]
      }));

    // Give the toolbar a sensible starting position so that it doesn't
    // animiate on to the screen from a far off corner.
    $toolbar
      .css({
        left: this.$entity.offset().left,
        top: this.$entity.offset().top
      });

    this.setElement($toolbar);
  },

  /**
   *
   */
  getToolbarRoot: function () {
    return this._fieldToolbarRoot;
  },

  /**
   * Indicates in the 'info' toolgroup that we're waiting for a server reponse.
   *
   * Prevents flickering loading indicator by only showing it after 0.6 seconds
   * and if it is shown, only hiding it after another 0.6 seconds.
   *
   * @param Boolean enabled
   *   Whether the loading indicator should be displayed or not.
   */
  setLoadingIndicator: function (enabled) {
    var that = this;
    if (enabled) {
      this._loader = setTimeout(function() {
        that.addClass('info', 'loading');
        that._loaderVisibleStart = new Date().getTime();
      }, 600);
    }
    else {
      var currentTime = new Date().getTime();
      clearTimeout(this._loader);
      if (this._loaderVisibleStart) {
        setTimeout(function() {
          that.removeClass('info', 'loading');
        }, this._loaderVisibleStart + 600 - currentTime);
      }
      this._loader = null;
      this._loaderVisibleStart = 0;
    }
  },

  /**
   * Generates a state-dependent label for the entity toolbar.
   */
  label: function () {
    // The entity label.
    var label = '"' + this.model.get('label') + '"';

    // Label of an active field, if it exists.
    var activeEditor = Drupal.edit.app.model.get('activeEditor');
    var activeFieldLabel = activeEditor && activeEditor.get('metadata').label;
    activeFieldLabel = activeFieldLabel && activeFieldLabel + ' — ' + label;

    // Label of a highlighted field, if it exists.
    var highlightedEditor = Drupal.edit.app.model.get('highlightedEditor');
    var highlightedFieldLabel = highlightedEditor && highlightedEditor.get('metadata').label;
    highlightedFieldLabel = highlightedFieldLabel && highlightedFieldLabel + ' — ' + label;

    this.$el
      .find('.edit-toolbar-label')
      .text(activeFieldLabel || highlightedFieldLabel || label);
  },

  /**
   * Adds classes to a toolgroup.
   *
   * @param String toolgroup
   *   A toolgroup name.
   */
  addClass: function (toolgroup, classes) {
    this._find(toolgroup).addClass(classes);
  },

  /**
   * Removes classes from a toolgroup.
   *
   * @param String toolgroup
   *   A toolgroup name.
   */
  removeClass: function (toolgroup, classes) {
    this._find(toolgroup).removeClass(classes);
  },

  /**
   * Finds a toolgroup.
   *
   * @param String toolgroup
   *   A toolgroup name.
   */
  _find: function (toolgroup) {
    return this.$el.find('.edit-toolbar .edit-toolgroup.' + toolgroup);
  },

  /**
   * Shows a toolgroup.
   *
   * @param String toolgroup
   *   A toolgroup name.
   */
  show: function (toolgroup) {
    this.$el.removeClass('edit-animate-invisible');
  }
});

})(jQuery, Backbone, Drupal, Drupal.debounce);
