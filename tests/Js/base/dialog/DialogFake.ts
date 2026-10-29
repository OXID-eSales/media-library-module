/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {DialogInterface} from '../../../../node_build/js/base/dialog/DialogInterface.ts';
import type {DialogOptions} from '../../../../node_build/js/base/dialog/types/DialogOptions.ts';

/**
 * Renders the opened dialog into a detached element, so tests can click its buttons
 * instead of digging the actions out of mock calls.
 */
export default class DialogFake implements DialogInterface {
    #element: HTMLElement | null = null;
    #options: DialogOptions | null = null;
    readonly #closed: HTMLElement[] = [];

    open(options: DialogOptions): HTMLElement {
        const modalEl = document.createElement('div');

        const body = document.createElement('div');
        if (typeof options.message === 'string') {
            body.innerHTML = options.message;
        } else {
            body.appendChild(options.message);
        }

        const footer = document.createElement('div');
        footer.className = 'fake-footer';
        (options.buttons ?? []).forEach((button) => {
            const template = document.createElement('template');
            template.innerHTML = (button.html ?? `<button type="button">${button.label ?? ''}</button>`).trim();
            const buttonEl = template.content.firstElementChild as HTMLElement;
            const action = button.action;
            if (action) {
                buttonEl.addEventListener('click', () => action(modalEl));
            }
            footer.appendChild(buttonEl);
        });

        modalEl.append(body, footer);
        this.#element = modalEl;
        this.#options = options;

        return modalEl;
    }

    close(modalEl: HTMLElement): void {
        this.#closed.push(modalEl);
    }

    get options(): DialogOptions {
        if (!this.#options) {
            throw new Error('No dialog was opened');
        }

        return this.#options;
    }

    get element(): HTMLElement {
        if (!this.#element) {
            throw new Error('No dialog was opened');
        }

        return this.#element;
    }

    isClosed(): boolean {
        return this.#element !== null && this.#closed.includes(this.#element);
    }

    findButton(text: string): HTMLElement {
        const button = Array.from(this.element.querySelectorAll<HTMLElement>('.fake-footer > *'))
            .find((el) => el.textContent?.trim() === text);
        if (!button) {
            throw new Error(`No button with text "${text}"`);
        }

        return button;
    }
}
