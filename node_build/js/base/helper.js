/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

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
            appendToHtml: false
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

        const $modal = $(modalLayout);
        if (opt.title !== undefined && opt.title !== '') {
            $('.modal-title', $modal).html(opt.title);
        }

        if (opt.message) {
            if (typeof opt.message === 'string') {
                $('.modal-body', $modal).html(opt.message);
            } else if (typeof opt.message === 'object') {
                $('.modal-body', $modal).empty().append(opt.message);
            }
        }

        if (opt.buttons.length) {
            opt.buttons.forEach(btn => {
                const $btn = $(btn.html || `<button type="${btn.type || 'button'}" class="${(btn.css || ['btn btn-outline-primary']).join(' ')}">${btn.label}</button>`);
                if (btn.attributes) {
                    $btn.attr(btn.attributes);
                }
                if (btn.action) {
                    $btn.on('click', () => btn.action($modal));
                }
                $('.modal-footer', $modal).append($btn);
            });
        }

        const $parent = opt.appendToHtml ? $(document.documentElement) : $(document.body);
        $parent.append($modal);

        const ModalConstructor = $.fn.modal.Constructor;
        const originalBackdropFn = ModalConstructor.prototype.backdrop;

        ModalConstructor.prototype.backdrop = function (callback) {
            const animate = this.$element.hasClass('fade') ? 'fade' : '';

            if (this.isShown && this.options.backdrop) {
                this.$backdrop = $('<div class="modal-backdrop ' + animate + '" />')
                    .appendTo(this.$element.parent());

                if (animate) this.$backdrop[0].offsetWidth;
                this.$backdrop.addClass('in');

                if (callback) callback();
            } else if (!this.isShown && this.$backdrop) {
                this.$backdrop.removeClass('in');
                const cb = () => {
                    this.removeBackdrop();
                    if (callback) callback();
                };
                $.support.transition && this.$element.hasClass('fade')
                    ? this.$backdrop.one('bsTransitionEnd', cb).emulateTransitionEnd(150)
                    : cb();
            } else if (callback) {
                callback();
            }
        };

        $modal.modal({
            backdrop: opt.backdrop,
            keyboard: opt.keyboard
        }).on('hidden.bs.modal', function () {
            $(this).remove();
        }).one('shown.bs.modal', function () {
            $('.modal-body input[type="text"]', this).length
                ? $('.modal-body input[type="text"]', this).focus()
                : $('.modal-footer .btn-primary', this).focus();
        });

        $modal.modal('show');

        return $modal;
    },
    confirm(msg, callback, title = ddh.translate('DD_CONFIRM'), warn = false) {
        const cssWarn = warn ? ' dd-warn' : '';
        const buttons = [
            {
                html: `<button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">${ddh.translate('DD_CANCEL')}</button>`
            },
            {
                html: `<button type="button" class="btn btn-primary" autofocus>${ddh.translate('DD_OK')}</button>`,
                action: ($modal) => {
                    $modal.modal('hide');
                    callback();
                }
            }
        ];

        this._dialog(msg, title, buttons, 'sm', `dd-modal-confirm${cssWarn}`);
    },
    prompt(msg, callback, title = ddh.translate('DD_CONFIRM'), value = '') {
        msg += `<div class="clearfix" style="margin-top: 10px;"><input type="text" name="prompt" class="form-control" value="${value}" /></div>`;
        const buttons = [
            {
                html: `<button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">${ddh.translate('DD_CANCEL')}</button>`
            },
            {
                html: `<button type="button" class="btn btn-primary" autofocus>${ddh.translate('DD_OK')}</button>`,
                action: ($modal) => {
                    $modal.modal('hide');
                    callback($('input[name=prompt]', $modal).val());
                }
            }
        ];

        const $modal = this._dialog(msg, title, buttons, 'sm', 'dd-modal-confirm');
        $('input[name=prompt]', $modal).on('keypress', function (e) {
            if (e.keyCode === 13) {
                $('.btn-primary', $modal).click();
            }
        });
    },
    alert(msg, title = 'Information') {
        const buttons = [{ html: `<button type="button" class="btn btn-primary" data-bs-dismiss="modal">${ddh.translate('DD_OK')}</button>` }];
        this._dialog(msg, title, buttons, 'sm', 'dd-modal-confirm');
    },
    translate(string) {
        return typeof i18n === 'object' && i18n[string] ? i18n[string] : string;
    }
};

// todo: This method is used only in VE and should be moved to vcms
export function areaselect() {
    $.fn.areaselect = function () {
        return this.each(function () {
            $(this).on('change', function () {
                const group = $(this).data('area-group-value');
                let area = null;

                if (typeof $().selectize === 'function' && this.selectize) {
                    this.selectize.refreshOptions(false);
                    if (this.selectize.getValue()) {
                        area = this.selectize.options[this.selectize.getValue()].data.area;
                    }
                } else {
                    area = $(this).val();
                }

                if (group) {
                    $('*[data-area]' + (group ? `[data-area-group="${group}"]` : '')).hide();
                }

                if (area) {
                    $('*[data-area="' + area + '"]').show();
                }
            }).trigger('change');
        });
    };
}
