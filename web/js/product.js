$(document).ready(function(){
    // Обработчик кнопки "Создать карточку товара"
    $('#create-product-button').on('click', function(e){
        e.preventDefault();
        $.ajax({
            url: baseUrl + '/product/create',
            type: 'GET',
            success: function(data){
                $('#product-modal .modal-body').html(data);
                $('#product-modal .modal-title').text('Создать карточку товара');
                $('#product-modal').modal('show'); // открываем модальное окно
            },
            error: function(){
                alert('Произошла ошибка при загрузке формы создания товара.');
            }
        });
    });

    // Обработчик кнопки "Редактировать"
    $(document).on('click', '.edit-product-button', function(e){
        e.preventDefault();
        var productId = $(this).data('id');
        $.ajax({
            url: baseUrl + '/product/update?id=' + productId,
            type: 'GET',
            success: function(data){
                $('#product-modal .modal-body').html(data);
                $('#product-modal .modal-title').text('Редактировать карточку товара');
                $('#product-modal').modal('show'); // открываем модальное окно
            },
            error: function(){
                alert('Произошла ошибка при загрузке формы редактирования товара.');
            }
        });
    });

    // Флаг для предотвращения повторной отправки формы
    var isSubmitting = false;

    // Перед привязкой снимаем предыдущие обработчики, чтобы не навесить их несколько раз
    $(document).off('beforeSubmit', '#product-form');

    // Обработчик отправки формы создания/редактирования товара через AJAX
    $(document).on('beforeSubmit', '#product-form', function(e){
        e.preventDefault();
        if (isSubmitting) {
            return false;
        }
        isSubmitting = true;
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response){
                if(response.success){
                    $('#product-modal').modal('hide'); // закрываем модальное окно
                    $.pjax.reload({container: '#product-pjax'}); // обновляем список карточек
                } else {
                    // Если есть ошибки валидации, обновляем форму
                    $('#product-modal .modal-body').html(response);
                }
            },
            error: function(){
                alert('Произошла ошибка при сохранении товара.');
            },
            complete: function(){
                isSubmitting = false; // сбрасываем флаг после завершения запроса
            }
        });
       // return false; // предотвращаем стандартную отправку формы
    });

    // Переменная для хранения ID товара, который нужно удалить
    var deleteProductId = null;

    // Обработчик кнопки "Удалить"
    $(document).on('click', '.delete-product-button', function(e){
        e.preventDefault();
        deleteProductId = $(this).data('id');
        $('#delete-product-modal').modal('show'); // открываем модальное окно подтверждения удаления
    });

    // Обработчик подтверждения удаления
    $('#confirm-delete-button').on('click', function(){
        if(deleteProductId){
            $.ajax({
                url: baseUrl + '/product/delete?id=' + deleteProductId,
                type: 'POST',
                success: function(response){
                    if(response.success){
                        $('#delete-product-modal').modal('hide'); // закрываем модальное окно
                        $('#product-' + deleteProductId).remove(); // удаляем карточку из списка
                    } else {
                        alert('Ошибка: ' + response.message);
                    }
                },
                error: function(){
                    alert('Произошла ошибка при удалении товара.');
                }
            });
        }
    });
});
