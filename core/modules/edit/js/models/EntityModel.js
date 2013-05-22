(function (_, $, Backbone, Drupal) {

"use strict";

/**
 * State of an in-place editable entity in the DOM.
 */
Drupal.edit.EntityModel = Backbone.Model.extend({

  defaults: {
    // The DOM element that represents this entity. It may seem bizarre to
    // have a DOM element in a Backbone Model, but we need to be able to map
    // entities in the DOM to EntityModels in memory.
    el: null,
    // An entity ID, of the form "<entity type>/<entity ID>", e.g. "node/1".
    id: null,
    // The label of the entity.
    label: null,
    // A Drupal.edit.FieldCollection for all fields of this entity.
    fields: null,

    // The attributes below are stateful. The ones above will never change
    // during the life of a EntityModel instance.

    // Indicates whether this instance of this entity is currently being
    // edited in-place.
    isActive: false,
    //
    isDirty: false,
    // The current processing state of an entity.
    state: 'inactive',
    // @see AppView.appStateChange()
    entityToolbar: null
  },

  /**
   * @inheritdoc
   */
  initialize: function () {
    this.set('fields', new Drupal.edit.FieldCollection());

    // Respond to state changes.
    this.on('change:state', this.stateChange, this);

    // Respond to field view changes.
    this.on('viewChanged', this.viewChange, this);

    // The state of the entity is largely dependent on the state of its
    // fields.
    this.get('fields').on('change:state', this.fieldStateChange, this);
  },

  /**
   * @todo We need to restrict the state progression of an entity.
   * @todo We need a special exception to go from anything to deactivating.
   */
  stateChange: function (entityModel, state, options) {
    var from = entityModel.previous('state');
    var to = state;
    switch (to) {
      case 'deactivating':
        // Return the fields to candidate state. A changed field may have to go
        // through confirmation first.
        entityModel.get('fields').each(function (fieldModel) {
          // If the field is already in the candidate state, trigger a change
          // event so that the entityModel can move to the next state in
          // deactivation.
          if (_.intersection([fieldModel.get('state')], ['candidate', 'highlighted']).length) {
            fieldModel.trigger('change:state', fieldModel, fieldModel.get('state'), options);
          }
          else {
            fieldModel.set('state', 'candidate', options);
          }
        });
        break;
      case 'closing':
        _.extend(options, {
            reason: 'stop'
          });
        this.get('fields').each(function (fieldModel) {
          fieldModel.set('state', 'inactive', options);
        });
        break;
      case 'closed':
        this.set('isActive', false);
        this.get('fields').each(function (fieldModel) {
          // fieldModel.destroy();
        });
        break;
      case 'launching':
        break;
      case 'opening':
        // Set the fields to candidate state.
        entityModel.get('fields').each(function (fieldModel) {
          fieldModel.set('state', 'candidate', options);
        });
        break;
      case 'opened':
        this.set('isActive', true);
        break;
      case 'committing':
        this.get('fields').chain()
          .filter(function (fieldModel) {
            return _.intersection([fieldModel.get('state')], Drupal.edit.app.changedEditorStates).length;
          })
          .each(function (fieldModel) {
            fieldModel.set('state', 'saving', options);
          });
        break;
      case 'quitcommitting':
        this.get('fields').chain()
          .filter(function (fieldModel) {
            return _.intersection([fieldModel.get('state')], Drupal.edit.app.changedEditorStates).length;
          })
          .each(function (fieldModel) {
            fieldModel.set('state', 'saving', options);
          });
        break;
      case 'activating':
        break;
      case 'active':
        break;
      case 'changed':
        break;
      case 'saving':
        break;
      case 'saved':
        break;
      case 'invalid':
        break;
    }
  },

  /**
   *
   */
  fieldStateChange: function (fieldModel, state, options) {
    var from = fieldModel.previous('state');
    var to = state;
    var entityModel = this;
    // Switch on the entityModel state.
    switch (this.get('state')) {
      case 'deactivating':
        // If the entity is set to closing, it must first confirm that all of its
        // fieldModels have returned to the candidate state before it can start
        // deactivating.
        var mayDeactivate = true;
        this.get('fields').each(function (fieldModel) {
          if (!_.intersection([fieldModel.get('state')], ['candidate', 'highlighted']).length) {
            mayDeactivate = false;
          }
        });
        if (mayDeactivate) {
          _.defer(function () {
            entityModel.set('state', 'closing')
          });
        }
        break;
      case 'closing':
        // If the entity is set to deactivating, it must first confirm that all of
        // its fieldModels have returned to inactive before it can transition to
        // inactive.
        var mayClose = true;
        this.get('fields').each(function (fieldModel) {
          if (fieldModel.get('state') !== 'inactive') {
            mayClose = false;
          }
        });
        if (mayClose) {
          _.defer(function () {
            entityModel.set('state', 'closed');
          })

        }
        break;
      case 'opening':
        // If the entity is set to opening, it must first confirm that all of
        // its fieldModels have transitioned to the candidate state before it
        // can declare that it is open.
        var mayOpen = true;
        this.get('fields').each(function (fieldModel) {
        if (!_.intersection([fieldModel.get('state')], ['candidate', 'highlighted']).length) {
            mayOpen = false;
          }
        });
        if (mayOpen) {
          _.defer(function () {
            entityModel.set('state', 'opened');
          });
        }
        break;
      case 'committing':
        var mayCommit = true;
        this.get('fields').each(function (fieldModel) {
          if (!_.intersection([fieldModel.get('state')], ['candidate', 'highlighted']).length) {
            mayCommit = false;
          }
        });
        if (mayCommit) {
          _.defer(function () {
            entityModel.save({
              callback: function () {
                entityModel.set('state', 'opened');
              }
            });
          })
        }
        break;
      case 'quitcommitting':
        var mayCommit = true;
        this.get('fields').each(function (fieldModel) {
          if (!_.intersection([fieldModel.get('state')], ['candidate', 'highlighted']).length) {
            mayCommit = false;
          }
        });
        if (mayCommit) {
          _.defer(function () {
            entityModel.save({
              callback: function () {
                entityModel.set('state', 'deactivating');
              }
            });
          })
        }
        break;
    }

    // Switch on the fieldModel state.
    switch (to) {
      case 'candidate':
        this.set('isDirty', false);
        break;
      case 'activating':
        break;
      case 'active':
        break;
      case 'changed':
        // The EntityToolbarView is using the isDirty attribute to reposition
        // the toolbar. This is a legacy holdout.
        this.set('isDirty', true);
        break;
      case 'saving':
        break;
      case 'saved':
        this.set('isDirty', false);
        break;
      case 'invalid':
        break;
    }
  },

  /**
   * Fires an AJAX request to the REST save URL for an entity.
   */
  save: function (options) {
    var entityModel = this;
    var id = 'edit-save-entity';
    // Create a temporary element to be able to use Drupal.ajax.
    var $el = $('#edit-entity-toolbar').find('.action-save'); // This is the span element inside the button.
    // Create a Drupal.ajax instance to load the form.
    Drupal.ajax[id] = new Drupal.ajax(id, $el, {
      url: drupalSettings.basePath + 'edit/entity/' + entityModel.id,
      event: 'edit-save.edit',
      progress: {
        type: 'none'
      },
      error: function (e) {
        // Clean up.
        $el.unbind('edit-save.edit');
        throw new Error(e);
      }
    });
    // Entity saved successfully.
    Drupal.ajax[id].commands.editEntitySaved = function(ajax, response, status) {
      // Remove the changed marker from all of the fields.
      entityModel.get('fields').each(function (fieldModel) {
        $(fieldModel.get('el')).find('.edit-editable').addBack().removeClass('edit-changed');
      });
      // Reset the list tracking changed fields.
      entityModel.changedFieldsInTempstore = [];
      // Clear the dirty flag on the entity.
      var savedEntity = Drupal.edit.collections.entities.get(response.data.entity_type + '/' + response.data.entity_id);
      if (savedEntity && 'set' in savedEntity) {
        savedEntity.set('isDirty', false);
      }
      // Clean up.
      $(ajax.element).unbind('edit-save.edit');

      if ('callback' in options && typeof options.callback === 'function') {
        options.callback.call(entityModel);
      }
    };
    $el.trigger('edit-save.edit');
  },

  /**
   * @inheritdoc
   */
  destroy: function (options) {
    Backbone.Model.prototype.destroy.apply(this, options);

    // Destroy all fields of this entity.
    // @todo that app should be responisble for destroying the fields.
    this.get('fields').each(function (fieldModel) {
      fieldModel.destroy();
    });
  },

  /**
   *
   */
  viewChange: function (view) {
    this.trigger('fieldViewChange', view);
  },

  /**
   * {@inheritdoc}
   */
  sync: function () {
    // We don't use REST updates to sync.
    return;
  }

});

Drupal.edit.EntityCollection = Backbone.Collection.extend({
  model: Drupal.edit.EntityModel
});

}(_, jQuery, Backbone, Drupal));
