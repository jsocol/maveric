// JavaScript Document

window.onload = function () {
	// Hide all the .metadata-content divs, except one from the query string
	var qs = new Querystring;
	var feature = qs.get('feature');
	
	$A($$('div.metadata-content')).each(function(div) {
		if ( !feature || !div.id.match(feature) ) {
			div.hide();
		}
	});
	
	// Add listener to each metadata-header
	$A($$('.metadata-header')).each(function(header) {
		var content = $(header.id.substr(0,header.id.indexOf("-"))+"-content");
		if ( content.getStyle('display') == 'none' ) {
			header.addClassName('closed');
		} else {
			header.addClassName('open');
		}
		header.observe('click', function() {
			if ( content.getStyle('display') == 'none' ) {
				Effect.BlindDown(content);
				header.addClassName('open');
				header.removeClassName('closed');
			} else {
				Effect.BlindUp(content);
				header.addClassName('closed');
				header.removeClassName('open');
			}
		});
	});
	
	// Add hover elements to links
	var resourceRegex = /resource\/view\/(\d+)/;
	var linkedResources = [];
	$A($$('.metadata-content a')).each(function(a){
		if (a.href.match(resourceRegex)) {
			var data = resourceRegex.exec(a.href);
			var r = parseInt(data[1], 10);
			a.observe('mouseover', function (e) {
				if ( linkedResources[r] ) {
					return hoverResource(linkedResources[r], a);
				}
				new Ajax.Request('/taps/resource/view/'+r+'.json', {
					method: 'get',
					onSuccess: function(transport, json) {
						linkedResources[r] = transport.responseText.evalJSON(true);
						return hoverResource(linkedResources[r], a);
					}
				});
			});
			a.observe('mouseout', function (e) {
				return hoverResource();
			});
		}
	});
};

// We'll be inserting a div into the DOM.
var hoverDiv;
function hoverResource ( resource, elem )
{
	
	// If we have a resource, add the div.
	if ( resource ) {
		// If the div is left over, use it.
		hoverDiv = ($('hover-context')) ? $('hover-context') : new Element('div', {'class':'hover', 'id':'hover-context'});
		
		// Set a little context message.
		hoverDiv.innerHTML  = "<h4>"+resource.name+"</h4>\n";
		hoverDiv.innerHTML += "<p>"+resource.description+"</p>";
		hoverDiv.innerHTML += "<h5>Tags</h5><ul>";
		for (var i=0;i<resource.tags.length;i++) {
			hoverDiv.innerHTML += "<li>"+resource.tags[i].name+"</li>";
		}
		hoverDiv.innerHTML += "</ul>";
		
		// Position the div below the element it goes with.
		hoverDiv.setStyle({
						  top: (elem.cumulativeOffset().top+elem.getHeight()+10)+'px',
						  left: (elem.cumulativeOffset().left-3)+'px'
						  });
		
		// Add it
		document.body.appendChild(hoverDiv);
	
	// if no resource was defined, clear the div away
	} else if (hoverDiv) {
		// if hoverDiv is defined, remove it
		hoverDiv.remove();
	}
}