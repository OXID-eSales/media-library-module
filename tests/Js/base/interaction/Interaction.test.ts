/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {describe, expect, it, vi} from 'vitest';
import Interaction from '../../../../node_build/js/base/interaction/Interaction.ts';
import {InteractionInterface} from '../../../../node_build/js/base/interaction/InteractionInterface.ts';
import {DialogInterface} from '../../../../node_build/js/base/dialog/DialogInterface.ts';
import {LanguageInterface} from '../../../../node_build/js/base/language/LanguageInterface.ts';
import DialogFake from '../dialog/DialogFake.ts';
import {InterfaceMock} from '../InterfaceMock.ts';

describe('Interaction', () => {
    describe('confirm', () => {
        it('should open a small confirm dialog with the message and title', () => {
            const message = crypto.randomUUID();
            const title = crypto.randomUUID();
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.confirm(message, vi.fn(), title);

            expect(dialogFake.options).toMatchObject({message, title, size: 'sm', css: 'dd-modal-confirm'});
        });

        it('should use the translated DD_CONFIRM title by default', () => {
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.confirm(crypto.randomUUID(), vi.fn());

            expect(dialogFake.options.title).toBe('[DD_CONFIRM]');
        });

        it('should add the warn css when warn is set', () => {
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.confirm(crypto.randomUUID(), vi.fn(), crypto.randomUUID(), true);

            expect(dialogFake.options.css).toBe('dd-modal-confirm dd-warn');
        });

        it('should close the dialog and call the callback on ok', () => {
            const callbackSpy = vi.fn();
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.confirm(crypto.randomUUID(), callbackSpy);
            dialogFake.findButton('[DD_OK]').click();

            expect(dialogFake.isClosed()).toBe(true);
            expect(callbackSpy).toHaveBeenCalledOnce();
        });

        it('should leave closing to bootstrap and not call the callback on cancel', () => {
            const callbackSpy = vi.fn();
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.confirm(crypto.randomUUID(), callbackSpy);
            const cancelButton = dialogFake.findButton('[DD_CANCEL]');
            cancelButton.click();

            expect(cancelButton.getAttribute('data-bs-dismiss')).toBe('modal');
            expect(callbackSpy).not.toHaveBeenCalled();
        });
    });

    describe('prompt', () => {
        it('should show the message and an input with the given value', () => {
            const value = crypto.randomUUID();
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.prompt('<p class="dd-message">message</p>', vi.fn(), crypto.randomUUID(), value);

            expect(dialogFake.element.querySelector('.dd-message')!.textContent).toBe('message');
            expect(getPromptInput(dialogFake).value).toBe(value);
        });

        it('should keep quotes and tags in the value instead of breaking the markup', () => {
            const value = 'file "name" <b>.png';
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.prompt(crypto.randomUUID(), vi.fn(), crypto.randomUUID(), value);

            expect(getPromptInput(dialogFake).value).toBe(value);
            expect(dialogFake.element.querySelector('b')).toBeNull();
        });

        it('should use the translated DD_CONFIRM title by default', () => {
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.prompt(crypto.randomUUID(), vi.fn());

            expect(dialogFake.options.title).toBe('[DD_CONFIRM]');
        });

        it('should close the dialog and pass the typed value to the callback on ok', () => {
            const typedValue = crypto.randomUUID();
            const callbackSpy = vi.fn();
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.prompt(crypto.randomUUID(), callbackSpy);
            getPromptInput(dialogFake).value = typedValue;
            dialogFake.findButton('[DD_OK]').click();

            expect(dialogFake.isClosed()).toBe(true);
            expect(callbackSpy).toHaveBeenCalledWith(typedValue);
        });

        it('should submit on enter', () => {
            const value = crypto.randomUUID();
            const callbackSpy = vi.fn();
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.prompt(crypto.randomUUID(), callbackSpy, crypto.randomUUID(), value);
            getPromptInput(dialogFake).dispatchEvent(new KeyboardEvent('keydown', {key: 'Enter'}));

            expect(dialogFake.isClosed()).toBe(true);
            expect(callbackSpy).toHaveBeenCalledWith(value);
        });

        it('should not submit on other keys', () => {
            const callbackSpy = vi.fn();
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.prompt(crypto.randomUUID(), callbackSpy);
            getPromptInput(dialogFake).dispatchEvent(new KeyboardEvent('keydown', {key: 'a'}));

            expect(dialogFake.isClosed()).toBe(false);
            expect(callbackSpy).not.toHaveBeenCalled();
        });

        it('should not call the callback on cancel', () => {
            const callbackSpy = vi.fn();
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.prompt(crypto.randomUUID(), callbackSpy);
            dialogFake.findButton('[DD_CANCEL]').click();

            expect(callbackSpy).not.toHaveBeenCalled();
        });
    });

    describe('alert', () => {
        it('should open a small dialog with the message and title', () => {
            const message = crypto.randomUUID();
            const title = crypto.randomUUID();
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.alert(message, title);

            expect(dialogFake.options).toMatchObject({message, title, size: 'sm', css: 'dd-modal-confirm'});
        });

        it('should use the translated DD_INFORMATION title by default', () => {
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.alert(crypto.randomUUID());

            expect(dialogFake.options.title).toBe('[DD_INFORMATION]');
        });

        it('should only have an ok button that bootstrap closes', () => {
            const dialogFake = new DialogFake();
            const sut = getSut({dialog: dialogFake});

            sut.alert(crypto.randomUUID());

            expect(dialogFake.options.buttons).toHaveLength(1);
            expect(dialogFake.findButton('[DD_OK]').getAttribute('data-bs-dismiss')).toBe('modal');
        });
    });

    const getPromptInput = (dialogFake: DialogFake): HTMLInputElement =>
        dialogFake.element.querySelector<HTMLInputElement>('input[name=prompt]')!;

    const getSut = ({
        dialog = new DialogFake(),
        language = InterfaceMock<LanguageInterface>({translate: (key: string) => `[${key}]`}),
    }: {
        dialog?: DialogInterface;
        language?: LanguageInterface;
    } = {}): InteractionInterface => new Interaction(dialog, language);
});
