import Swiper from "swiper/swiper-bundle";
import 'swiper/swiper-bundle.css';
$(function(){
    $(".swiper-container").each(function(i, el){
        let data = $(el).data();
        let swiper = new Swiper(el, data);
    })
})