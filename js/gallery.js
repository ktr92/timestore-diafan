$(document).ready(function(){
  
  if(document.location.hash)
    var hash = document.location.hash.replace("#", "");

  // console.log(hash);
  
	var items = $('#stage li'),
		itemsByTags = {};
    
	// console.log(items);
  
	// Looping though all the li items:
  // items = items.reverse();
	
	items.each(function(i){
		var elem = $(this),
			tags = elem.data('tags').split(',');
		
		// Adding a data-id attribute. Required by the Quicksand plugin:
		elem.attr('data-id',i);
		
		$.each(tags,function(key,value){
			
			// Removing extra whitespace:
			value = $.trim(value);
			
			if(!(value in itemsByTags)){
				// Create an empty array to hold this item:
				itemsByTags[value] = [];
			}
			
			// Each item is added to one array per tag:
			itemsByTags[value].push(elem);
		});
	});

	// Creating the "Everything" option in the menu:
	createList('Все элементы',items);

	// Looping though the arrays in itemsByTags:
	$.each(itemsByTags,function(k,v){
		createList(k,v);
	});
  
	$('#filter a').on('click',function(e){
		var link = $(this);
		
		link.addClass('active').siblings().removeClass('active');
		
		// Using the Quicksand plugin to animate the li items.
		// It uses data('list') defined by our createList function:
		
		/*$('#stage').quicksand(link.data('list').find('li'));*/
		e.preventDefault();
	});
	
  // if()
  
	$('#filter a:first').click();
	
	function createList(text,items){
		
		// This is a helper function that takes the
		// text of a menu button and array of li items
		
		// Creating an empty unordered list:
		var ul = $('<ul>',{'class':'hidden'});
		
		$.each(items,function(){
			// Creating a copy of each li item
			// and adding it to the list:
			
			$(this).clone().appendTo(ul);
				
			// console.log($(this).children('.fancy'));		
		});
    
		ul.appendTo('#gallery');
    
    // console.log(ul);
    
    //ul.prototype.reverse();
		// Creating a menu item. The unordered list is added
		// as a data parameter (available via .data('list'):
		
		var a = $('<a>',{
			html: text,
			href:'#',
			data: {list:ul}
		});
    
    a.appendTo('#filter');
	}
});