BX.namespace('Otus.Workday_Confirm');
BX.addCustomEvent('onTimeManWindowOpen', function(e) {
    console.log(1111);
    const wrapper = BXTIMEMAN.WND.LAYOUT;
    if (wrapper.hasAttribute('data-has-custom-handler')) {
        return;
    }
    wrapper.setAttribute('data-has-custom-handler', 'Y');

    BX.Event.bind(wrapper, 'click',	function (e) {
            //skip handle manual capture events
            if (e.detail?.isManual) {
                return;
            }

            //skip handle other childs capture events
            if (!e.target.matches('button.ui-btn.ui-btn-success.ui-btn-icon-start')) {
                return;
            }

            const button = wrapper.querySelector('button.ui-btn.ui-btn-success.ui-btn-icon-start');
            if (!button) {
                return;
            }

            BX.Event.unbindAll(button);
            BX.Otus.Workday_Confirm.ConfirmWorkday();
        },
        {
            capture: true
        }
    );

});

BX.addCustomEvent('onPopupAfterClose', function(popup) {
    if (popup.uniquePopupId !== 'workday-confirm') {
        return;
    }

    const button = BXTIMEMAN.WND.LAYOUT.querySelector('button.ui-btn.ui-btn-success.ui-btn-icon-start');
    const customClick = new CustomEvent('click', {
        detail: { isManual: true },
        bubbles: false,
    });
    button.dispatchEvent(customClick);
});

BX.Otus.Workday_Confirm.ConfirmWorkday = function() {
    const popupWorkDay = BX.PopupWindowManager.create("workday-confirm", null, {
        compatibleMode: true,
        content: 'Вы хотите начать рабочий день?',
        width: 400, // ширина окна
        height: 200, // высота окна
        zIndex: 100, // z-index
        offsetTop: 0,
        offsetLeft: 0,
        closeIcon: {
            // объект со стилями для иконки закрытия, при null - иконки не будет
            opacity: 1
        },
        titleBar: "Начало рабочего дня",
        closeByEsc: true, // закрытие окна по esc
        darkMode: false, // окно будет светлым или темным
        autoHide: true, // закрытие при клике вне окна
        draggable: true, // можно двигать или нет
        resizable: true, // можно ресайзить
        min_height: 100, // минимальная высота окна
        min_width: 100, // минимальная ширина окна
        lightShadow: true, // использовать светлую тень у окна
        angle: false, // появится уголок
        overlay: {
            backgroundColor: 'black',
            opacity: 500
        },
        buttons: [
            new BX.PopupWindowButton({
                text: 'Начать рабочий день',
                id: 'save-btn', // идентификатор
                className: 'ui-btn ui-btn-success', // доп. классы
                events: {
                    click: function () {
                        const button = BXTIMEMAN.WND.LAYOUT.querySelector('button.ui-btn.ui-btn-success.ui-btn-icon-start');
                        BX.Event.bind(button, 'click', BX.proxy(BX.CTimeManWindow.prototype.MainButtonClick, BXTIMEMAN.WND));
                        popupWorkDay.close();
                    }
                }
            }),
            new BX.PopupWindowButton({
                text: 'Отмена',
                id: 'copy-btn',
                className: 'ui-btn ui-btn-primary',
                events: {
                    click: function () {
                        popupWorkDay.close();
                    }
                }
            })
        ],
    });

    popupWorkDay.show();
}