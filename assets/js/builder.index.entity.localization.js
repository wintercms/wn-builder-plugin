/*
 * Builder Index controller Localization entity controller
 */
+function ($) { "use strict";

    if ($.wn.builder === undefined)
        $.wn.builder = {}

    if ($.wn.builder.entityControllers === undefined)
        $.wn.builder.entityControllers = {}

    var Base = $.wn.builder.entityControllers.base,
        BaseProto = Base.prototype

    var Localization = function(indexController) {
        Base.call(this, 'localization', indexController)
    }

    Localization.prototype = Object.create(BaseProto)
    Localization.prototype.constructor = Localization

    // PUBLIC METHODS
    // ============================

    Localization.prototype.cmdCreateLanguage = function(ev) {
        this.indexController.openOrLoadMasterTab($(ev.target), 'onLanguageCreateOrOpen', this.newTabId())
    }

    Localization.prototype.cmdOpenLanguage = function(ev) {
        var language = $(ev.currentTarget).data('id'),
            pluginCode = $(ev.currentTarget).data('pluginCode')

        this.indexController.openOrLoadMasterTab($(ev.target), 'onLanguageCreateOrOpen', this.makeTabId(pluginCode+'-'+language), {
            original_language: language
        })
    }

    Localization.prototype.cmdSaveLanguage = function(ev) {
        var $target = $(ev.currentTarget),
            $form = $target.closest('form')

        $target.request('onLanguageSave').done(
            this.proxy(this.saveLanguageDone)
        )
    }

    Localization.prototype.cmdDeleteLanguage = function(ev) {
        var $target = $(ev.currentTarget)
        $.wn.confirm($target.data('confirm'), this.proxy(this.deleteConfirmed))
    }

    Localization.prototype.cmdCopyMissingStrings = function(ev) {
        var $form = $(ev.currentTarget),
            language = $form.find('select[name=language]').val(),
            $masterTabPane = this.getMasterTabsActivePane()

        $form.trigger('close.oc.popup')

        $.wn.stripeLoadIndicator.show()
        $masterTabPane.find('form').request('onLanguageCopyStringsFrom', {
            data: {
                copy_from: language
            }
        }).always(
            $.wn.builder.indexController.hideStripeIndicatorProxy
        ).done(
            this.proxy(this.copyStringsFromDone)
        )
    }

    // EVENT HANDLERS
    // ============================

    // INTERNAL BUILDER API
    // ============================

    Localization.prototype.languageUpdated = function(plugin) {
        var languageForm = this.findDefaultLanguageForm(plugin)

        if (!languageForm) {
            return
        }

        var $languageForm = $(languageForm)

        if (!$languageForm.hasClass('oc-data-changed')) {
            this.updateLanguageFromServer($languageForm)
        }
        else {
            // If there are changes - merge language from server
            // in the background. As this operation is not 100% 
            // reliable, it could be a good idea to display a
            // warning when the user navigates to the tab.

            this.mergeLanguageFromServer($languageForm)
        }
    }

    Localization.prototype.updateOnScreenStrings = function(plugin) {
        var stringElements = document.body.querySelectorAll('span[data-localization-key][data-plugin="'+plugin+'"]')

        $.wn.builder.dataRegistry.get($('#builder-plugin-selector-panel form'), plugin, 'localization', null, function(data){
            for (var i=stringElements.length-1; i>=0; i--) {
                var stringElement = stringElements[i],
                    stringKey = stringElement.getAttribute('data-localization-key')

                if (data[stringKey] !== undefined) {
                    stringElement.textContent = data[stringKey]
                }
                else {
                    stringElement.textContent = stringKey
                }
            }
        })
    }

    // INTERNAL METHODS
    // ============================

    Localization.prototype.saveLanguageDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()
        
        $masterTabPane.find('input[name=original_language]').val(data.builderResponseData.language)
        this.updateMasterTabIdAndTitle($masterTabPane, data.builderResponseData)
        this.unhideFormDeleteButton($masterTabPane)

        this.getLanguageList().fileList('markActive', data.builderResponseData.tabId)
        this.getIndexController().unchangeTab($masterTabPane)

        if (data.builderResponseData.registryData !== undefined) {
            var registryData = data.builderResponseData.registryData

            $.wn.builder.dataRegistry.set(registryData.pluginCode, 'localization', null, registryData.strings, {suppressLanguageEditorUpdate: true})
            $.wn.builder.dataRegistry.set(registryData.pluginCode, 'localization', 'sections', registryData.sections)
        }
    }

    Localization.prototype.getLanguageList = function() {
        return $('#layout-side-panel form[data-content-id=localization] [data-control=filelist]')
    }

    Localization.prototype.getCodeEditor = function($tab) {
        // Returns the Monaco wrapper (not the raw editor) to use wrapper methods
        // The wrapper provides setValue(), getValue(), insert(), etc.
        return $tab.find('div[data-field-name=strings] div[data-control=codeeditor]').data('oc.codeEditor')
    }

    Localization.prototype.deleteConfirmed = function() {
        var $masterTabPane = this.getMasterTabsActivePane(),
            $form = $masterTabPane.find('form')

        $.wn.stripeLoadIndicator.show()
        $form.request('onLanguageDelete').always(
            $.wn.builder.indexController.hideStripeIndicatorProxy
        ).done(
            this.proxy(this.deleteDone)
        )
    }

    Localization.prototype.deleteDone = function() {
        var $masterTabPane = this.getMasterTabsActivePane()

        this.getIndexController().unchangeTab($masterTabPane)
        this.forceCloseTab($masterTabPane)
    }

    Localization.prototype.copyStringsFromDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var responseData = data.builderResponseData,
            $masterTabPane = this.getMasterTabsActivePane(),
            $form = $masterTabPane.find('form'),
            codeEditorWrapper = this.getCodeEditor($masterTabPane),
            newStringMessage = $form.data('newStringMessage'),
            mismatchMessage = $form.data('structureMismatch')

        // Use Monaco wrapper API instead of ACE's getSession()
        codeEditorWrapper.setValue(responseData.strings)

        // Convert ACE annotations to Monaco decorations (visual highlights)
        // ACE uses 0-indexed rows, Monaco uses 1-indexed lines
        // Using decorations instead of markers to avoid error-like squiggly underlines
        var decorations = []
        for (var i=responseData.updatedLines.length-1; i>=0; i--) {
            var line = responseData.updatedLines[i]

            decorations.push({
                range: new codeEditorWrapper.monaco.Range(
                    line + 1,           // Convert 0-indexed to 1-indexed
                    1,                  // Start column
                    line + 1,           // End line (same line)
                    Number.MAX_VALUE    // End column (end of line)
                ),
                options: {
                    isWholeLine: true,
                    className: 'builder-new-translation-line',         // Background highlight
                    linesDecorationsClassName: 'builder-new-translation-gutter',  // Gutter indicator
                    hoverMessage: { value: newStringMessage }          // Tooltip on hover
                }
            })
        }

        // Set decorations using wrapper method
        codeEditorWrapper.setDecorations('builderLocalization', decorations)

        if (responseData.mismatch) {
            $.wn.alert(mismatchMessage)
        }
    }

    Localization.prototype.findDefaultLanguageForm = function(plugin) {
        var forms = document.body.querySelectorAll('form[data-entity=localization]')

        for (var i=forms.length-1; i>=0; i--) {
            var form = forms[i],
                pluginInput = form.querySelector('input[name=plugin_code]'),
                languageInput = form.querySelector('input[name=original_language]')

            if (!pluginInput || pluginInput.value != plugin) {
                continue
            }

            if (!languageInput) {
                continue
            }

            if (form.getAttribute('data-default-language') == languageInput.value) {
                return form
            }
        }

        return null
    }

    Localization.prototype.updateLanguageFromServer = function($languageForm) {
        var self = this

        $languageForm.request('onLanguageGetStrings').done(function(data) {
            self.updateLanguageFromServerDone($languageForm, data)
        })
    }

    Localization.prototype.updateLanguageFromServerDone = function($languageForm, data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var responseData = data.builderResponseData,
            $tabPane = $languageForm.closest('.tab-pane'),
            codeEditorWrapper = this.getCodeEditor($tabPane)

        if (!responseData.strings) {
            return
        }

        // Use Monaco wrapper API
        codeEditorWrapper.setValue(responseData.strings)
        this.unmodifyTab($tabPane)
    }

    Localization.prototype.mergeLanguageFromServer = function($languageForm) {
        var language = $languageForm.find('input[name=original_language]').val(),
            self = this

        $languageForm.request('onLanguageCopyStringsFrom', {
            data: {
                copy_from: language
            }
        }).done(function(data) {
            self.mergeLanguageFromServerDone($languageForm, data)
        })
    }

    Localization.prototype.mergeLanguageFromServerDone = function($languageForm, data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var responseData = data.builderResponseData,
            $tabPane = $languageForm.closest('.tab-pane'),
            codeEditorWrapper = this.getCodeEditor($tabPane)

        // Use Monaco wrapper API
        codeEditorWrapper.setValue(responseData.strings)
        // Clear any decorations
        codeEditorWrapper.setDecorations('builderLocalization', [])
    }

    // REGISTRATION
    // ============================

    $.wn.builder.entityControllers.localization = Localization;

}(window.jQuery);