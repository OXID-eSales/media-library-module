/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {afterEach, describe, expect, it, vi} from 'vitest';
import Dialog from '../../../../node_build/js/base/dialog/Dialog.ts';
import {DialogInterface} from '../../../../node_build/js/base/dialog/DialogInterface.ts';
import type {DialogOptions} from '../../../../node_build/js/base/dialog/types/DialogOptions.ts';

describe('Dialog', () => {
    const removeListeners: Array<() => void> = [];

    afterEach(() => {
        removeListeners.splice(0).forEach((remove) => remove());
        vi.useRealTimers();
        document.body.innerHTML = '';
        document.body.className = '';
        document.body.removeAttribute('style');
    });

    describe('open', () => {
        it('should append the modal to the body and return it', () => {
            const sut = getSut();

            const modalEl = sut.open({message: 'message'});

            expect(modalEl.parentElement).toBe(document.body);
            expect(modalEl.classList.contains('modal')).toBe(true);
        });

        it('should use size sm and css dd-dialog by default', () => {
            const sut = getSut();

            const modalEl = sut.open({message: 'message'});

            expect(modalEl.classList.contains('dd-dialog')).toBe(true);
            expect(modalEl.querySelector('.modal-dialog')!.classList.contains('modal-sm')).toBe(true);
        });

        it('should use the given size and css', () => {
            const sut = getSut();

            const modalEl = sut.open({message: 'message', size: 'lg', css: 'dd-one dd-two'});

            expect(modalEl.classList.contains('dd-one')).toBe(true);
            expect(modalEl.classList.contains('dd-two')).toBe(true);
            expect(modalEl.querySelector('.modal-dialog')!.classList.contains('modal-lg')).toBe(true);
        });

        it('should render the title as html', () => {
            const sut = getSut();

            const modalEl = sut.open({message: 'message', title: '<b>title</b>'});

            expect(modalEl.querySelector('.modal-title b')!.textContent).toBe('title');
        });

        it('should leave the title empty when none is given', () => {
            const sut = getSut();

            const modalEl = sut.open({message: 'message'});

            expect(modalEl.querySelector('.modal-title')!.innerHTML).toBe('');
        });

        it('should render a string message as html', () => {
            const sut = getSut();

            const modalEl = sut.open({message: '<p class="dd-message">message</p>'});

            expect(modalEl.querySelector('.modal-body .dd-message')!.textContent).toBe('message');
        });

        it('should append an element message', () => {
            const message = document.createElement('div');
            const sut = getSut();

            const modalEl = sut.open({message});

            expect(message.parentElement).toBe(modalEl.querySelector('.modal-body'));
        });

        it('should open with an empty body when a JS caller leaves the message out', () => {
            const sut = getSut();

            const modalEl = sut.open({} as DialogOptions);

            expect(modalEl.querySelector('.modal-body')!.innerHTML).toBe('');
        });

        it('should render a button from html', () => {
            const sut = getSut();

            const modalEl = sut.open({message: 'message', buttons: [{html: '<a class="dd-link" href="#">link</a>'}]});

            expect(modalEl.querySelector('.modal-footer .dd-link')!.textContent).toBe('link');
        });

        it('should build a plain button from label with default type and css', () => {
            const sut = getSut();

            const modalEl = sut.open({message: 'message', buttons: [{label: 'label'}]});

            const button = modalEl.querySelector<HTMLButtonElement>('.modal-footer button')!;
            expect(button.innerHTML).toBe('label');
            expect(button.type).toBe('button');
            expect(button.className).toBe('btn btn-outline-primary');
        });

        it('should build a plain button with the given type and css', () => {
            const sut = getSut();

            const modalEl = sut.open({message: 'message', buttons: [{label: 'label', type: 'submit', css: ['btn', 'dd-submit']}]});

            const button = modalEl.querySelector<HTMLButtonElement>('.modal-footer button')!;
            expect(button.type).toBe('submit');
            expect(button.className).toBe('btn dd-submit');
        });

        it('should set the button attributes', () => {
            const sut = getSut();

            const modalEl = sut.open({message: 'message', buttons: [{label: 'label', attributes: {'data-bs-dismiss': 'modal'}}]});

            expect(modalEl.querySelector('.modal-footer button')!.getAttribute('data-bs-dismiss')).toBe('modal');
        });

        it('should call the button action with the modal element on click', () => {
            const action = vi.fn();
            const sut = getSut();

            const modalEl = sut.open({message: 'message', buttons: [{label: 'label', action}]});
            modalEl.querySelector<HTMLButtonElement>('.modal-footer button')!.click();

            expect(action).toHaveBeenCalledWith(modalEl);
        });

        it('should show the modal', () => {
            const shownModals = listenFor('show.bs.modal');
            const sut = getSut();

            const modalEl = sut.open({message: 'message'});

            expect(shownModals).toEqual([modalEl]);
        });

        it('should remove the modal element once it is hidden', () => {
            const sut = getSut();

            const modalEl = sut.open({message: 'message'});
            modalEl.dispatchEvent(new Event('hidden.bs.modal'));

            expect(modalEl.isConnected).toBe(false);
        });
    });

    describe('close', () => {
        it('should hide the modal', () => {
            vi.useFakeTimers();
            const sut = getSut();
            const modalEl = sut.open({message: 'message'});
            // let bootstrap finish the show transition, it ignores hide() while still showing
            vi.runAllTimers();
            const hiddenModals = listenFor('hide.bs.modal');

            sut.close(modalEl);

            expect(hiddenModals).toEqual([modalEl]);
        });

        it('should do nothing for an element that is not a modal', () => {
            const hiddenModals = listenFor('hide.bs.modal');
            const sut = getSut();

            sut.close(document.createElement('div'));

            expect(hiddenModals).toEqual([]);
        });
    });

    /**
     * Collects the targets of a bootstrap modal event, e.g. show.bs.modal.
     */
    const listenFor = (type: string): EventTarget[] => {
        const targets: EventTarget[] = [];
        const listener = (event: Event): void => {
            targets.push(event.target!);
        };
        document.addEventListener(type, listener);
        removeListeners.push(() => document.removeEventListener(type, listener));

        return targets;
    };

    const getSut = (): DialogInterface => new Dialog();
});
