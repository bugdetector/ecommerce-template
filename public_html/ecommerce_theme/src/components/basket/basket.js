$(function ($) {
    $(document).on("click", ".basket-item", function (e) {
        e.stopPropagation();
    }).on("click", ".save-quantity", function (e) {
        e.preventDefault();
        let itemId = $(this).data("item");
        let quantityInput = $(this).closest(".basket-item").find(`.quantity[data-item="${itemId}"]`);
        let variationInput = $(this).closest(".basket-item").find(`.variation_select[data-item="${itemId}"]`);
        let variation = variationInput.length > 0 ? variationInput.val() : $(this).data("variant");
        let refresh = $(this).hasClass("refresh-after-add");
        let place = $(this).parents(".product-list-container").data("place");
        if (!variation) {
            variation = null;
        }
        bootbox.prompt({
            title: _t("please_enter_quantity"),
            value: quantityInput.val(),
            centerVertical: true,
            inputType: 'number',
            min: 1,
            buttons: {
                cancel: {
                    label: _t("cancel"),
                    className: 'btn-danger',
                },
                confirm: {
                    label: _t("add_to_basket"),
                    className: 'btn-info'
                }
            },
            callback: function (result) {
                if (result) {
                    saveItemToBasket(itemId, result, variation, refresh, place);
                }
            }
        });
    }).on("click", ".quantity-down, .quantity-up", function () {
        let itemId = $(this).data("item");
        let quantityInput = $(this).closest(".basket-item").find(`.quantity[data-item="${itemId}"]`);
        let variationInput = $(this).closest(".basket-item").find(`.variation_select[data-item="${itemId}"]`);
        let variation = variationInput.length > 0 ? variationInput.val() : $(this).data("variant");
        let quantity = quantityInput.val();
        if ($(this).hasClass("quantity-down")) {
            quantity--;
        } else {
            quantity++;
        }
        if (!variation) {
            variation = null;
        }
        saveItemToBasket(itemId, quantity, variation);

    }).on("click", ".drop-from-basket", function (e) {
        e.preventDefault();
        let itemId = $(this).data("item");
        let variation = $(this).data("variant");
        alert({
            message: _t("record_remove_accept"),
            callback: function () {
                saveItemToBasket(itemId, 0, variation);
                let basketProductCard = $(`.basket-item .drop-from-basket[data-item='${itemId}'][data-variant='${variation}']`)
                    .closest(".basket-item");
                basketProductCard.fadeOut("slow").delay(500, function () {
                    basketProductCard.remove();
                });
                let navCard = $(`.nav-item.basket-item[data-item='${itemId}'][data-variant='${variation}']`);
                navCard.fadeOut("slow").delay(500, function () {
                    navCard.remove();
                });
            }
        })
    }).on("click", ".empty-basket", function (e) {
        e.preventDefault();
        alert({
            message: _t("empty_basket_confirm"),
            callback: function () {
                $.ajax({
                    url: root + "/api/cleanBasket",
                    success: function () {
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                });
            }
        })
    }).on("click", ".confirm_sundries", function (e) {
        e.preventDefault();
        let button = $(this);
        alert({
            message: _t("sundries_delivery_confirm"),
            okLabel: _t("yes"),
            callback: function () {
                $(document).off("click", ".confirm_sundries");
                button.click();
            }
        });
    }).on("click", ".nonlogin-add-to-basket", function (e) {
        e.preventDefault();
        let loginFrame = $(`<iframe class='w-100 border-0 rounded' src='${root}/login' style='min-height: 80vh;' ></iframe>`);
        let dialog = bootbox.dialog({
            title: _t("login"),
            message: loginFrame,
            closeButton: true,
        });
        loginFrame.on("load", function (e) {
            let frameUrl = loginFrame[0].contentWindow.location.href;
            if (![
                root + "/login",
                root + "/register",
                root + "/forgetpassword"
            ].includes(frameUrl)) {
                dialog.modal("hide");
                location.reload();
            }
        })
    })

    window.saveItemToBasket = function (itemId, quantity = null, variation = null, refresh = false, place = null) {
        let data = { itemId: itemId };
        if (quantity !== null) {
            data.quantity = quantity;
        }
        if (variation !== null) {
            data.variation = variation;
        }
        if(place !== null){
            data.place = place;
        }
        $.ajax({
            url: `${root}/api/addItemToBasket`,
            method: "post",
            dataType: "json",
            data: data,
            success: function (response) {
                if (refresh) {
                    location.reload();
                }
                let data = response.data;
                let itemCard = $(`.product-item[data-item='${data.product}']`);
                let itemName = itemCard.find(".item-name").first().text();
                toastr.success(
                    _t("added_to_basket", [
                        data.quantity + ", " + itemName
                    ])
                );
                if (data.quantity > 0 && $(`.shopping-basket .basket-item[data-item='${data.product}'][data-variant='${variation}']`).length == 0) {
                    let itemImageUrl = itemCard.find("img").attr("src");
                    let variationName = $(".variation_select:first").find(`option[value='${variation}']`).text();
                    let template =
                        `<div class="nav-item basket-item" data-item="${data.product}" data-variant='${variation}'>
                            <div class=" d-flex align-items-center dropdown-item" href="#">
                                <img src="${itemImageUrl}" 
                                alt="${itemName}" 
                                class="dropdown-list-image me-3 rounded-circle">
                                <div class="">
                                    <text class="fw-bolder">
                                        ${itemName} ${variation ? ` - ${variationName}` : ""}
                                    </text>
                                    <br>
                                    <button type='button' class='btn btn-sm btn-danger drop-from-basket'
                                    data-item='${data.product}' data-variant='${variation}'>
                                        <i class='fa fa-trash'></i>
                                    </button>
                                    <div class='btn-group my-2'>
                                        <button type='button' class='btn btn-sm btn-info quantity-down'
                                        data-item='${data.product}' data-variant='${variation}'>
                                            <i class='fa fa-minus'></i>
                                        </button>
                                        <input type='number' class='btn btn-sm btn-primary quantity'
                                        data-item='${data.product}' data-variant='${variation}'
                                        value='${data.quantity}' readonly/>
                                        <button type='button' class='btn btn-sm btn-info quantity-up'
                                        data-item='${data.product}' data-variant='${variation}'>
                                            <i class='fa fa-plus'></i>
                                        </button>
                                    </div>
                                    <div class="total-value fw-bolder" data-item="${data.product}" data-variant='${variation}'>
                                        0.00
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    $(".shopping-basket .dropdown-menu .checkout-section").after(template);
                }
                if (data.product && variation) {
                    $(`.quantity[data-item="${data.product}"][data-variant='${variation}']`).val(data.quantity);
                    $(`.item-vat[data-item="${data.product}"][data-variant='${variation}']`).text(`₺${data.item_vat.toFixed(2)}`);
                    $(`.total-value[data-item="${data.product}"][data-variant='${variation}']`).text(`₺${data.total_price.toFixed(2)}`);
                    $(`.my-price[data-item="${data.product}"][data-variant='${variation}']`).text(data.item_per_price.toFixed(2));
                } else {
                    $(`.quantity[data-item="${data.product}"]`).val(data.quantity);
                    $(`.item-vat[data-item="${data.product}"]`).text(`₺${data.item_vat.toFixed(2)}`);
                    $(`.total-value[data-item="${data.product}"]`).text(`₺${data.total_price.toFixed(2)}`);
                    $(`.my-price[data-item="${data.product}"]`).text(data.item_per_price.toFixed(2));
                }
                $(".basket-subtotal").text(data.subtotal.toFixed(2));
                $(".shop-item-count").text(data.item_count);
                $(".delivery-value").text(data.delivery.toFixed(2));
                $(".vat-value").text(data.vat.toFixed(2));
                $(".basket-total-value").text(data.total.toFixed(2));

                var basketIcon = $(".shopping-basket .fa-shopping-basket").parent();
                basketIcon.addClass("animate__animated animate__swing");
                setTimeout(function () {
                    basketIcon.removeClass("animate__animated animate__swing");
                }, 1000);
                if (data.for_free_delivery > 0) {
                    $(".for-free-delivery").closest(".alert").fadeIn();
                    $(".for-free-delivery").text(data.for_free_delivery.toFixed(2));
                } else {
                    $(".for-free-delivery").closest(".alert").fadeOut();
                }
            },
            error: function (response) {
                let quantity = response.responseJSON.data.quantity;
                saveItemToBasket(itemId, quantity, variation);
            }
        })
    }
})