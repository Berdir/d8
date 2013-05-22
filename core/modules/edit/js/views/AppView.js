(function ($, _, Backbone, Drupal, drupalSettings) {

"use strict";

/**
 *
 */
Drupal.edit.AppView = Backbone.View.extend({

  // Configuration for state handling.
  activeEditorStates: [],
  singleEditorStates: [],

  // Ephemeral storage for changed fields that persists through field
  // rerendering.
  changedFieldsInTempstore: [],

  /**
   * {@inheritdoc}
   *
   * @param Object options
   *   An object with the following keys:
   *   - Drupal.edit.AppModel model: the application state model
   *   - Drupal.edit.EntityCollection entitiesCollection: all on-page entities
   *   - Drupal.edit.FieldCollection fieldsCollection: all on-page fields
   */
  initialize: function (options) {
    // AppView's configuration for handling states.
    // @see Drupal.edit.FieldModel.states
    this.activeEditorStates = ['activating', 'active'];
    this.singleEditorStates = ['highlighted', 'activating', 'active'];
    this.changedEditorStates = ['saving', 'changed', 'invalid'];

    options.entitiesCollection
      // Track app state.
      .on('change:state', this.appStateChange, this);
      //.on('change:isActive', this.enforceSingleActiveEntity, this);

    options.fieldsCollection
      // Track app state.
      .on('change:state', this.editorStateChange, this)
      // Respond to field model HTML representation change events.
      .on('change:html', this.renderUpdatedField, this)
      // Respond to addition.
      .on('add', this.rerenderedFieldToCandidate, this)
      // Respond to destruction.
      .on('destroy', this.teardownEditor, this);
  },

  /**
   * Handles setup/teardown and state changes when the active entity changes.
   *
   * @param Drupal.edit.EntityModel entityModel
   *   An instance of the EntityModel class.
   * @param Boolean isActive
   *   A boolean that represents the changed active state of the entityModel.
   */
  appStateChange: function (entityModel, state, options) {
    var app = this;
    switch (state) {
      case 'launching':
          // Create an entity toolbar.
        var entityToolbar = new Drupal.edit.EntityToolbarView({
          el: entityModel.get('el'),
          model: entityModel,
          appModel: this.model
        });
        entityModel.set('entityToolbar', entityToolbar);
        // Move all fields of this entity from the 'inactive' state to the
        // 'candidate' state.
        entityModel.get('fields').each(function (fieldModel) {
          // Set up editors; they must be notified of state changes.
          app.setupEditor(fieldModel);
        });
        _.defer(function () {
          entityModel.set('state', 'opening');;
        });
        break;
      case 'closed':
        var entityToolbar = entityModel.get('entityToolbar');
        if (entityToolbar) {
          entityModel.get('entityToolbar').remove();
          entityModel.set('entityToolbar', null);
        }
        // Teardown the editors.
        entityModel.get('fields').each(function (fieldModel) {
          // Second, tear down editors.
          app.teardownEditor(fieldModel);
        });
        break;
    }
  },

  /**
   * Accepts or reject editor (Editor) state changes.
   *
   * This is what ensures that the app is in control of what happens.
   *
   * @param String from
   *   The previous state.
   * @param String to
   *   The new state.
   * @param null|Object context
   *   The context that is trying to trigger the state change.
   * @param Function callback
   *   The callback function that should receive the state acceptance result.
   */
  acceptEditorStateChange: function (from, to, context) {
    var accept = true;

    // If the app is in view mode, then reject all state changes except for
    // those to 'inactive'.
    if (context && (context.reason === 'stop' || context.reason === 'rerender')) {
      if (from === 'candidate' && to === 'inactive') {
        accept = true;
      }
    }
    // Handling of edit mode state changes is more granular.
    else {
      // In general, enforce the states sequence. Disallow going back from a
      // "later" state to an "earlier" state, except in explicitly allowed
      // cases.
      if (!Drupal.edit.FieldModel.followsStateSequence(from, to)) {
        accept = false;
        // Allow: activating/active -> candidate.
        // Necessary to stop editing a property.
        if (_.indexOf(this.activeEditorStates, from) !== -1 && to === 'candidate') {
          accept = true;
        }
        // Allow: changed/invalid -> candidate.
        // Necessary to stop editing a property when it is changed or invalid.
        else if ((from === 'changed' || from === 'invalid') && to === 'candidate') {
          accept = true;
        }
        // Allow: highlighted -> candidate.
        // Necessary to stop highlighting a property.
        else if (from === 'highlighted' && to === 'candidate') {
          accept = true;
        }
        // Allow: saved -> candidate.
        // Necessary when successfully saved a property.
        else if (from === 'saved' && to === 'candidate') {
          accept = true;
        }
        // Allow: invalid -> saving.
        // Necessary to be able to save a corrected, invalid property.
        else if (from === 'invalid' && to === 'saving') {
          accept = true;
        }
      }

      // If it's not against the general principle, then here are more
      // disallowed cases to check.
      if (accept) {
        // Reject going from activating/active to candidate because of a
        // mouseleave.
        if (_.indexOf(this.activeEditorStates, from) !== -1 && to === 'candidate') {
          if (context && context.reason === 'mouseleave') {
            accept = false;
          }
        }
        // When attempting to stop editing a changed/invalid property, ask for
        // confirmation.
        else if ((from === 'changed' || from === 'invalid') && to === 'candidate') {
          if (context && context.reason === 'mouseleave') {
            accept = false;
          }
          else {
            // Check whether the transition has been confirmed?
            if (context && context.confirmed) {
              accept = true;
            }
            // Confirm this transition.
            else {
              // Do not accept this change right now, instead open a modal
              // that will ask the user to confirm his choice.
              accept = false;
              // The callback will be called from the helper function.
              this._confirmStopEditing({
                callback: (context || {}).callback || function () {}
              });
            }
          }
        }
      }
    }

    return accept;
  },

  /**
   * Sets up the in-place editor for the given field.
   *
   * Must happen before the fieldModel's state is changed to 'candidate'.
   *
   * @param Drupal.edit.FieldModel fieldModel
   *   The field for which an in-place editor must be set up.
   */
  setupEditor: function (fieldModel) {
    // Get the corresponding entity toolbar.
    var entityModel = fieldModel.get('entity');
    var entityToolbar = entityModel.get('entityToolbar');
    // Get the field toolbar DOM root from the entity toolbar.
    var fieldToolbarRoot = entityToolbar.getToolbarRoot();
    // Create in-place editor.
    var editorName = fieldModel.get('metadata').editor;
    var editorModel = new Drupal.edit.EditorModel();
    var editorView = new Drupal.edit.editors[editorName]({
      el: $(fieldModel.get('el')),
      model: editorModel,
      fieldModel: fieldModel
    });

    // Create in-place editor's toolbar — positions appropriately above the
    // edited element.
    var toolbarView = new Drupal.edit.FieldToolbarView({
      el: fieldToolbarRoot,
      model: fieldModel,
      $editedElement: $(editorView.getEditedElement()),
      // @todo editorView is needed for the getEditUISetting method. Maybe we
      // can factor out this dependency and put it in the metadata of the
      // Drupal.edit.Metadata object for this field.
      editorView: editorView,
      entityModel: entityModel
    });

    // Create decoration for edited element: padding if necessary, sets classes
    // on the element to style it according to the current state.
    var decorationView = new Drupal.edit.EditorDecorationView({
      el: $(editorView.getEditedElement()),
      model: fieldModel,
      editorView: editorView
    });

    // Track these three views in FieldModel so that we can tear them down
    // correctly.
    fieldModel.editorView = editorView;
    fieldModel.toolbarView = toolbarView;
    fieldModel.decorationView = decorationView;
  },

  /**
   * Tears down the in-place editor for the given field.
   *
   * Must happen after the fieldModel's state is changed to 'inactive'.
   *
   * @param Drupal.edit.FieldModel fieldModel
   *   The field for which an in-place editor must be torn down.
   */
  teardownEditor: function (fieldModel) {
    // Early-return if this field was not yet decorated.
    if (fieldModel.editorView === undefined) {
      return;
    }

    // Unbind event handlers; remove toolbar element; delete toolbar view.
    fieldModel.toolbarView.remove();
    delete fieldModel.toolbarView;

    // Unbind event handlers; delete decoration view. Don't remove the element
    // because that would remove the field itself.
    fieldModel.decorationView.remove();
    delete fieldModel.decorationView;

    // Unbind event handlers; delete editor view. Don't remove the element
    // because that would remove the field itself.
    fieldModel.editorView.remove();
    delete fieldModel.editorView;
  },

  /**
   * Asks the user to confirm whether he wants to stop editing via a modal.
   *
   * @see acceptEditorStateChange()
   */
  _confirmStopEditing: function (options) {
    var activeEntity = Drupal.edit.collections.entities.where({ isActive: true })[0];
    // Set the active entity to opened while we confirm the field changes.
    activeEntity.set('state', 'opened');
    // Only instantiate if there isn't a modal instance visible yet.
    if (!this.model.get('activeModal')) {
      var that = this;
      var modal = new Drupal.edit.ModalView({
        model: this.model,
        message: Drupal.t('You have unsaved changes'),
        buttons: [
          { action: 'save', type: 'submit', classes: 'action-save edit-button', label: Drupal.t('Save') },
          { action: 'discard', classes: 'action-cancel edit-button', label: Drupal.t('Discard changes') }
        ],
        callback: function (event, action) {
          // The active modal has been removed.
          that.model.set('activeModal', null);
          // If the targetState is saving, the field must be saved, then the
          // entity must be saved.
          if (action === 'save') {
            activeEntity.set('state', 'quitcommitting');
          }
          else {
            activeEntity.set('state', 'deactivating', {
              confirmed: true
            });
          }
        }
      });
      this.model.set('activeModal', modal);
      // The modal will set the activeModal property on the model when rendering
      // to prevent multiple modals from being instantiated.
      modal.render();
    }
  },

  /**
   * Reacts to field state changes; tracks global state.
   *
   * @param Drupal.edit.FieldModel fieldModel
   * @param String state
   *   The state of the associated field. One of Drupal.edit.FieldModel.states.
   */
  editorStateChange: function (fieldModel, state) {
    var from = fieldModel.previous('state');
    var to = state;

    // Keep track of the highlighted editor in the global state.
    if (_.indexOf(this.singleEditorStates, to) !== -1 && this.model.get('highlightedEditor') !== fieldModel) {
      this.model.set('highlightedEditor', fieldModel);
    }
    else if (this.model.get('highlightedEditor') === fieldModel && to === 'candidate' || to === 'inactive') {
      this.model.set('highlightedEditor', null);
    }

    // Keep track of the active editor in the global state.
    if (_.indexOf(this.activeEditorStates, to) !== -1 && this.model.get('activeEditor') !== fieldModel) {
      this.model.set('activeEditor', fieldModel);
    }
    else if (this.model.get('activeEditor') === fieldModel && to === 'candidate') {
      if (from === 'changed' || from === 'invalid') {
        fieldModel.editorView.revert();
      }
      this.model.set('activeEditor', null);
    }
  },

  /**
   *
   */
  enableEditor: function (fieldModel) {
    // check if there's an active editor.
    var activeEditor = this.model.get('activeEditor');

    // Do nothing if the fieldModel is already the active editor.
    if (fieldModel === activeEditor) {
      return;
    }
    if (activeEditor) {
      // If there is, check if the model is changed.
      if (activeEditor.get('state') === 'changed') {
        // Save a reference to the changed field so it can be marked as
        // as changed until the tempStore is pushed to permanent storage.
        this.changedFieldsInTempstore.push(activeEditor.id);
        // Attempt to save the field.
        activeEditor.set({'state': 'saving'}, {
          // This callback will be invoked if the activeEditor field is
          // successfully saved.
          callback: function () {
            // Set the new fieldModel to activating.
            fieldModel.set('state', 'activating');
          }
        });
      }
      // else, set it to a candidate.
      else {
        activeEditor.set('state', 'candidate');
        // Set the new fieldModel to activating.
        fieldModel.set('state', 'activating');
      }
    }
    else {
      // Set the new fieldModel to activating.
      fieldModel.set('state', 'activating');
    }
  },


  /**
   * Render an updated field (a field whose 'html' attribute changed).
   *
   * @param Drupal.edit.FieldModel fieldModel
   *   The FieldModel whose 'html' attribute changed.
   */
  renderUpdatedField: function (fieldModel) {
    // Get data necessary to rerender property before it is unavailable.
    var html = fieldModel.get('html');
    var $fieldWrapper = $(fieldModel.get('el'));
    var $context = $fieldWrapper.parent();

    // First set the state to 'candidate', to allow all attached views to
    // clean up all their "active state"-related changes.
    fieldModel.set('state', 'candidate');

    // Set the field's state to 'inactive', to enable the updating of its DOM
    // value.
    fieldModel.set('state', 'inactive', { reason: 'rerender' });

    // Destroy the field model; this will cause all attached views to be
    // destroyed too, and removal from all collections in which it exists.
    fieldModel.destroy();

    // Replace the old content with the new content.
    $fieldWrapper.replaceWith(html);

    // Attach behaviors again to the modified piece of HTML; this will create
    // a new field model and call rerenderedFieldToCandidate() with it.
    Drupal.attachBehaviors($context);
  },

  /**
   * If the new in-place editable field is for the entity that's currently
   * being edited, then transition it to the 'candidate' state.
   *
   * This happens when a field was modified, saved and hence rerendered.
   *
   * @param Drupal.edit.FieldModel fieldModel
   *   A field that was just added to the collection of fields.
   */
  rerenderedFieldToCandidate: function (fieldModel) {
    var activeEntity = Drupal.edit.collections.entities.where({ isActive: true })[0];

    // Early-return if there is no active entity.
    if (activeEntity === null) {
      return;
    }

    // If the field's entity is the active entity, make it a candidate.
    if (fieldModel.get('entity') === activeEntity) {
      this.setupEditor(fieldModel);
      fieldModel.set('state', 'candidate');
    }

    // If the field change was only saved to tempstore, mark the field as
    // changed. The changed marker will be cleared when the
    // Drupal.edit.app.AppView.prototype.save() method is called.
    for (var i = 0, fields = this.changedFieldsInTempstore; i < fields.length; i++) {
      var changedFieldModel = fields[i];
      if (changedFieldModel === fieldModel.id) {
        var $field = $(fieldModel.get('el'));
        if ($field.is('.edit-editable')) {
          $field.addClass('edit-changed');
        }
        else {
          $field.find('.edit-editable').addClass('edit-changed');
        }
      }
    }
  },

  /**
   * EntityModel Collection change handler, called on change:isActive, enforces
   * a single active entity.
   *
   * @param Drupal.edit.EntityModel
   *   The entityModel instance whose active state has changed.
   */
  enforceSingleActiveEntity: function (changedEntityModel) {
    // When an entity is deactivated, we don't need to enforce anything.
    if (changedEntityModel.get('isActive') === false) {
      return;
    }

    // This entity was activated; deactivate all other entities.
    changedEntityModel.collection.chain()
      .filter(function (entityModel) {
        return entityModel.get('isActive') === true && entityModel !== changedEntityModel;
      })
      .each(function (entityModel) {
        entityModel.set('isActive', false);
      });
  }
});

}(jQuery, _, Backbone, Drupal, drupalSettings));
