/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import {afterEach, describe, expect, it, vi} from 'vitest';
import AltTextManager from '../../../../node_build/js/base/altText/AltTextManager.ts';
import {AltTextManagerInterface} from '../../../../node_build/js/base/altText/AltTextManagerInterface.ts';
import {MediaRequestInterface} from '../../../../node_build/js/base/media/MediaRequestInterface.ts';
import {LanguageInterface} from '../../../../node_build/js/base/language/LanguageInterface.ts';
import type {AltTexts, AltTextSaveResult} from '../../../../node_build/js/base/media/types/AltTexts.ts';
import {InterfaceMock} from '../InterfaceMock.ts';

describe('AltTextManager', () => {
    afterEach(() => {
        vi.useRealTimers();
    });

    describe('show', () => {
        it('should render an input with the alt text of each language', async () => {
            const altTexts = {0: crypto.randomUUID(), 1: crypto.randomUUID()};
            const form = prepareForm();
            const sut = getSut({mediaRequest: prepareMediaRequestMock({altTexts})});

            await sut.show(crypto.randomUUID(), form);

            expect(findInput(form, '0').value).toBe(altTexts[0]);
            expect(findInput(form, '1').value).toBe(altTexts[1]);
            expect(findInput(form, '1').placeholder).toBe('[DD_MEDIA_ALT_TEXT_PLACEHOLDER]');
        });

        it('should load the alt texts of the given file', async () => {
            const objectId = crypto.randomUUID();
            const mediaRequestSpy = prepareMediaRequestMock();
            const sut = getSut({mediaRequest: mediaRequestSpy});

            await sut.show(objectId, prepareForm());

            expect(mediaRequestSpy.getAltTexts).toHaveBeenCalledWith(objectId);
        });

        it('should show only the label and input of the selected language', async () => {
            const form = prepareForm();
            const sut = getSut();

            await sut.show(crypto.randomUUID(), form);

            expect(isShown(findWrapper(form, '0'))).toBe(false);
            expect(isShown(findWrapper(form, '1'))).toBe(true);
            expect(findWrapper(form, '1').querySelector('label')?.textContent).toBe('[DD_MEDIA_ALT_TEXT]');
        });

        it('should show the label and input of a newly selected language', async () => {
            const form = prepareForm();
            const sut = getSut();
            await sut.show(crypto.randomUUID(), form);

            selectLanguage(form, '0');

            expect(isShown(findWrapper(form, '0'))).toBe(true);
            expect(isShown(findWrapper(form, '1'))).toBe(false);
        });

        it('should render empty inputs when the alt texts can not be loaded', async () => {
            const mediaRequest = prepareMediaRequestMock();
            vi.mocked(mediaRequest.getAltTexts).mockRejectedValue(new Error(crypto.randomUUID()));
            const form = prepareForm();
            const sut = getSut({mediaRequest});

            await sut.show(crypto.randomUUID(), form);

            expect(findInput(form, '0').value).toBe('');
            expect(findInput(form, '1').value).toBe('');
        });

        it('should ignore the alt texts of a file that is no longer shown', async () => {
            const previousFile = prepareDeferred<AltTexts>();
            const currentAltTexts = {0: crypto.randomUUID(), 1: crypto.randomUUID()};
            const mediaRequest = prepareMediaRequestMock();
            vi.mocked(mediaRequest.getAltTexts)
                .mockReturnValueOnce(previousFile.promise)
                .mockResolvedValueOnce(currentAltTexts);
            const form = prepareForm();
            const sut = getSut({mediaRequest});

            const previousShow = sut.show(crypto.randomUUID(), form);
            await sut.show(crypto.randomUUID(), form);
            previousFile.resolve({0: crypto.randomUUID(), 1: crypto.randomUUID()});
            await previousShow;

            expect(findInput(form, '1').value).toBe(currentAltTexts[1]);
        });
    });

    describe('save', () => {
        it('should save the alt texts of all languages for the shown file', async () => {
            const objectId = crypto.randomUUID();
            const altTexts = {0: crypto.randomUUID(), 1: crypto.randomUUID()};
            const mediaRequestSpy = prepareMediaRequestMock();
            const form = prepareForm();
            const sut = getSut({mediaRequest: mediaRequestSpy});
            await sut.show(objectId, form);
            findInput(form, '0').value = altTexts[0];
            findInput(form, '1').value = altTexts[1];

            await clickSave(form);

            expect(mediaRequestSpy.saveAltTexts).toHaveBeenCalledOnce();
            expect(mediaRequestSpy.saveAltTexts).toHaveBeenCalledWith(objectId, altTexts);
        });

        it('should show the message the shop sent on success', async () => {
            const message = crypto.randomUUID();
            const form = prepareForm();
            const sut = getSut({mediaRequest: prepareMediaRequestMock({saveResult: {success: true, message}})});
            await sut.show(crypto.randomUUID(), form);

            await clickSave(form);

            expect(findSuccess(form).textContent).toBe(message);
            expect(isShown(findSuccess(form))).toBe(true);
            expect(isShown(findError(form))).toBe(false);
        });

        it('should show an error when the shop reports no success', async () => {
            const form = prepareForm();
            const sut = getSut({
                mediaRequest: prepareMediaRequestMock({saveResult: {success: false, message: crypto.randomUUID()}}),
            });
            await sut.show(crypto.randomUUID(), form);

            await clickSave(form);

            expect(findError(form).textContent).toBe('[DD_MEDIA_ALT_TEXT_SAVE_ERROR]');
            expect(isShown(findError(form))).toBe(true);
            expect(isShown(findSuccess(form))).toBe(false);
        });

        it('should show an error when the request fails', async () => {
            const mediaRequest = prepareMediaRequestMock();
            vi.mocked(mediaRequest.saveAltTexts).mockRejectedValue(new Error(crypto.randomUUID()));
            const form = prepareForm();
            const sut = getSut({mediaRequest});
            await sut.show(crypto.randomUUID(), form);

            await clickSave(form);

            expect(findError(form).textContent).toBe('[DD_MEDIA_ALT_TEXT_SAVE_ERROR]');
            expect(isShown(findError(form))).toBe(true);
        });

        it('should disable the save button while saving', async () => {
            const saving = prepareDeferred<AltTextSaveResult>();
            const mediaRequest = prepareMediaRequestMock();
            vi.mocked(mediaRequest.saveAltTexts).mockReturnValue(saving.promise);
            const form = prepareForm();
            const sut = getSut({mediaRequest});
            await sut.show(crypto.randomUUID(), form);

            findSaveButton(form).click();
            expect(findSaveButton(form).disabled).toBe(true);

            saving.resolve({success: true, message: ''});
            await vi.waitFor(() => expect(findSaveButton(form).disabled).toBe(false));
        });

        it('should save only the file shown last when the form is reused', async () => {
            const currentObjectId = crypto.randomUUID();
            const mediaRequestSpy = prepareMediaRequestMock();
            const form = prepareForm();
            const sut = getSut({mediaRequest: mediaRequestSpy});
            await sut.show(crypto.randomUUID(), form);
            await sut.show(currentObjectId, form);

            await clickSave(form);

            expect(mediaRequestSpy.saveAltTexts).toHaveBeenCalledOnce();
            expect(mediaRequestSpy.saveAltTexts).toHaveBeenCalledWith(currentObjectId, expect.anything());
        });

        it('should hide the message after 4 seconds', async () => {
            vi.useFakeTimers();
            const form = prepareForm();
            const sut = getSut();
            await sut.show(crypto.randomUUID(), form);

            findSaveButton(form).click();
            await vi.advanceTimersByTimeAsync(0);
            expect(isShown(findSuccess(form))).toBe(true);

            await vi.advanceTimersByTimeAsync(4000);
            expect(isShown(findSuccess(form))).toBe(false);
        });
    });

    /**
     * The alt text part of the details form in ddoemedia.html.twig, language 1 is selected.
     */
    const prepareForm = (): HTMLElement => {
        const form = document.createElement('form');
        form.innerHTML = `
            <select class="form-select dd-media-alttext-language">
                <option value="0">Deutsch</option>
                <option value="1" selected>English</option>
            </select>
            <div class="form-group dd-media-alttext-inputs"></div>
            <div class="alert alert-danger dd-media-alttext-error" style="display:none"></div>
            <div class="alert alert-success dd-media-alttext-success" style="display:none"></div>
            <button type="button" class="btn btn-primary dd-media-alttext-save-btn"></button>
        `;

        return form;
    };

    const prepareMediaRequestMock = ({
        altTexts = {},
        saveResult = {success: true, message: crypto.randomUUID()},
    }: {
        altTexts?: AltTexts;
        saveResult?: AltTextSaveResult;
    } = {}): MediaRequestInterface => InterfaceMock<MediaRequestInterface>({
        getAltTexts: vi.fn().mockResolvedValue(altTexts),
        saveAltTexts: vi.fn().mockResolvedValue(saveResult),
    });

    const prepareDeferred = <T>(): {promise: Promise<T>; resolve: (value: T) => void} => {
        let resolve: (value: T) => void = () => {};
        const promise = new Promise<T>(res => {
            resolve = res;
        });

        return {promise, resolve};
    };

    const findWrapper = (form: HTMLElement, languageId: string): HTMLElement =>
        form.querySelector<HTMLElement>(`.dd-media-alttext-input-wrapper[data-lang-id="${languageId}"]`)!;

    const findInput = (form: HTMLElement, languageId: string): HTMLInputElement =>
        findWrapper(form, languageId).querySelector<HTMLInputElement>('input')!;

    const findSaveButton = (form: HTMLElement): HTMLButtonElement =>
        form.querySelector<HTMLButtonElement>('.dd-media-alttext-save-btn')!;

    const findError = (form: HTMLElement): HTMLElement => form.querySelector<HTMLElement>('.dd-media-alttext-error')!;

    const findSuccess = (form: HTMLElement): HTMLElement =>
        form.querySelector<HTMLElement>('.dd-media-alttext-success')!;

    const isShown = (element: HTMLElement): boolean => element.style.display !== 'none';

    const selectLanguage = (form: HTMLElement, languageId: string): void => {
        const select = form.querySelector<HTMLSelectElement>('.dd-media-alttext-language')!;
        select.value = languageId;
        select.dispatchEvent(new Event('change'));
    };

    const clickSave = async (form: HTMLElement): Promise<void> => {
        findSaveButton(form).click();
        await vi.waitFor(() => expect(findSaveButton(form).disabled).toBe(false));
    };

    const getSut = ({
        mediaRequest = prepareMediaRequestMock(),
        language = InterfaceMock<LanguageInterface>({translate: (key: string) => `[${key}]`}),
    }: {
        mediaRequest?: MediaRequestInterface;
        language?: LanguageInterface;
    } = {}): AltTextManagerInterface => new AltTextManager(mediaRequest, language);
});
