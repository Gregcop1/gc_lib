$.noConflict();
jQuery.fn.extend({
  findPos : function() {
       var obj = jQuery(this).get(0);
       var curleft = obj.offsetLeft || 0;
       var curtop = obj.offsetTop || 0;
       while (obj = obj.offsetParent) {
     curleft += obj.offsetLeft
         curtop += obj.offsetTop
       }
       return {x:curleft,y:curtop};
  }
});

jQuery(document).ready(function() {
    jQuery('<ul/>')
        .addClass('autocomplete_container')
        .appendTo('body');
    jQuery('<div/>')
        .addClass('resultList')
        .appendTo(jQuery('input.autocomplete').parent());

    jQuery('input.autocomplete').keydown(gc.autocomplete.keyDown)
    jQuery('input.autocomplete').keyup(gc.autocomplete.keyUp)
});

if(!gc) {
    var gc = {};
}

gc.autocomplete = {
    autocomplete_array: [],
    currentField: '',

    keyUp: function(e){
        switch (e.keyCode) {
            case 13 :
                jQuery('.autocomplete_container .highlighted').trigger('click');
            break;

            case 40:
                gc.autocomplete.next();
            break;

            case 38:
                gc.autocomplete.prev();
            break;

            default :
                gc.autocomplete.change(this);
        }
    },

    keyDown: function(e){
        switch (e.keyCode) {
            case 13 :
            case 40:
            case 38:
                e.stopPropagation();
                e.preventDefault();
            break;
        }
    },

    next: function() {
        var next = jQuery('.autocomplete_container .highlighted').next();
        if(next.index()==-1){
            next = jQuery('.autocomplete_container li:first-child');
        }
        this.highlightWord(next);
    },

    prev: function() {
        var prev = jQuery('.autocomplete_container .highlighted').prev();
        if(prev.index()==-1){
            prev = jQuery('.autocomplete_container li:last-child');
        }
        this.highlightWord(prev);
    },

    change: function(field) {
        gc.autocomplete.currentField = field;
        var possibilities = this.autocomplete_array[jQuery(gc.autocomplete.currentField).attr('autocomplete_array')];
        var value = jQuery(gc.autocomplete.currentField).val();
        var list = jQuery('.autocomplete_container')
                        .css({
                            'top': parseInt(jQuery(gc.autocomplete.currentField).findPos().y+20)+'px',
                            'left': parseInt(jQuery(gc.autocomplete.currentField).findPos().x)+'px',
                        });

        list.empty();
        list.append(jQuery('<li/>')
                        .attr('value', 'new')
                        .attr('label', value)
                        .html('Créer <strong>'+value+'</strong>')
                        .click(this.selectWordOnList));
        
        for(i in possibilities) {
            var curr = possibilities[i];

            var reg = new RegExp(value.split('').join('[\\w+@+:]*'), "i");
            if((typeof curr === "object") && curr.label.match(reg)){
                list.append(jQuery('<li/>')
                                .attr('value', curr.id)
                                .attr('label', curr.label)
                                .html(curr.label)
                                .click(this.selectWordOnList));
            }
        }

        if(value && list.children('li').length) {
            this.showAutoCompletePopup();
        }else {
            this.removeAutoCompletePopup()
        }
    },

    showAutoCompletePopup: function() {
        this.highlightWord(jQuery('.autocomplete_container').children('li').first());
        jQuery('.autocomplete_container').show();
    },

    removeAutoCompletePopup: function() {
        jQuery('.autocomplete_container').empty()
                                        .hide();
    },

    selectWordOnList: function() {
        gc.autocomplete.selectWord({
            id: jQuery(this).attr('value'),
            label: jQuery(this).attr('label')
        });
        gc.autocomplete.removeAutoCompletePopup();
    },

    highlightWord: function(container) {
        container.siblings('li').removeClass('highlighted');
        container.addClass('highlighted');
    },

    selectWord: function(word) {
        console.log([ 'Mot selectionné', word ])
    }
};