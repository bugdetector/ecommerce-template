import "./category_navbar.scss";

$(function($){
    $(".navbar-toggler").on("click", function(){
        setTimeout(function(){
            $("body").toggleClass("sidebar-toggled");
        }, 500);
    })
    $(document).on("click", "#category-tree .dropdown-menu a", function(e){
        $(this).parents(".dropdown-menu").addClass('d-block');
    });
})