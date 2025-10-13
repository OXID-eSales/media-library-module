import FileManager from './fileManager.js';

class AltAttributeManager {
    constructor(actionLink = '') {
        this.fileManager = new FileManager(actionLink);
        this.altTexts = {};
    }

    loadAltTexts(objectId, detailForm, actionLink) {
        const $detailForm = $(detailForm);
        this._showAltTextLoading($detailForm);
        this._hideMessages($detailForm);
        this.fileManager.setActionLink(actionLink);
        this.altTexts = {};
        return this.fileManager.getAltTexts(objectId)
            .then(data => {
                this.altTexts = data.altTexts || {};
                this._renderAltTextInputs($detailForm);
            })
            .catch(error => {
                this.altTexts = {};
                this._renderAltTextInputs($detailForm);
            })
            .finally(() => {
                this._hideAltTextLoading($detailForm);
            });
    }

    bindAltTextEvents(detailForm, actionLink) {
        const $detailForm = $(detailForm);
        const langSelect = $detailForm.find('.dd-media-alttext-language');
        const altInputsContainer = $detailForm.find('.dd-media-alttext-inputs');
        const saveBtn = $detailForm.find('.dd-media-alttext-save-btn');
        const errorDiv = $detailForm.find('.dd-media-alttext-error');
        const successDiv = $detailForm.find('.dd-media-alttext-success');

        langSelect.off('change.alttext');
        saveBtn.off('click.alttext');

        langSelect.on('change.alttext', () => {
            const selectedLang = langSelect.val();
            altInputsContainer.children().each(function () {
                $(this).toggle(this.dataset.langId == selectedLang);
            });
        });

        saveBtn.on('click.alttext', () => {
            const objectId = $detailForm[0].dataset.mediaId;
            const altTexts = {};
            altInputsContainer.find('input.dd-media-alttext-input').each(function () {
                altTexts[$(this).data('langId')] = $(this).val();
            });
            this._showAltTextLoading($detailForm);
            saveBtn.prop('disabled', true);
            errorDiv.hide().text('');
            successDiv.hide().text('');
            this.fileManager.setActionLink(actionLink);
            this.fileManager.saveAltText(objectId, altTexts)
                .then(data => {
                    if (data.success) {
                        successDiv.text(data.message).show();
                    } else {
                        errorDiv.text(window.OXID_TRANSLATIONS.DD_MEDIA_ALT_TEXT_SAVE_ERROR).show();
                    }
                    setTimeout(() => {
                        errorDiv.fadeOut();
                        successDiv.fadeOut();
                    }, 4000);
                })
                .catch(() => {
                    errorDiv.text(window.OXID_TRANSLATIONS.DD_MEDIA_ALT_TEXT_SAVE_ERROR).show();
                    setTimeout(() => {
                        errorDiv.fadeOut();
                    }, 4000);
                })
                .finally(() => {
                    this._hideAltTextLoading($detailForm);
                    saveBtn.prop('disabled', false);
                });
        });
    }

    _renderAltTextInputs($detailForm) {
        const langSelect = $detailForm.find('.dd-media-alttext-language').get(0);
        const altInputsContainer = $detailForm.find('.dd-media-alttext-inputs').get(0);
        if (!langSelect || !altInputsContainer) return;
        altInputsContainer.innerHTML = '';
        const ALT_TEXT_LABEL = window.OXID_TRANSLATIONS.DD_MEDIA_ALT_TEXT;
        const ALT_TEXT_PLACEHOLDER = window.OXID_TRANSLATIONS.DD_MEDIA_ALT_TEXT_PLACEHOLDER;
        const languages = Array.from(langSelect.options).map(opt => ({
            id: opt.value,
            name: opt.textContent
        }));
        languages.forEach(lang => {
            const inputId = `media-alttext-input-${lang.id}`;
            const label = document.createElement('label');
            label.setAttribute('for', inputId);
            label.textContent = ALT_TEXT_LABEL;
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control dd-media-alttext-input';
            input.dataset.langId = lang.id;
            input.id = inputId;
            input.placeholder = ALT_TEXT_PLACEHOLDER;
            input.value = this.altTexts[String(lang.id)] || '';
            const isSelected = langSelect.value == lang.id;
            input.style.display = isSelected ? '' : 'none';
            label.style.display = isSelected ? '' : 'none';
            altInputsContainer.appendChild(label);
            altInputsContainer.appendChild(input);
        });
    }

    _showAltTextLoading($detailForm) {
        const altInputsContainer = $detailForm.find('.dd-media-alttext-inputs');
        if (altInputsContainer.find('.dd-alttext-spinner').length === 0) {
            altInputsContainer.append('<div class="dd-alttext-spinner text-center my-2"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></div>');
        }
    }

    _hideAltTextLoading($detailForm) {
        $detailForm.find('.dd-alttext-spinner').remove();
    }

    _hideMessages($detailForm) {
        const errorDiv = $detailForm.find('.dd-media-alttext-error');
        const successDiv = $detailForm.find('.dd-media-alttext-success');
        errorDiv.hide().text('');
        successDiv.hide().text('');
    }
}

export default AltAttributeManager;
