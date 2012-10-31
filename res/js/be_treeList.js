$.noConflict();
jQuery(document).ready(function() {
    jQuery('.category_tree li').click(treeClickOnItem);
});

function treeClickOnItem() {
	if(!jQuery(this).hasClass('notSelectable')) {
    	var select = jQuery(this).parents('.thumbnails').siblings(':first-child').children('select');
    	var field = jQuery(this).parents('table').next('input[type="hidden"]');
    	var classes = jQuery(this).attr('class').split(' ');
    	var id = classes[0].substr(5);
    	if(id && !select.children('option[value="'+id+'"]').html()) {
    		select.append(jQuery('<option/>')
    						.attr('value',id)
    						.attr('title',jQuery(this).html())
    						.html(jQuery(this).html())
    					);
    		if(field.attr('value')) {
    			field.attr('value', field.attr('value')+','+id);
    		}else {
    			field.attr('value', id);
    		}
    	}
    } 
}