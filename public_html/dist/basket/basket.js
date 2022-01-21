/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "../../";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 21);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./ecommerce_theme/src/components/basket/basket.js":
/*!*********************************************************!*\
  !*** ./ecommerce_theme/src/components/basket/basket.js ***!
  \*********************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _basket_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./basket.scss */ "./ecommerce_theme/src/components/basket/basket.scss");
/* harmony import */ var _basket_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_basket_scss__WEBPACK_IMPORTED_MODULE_0__);
$(function($){$(document).on("click",".basket-item",function(e){e.stopPropagation();}).on("click",".save-quantity",function(e){e.preventDefault();var itemId=$(this).data("item");var quantityInput=$(this).closest(".basket-item").find(".quantity[data-item=\"".concat(itemId,"\"]"));var variationInput=$(this).closest(".basket-item").find(".variation_select[data-item=\"".concat(itemId,"\"]"));var variation=variationInput.length>0?variationInput.val():$(this).data("variant");var refresh=$(this).hasClass("refresh-after-add");var place=$(this).parents(".product-list-container").data("place");if(!variation){variation=null;}bootbox.prompt({title:_t("please_enter_quantity"),value:quantityInput.val(),centerVertical:true,inputType:'number',min:1,buttons:{cancel:{label:_t("cancel"),className:'btn-danger'},confirm:{label:_t("add_to_basket"),className:'btn-info'}},callback:function callback(result){if(result){saveItemToBasket(itemId,result,variation,refresh,place);}}});}).on("click",".quantity-down, .quantity-up",function(){var itemId=$(this).data("item");var quantityInput=$(this).closest(".basket-item").find(".quantity[data-item=\"".concat(itemId,"\"]"));var variationInput=$(this).closest(".basket-item").find(".variation_select[data-item=\"".concat(itemId,"\"]"));var variation=variationInput.length>0?variationInput.val():$(this).data("variant");var quantity=quantityInput.val();if($(this).hasClass("quantity-down")){quantity--;}else{quantity++;}if(!variation){variation=null;}saveItemToBasket(itemId,quantity,variation);}).on("click",".drop-from-basket",function(e){e.preventDefault();var itemId=$(this).data("item");var variation=$(this).data("variant");alert({message:_t("record_remove_accept"),callback:function callback(){saveItemToBasket(itemId,0,variation);var basketProductCard=$(".basket-item .drop-from-basket[data-item='".concat(itemId,"'][data-variant='").concat(variation,"']")).closest(".basket-item");basketProductCard.fadeOut("slow").delay(500,function(){basketProductCard.remove();});var navCard=$(".nav-item.basket-item[data-item='".concat(itemId,"'][data-variant='").concat(variation,"']"));navCard.fadeOut("slow").delay(500,function(){navCard.remove();});}});}).on("click",".empty-basket",function(e){e.preventDefault();alert({message:_t("empty_basket_confirm"),callback:function callback(){$.ajax({url:root+"/api/cleanBasket",success:function success(){setTimeout(function(){location.reload();},1000);}});}});}).on("click",".confirm_sundries",function(e){e.preventDefault();var button=$(this);alert({message:_t("sundries_delivery_confirm"),okLabel:_t("yes"),callback:function callback(){$(document).off("click",".confirm_sundries");button.click();}});}).on("click",".nonlogin-add-to-basket",function(e){e.preventDefault();var loginFrame=$("<iframe class='w-100 border-0 rounded' src='".concat(root,"/login' style='min-height: 80vh;' ></iframe>"));var dialog=bootbox.dialog({title:_t("login"),message:loginFrame,closeButton:true});loginFrame.on("load",function(e){var frameUrl=loginFrame[0].contentWindow.location.href;if(![root+"/login",root+"/register",root+"/forgetpassword"].includes(frameUrl)){dialog.modal("hide");location.reload();}});});window.saveItemToBasket=function(itemId){var quantity=arguments.length>1&&arguments[1]!==undefined?arguments[1]:null;var variation=arguments.length>2&&arguments[2]!==undefined?arguments[2]:null;var refresh=arguments.length>3&&arguments[3]!==undefined?arguments[3]:false;var place=arguments.length>4&&arguments[4]!==undefined?arguments[4]:null;var data={itemId:itemId};if(quantity!==null){data.quantity=quantity;}if(variation!==null){data.variation=variation;}if(place!==null){data.place=place;}$.ajax({url:"".concat(root,"/api/addItemToBasket"),method:"post",dataType:"json",data:data,success:function success(response){if(refresh){location.reload();}var data=response.data;var itemCard=$(".product-item[data-item='".concat(data.product,"']"));var itemName=itemCard.find(".item-name").first().text();toastr.success(_t("added_to_basket",[data.quantity+", "+itemName]));if(data.quantity>0&&$(".shopping-basket .basket-item[data-item='".concat(data.product,"'][data-variant='").concat(variation,"']")).length==0){var itemImageUrl=itemCard.find("img").attr("src");var variationName=$(".variation_select:first").find("option[value='".concat(variation,"']")).text();var template="<div class=\"nav-item basket-item\" data-item=\"".concat(data.product,"\" data-variant='").concat(variation,"'>\n                            <div class=\" d-flex align-items-center dropdown-item\" href=\"#\">\n                                <img src=\"").concat(itemImageUrl,"\" \n                                alt=\"").concat(itemName,"\" \n                                class=\"dropdown-list-image me-3 rounded-circle\">\n                                <div class=\"\">\n                                    <text class=\"fw-bold\">\n                                        ").concat(itemName," ").concat(variation?" - ".concat(variationName):"","\n                                    </text>\n                                    <br>\n                                    <button type='button' class='btn btn-sm btn-danger drop-from-basket'\n                                    data-item='").concat(data.product,"' data-variant='").concat(variation,"'>\n                                        <i class='fa fa-trash'></i>\n                                    </button>\n                                    <div class='btn-group my-2'>\n                                        <button type='button' class='btn btn-sm btn-info quantity-down'\n                                        data-item='").concat(data.product,"' data-variant='").concat(variation,"'>\n                                            <i class='fa fa-minus'></i>\n                                        </button>\n                                        <input type='number' class='btn btn-sm btn-primary quantity'\n                                        data-item='").concat(data.product,"' data-variant='").concat(variation,"'\n                                        value='").concat(data.quantity,"' readonly/>\n                                        <button type='button' class='btn btn-sm btn-info quantity-up'\n                                        data-item='").concat(data.product,"' data-variant='").concat(variation,"'>\n                                            <i class='fa fa-plus'></i>\n                                        </button>\n                                    </div>\n                                    <div class=\"total-value fw-bold\" data-item=\"").concat(data.product,"\" data-variant='").concat(variation,"'>\n                                        0.00\n                                    </div>\n                                </div>\n                            </div>\n                        </div>");$(".shopping-basket .dropdown-menu .checkout-section").after(template);}if(data.product&&variation){$(".quantity[data-item=\"".concat(data.product,"\"][data-variant='").concat(variation,"']")).val(data.quantity);$(".item-vat[data-item=\"".concat(data.product,"\"][data-variant='").concat(variation,"']")).text("\u20BA".concat(data.item_vat.toFixed(2)));$(".total-value[data-item=\"".concat(data.product,"\"][data-variant='").concat(variation,"']")).text("\u20BA".concat(data.total_price.toFixed(2)));$(".my-price[data-item=\"".concat(data.product,"\"][data-variant='").concat(variation,"']")).text(data.item_per_price.toFixed(2));}else{$(".quantity[data-item=\"".concat(data.product,"\"]")).val(data.quantity);$(".item-vat[data-item=\"".concat(data.product,"\"]")).text("\u20BA".concat(data.item_vat.toFixed(2)));$(".total-value[data-item=\"".concat(data.product,"\"]")).text("\u20BA".concat(data.total_price.toFixed(2)));$(".my-price[data-item=\"".concat(data.product,"\"]")).text(data.item_per_price.toFixed(2));}$(".basket-subtotal").text(data.subtotal.toFixed(2));$(".shop-item-count").text(data.item_count);$(".delivery-value").text(data.delivery.toFixed(2));$(".vat-value").text(data.vat.toFixed(2));$(".basket-total-value").text(data.total.toFixed(2));var basketIcon=$(".shopping-basket .fa-shopping-basket").parent();basketIcon.addClass("animate__animated animate__swing");setTimeout(function(){basketIcon.removeClass("animate__animated animate__swing");},1000);if(data.for_free_delivery>0){$(".for-free-delivery").closest(".alert").fadeIn();$(".for-free-delivery").text(data.for_free_delivery.toFixed(2));}else{$(".for-free-delivery").closest(".alert").fadeOut();}},error:function error(response){var quantity=response.responseJSON.data.quantity;saveItemToBasket(itemId,quantity,variation);}});};});

/***/ }),

/***/ "./ecommerce_theme/src/components/basket/basket.scss":
/*!***********************************************************!*\
  !*** ./ecommerce_theme/src/components/basket/basket.scss ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ 21:
/*!***************************************************************!*\
  !*** multi ./ecommerce_theme/src/components/basket/basket.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! ./ecommerce_theme/src/components/basket/basket.js */"./ecommerce_theme/src/components/basket/basket.js");


/***/ })

/******/ });
//# sourceMappingURL=basket.js.map