/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {inject, injectable} from 'tsyringe';
import {AltTextManagerInterface} from './AltTextManagerInterface.ts';
import {MediaRequestInterface} from '../media/MediaRequestInterface.ts';
import {LanguageInterface} from '../language/LanguageInterface.ts';
import type {AltTexts} from '../media/types/AltTexts.ts';

const MESSAGE_DURATION = 4000;

type AltTextElements = {
    languageSelect: HTMLSelectElement;
    inputs: HTMLElement;
    saveButton: HTMLButtonElement;
    error: HTMLElement;
    success: HTMLElement;
};

@injectable()
export default class AltTextManager implements AltTextManagerInterface {
    #objectId = '';
    #listeners: AbortController | null = null;
    #messageTimer: ReturnType<typeof setTimeout> | undefined;

    constructor(
        @inject(MediaRequestInterface) private readonly mediaRequest: MediaRequestInterface,
        @inject(LanguageInterface) private readonly language: LanguageInterface,
    ) {}

    async show(objectId: string, detailForm: HTMLElement): Promise<void> {
        const elements = this.#findElements(detailForm);
        if (!elements) {
            return;
        }

        this.#objectId = objectId;
        this.#bindEvents(objectId, elements);
        this.#hideMessages(elements);
        this.#showLoading(elements);

        let altTexts: AltTexts = {};
        try {
            altTexts = await this.mediaRequest.getAltTexts(objectId);
        } catch {
            // MediaRequest logs the error, show empty inputs.
        }

        if (this.#objectId !== objectId) {
            return;
        }

        this.#renderInputs(elements, altTexts);
    }

    #findElements(detailForm: HTMLElement): AltTextElements | null {
        const languageSelect = detailForm.querySelector<HTMLSelectElement>('.dd-media-alttext-language');
        const inputs = detailForm.querySelector<HTMLElement>('.dd-media-alttext-inputs');
        const saveButton = detailForm.querySelector<HTMLButtonElement>('.dd-media-alttext-save-btn');
        const error = detailForm.querySelector<HTMLElement>('.dd-media-alttext-error');
        const success = detailForm.querySelector<HTMLElement>('.dd-media-alttext-success');
        if (!languageSelect || !inputs || !saveButton || !error || !success) {
            return null;
        }

        return {languageSelect, inputs, saveButton, error, success};
    }

    #bindEvents(objectId: string, elements: AltTextElements): void {
        this.#listeners?.abort();
        this.#listeners = new AbortController();
        const {signal} = this.#listeners;

        elements.languageSelect.addEventListener('change', () => {
            this.#showSelectedLanguage(elements);
        }, {signal});

        elements.saveButton.addEventListener('click', () => {
            void this.#save(objectId, elements);
        }, {signal});
    }

    #renderInputs(elements: AltTextElements, altTexts: AltTexts): void {
        elements.inputs.replaceChildren(...Array.from(elements.languageSelect.options, option => {
            const inputId = `media-alttext-input-${option.value}`;

            const label = document.createElement('label');
            label.htmlFor = inputId;
            label.textContent = this.language.translate('DD_MEDIA_ALT_TEXT');

            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control dd-media-alttext-input';
            input.id = inputId;
            input.dataset.langId = option.value;
            input.placeholder = this.language.translate('DD_MEDIA_ALT_TEXT_PLACEHOLDER');
            input.value = altTexts[option.value] ?? '';

            const wrapper = document.createElement('div');
            wrapper.className = 'dd-media-alttext-input-wrapper';
            wrapper.dataset.langId = option.value;
            wrapper.append(label, input);

            return wrapper;
        }));

        this.#showSelectedLanguage(elements);
    }

    #showSelectedLanguage(elements: AltTextElements): void {
        const selected = elements.languageSelect.value;
        elements.inputs.querySelectorAll<HTMLElement>('.dd-media-alttext-input-wrapper').forEach(wrapper => {
            wrapper.style.display = wrapper.dataset.langId === selected ? '' : 'none';
        });
    }

    async #save(objectId: string, elements: AltTextElements): Promise<void> {
        const altTexts: AltTexts = {};
        elements.inputs.querySelectorAll<HTMLInputElement>('input.dd-media-alttext-input').forEach(input => {
            altTexts[input.dataset.langId ?? ''] = input.value;
        });

        this.#hideMessages(elements);
        this.#showLoading(elements);
        elements.saveButton.disabled = true;

        try {
            const result = await this.mediaRequest.saveAltTexts(objectId, altTexts);
            if (result.success) {
                this.#showMessage(elements.success, result.message);
            } else {
                this.#showMessage(elements.error, this.language.translate('DD_MEDIA_ALT_TEXT_SAVE_ERROR'));
            }
        } catch {
            this.#showMessage(elements.error, this.language.translate('DD_MEDIA_ALT_TEXT_SAVE_ERROR'));
        } finally {
            this.#hideLoading(elements);
            elements.saveButton.disabled = false;
        }
    }

    #showMessage(element: HTMLElement, message: string): void {
        element.textContent = message;
        element.style.display = '';

        clearTimeout(this.#messageTimer);
        this.#messageTimer = setTimeout(() => {
            element.style.display = 'none';
        }, MESSAGE_DURATION);
    }

    #hideMessages(elements: AltTextElements): void {
        clearTimeout(this.#messageTimer);
        for (const element of [elements.error, elements.success]) {
            element.style.display = 'none';
            element.textContent = '';
        }
    }

    #showLoading(elements: AltTextElements): void {
        if (elements.inputs.querySelector('.dd-alttext-spinner')) {
            return;
        }

        const spinner = document.createElement('div');
        spinner.className = 'dd-alttext-spinner text-center my-2';
        spinner.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        elements.inputs.append(spinner);
    }

    #hideLoading(elements: AltTextElements): void {
        elements.inputs.querySelector('.dd-alttext-spinner')?.remove();
    }
}
