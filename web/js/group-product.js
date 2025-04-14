// web/js/group-product.js

$(document).ready(function(){
    /**
     * Обработчик кнопки "Выполнено"
     */
    $(document).on('click', '.complete-order', function(){
        var groupId = $(this).data('id');
        updateOrderStatus(groupId, 'Выполнено', $(this));
    });

    /**
     * Обработчик кнопки "Отклонено"
     */
    $(document).on('click', '.reject-order', function(){
        var groupId = $(this).data('id');
        updateOrderStatus(groupId, 'Отклонено', $(this));
    });

    /**
     * Функция для обновления статуса заказа
     * @param {number} groupId - ID заказа
     * @param {string} status - Новый статус
     * @param {object} button - Кнопка, вызвавшая действие
     */
    function updateOrderStatus(groupId, status, button){
        if(!confirm('Вы уверены, что хотите установить статус "' + status + '" для этого заказа?')){
            return;
        }

        $.ajax({
            url: baseUrl + '/group-product/update-status',
            type: 'POST',
            data: {
                id: groupId,
                status: status,
                _csrf: csrfToken // Обязательно передаем CSRF-токен
            },
            success: function(response){
                if(response.success){
                    // Обновляем статус на странице
                    var panelHeading = $('#heading' + groupId).find('.status-label');
                    // Удаляем все возможные классы статуса
                    panelHeading.removeClass('label-primary label-success label-danger label-default');

                    // Добавляем новый класс статуса
                    switch(status){
                        case 'Выполнено':
                            panelHeading.addClass('label-success');
                            break;
                        case 'Отклонено':
                            panelHeading.addClass('label-danger');
                            break;
                        default:
                            panelHeading.addClass('label-default');
                            break;
                    }

                    // Обновляем текст статуса
                    panelHeading.text(status);

                    // Удаляем кнопки после изменения статуса
                    button.closest('.btn-group').remove();

                    // Если статус "Выполнено", добавляем кнопку "Скачать чек"
                    if(status === 'Выполнено' && response.receipt_url){
                        var panelBody = $('#collapse' + groupId).find('.panel-body');
                        // Проверяем, есть ли уже кнопка "Скачать чек", чтобы избежать дублирования
                        if(panelBody.find('a.btn-primary').length === 0){
                            panelBody.append('<a href="' + response.receipt_url + '" class="btn btn-primary" data-pjax="0">Скачать чек</a>');
                        }
                    }

                    // Отображение сообщения об успехе с использованием Bootstrap Alert
                    showAlert(response.message, 'success');
                } else {
                    // Отображение сообщения об ошибке
                    showAlert('Ошибка: ' + response.message, 'danger');
                }
            },
            error: function(xhr, status, error){
                console.error('AJAX Error:', status, error);
                showAlert('Произошла ошибка при обновлении статуса заказа.', 'danger');
            }
        });
    }

    /**
     * Функция для отображения Bootstrap Alert
     * @param {string} message - Сообщение
     * @param {string} type - Тип алерта (success, danger, etc.)
     */
    function showAlert(message, type){
        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span></button>' +
            message + '</div>';
        $('.group-product-index').prepend(alertHtml);
    }
});
