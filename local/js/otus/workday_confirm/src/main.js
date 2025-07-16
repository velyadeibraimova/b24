BX.namespace('Otus.Workday_Confirm');
console.log('[Otus.Workday_Confirm] Namespace initialized');

BX.addCustomEvent('onTimeManWindowOpen', function(e) {
	console.log('[onTimeManWindowOpen] Event triggered', e);
	const wrapper = BXTIMEMAN.WND.LAYOUT;
	console.log('[onTimeManWindowOpen] wrapper:', wrapper);
	if (wrapper.hasAttribute('data-has-custom-handler')) {
		console.log('[onTimeManWindowOpen] Already has custom handler, skipping');
		return;
	}
	wrapper.setAttribute('data-has-custom-handler', 'Y');
	console.log('[onTimeManWindowOpen] Custom handler set');

	BX.Event.bind(wrapper, 'click',	function (e) {
			console.log('[wrapper click] Event:', e);
			//skip handle manual capture events
			if (e.detail?.isManual) {
				console.log('[wrapper click] isManual, skipping');
				return;
			}

			//skip handle other childs capture events
			if (!e.target.matches('button.ui-btn.ui-btn-success.ui-btn-icon-start')) {
				console.log('[wrapper click] Target does not match button selector, skipping', e.target);
				return;
			}

			const button = wrapper.querySelector('button.ui-btn.ui-btn-success.ui-btn-icon-start');
			console.log('[wrapper click] Button found:', button);
			if (!button) {
				console.log('[wrapper click] Button not found, aborting');
				return;
			}

			BX.Event.unbindAll(button);
			console.log('[wrapper click] Unbound all events from button');
			BX.Otus.Workday_Confirm.ConfirmWorkday();
		},
		{
			capture: true
		}
	);

	console.log('[onTimeManWindowOpen] Click handler bound');
});

BX.addCustomEvent('onPopupAfterClose', function(popup) {
	console.log('[onPopupAfterClose] Event triggered', popup);
	if (popup.uniquePopupId !== 'workday-confirm') {
		console.log('[onPopupAfterClose] Not workday-confirm popup, skipping');
		return;
	}

	const button = BXTIMEMAN.WND.LAYOUT.querySelector('button.ui-btn.ui-btn-success.ui-btn-icon-start');
	console.log('[onPopupAfterClose] Button for dispatch:', button);
	const customClick = new CustomEvent('click', {
		detail: { isManual: true },
		bubbles: false,
	});
	if (button) {
		console.log('[onPopupAfterClose] Dispatching custom click event');
		button.dispatchEvent(customClick);
	} else {
		console.log('[onPopupAfterClose] Button not found, cannot dispatch custom click');
	}
});

BX.Otus.Workday_Confirm.ConfirmWorkday = function() {
	console.log('[ConfirmWorkday] Called');
	const popupWorkDay = BX.PopupWindowManager.create("workday-confirm", null, {
		compatibleMode: true,
		content: BX.message('OTUS_CONFIRM_CONTENT'),
		width: 400, // ширина окна
		height: 200, // высота окна
		zIndex: 100, // z-index
		offsetTop: 0,
		offsetLeft: 0,
		closeIcon: {
			// объект со стилями для иконки закрытия, при null - иконки не будет
			opacity: 1
		},
		titleBar: BX.message('OTUS_CONFIRM_TITLE'),
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
			opacity: 0.5
		},
		buttons: [
			new BX.PopupWindowButton({
				text: BX.message('OTUS_CONFIRM_YES'), // текст кнопки
				id: 'save-btn', // идентификатор
				className: 'ui-btn ui-btn-success', // доп. классы
				events: {
					click: function () {
						console.log('[ConfirmWorkday] YES button clicked');
						const button = BXTIMEMAN.WND.LAYOUT.querySelector('button.ui-btn.ui-btn-success.ui-btn-icon-start');
						console.log('[ConfirmWorkday] YES button, main button:', button);
						if (button) {
							BX.Event.bind(button, 'click', BX.proxy(BX.CTimeManWindow.prototype.MainButtonClick, BXTIMEMAN.WND));
							console.log('[ConfirmWorkday] Bound MainButtonClick to button');
						} else {
							console.log('[ConfirmWorkday] Main button not found');
						}
						popupWorkDay.close();
						console.log('[ConfirmWorkday] Popup closed after YES');
					}
				}
			}),
			new BX.PopupWindowButton({
				text: BX.message('OTUS_CONFIRM_NO'),
				id: 'copy-btn',
				className: 'ui-btn ui-btn-primary',
				events: {
					click: function () {
						console.log('[ConfirmWorkday] NO button clicked');
						popupWorkDay.close();
						console.log('[ConfirmWorkday] Popup closed after NO');
					}
				}
			})
		],
	});

	console.log('[ConfirmWorkday] Popup created', popupWorkDay);
	popupWorkDay.show();
	console.log('[ConfirmWorkday] Popup shown');
}