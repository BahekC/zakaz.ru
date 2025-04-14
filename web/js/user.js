// web/js/user.js

$(document).ready(function(){
    /**
     * Обработчик кнопки "Назначить модератором"
     */
    $(document).on('click', '.assign-moderator', function(e){
        e.preventDefault();
        var userId = $(this).data('id');
        if(!confirm('Вы уверены, что хотите назначить этого пользователя модератором?')){
            return;
        }

        $.ajax({
            url: baseUrl +'/user/assign-moderator',
            type: 'POST',
            data: {
                id: userId,
                //_csrf: yii.getCsrfToken() // Получение CSRF-токена
            },
            success: function(response){
                if(response.success){
                    // Обновляем метку роли
                    $('#user-' + userId + ' .label').text(response.role_label);

                    // Заменяем кнопку назначения на кнопку снятия
                    $('#user-' + userId + ' .assign-moderator').remove();
                    $('#user-' + userId + ' .label').after(
                        '<a href="#" class="btn btn-sm btn-danger remove-moderator" data-id="' + userId + '" title="Снять модератора">' +
                        '<span class="glyphicon glyphicon-star-empty"></span>' +
                        '</a>'
                    );
                } else {
                    alert('Ошибка: ' + response.message);
                }
            },
            error: function(){
                alert('Произошла ошибка при назначении модератора.');
            }
        });
    });

    /**
     * Обработчик кнопки "Снять модератора"
     */
    $(document).on('click', '.remove-moderator', function(e){
        e.preventDefault();
        var userId = $(this).data('id');
        if(!confirm('Вы уверены, что хотите снять этого пользователя с роли модератора?')){
            return;
        }

        $.ajax({
            url: baseUrl +'/user/remove-moderator',
            type: 'POST',
            data: {
                id: userId,
                //_csrf: yii.getCsrfToken() // Получение CSRF-токена
            },
            success: function(response){
                if(response.success){
                    // Обновляем метку роли
                    $('#user-' + userId + ' .label').text(response.role_label);

                    // Заменяем кнопку снятия на кнопку назначения
                    $('#user-' + userId + ' .remove-moderator').remove();
                    $('#user-' + userId + ' .label').after(
                        '<a href="#" class="btn btn-sm btn-warning assign-moderator" data-id="' + userId + '" title="Назначить модератором">' +
                        '<span class="glyphicon glyphicon-star"></span>' +
                        '</a>'
                    );
                } else {
                    alert('Ошибка: ' + response.message);
                }
            },
            error: function(){
                alert('Произошла ошибка при снятии модератора.');
            }
        });
    });
});
