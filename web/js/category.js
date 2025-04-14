// web/js/category.js

$(document).ready(function(){
    /**
     * Обработчик кнопки "Удалить"
     */
    $(document).on('click', '.delete-category', function(e){
        e.preventDefault();
        var categoryId = $(this).data('id');
        $.ajax({
            url: baseUrl +'/category/delete?id=' + categoryId, // Убедитесь, что URL корректный
            type: 'POST',
            success: function(response){
                if(response.success){
                    // Удаляем элемент списка с плавным исчезновением
                    $('#category-' + categoryId).fadeOut(300, function(){
                        $(this).remove();
                    });
                } else {
                    alert('Ошибка: ' + response.message);
                }
            },
            error: function(){
                alert('Произошла ошибка при удалении категории.');
            }
        });
    });

    /**
     * Обработчик кнопки "Редактировать"
     */
    $(document).on('click', '.edit-category', function(e){
        e.preventDefault();
        var categoryId = $(this).data('id');
        var nameDiv = $('#category-name-' + categoryId);
        var currentName = nameDiv.text().trim();

        // Заменяем наименование на поле ввода
        nameDiv.html('<input type="text" class="form-control input-sm" id="edit-input-' + categoryId + '" value="' + currentName + '">');

        // Заменяем кнопки редактирования и удаления на кнопку сохранения
        var buttonsDiv = $('#category-' + categoryId).find('.col-md-4');
        buttonsDiv.html(
            '<button class="btn btn-sm btn-success save-category" data-id="' + categoryId + '" title="Сохранить">' +
            '<span class="glyphicon glyphicon-ok"></span>' +
            '</button>'
        );
    });

    /**
     * Обработчик кнопки "Сохранить"
     */
    $(document).on('click', '.save-category', function(e){
        e.preventDefault();
        var categoryId = $(this).data('id');
        var newName = $('#edit-input-' + categoryId).val().trim();

        if(newName === ''){
            alert('Наименование категории не может быть пустым.');
            return;
        }

        $.ajax({
            url: baseUrl +'/category/update?id=' + categoryId, // Убедитесь, что URL корректный
            type: 'POST',
            data: {
                name: newName,
                //_csrf: yii.getCsrfToken() // Получение CSRF-токена
            },
            success: function(response){
                if(response.success){
                    // Обновляем наименование и восстанавливаем кнопки редактирования и удаления
                    $('#category-name-' + categoryId).text(response.name);

                    var buttonsDiv = $('#category-' + categoryId).find('.col-md-4');
                    buttonsDiv.html(
                        '<a href="#" class="btn btn-sm btn-warning edit-category" data-id="' + categoryId + '" title="Редактировать">' +
                        '<span class="glyphicon glyphicon-edit"></span>' +
                        '</a> ' +
                        '<a href="#" class="btn btn-sm btn-danger delete-category" data-id="' + categoryId + '" title="Удалить">' +
                        '<span class="glyphicon glyphicon-trash"></span>' +
                        '</a>'
                    );
                } else {
                    alert('Ошибка: ' + response.message);
                }
            },
            error: function(){
                alert('Произошла ошибка при сохранении изменений.');
            }
        });
    });

    /**
     * Обработчик формы добавления новой категории
     */
    $(document).on('submit', '#add-category-form', function(e){
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url:form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response){
                if(response.success){
                    // Закрываем модальное окно
                    $('#addCategoryModal').modal('hide');

                    // Очищаем поле ввода
                    form.find('input[name="Category[name]"]').val('');

                    // Добавляем новую категорию в список
                    var newCategoryHtml = '<li class="list-group-item" id="category-' + response.id + '">' +
                        '<div class="row">' +
                        '<div class="col-md-8" id="category-name-' + response.id + '">' +
                        response.name +
                        '</div>' +
                        '<div class="col-md-4 text-right">' +
                        '<a href="#" class="btn btn-sm btn-warning edit-category" data-id="' + response.id + '" title="Редактировать">' +
                        '<span class="glyphicon glyphicon-edit"></span>' +
                        '</a> ' +
                        '<a href="#" class="btn btn-sm btn-danger delete-category" data-id="' + response.id + '" title="Удалить">' +
                        '<span class="glyphicon glyphicon-trash"></span>' +
                        '</a>' +
                        '</div>' +
                        '</div>' +
                        '</li>';

                    // Добавляем элемент в список с анимацией
                    $('.list-group').prepend(newCategoryHtml).hide().fadeIn(300);

                } else {
                    alert('Ошибка: ' + response.message);
                }
            },
            error: function(){
                alert('Произошла ошибка при добавлении категории.');
            }
        });
    });
});
