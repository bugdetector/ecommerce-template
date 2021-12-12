import "./jquery.creditCardValidator";
import "./payment.scss";
$(function ($) {
    $("#input_card_number").on("keydown", function(e){
        if(e.which != 8){//backspace
            let rawNumber = this.value.replaceAll(" ", "");
            if(rawNumber.length > 0 && rawNumber.length % 4 == 0 && rawNumber.length < 16){
                this.value += " ";
            }
        }
    }).on("keyup", function (e) {
        let cardInfo = $(this).validateCreditCard();
        let cardImageClass = null;
        if (cardInfo.card_type) {
            // laser, dankort, maestro, uatp, mir icons not supported.
            switch (cardInfo.card_type.name) {
                case "diners_club_carte_blanche":
                case "diners_club_carte_blanche":
                    cardImageClass = "cc-diners-club";
                    break;
                case "visa_electron":
                    cardImageClass = "cc-visa";
                    break;
                default:
                    cardImageClass = "cc-" + cardInfo.card_type.name;
                    break;


            }
        }
        if (cardImageClass) {
            $("#card-type-image").html(
                `<i class='fab fa-${cardImageClass}'></i>`
            );
        } else {
            $("#card-type-image").html("<i class='fa fa-credit-card'></i>");
        }

        if(cardInfo.valid){
            focusNext(this);
        }
    })

    $("#input_card_expire").on("keydown", function (e) {
        if (e.which != 8) { // backspace
            let value = this.value + e.key;
            if(e.which == 229){ // android space
                value = this.value + " ";
            }
            let currentYear = (new Date()).getFullYear().toString();
            if (value.length == 1) {
                if (value > 1) {
                    this.value = "0" + value + "/" + currentYear.slice(0, -2);
                    e.preventDefault();
                }
            } else if (value.length == 2) {
                value = parseInt(value);
                if (value <= 9) {
                    this.value = "0" + value + "/" + currentYear.slice(0, -2);
                    e.preventDefault();
                } else if (value <= 12) {
                    this.value = value + "/" + currentYear.slice(0, -2);
                    e.preventDefault();
                } else {
                    e.preventDefault();
                }
            } else if (value.length == 7) {
                let input = value.split("/");
                let inputDate = new Date();
                inputDate.setMonth(input[0] - 1);
                inputDate.setFullYear(input[1]);

                if (inputDate.getTime() < (new Date()).getTime()) {
                    e.preventDefault();
                }else{
                    focusNext(this); 
                }               
            } else if (value.length > 7) {
                e.preventDefault();
            }
        }
    }).on("keyup", function(){
        this.value = this.value.replaceAll(" ", "");
    })

    $("#input_card_cv2").on("keydown, keyup", function(e){
        if(e.which != 8){//backspace
            if(this.value.length == 3){
                e.preventDefault();
                focusNext(this);
            }
        }
    })

    function focusNext(element){
        setTimeout(function(e){
            var focusable = $('input').filter(':visible');
            focusable.eq(focusable.index(element) + 1).focus();
        }, 50);
    }

    $(".saved_card").on("change", function(){
        console.log(this);
        $(".saved_card:not([value='" + this.value +"'])").each(function(i, el){
            $(el).closest(".card-body").addClass("btn-light").removeClass("border-success");
        })
        $(this).closest(".card-body").removeClass("btn-light").addClass("border-success");
    })

    $(".saved_card").each(function(i, el){
        let cardInfo = $(el).closest(".card-body").find(".card-number").validateCreditCard();
        let cardImageClass = null;
        if (cardInfo.card_type) {
            // laser, dankort, maestro, uatp, mir icons not supported.
            switch (cardInfo.card_type.name) {
                case "diners_club_carte_blanche":
                case "diners_club_carte_blanche":
                    cardImageClass = "cc-diners-club";
                    break;
                case "visa_electron":
                    cardImageClass = "cc-visa";
                    break;
                default:
                    cardImageClass = "cc-" + cardInfo.card_type.name;
                    break;


            }
        }
        let icon = $(el).closest(".card-body").find(".card-icon");
        if (cardImageClass) {
            icon.addClass(`fab fa-${cardImageClass}`);
        } else {
            icon.addClass(`fa fa-credit-card`);
        }
    })

    $(".remove-card").on("click", function(e){
        e.preventDefault();
        let button = $(this);
        let cardId = button.data("card-id");
        bootbox.confirm({
            title: _t("warning"),
            message: _t("record_remove_accept"),
            callback: function(result){
                if(result){
                    $.ajax({
                        url: `${root}/ajax/removeCard`,
                        method: "post",
                        data: {"cardId" : cardId},
                        success: function(){
                            button.closest(".card").fadeOut();
                        }
                    });
                }
            }
        })
    })
})