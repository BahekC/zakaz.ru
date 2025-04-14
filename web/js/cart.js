// web/js/cart.js

$(document).ready(function(){
    /**
     * Добавление товара в корзину
     */
    $(document).on('click', '.add-to-cart', function(){
        var productId = $(this).data('product-id');
        var qty = $('.quantity-field[data-product-id="' + productId + '"]').val();

        $.ajax({
            url: baseUrl + '/cart/add', // baseUrl определяется глобально
            type: 'POST',
            data: {
                idProduct: productId,
                quantity: qty,
                //_csrf: csrfToken // csrfToken определяется глобально
            },
            success: function(response){
                if(response.success){
                    alert(response.message);
                    // Обновление количества в навигационном меню
                    updateCartCount(response.cartCount);
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error){
                console.error('AJAX Error:', status, error);
                alert('Ошибка при добавлении товара в корзину.');
            }
        });
    });

    /**
     * Увеличение количества товара на странице товара
     */
    $(document).on('click', '.increase-qty', function(){
        var productId = $(this).data('product-id');
        var qtyInput = $('.quantity-field[data-product-id="' + productId + '"]');
        var currentVal = parseInt(qtyInput.val());
        if (!isNaN(currentVal)) {
            qtyInput.val(currentVal + 1);
        }
    });

    /**
     * Уменьшение количества товара на странице товара
     */
    $(document).on('click', '.decrease-qty', function(){
        var productId = $(this).data('product-id');
        var qtyInput = $('.quantity-field[data-product-id="' + productId + '"]');
        var currentVal = parseInt(qtyInput.val());
        if (!isNaN(currentVal) && currentVal > 1) {
            qtyInput.val(currentVal - 1);
        }
    });

    /**
     * Увеличение количества товара в корзине
     */
    $(document).on('click', '.cart-increase-qty', function(){
        var productId = $(this).data('product-id');
        var qtyInput = $('.cart-quantity-field[data-product-id="' + productId + '"]');
        var currentVal = parseInt(qtyInput.val());
        if (!isNaN(currentVal)) {
            var newVal = currentVal + 1;
            qtyInput.val(newVal);
            updateCart(productId, newVal);
        }
    });

    /**
     * Уменьшение количества товара в корзине
     */
    $(document).on('click', '.cart-decrease-qty', function(){
        var productId = $(this).data('product-id');
        var qtyInput = $('.cart-quantity-field[data-product-id="' + productId + '"]');
        var currentVal = parseInt(qtyInput.val());
        if (!isNaN(currentVal) && currentVal > 1) {
            var newVal = currentVal - 1;
            qtyInput.val(newVal);
            updateCart(productId, newVal);
        }
    });

    /**
     * Обработка ручного ввода количества товара в корзине
     */
    $(document).on('change', '.cart-quantity-field', function(){
        var productId = $(this).data('product-id');
        var qty = parseInt($(this).val());
        if (isNaN(qty) || qty < 1) {
            qty = 1;
            $(this).val(qty);
        }
        updateCart(productId, qty);
    });

    /**
     * Удаление товара из корзины
     */
    $(document).on('click', '.remove-item', function(){
        if(!confirm('Вы уверены, что хотите удалить этот товар из корзины?')){
            return;
        }
        var productId = $(this).data('product-id');
        $.ajax({
            url: baseUrl + '/cart/remove', // baseUrl определяется глобально
            type: 'POST',
            data: {
                idProduct: productId,
                //_csrf: csrfToken // csrfToken определяется глобально
            },
            success: function(response){
                if(response.success){
                    alert(response.message);
                    // Обновление количества в навигационном меню
                    updateCartCount(response.cartCount);
                    // Обновление содержимого корзины
                    location.reload(); // Можно оптимизировать, обновляя только часть страницы
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error){
                console.error('AJAX Error:', status, error);
                alert('Ошибка при удалении товара из корзины.');
            }
        });
    });

    /**
     * Оформление заказа
     */
    $(document).on('click', '#checkout-button', function(){
        if(confirm('Вы уверены, что хотите оформить заказ?')){
            $.ajax({
                url: baseUrl + '/cart/checkout', // baseUrl определяется глобально
                type: 'POST',
                data: {
                    //_csrf: csrfToken // csrfToken определяется глобально
                },
                success: function(response){
                    if(response.success){
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(){
                    alert('Ошибка при оформлении заказа.');
                }
            });
        }
    });

    /**
     * Функция обновления количества товаров в корзине
     * @param {number} cartCount - Новое количество товаров
     */
    function updateCartCount(cartCount){
        if(cartCount > 0){
            $('.navbar-nav .cart-count').html(' (' + cartCount + ')');
        } else {
            $('.navbar-nav .cart-count').html('');
        }
    }

    /**
     * Функция обновления корзины
     * @param {number} productId - ID продукта
     * @param {number} quantity - Новое количество
     */
    function updateCart(productId, quantity){
        $.ajax({
            url: baseUrl + '/cart/update', // baseUrl определяется глобально
            type: 'POST',
            data: {
                idProduct: productId,
                quantity: quantity,
                //_csrf: csrfToken // csrfToken определяется глобально
            },
            success: function(response){
                if(response.success){
                    // Обновление общей суммы и количества товаров в корзине
                    updateCartCount(response.cartCount);
                    // Можно обновить отображение общей суммы без перезагрузки страницы
                    location.reload(); // Перезагрузка страницы для обновления отображения корзины
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error){
                console.error('AJAX Error:', status, error);
                alert('Ошибка при обновлении корзины.');
            }
        });
    }
});

$(document).ready(function(){
    /**
     * Добавление товара в корзину
     */
    $(document).on('click', '.add-to-cart-btn', function(){
        var productId = $(this).data('product-id');
        var price = $(this).data('price');
        var availableCount = $(this).data('count');
        var quantity = $('.quantity-field[data-product-id="' + productId + '"]').val();

        // Проверка количества
        if(quantity < 1){
            alert('Количество должно быть не менее 1.');
            return;
        }

        if(quantity > availableCount){
            alert('Запрашиваемое количество превышает доступное на складе.');
            return;
        }

        $.ajax({
            url: baseUrl + '/cart/add', // baseUrl определяется глобально
            type: 'POST',
            data: {
                idProduct: productId,
                quantity: quantity,
                //_csrf: csrfToken // csrfToken определяется глобально
            },
            success: function(response){
                if(response.success){
                    $('#cart-message').html('<div class="alert alert-success">' + response.message + '</div>');
                    // Обновление количества в навигационном меню
                    updateCartCount(response.cartCount);
                } else {
                    $('#cart-message').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr, status, error){
                console.error('AJAX Error:', status, error);
                $('#cart-message').html('<div class="alert alert-danger">Ошибка при добавлении товара в корзину.</div>');
            }
        });
    });

    /**
     * Обработка ручного ввода количества товара
     */
    $(document).on('change', '.quantity-field', function(){
        var productId = $(this).data('product-id');
        var qty = parseInt($(this).val());
        var maxVal = parseInt($(this).attr('max'));

        if (isNaN(qty) || qty < 1) {
            qty = 1;
            $(this).val(qty);
        } else if (qty > maxVal) {
            qty = maxVal;
            $(this).val(qty);
            alert('Запрашиваемое количество превышает доступное на складе.');
        }
    });

    /**
     * Функция обновления количества товаров в корзине
     * @param {number} cartCount - Новое количество товаров
     */
    function updateCartCount(cartCount){
        if(cartCount > 0){
            $('.navbar-nav .cart-count').html(' (' + cartCount + ')');
        } else {
            $('.navbar-nav .cart-count').html('');
        }
    }
});

