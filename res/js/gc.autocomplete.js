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

String.prototype.format = function() {
  var args = arguments;
  return this.replace(/{(\d+)}/g, function(match, number) {
    return typeof args[number] != 'undefined'
      ? args[number]
      : match
    ;
  });
};

String.prototype.decodeURIComponent = function() {
  return decodeURIComponent(this);
};

jQuery(document).ready(function() {
    jQuery('<ul/>')
        .addClass('autocomplete_container')
        .insertAfter(jQuery('input.autocomplete').parents('.typo3-TCEforms'));
    jQuery('<div/>')
        .addClass('resultList')
        .appendTo(jQuery('input.autocomplete').parent());

    jQuery('input.autocomplete').keydown(gc.autocomplete.keyDown)
    jQuery('input.autocomplete').keyup(gc.autocomplete.keyUp)
    gc.autocomplete.buildSelectedList(jQuery('input.autocomplete'));
});

if(!gc) {
    var gc = {};
}

gc.autocomplete = {
    configuration_array: [],
    autocomplete_array: [],
    currentField: '',
    keyUp: function(e){
        switch (e.keyCode) {
            case 188 :
            case 186 :
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
            case 188 :
            case 186 :
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
        var value = jQuery(gc.autocomplete.currentField).val();

        if(value!='') {
            var possibilities = this.autocomplete_array[jQuery(gc.autocomplete.currentField).attr('autocomplete_array')];
            var fieldPos = jQuery(gc.autocomplete.currentField).findPos();

            var list = jQuery('.autocomplete_container')
                            .css({
                                'top': parseInt(fieldPos.y-27)+'px',
                                'left': parseInt(fieldPos.x)+'px',
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
            }
        }else {
            gc.autocomplete.removeAutoCompletePopup();
        }
    },

    showAutoCompletePopup: function() {
        this.highlightWord(jQuery('.autocomplete_container').children('li').first());
        jQuery('.autocomplete_container').show();
    },

    removeAutoCompletePopup: function() {
        jQuery(gc.autocomplete.currentField).val('');
        jQuery('.autocomplete_container').empty()
                                        .hide();
    },

    selectWordOnList: function() {
        gc.autocomplete.selectWord({
            id: jQuery(this).attr('value'),
            label: jQuery(this).attr('label')
        });
    },

    highlightWord: function(container) {
        container.siblings('li').removeClass('highlighted');
        container.addClass('highlighted');
    },

    selectWord: function(word) {
        if(this.isItANewWord(word)) {
            var possibilities = this.autocomplete_array[jQuery(gc.autocomplete.currentField).attr('autocomplete_array')];
            var configuration = this.configuration_array[jQuery(gc.autocomplete.currentField).attr('autocomplete_array')]

            new Ajax.Request('ajax.php', {
                method: 'get',
                parameters: 'ajaxID=tx_gclib_TCAform_autocomplete::saveNewAutoCompleteItem&tableName='+configuration.tableName+'&labelField='+configuration.labelField+'&word='+word.label+'&storagePid='+configuration.storagePid,
                onComplete: function(xhr, json) {
                    var newWord = {
                        id: json.id,
                        label: json.label
                    };
                    this.autocomplete_array[jQuery(gc.autocomplete.currentField).attr('autocomplete_array')].push(newWord);
                    gc.autocomplete.sendToList(newWord);
                }.bind(this),
                onT3Error: function(xhr, json) {
                    console.log('error')
                }.bind(this)
            });
        }else {
            this.sendToList(word);
        }

    },

    sendToList: function(word) {
        var targetField = jQuery(this.currentField).siblings('input[name="'+jQuery(this.currentField).attr('name').replace('_auto','')+'"]');
        var configuration = this.configuration_array[jQuery(gc.autocomplete.currentField).attr('autocomplete_array')]
        var currentValues = jQuery(targetField).attr('value').decodeURIComponent().split(',');
        if(jQuery(targetField).attr('value')=='') {
            currentValues = [];
        }

        var newWord = this.getWordByLabel(word.label);
        if(newWord) {
            var newValue = configuration.fieldFormat.format(newWord.id, newWord.label)

            if(jQuery.inArray(newValue, currentValues) ==-1 ){
                currentValues.push(newValue);
                jQuery(targetField).attr('value', currentValues.join(','));
                this.buildSelectedList();
            }

            gc.autocomplete.removeAutoCompletePopup();
        }
    },

    isItANewWord: function(word) {
        var possibilities = this.autocomplete_array[jQuery('input[name="'+jQuery(this.currentField).attr('name')+'"]').attr('autocomplete_array')];
        for(var i in possibilities){
            if(typeof possibilities[i] == "object" && possibilities[i].label == word.label) {
                return false;
            }
        }

        return true;
    },

    buildSelectedList: function(field) {
        if(!field) {
            field = jQuery(this.currentField);
        }
        var targetField = field.siblings('input[name="'+field.attr('name').replace('_auto','')+'"]');
        var possibilities = this.autocomplete_array[jQuery('input[name="'+field.attr('name')+'"]').attr('autocomplete_array')];
        var list = field.siblings('.resultList');
        var values = targetField.attr('value').split(',');

        list.empty();
        if(targetField.attr('value')!='' && values.length) {
            for(var i in values) {
                if(typeof values[i] == "string") {
                    var id = values[i].match(/\d+/g)[0];
                    var obj = this.getWordById(id, field);
                    if(obj) {
                        list.append(jQuery('<a/>').attr('href','javascript:;')
                                            .attr('label',obj.label)
                                            .addClass('autocomplete_item')
                                            .html(obj.label+'&nbsp;&#10006;')
                                            .click(this.removeFromList));
                    }
                }
            }
        }
    },

    getWordByLabel: function(label, field) {
        if(!field) {
            field = jQuery(this.currentField);
        }
        var possibilities = this.autocomplete_array[jQuery('input[name="'+field.attr('name')+'"]').attr('autocomplete_array')];
        for(var i in possibilities) {
            if(possibilities[i].label == label) {
                return possibilities[i];
            }
        }

        return null;
    },

    getWordById: function(id, field) {
        if(!field) {
            field = jQuery(this.currentField);
        }
        var possibilities = this.autocomplete_array[jQuery('input[name="'+field.attr('name')+'"]').attr('autocomplete_array')];
        for(var i in possibilities) {
            if(possibilities[i].id == id) {
                return possibilities[i];
            }
        }

        return null;
    },

    removeFromList: function() {
        var field = jQuery(this).parent().siblings('.autocomplete');
        gc.autocomplete.currentField = field;
        var targetField = field.siblings('input[name="'+field.attr('name').replace('_auto','')+'"]');
        var values = targetField.attr('value').decodeURIComponent().split(',');
        console.log(field,values);

        var i = 0;
        while(i < values.length) {
            var id = values[i].match(/\d+/g);
            var obj = gc.autocomplete.getWordById(id, field);
            if( obj && obj.label == jQuery(this).attr('label') ) {
                values.splice(i,1);
            }else {
                i++;
            }
        }

        jQuery(targetField).attr('value', values.join(','));
        gc.autocomplete.buildSelectedList();
    }
};