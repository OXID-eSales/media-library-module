/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {inject, injectable} from 'tsyringe';
import {InteractionInterface} from './InteractionInterface.ts';
import {DialogInterface} from '../dialog/DialogInterface.ts';
import {LanguageInterface} from '../language/LanguageInterface.ts';

@injectable()
export default class Interaction implements InteractionInterface {
    constructor(
        @inject(DialogInterface) private readonly dialog: DialogInterface,
        @inject(LanguageInterface) private readonly language: LanguageInterface,
    ) {}

    confirm(message: string, callback: () => void, title = this.language.translate('DD_CONFIRM'), warn = false): void {
        this.dialog.open({
            message,
            title,
            buttons: [
                {html: this.#cancelButton()},
                {
                    html: this.#okButton(),
                    action: (modalEl) => {
                        this.dialog.close(modalEl);
                        callback();
                    },
                },
            ],
            size: 'sm',
            css: 'dd-modal-confirm' + (warn ? ' dd-warn' : ''),
        });
    }

    prompt(message: string, callback: (value: string) => void, title = this.language.translate('DD_CONFIRM'), value = ''): void {
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'prompt';
        input.className = 'form-control';
        input.value = value;

        const inputWrapper = document.createElement('div');
        inputWrapper.className = 'clearfix';
        inputWrapper.style.marginTop = '10px';
        inputWrapper.appendChild(input);

        const body = document.createElement('div');
        body.innerHTML = message;
        body.appendChild(inputWrapper);

        const submit = (modalEl: HTMLElement): void => {
            this.dialog.close(modalEl);
            callback(input.value);
        };

        const modalEl = this.dialog.open({
            message: body,
            title,
            buttons: [
                {html: this.#cancelButton()},
                {html: this.#okButton(), action: submit},
            ],
            size: 'sm',
            css: 'dd-modal-confirm',
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                submit(modalEl);
            }
        });
    }

    alert(message: string, title = this.language.translate('DD_INFORMATION')): void {
        this.dialog.open({
            message,
            title,
            buttons: [
                {html: `<button type="button" class="btn btn-primary" data-bs-dismiss="modal">${this.language.translate('DD_OK')}</button>`},
            ],
            size: 'sm',
            css: 'dd-modal-confirm',
        });
    }

    #cancelButton(): string {
        return `<button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">${this.language.translate('DD_CANCEL')}</button>`;
    }

    #okButton(): string {
        return `<button type="button" class="btn btn-primary" autofocus>${this.language.translate('DD_OK')}</button>`;
    }
}
