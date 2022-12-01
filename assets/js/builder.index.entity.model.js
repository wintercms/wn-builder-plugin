/*
 * Builder Index controller Model entity controller
 */
+function ($) { "use strict";

    if ($.wn.builder === undefined)
        $.wn.builder = {}

    if ($.wn.builder.entityControllers === undefined)
        $.wn.builder.entityControllers = {}

    var Base = $.wn.builder.entityControllers.base,
        BaseProto = Base.prototype

    var Model = function(indexController) {
        Base.call(this, 'model', indexController)
    }

    Model.prototype = Object.create(BaseProto)
    Model.prototype.constructor = Model

    // PUBLIC METHODS
    // ============================

    Model.prototype.cmdCreateModel = function(ev) {
        var $target = $(ev.currentTarget)

        $target.one('shown.oc.popup', this.proxy(this.onModelPopupShown))

        $target.popup({
            handler: 'onModelLoadPopup'
        })
    }

    Model.prototype.cmdDeleteModel = function(ev) {
        var $target = $(ev.currentTarget),
            model = $target.data('modelClass')

        $.wn.confirm($target.data('confirm'), () => {
            $.request('onModelDelete', {
                data: {
                    model: model,
                },
                flash: true
            })
        })
    }

    Model.prototype.cmdApplyModelSettings = function(ev) {
        var $form = $(ev.currentTarget),
            self = this

        $.wn.stripeLoadIndicator.show()
        $form.request('onModelSave').always(
            $.wn.builder.indexController.hideStripeIndicatorProxy
        ).done(function(data){
            $form.trigger('close.oc.popup')

            self.applyModelSettingsDone(data)
        })
    }

    // EVENT HANDLERS
    // ============================

    Model.prototype.onModelPopupShown = function(ev, button, popup) {
        $(popup).find('input[name=className]').focus()
    }

    // INTERNAL METHODS
    // ============================

    Model.prototype.applyModelSettingsDone = function(data) {
        if (data.builderResponseData.registryData !== undefined) {
            var registryData = data.builderResponseData.registryData

            $.wn.builder.dataRegistry.set(registryData.pluginCode, 'model-classes', null, registryData.models)
        }
    }

    // REGISTRATION
    // ============================

    $.wn.builder.entityControllers.model = Model;

}(window.jQuery);
