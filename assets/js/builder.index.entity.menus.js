/*
 * Builder Index controller Menus entity controller
 */
+function ($) { "use strict";

    if ($.wn.builder === undefined)
        $.wn.builder = {}

    if ($.wn.builder.entityControllers === undefined)
        $.wn.builder.entityControllers = {}

    var Base = $.wn.builder.entityControllers.base,
        BaseProto = Base.prototype

    var Menus = function(indexController) {
        Base.call(this, 'menus', indexController)
    }

    Menus.prototype = Object.create(BaseProto)
    Menus.prototype.constructor = Menus

    // PUBLIC METHODS
    // ============================

    Menus.prototype.cmdOpenMenus = function(ev) {
        var currentPlugin = this.getSelectedPlugin()

        if (!currentPlugin) {
            alert('Please select a plugin first')
            return
        }

        this.indexController.openOrLoadMasterTab($(ev.target), 'onMenusOpen', this.makeTabId(currentPlugin))
    }

    Menus.prototype.cmdSaveMenus = function(ev) {
        var $target = $(ev.currentTarget),
            $form = $target.closest('form'),
            $inspectorContainer = $form.find('.inspector-container')

        if (!$.wn.inspector.manager.applyValuesFromContainer($inspectorContainer)) {
            return
        }

        var menus = $.wn.builder.menubuilder.controller.getJson($form.get(0))

        $target.request('onMenusSave', {
            data: {
                menus: menus
            }
        }).done(
            this.proxy(this.saveMenusDone)
        )
    }

    Menus.prototype.cmdAddMainMenuItem = function(ev) {
        $.wn.builder.menubuilder.controller.addMainMenuItem(ev)
    }

    Menus.prototype.cmdAddSideMenuItem = function(ev) {
        $.wn.builder.menubuilder.controller.addSideMenuItem(ev)
    }

    Menus.prototype.cmdDeleteMenuItem = function(ev) {
        $.wn.builder.menubuilder.controller.deleteMenuItem(ev)
    }

    // INTERNAL METHODS
    // ============================

    Menus.prototype.saveMenusDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()

        this.getIndexController().unchangeTab($masterTabPane)
    }

    // REGISTRATION
    // ============================

    $.wn.builder.entityControllers.menus = Menus;

}(window.jQuery);