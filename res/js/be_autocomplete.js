$.noConflict();
jQuery(document).ready(function() {
    jQuery('<ul/>')
        .addClass('autocomplete_container')
        .insertAfter('input.autocomplete');

    jQuery('input.autocomplete').keyup(autocompleteChange)
});

var autocomplete_array = [];
function autocompleteChange() {
    var possibilities = autocomplete_array[jQuery(this).attr('autocomplete_array')];
    var value = jQuery(this).val();
    var list = jQuery(this).siblings('.autocomplete_container');

    list.empty();
    for(i in possibilities) {
        var curr = possibilities[i];
        var reg = new RegExp(value, "i");
        if((typeof curr === "object") && curr.label.match(reg)){
            list.append(jQuery('<li/>')
                            .attr('value', curr.id)
                            .html(curr.label));
        }
    }

    if(value && list.children('li').length) {
        list.show();
    }else {
        list.hide();
    }
}