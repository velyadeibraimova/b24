BX.BookGrid = {
    deleteElement: function (componentId, id) {
        BX.UI.Notification.Center.notify({
            content: 'Вы уверены, что хотите удалить этот элемент?',
            autoHideDelay: 5000,
            actions: [
                {
                    title: 'Да',
                    events: {
                        click: function () {
                            BX.ajax.runComponentAction(
                                'otus:book.grid',
                                'deleteElement',
                                {
                                    mode: 'ajax',
                                    data: {id: id}
                                }
                            ).then(function (response) {
                                if (response.data.success) {
                                    BX.Main.gridManager.getInstanceById(componentId).reload();
                                } else {
                                    BX.UI.Notification.Center.notify({
                                        content: 'Ошибка при удалении: ' + response.data.errors.join(', '),
                                        autoHideDelay: 5000,
                                    });
                                }
                            });
                        }
                    }
                },
                {
                    title: 'Нет',
                    events: {
                        click: function () {
                            this.parentNode.parentNode.remove();
                        }
                    }
                }
            ]
        });
    },
    createTestElement: function () {
        BX.ajax.runComponentAction(
            'otus:book.grid',
            'createTestElement',
            {
                mode: 'ajax'
            }
        ).then(function (response) {
            if (response.data.success) {
                BX.Main.gridManager.getInstanceById('BOOK_GRID').reload();
            } else {
                BX.UI.Notification.Center.notify({
                    content: 'Ошибка при создании тестового элемента: ' + response.data.errors.join(', '),
                    autoHideDelay: 5000,
                });
            }
        });
    }

};
