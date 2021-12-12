import "bootstrap-4-autocomplete";
import "./navbar-search.scss";
$(function($){
    $(document).on("input", '#navbar-search-input', function(e){
        let search = this.value;
        $.ajax({
            url: root + "/api/search",
            data : {search: search},
            dataType : "json",
            success : function(response){
                $('#navbar-search-input').autocomplete({
                    source: response.data,
                    onSelectItem: function(item, element) {
                        window.location = `${root}/products?search=` + encodeURIComponent(item.label);
                    },
                    highlightClass: 'text-danger',
                    treshold: 0,
                    maximumItems: 15
                }).trigger("keyup");
            }
        })
    })
})