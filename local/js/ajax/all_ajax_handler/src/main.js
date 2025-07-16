BX.ready(function () {
    BX.addCustomEvent("onAjaxSuccess", function (data, config) {
        console.log("✅ AJAX запрос завершился успешно.");
        console.log("➡️ Ответ:", data);
        console.log("➡️ Конфиг запроса:", config);

        if (config && config.url && data.okMessage === 'Комментарий добавлен.') {
            BX.UI.Notification.Center.notify({
                content: "Комментарий добавлен!",
                autoHideDelay: 2000
            });
        }
    });
});
