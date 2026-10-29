/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {injectable} from 'tsyringe';
import {Modal} from 'bootstrap';
import {DialogInterface} from './DialogInterface.ts';
import type {DialogOptions} from './types/DialogOptions.ts';
import type {DialogButton} from './types/DialogButton.ts';

@injectable()
export default class Dialog implements DialogInterface {
    open(options: DialogOptions): HTMLElement {
        const modalEl = this.#createModal(options.size ?? 'sm', options.css ?? 'dd-dialog');

        if (options.title) {
            modalEl.querySelector('.modal-title')!.innerHTML = options.title;
        }

        const body = modalEl.querySelector('.modal-body')!;
        if (options.message instanceof HTMLElement) {
            body.appendChild(options.message);
        } else if (options.message) {
            body.innerHTML = options.message;
        }

        const footer = modalEl.querySelector('.modal-footer')!;
        (options.buttons ?? []).forEach((button) => {
            footer.appendChild(this.#createButton(button, modalEl));
        });

        document.body.appendChild(modalEl);
        modalEl.addEventListener('hidden.bs.modal', () => {
            modalEl.remove();
        });

        Modal.getOrCreateInstance(modalEl, {
            backdrop: options.backdrop ?? false,
            keyboard: options.keyboard ?? false,
            focus: true,
        }).show();

        return modalEl;
    }

    close(modalEl: HTMLElement): void {
        Modal.getInstance(modalEl)?.hide();
    }

    #createModal(size: string, css: string): HTMLElement {
        return this.#fromHtml(`
            <div class="modal fade ${css}" tabindex="-1" role="dialog" aria-labelledby="Confirm" aria-hidden="true">
                <div class="modal-dialog modal-${size}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"></h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body"></div>
                        <div class="modal-footer"></div>
                    </div>
                </div>
            </div>`);
    }

    #createButton(options: DialogButton, modalEl: HTMLElement): HTMLElement {
        let button: HTMLElement;
        if (options.html) {
            button = this.#fromHtml(options.html);
        } else {
            const plainButton = document.createElement('button');
            plainButton.type = options.type ?? 'button';
            plainButton.className = (options.css ?? ['btn', 'btn-outline-primary']).join(' ');
            plainButton.innerHTML = options.label ?? '';
            button = plainButton;
        }

        Object.entries(options.attributes ?? {}).forEach(([name, value]) => button.setAttribute(name, value));

        const action = options.action;
        if (action) {
            button.addEventListener('click', () => action(modalEl));
        }

        return button;
    }

    #fromHtml(html: string): HTMLElement {
        const template = document.createElement('template');
        template.innerHTML = html.trim();

        return template.content.firstElementChild as HTMLElement;
    }
}
