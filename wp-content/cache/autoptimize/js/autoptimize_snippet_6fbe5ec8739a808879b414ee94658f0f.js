jQuery(document).ready(function($){'use strict';var maps=[];$('.cmb-type-pw-map').each(function(){initializeMap($(this));});function initializeMap(mapInstance){var searchInput=mapInstance.find('.pw-map-search');var mapCanvas=mapInstance.find('.pw-map');var latitude=mapInstance.find('.pw-map-latitude');var longitude=mapInstance.find('.pw-map-longitude');var latLng=new google.maps.LatLng(0,0);var zoom=1;if(latitude.val().length>0&&longitude.val().length>0){latLng=new google.maps.LatLng(latitude.val(),longitude.val());zoom=17;}
var mapOptions={center:latLng,zoom:zoom};var map=new google.maps.Map(mapCanvas[0],mapOptions);latitude.on('change',function(){map.setCenter(new google.maps.LatLng(latitude.val(),longitude.val()));});longitude.on('change',function(){map.setCenter(new google.maps.LatLng(latitude.val(),longitude.val()));});var markerOptions={map:map,draggable:true,title:'Drag to set the exact location'};var marker=new google.maps.Marker(markerOptions);if(latitude.val().length>0&&longitude.val().length>0){marker.setPosition(latLng);}
var autocomplete=new google.maps.places.Autocomplete(searchInput[0]);autocomplete.bindTo('bounds',map);google.maps.event.addListener(autocomplete,'place_changed',function(){var place=autocomplete.getPlace();if(!place.geometry){return;}
if(place.geometry.viewport){map.fitBounds(place.geometry.viewport);}else{map.setCenter(place.geometry.location);map.setZoom(17);}
marker.setPosition(place.geometry.location);latitude.val(place.geometry.location.lat());longitude.val(place.geometry.location.lng());});$(searchInput).keypress(function(event){if(13===event.keyCode){event.preventDefault();}});google.maps.event.addListener(marker,'drag',function(){latitude.val(marker.getPosition().lat());longitude.val(marker.getPosition().lng());});maps.push(map);}
if(typeof postboxes!=='undefined'){postboxes.pbshow=function(){var arrayLength=maps.length;for(var i=0;i<arrayLength;i++){var mapCenter=maps[i].getCenter();google.maps.event.trigger(maps[i],'resize');maps[i].setCenter(mapCenter);}};}
$('.cmb-repeatable-group').on('cmb2_add_row',function(event,newRow){var groupWrap=$(newRow).closest('.cmb-repeatable-group');groupWrap.find('.cmb-type-pw-map').each(function(){initializeMap($(this));});});});