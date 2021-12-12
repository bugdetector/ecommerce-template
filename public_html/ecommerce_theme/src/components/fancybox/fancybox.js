import "@fancyapps/fancybox";
import "./fancybox.scss";


$(function(){
    $().fancybox({
      selector: '[data-fancybox="gallery"]:not(.swiper-slide-duplicate)',
      backFocus : false 
    });
})

