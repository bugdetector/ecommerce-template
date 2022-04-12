$(function(){
    let input = $("#input_delivery_date");
    let default_value = input.val();
    input.val("");
    let disabledDays = input.data("days-of-week-disabled");
    if(!disabledDays){
        disabledDays = [];
    }
    if(disabledDays && disabledDays.length < 7){
        disabledDays.push(6);
        disabledDays.push(0);
        flatpickr(input, {
            dateFormat: "d-m-Y",
            locale: language,
            defaultDate: default_value,
            allowInput: true,
            minDate: input.data("start-of"),
            maxDate: moment().add(2, "week").format("DD-MM-YYYY"),
            "disable": [
                function(date) {
                    // return true to disable
                    return disabledDays.includes(date.getDay());
        
                }
            ]
        });
    }else if(disabledDays && disabledDays.length == 7){
        input.attr("disabled", "disabled");
        input.attr("placeholder", "Your postcode does not exist in delivery list.");
    }
    input.val(default_value);
})