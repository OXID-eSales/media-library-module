class AltAttributeManager {

    loadAltTexts(objectId, $detailForm, actionLink) {
        this._showAltTextLoading($detailForm);
        this._setAltTextSaveDisabled($detailForm, true);
        return this._fetchJSON(actionLink + 'cl=ddoemedialibrary_media_alt_text&fnc=getAltTexts&objectId=' + encodeURIComponent(objectId))
            .then(data => {
                this.altTexts = data.altTexts || {};
                this._renderAltTextInputs($detailForm);
            })
            .finally(() => {
                this._hideAltTextLoading($detailForm);
                this._setAltTextSaveDisabled($detailForm, false);
            });
    }

    bindAltTextEvents($detailForm, actionLink) {
        const langSelect = $detailForm.find('.dd-media-alttext-language');
        const altInputsContainer = $detailForm.find('.dd-media-alttext-inputs');
        const saveBtn = $detailForm.find('.dd-media-alttext-save-btn');
        const errorDiv = $detailForm.find('.dd-media-alttext-error');
        const successDiv = $detailForm.find('.dd-media-alttext-success');

        // Remove previous event handlers to prevent duplicates
        langSelect.off('change.alttext');
        saveBtn.off('click.alttext');

        // Show/hide the correct input when language changes
        langSelect.on('change.alttext', () => {
            const selectedLang = langSelect.val();
            altInputsContainer.children().each(function() {
                $(this).toggle(this.dataset.langId == selectedLang);
            });
        });

        // Save alt texts when the save button is clicked
        saveBtn.on('click.alttext', () => {
            const objectId = $detailForm.data('media-id');
            const altTexts = {};
            // Collect all alt text values by language
            altInputsContainer.find('input.dd-media-alttext-input').each(function() {
                altTexts[$(this).data('langId')] = $(this).val();
            });

            // Show loading spinner and disable save button
            this._showAltTextLoading($detailForm);
            this._setAltTextSaveDisabled($detailForm, true);
            errorDiv.hide().text('');
            successDiv.hide().text('');

            // Prepare form data for AJAX
            const formData = new URLSearchParams();
            formData.append('objectId', objectId);
            Object.entries(altTexts).forEach(([langId, value]) => {
                formData.append(`altTexts[${langId}]`, value);
            });

            // Send AJAX request to save alt texts
            fetch(actionLink + 'cl=ddoemedialibrary_media_alt_text&fnc=saveAltText', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString(),
                credentials: 'same-origin'
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        successDiv.text(data.message).show();
                    } else {
                        errorDiv.text(window.OXID_TRANSLATIONS?.DD_MEDIA_ALT_TEXT_SAVE_ERROR || 'Failed to save alt texts.').show();
                    }
                    setTimeout(() => {
                        errorDiv.fadeOut();
                        successDiv.fadeOut();
                    }, 4000);
                })
                .catch(() => {
                    errorDiv.text(window.OXID_TRANSLATIONS?.DD_MEDIA_ALT_TEXT_SAVE_ERROR || 'Failed to save alt texts.').show();
                    setTimeout(() => {
                        errorDiv.fadeOut();
                    }, 4000);
                })
                .finally(() => {
                    this._hideAltTextLoading($detailForm);
                    this._setAltTextSaveDisabled($detailForm, false);
                    this.loadAltTexts(objectId, $detailForm, actionLink);
                });
        });
    }

    _renderAltTextInputs($detailForm) {
        const langSelect = $detailForm.find('.dd-media-alttext-language').get(0);
        const altInputsContainer = $detailForm.find('.dd-media-alttext-inputs').get(0);
        if (!langSelect || !altInputsContainer) return;
        altInputsContainer.innerHTML = '';

        // Get translation strings from global variable set by Twig
        const ALT_TEXT_LABEL = window.OXID_TRANSLATIONS?.DD_MEDIA_ALT_TEXT || 'Alt text';
        const ALT_TEXT_PLACEHOLDER = window.OXID_TRANSLATIONS?.DD_MEDIA_ALT_TEXT_PLACEHOLDER || 'Enter alt text...';

        // Get languages from the select options
        const languages = Array.from(langSelect.options).map(opt => ({
            id: opt.value,
            name: opt.textContent
        }));

        // Render an input for each language
        languages.forEach(lang => {
            const inputId = `media-alttext-input-${lang.id}`;

            // Create label
            const label = document.createElement('label');
            label.setAttribute('for', inputId);
            label.textContent = ALT_TEXT_LABEL;

            // Create input
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control dd-media-alttext-input ';
            input.dataset.langId = lang.id;
            input.id = inputId;
            input.placeholder = ALT_TEXT_PLACEHOLDER;
            input.value = this.altTexts[String(lang.id)] || '';

            // Show only the input for the selected language
            const isSelected = langSelect.value == lang.id;
            input.style.display = isSelected ? '' : 'none';
            label.style.display = isSelected ? '' : 'none';

            altInputsContainer.appendChild(label);
            altInputsContainer.appendChild(input);
        });
    }

    _showAltTextLoading($detailForm) {
        const altInputsContainer = $detailForm.find('.dd-media-alttext-inputs');
        altInputsContainer.html('<div class="dd-alttext-spinner text-center my-2"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></div>');
    }
    _hideAltTextLoading($detailForm) {
        $detailForm.find('.dd-alttext-spinner').remove();
    }

    _setAltTextSaveDisabled($detailForm, disabled) {
        $detailForm.find('.dd-media-alttext-save-btn').prop('disabled', disabled);
    }

    _fetchJSON(url, options) {
        // Always send cookies for session authentication
        return fetch(url, Object.assign({ credentials: 'same-origin' }, options)).then(r => r.json());
    }
}

export default AltAttributeManager;
