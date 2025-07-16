BX.namespace('SysP.UserPropBooking');

BX.ready(function() {
	const bookingLinks = document.querySelectorAll('a[data-type="userpropbooking"]');

	bookingLinks.forEach(function(link) {
		BX.bind(link, 'click', function(e) {
			e.preventDefault();

			let procedureId= link.getAttribute('data-proc-id');
			let doctorId = link.getAttribute('data-doctor-id');

			BX.SysP.UserPropBooking.popup = BX.SysP.UserPropBooking.createPopup(procedureId, doctorId);
			BX.SysP.UserPropBooking.popup.show();
		});
	});
})

BX.SysP.UserPropBooking.createPopup = function(procedureId, doctorId) {
	if (procedureId === '' || doctorId === '') {
		return;
	}

	var formContent = BX.create('div', {
		props: {className: 'ui-form'},
		html: `
			<div id="userprop-booking-errors" class="ui-alert ui-alert-danger" style="display:none;"></div>			
			<div class="ui-form-row">
				<div class="ui-form-label">
					<div class="ui-ctl-label-text">${BX.message('SYSP_USERPROP_BOOKING_FIO_LABEL')}</div>
				</div>
				<div class="ui-form-content">
					<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
						<input type="text" class="ui-ctl-element" id="userprop-booking-fio" placeholder="${BX.message('SYSP_USERPROP_BOOKING_FIO_PLACEHOLDER')}">
					</div>
				</div>
			</div>
			<div class="ui-form-row">
				<div class="ui-form-label">
					<div class="ui-ctl-label-text">${BX.message('SYSP_USERPROP_BOOKING_DT_LABEL')}</div>
				</div>
				<div class="ui-form-content">
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-datetime ui-ctl-w100">
						<div class="ui-ctl-after ui-ctl-icon-calendar"></div>
						<input type="text" class="ui-ctl-element" id="userprop-booking-datetime" placeholder="${BX.message('SYSP_USERPROP_BOOKING_DT_PLACEHOLDER')}">
					</div>
				</div>
			</div>
			<input type="hidden" id="userprop-booking-doctor-id" value="${doctorId}">
			<input type="hidden" id="userprop-booking-procedure-id" value="${procedureId}">
		`
	});

	return BX.PopupWindowManager.create("userpropbooking-popup", null, {
		compatibleMode: true,
		content: formContent,
		width: 500, // ширина окна
		height: 430, // высота окна
		zIndex: 100, // z-index
		offsetTop: 0,
		offsetLeft: 0,
		closeIcon: {
			// объект со стилями для иконки закрытия, при null - иконки не будет
			opacity: 1
		},
		titleBar: BX.message('SYSP_USERPROP_BOOKING_POPUP_TITLE'),
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
				text: BX.message('SYSP_USERPROP_BOOKING_POPUP_APPLY'), // текст кнопки
				id: 'save-btn', // идентификатор
				className: 'ui-btn ui-btn-success', // доп. классы
				events: {
					click: function () {
						const errorsBlock = BX('userprop-booking-errors');
						BX.hide(errorsBlock);

						let fio = BX('userprop-booking-fio').value;
						let dateString = BX('userprop-booking-datetime').value;
						let procedureId = BX('userprop-booking-procedure-id').value;
						let doctorId = BX('userprop-booking-doctor-id').value;

						if (fio === '') {
							errorsBlock.innerHTML = BX.message('SYSP_USERPROP_BOOKING_ERROR_FIO');
							BX.show(errorsBlock);
							return;
						}

						if (/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$/.test(dateString) === false) {
							errorsBlock.innerHTML = BX.message('SYSP_USERPROP_BOOKING_ERROR_DATE');
							BX.show(errorsBlock);
							return;
						}

						BX.ajax.runAction('sysp:userpropbooking.BookingController.create', {
							data: {
								name: fio,
								dateString: dateString,
								doctorId: doctorId,
								procedureId: procedureId
							}
						}).then(function(response) {
							console.log('Успех:', response);
							window.location.href = '/services/lists/18/element/0/' + response.data['id'] + '/?list_section_id=';
						}).catch(function(response) {
							const errorsBlock = BX('userprop-booking-errors');
							errorsBlock.innerHTML = response.errors[0].message
							errorsBlock.style.display = 'block';
							console.error('Ошибка:', response);
						});
					}
				}
			}),
			new BX.PopupWindowButton({
				text: BX.message('SYSP_USERPROP_BOOKING_POPUP_CANCEL'),
				id: 'copy-btn',
				className: 'ui-btn ui-btn-primary',
				events: {
					click: function () {
						BX.SysP.UserPropBooking.popup.destroy();
					}
				}
			})
		],
		events: {
			onAfterPopupShow: function () {
				BX.bind(
					BX('userprop-booking-datetime'),
					'click',
					function() {
						BX.calendar({
							node: this,
							field: this,
							bTime: true,
							bHideTime: false,
						});
					}
				)
			}
		}
	});
}