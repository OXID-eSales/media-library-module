/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import { Modal } from "bootstrap";

export const ddh = {
    _dialog(msg, title, buttons, size = 'sm', css = 'dd-dialog') {
        let opt = {
            message: msg,
            title,
            buttons,
            size,
            css,
            backdrop: false,
            keyboard: false,
        };

        if (typeof msg === 'object') {
            opt = Object.assign(opt, msg);
        }

        const modalSize = `modal-${opt.size || 'sm'}`;
        const modalLayout = `
            <div class="modal fade ${opt.css}" tabindex="-1" role="dialog" aria-labelledby="Confirm" aria-hidden="true">
                <div class="modal-dialog ${modalSize}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"></h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body"></div>
                        <div class="modal-footer"></div>
                    </div>
                </div>
            </div>`;

        const template = document.createElement("template");
        template.innerHTML = modalLayout.trim();
        const modalEl = template.content.firstElementChild;

        if (opt.title !== undefined && opt.title !== '') {
            modalEl.querySelector(".modal-title").innerHTML = opt.title;
        }

        if (opt.message) {
            const body = modalEl.querySelector(".modal-body");
            if (typeof opt.message === "string") {
                body.innerHTML = opt.message;
            } else if (opt.message instanceof HTMLElement) {
                body.innerHTML = "";
                body.appendChild(opt.message);
            }
        }

        if (opt.buttons.length) {
            const footer = modalEl.querySelector(".modal-footer");
            opt.buttons.forEach((btn) => {
                let button;
                if (btn.html) {
                    const tmpl = document.createElement("template");
                    tmpl.innerHTML = btn.html.trim();
                    button = tmpl.content.firstElementChild;
                } else {
                    button = document.createElement("button");
                    button.type = btn.type || "button";
                    button.className = (btn.css || ["btn", "btn-outline-primary"]).join(" ");
                    button.innerHTML = btn.label || "";
                }

                if (btn.attributes) {
                    Object.entries(btn.attributes).forEach(([k, v]) => button.setAttribute(k, v));
                }

                if (btn.action) {
                    button.addEventListener("click", () => btn.action(modalEl));
                }

                footer.appendChild(button);
            });
        }
        document.body.appendChild(modalEl);

        const modalInstance = Modal.getOrCreateInstance(modalEl, {
            backdrop: opt.backdrop,
            keyboard: opt.keyboard,
            focus: true,
        });

        modalEl.addEventListener("hidden.bs.modal", () => {
            modalEl.remove();
        });

        modalInstance.show();

        return modalEl;
    },
    confirm(msg, callback, title = ddh.translate('DD_CONFIRM'), warn = false) {
        const cssWarn = warn ? ' dd-warn' : '';
        const buttons = [
            {
                html: `<button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">${ddh.translate('DD_CANCEL')}</button>`
            },
            {
                html: `<button type="button" class="btn btn-primary" autofocus>${ddh.translate('DD_OK')}</button>`,
                action: (modalEl) => {
                    Modal.getInstance(modalEl).hide();
                    callback();
                },
            },
        ];

        this._dialog(msg, title, buttons, 'sm', `dd-modal-confirm${cssWarn}`);
    },
    prompt(msg, callback, title = ddh.translate('DD_CONFIRM'), value = '') {
        msg += `<div class="clearfix" style="margin-top: 10px;">
                  <input type="text" name="prompt" class="form-control" value="${value}" />
                </div>`;

        const buttons = [
            {
                html: `<button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">${ddh.translate('DD_CANCEL')}</button>`
            },
            {
                html: `<button type="button" class="btn btn-primary" autofocus>${ddh.translate('DD_OK')}</button>`,
                action: (modalEl) => {
                    Modal.getInstance(modalEl).hide();
                    const inputVal = modalEl.querySelector("input[name=prompt]").value;
                    callback(inputVal);
                },
            },
        ];

        const modalEl = this._dialog(msg, title, buttons, 'sm', 'dd-modal-confirm');

        const inputEl = modalEl.querySelector("input[name=prompt]");
        if (inputEl) {
            inputEl.addEventListener("keypress", (e) => {
                if (e.key === 13) {
                    modalEl.querySelector(".btn-primary").click();
                }
            });
        }
    },
    alert(msg, title = 'Information') {
        const buttons = [{ html: `<button type="button" class="btn btn-primary" data-bs-dismiss="modal">${ddh.translate('DD_OK')}</button>` }];
        this._dialog(msg, title, buttons, 'sm', 'dd-modal-confirm');
    },
    translate(string) {
        return typeof i18n === 'object' && i18n[string] ? i18n[string] : string;
    }
};
