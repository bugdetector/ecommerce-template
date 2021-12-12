
import "./_global.scss";
import toastr from "toastr";
import "toastr/build/toastr.min.css";

window.toastr = toastr;
toastr.options.progressBar = true;
toastr.options.positionClass = "toast-bottom-right";

$(function($){
    $(document).on("change", "#input_address, #input_billing_address", function(){
        if($(this).val() == "#add-new-address"){
            $(this).val("");
            window.location.assign(
                root + "/profile?add-address=true"
            );
        }
    })

    // Scroll to top button appear
    $(document).on('scroll', function () {
        var scrollDistance = $(this).scrollTop();
        if (scrollDistance > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function (e) {
        e.preventDefault();
        window.scroll({
            top: 0,
            behavior: 'smooth'
          });
    });
})