require([
	'jquery'
	],
		
	function($){
		var geocoder;
		var map;
		var places;
		var markers = [];
		var side_bar_html = "";
		var infoWindow;
		var mapOptions = {
				zoom: 16,
				mapTypeId: google.maps.MapTypeId.ROADMAP
				//mapTypeId: 'roadmap'
			};
							
			// Display a map on the page
			map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
			map.setTilt(45);
			
			$('#load-map-data').click(function(){
				var loader = new varienLoader(true);
				$('#load-map-data').addClass('disabled').attr('disabled', true);
				//Element.show('loading-mask');
				var urlDomain = window.location.href;
				var arr = urlDomain.split("/");
				$.post(pathJson,$('#edit_form').serialize(), function(response){
					//Element.hide('loading-mask');
					$('#load-map-data').removeClass('disabled').attr('disabled', false);
					
					$('#store_latitude').val(response.lat);
					$('#store_longitude').val(response.long)
					loadMap(map);
				},'json');
				
				return false;
			});
			loadMap(map);
			
			function loadMap(map){
				var latitude = document.getElementById('store_latitude').value;
				var longitude = document.getElementById('store_longitude').value;
				var myLatlng = new google.maps.LatLng(latitude,longitude);
				var bounds = new google.maps.LatLngBounds();
				clearMarkers();
					if(latitude != '' && longitude != ''){
								var position = new google.maps.LatLng(latitude,longitude);
									bounds.extend(position);
									marker = new google.maps.Marker({
										position: position,
										map: map,
										title: "Location"
									});
									//bindInfoWindow(marker, map, infoWindow, places.content);
									markers.push(marker);							
									//side_bar_html += '<a href="javascript:myclick(' + (markers.length-1) + ')">' + places.infowindow + '</a>';
									// Automatically center the map fitting all markers on the screen
									map.fitBounds(bounds);
									
									setTimeout(function(){										
										google.maps.event.trigger(map, 'resize');
										map.panTo(marker.getPosition());
										map.setZoom(16);
									},2000);
									
									$('#storelocator_tabs_location').click(function(){
										setTimeout(function(){
											google.maps.event.trigger(map, 'resize');
											map.panTo(marker.getPosition());
											map.setZoom(16);
										},100);
									});
					}else{
									var position = new google.maps.LatLng(0,0);
									bounds.extend(position);
									marker = new google.maps.Marker({
										position: position,
										map: map,
										title: "Location"
									});
									//bindInfoWindow(marker, map, infoWindow, places.content);
									markers.push(marker);							
									//side_bar_html += '<a href="javascript:myclick(' + (markers.length-1) + ')">' + places.infowindow + '</a>';
									// Automatically center the map fitting all markers on the screen
									map.fitBounds(bounds);
									$('#storelocator_tabs_location').click(function(){
										setTimeout(function(){
											google.maps.event.trigger(map, 'resize');
											map.setZoom(2);
										});
									});
					}
				
				google.maps.event.addListener(map, 'click', function(event) {
					placeMarker(event.latLng);
					document.getElementById('store_latitude').value = event.latLng.lat();
					document.getElementById('store_longitude').value = event.latLng.lng();
				});
				
				function placeMarker(location) {
				  if ( marker ) {
					marker.setPosition(location);
				  } else {
					marker = new google.maps.Marker({
					  position: location,
					  map: map
					});
					
				  }
				}	
			}
			
			function setAllMap(map) {
			  for (var i = 0; i < markers.length; i++) {
				markers[i].setMap(map);
			  }
			}
			function clearMarkers() {
			  setAllMap(null);
			}
		}
);