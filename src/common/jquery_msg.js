	function getMessages() {
		$("#mesgLoad").innerHTML = "📡";
		$(".msgItem").remove();
		$rowNum = 0;
		$.getJSON("/agents/messages.php?limit=18", null, function(data) { 
		$.each( data, function(idx) {
				if ( data[idx].dest == callsign ) {
				ts = new Date( data[idx].timestamp * 1000 );
				$("#messages").append( '<tr class=\"msgItem tome\"><td><img class=mesgSymbol src=symbol/' + data[idx].symbol + '></td><td class=msgLeft>' + data[idx].callsign + ' &rarr; ' + data[idx].dest + '<br>' + data[idx].tactical + '</td><td class=msgCenter> ' + data[idx].message + '</td><td class=msgRight>' + ts.toString() + '</td></tr>' );
				} else if ( data[idx].callsign == callsign) {
				$("#messages").append( '<tr class=\"msgItem fromme\"><td><img class=mesgSymbol src=symbol/' + data[idx].symbol + '></td><td class=msgLeft>' + data[idx].callsign + ' &rarr; ' + data[idx].dest + '<br>' + data[idx].tactical + '</td><td class=msgCenter> ' + data[idx].message + '</td><td class=msgRight>' + ts.toString() + '</td></tr>' );
				} else if ( $rowNum % 2 == 0 ) {
				$("#messages").append( '<tr class=\"msgItem evenRow\"><td><img class=mesgSymbol src=symbol/' + data[idx].symbol + '></td><td class=msgLeft>' + data[idx].callsign + ' &rarr; ' + data[idx].dest + '<br>' + data[idx].tactical + '</td><td class=msgCenter> ' + data[idx].message + '</td><td class=msgRight>' + ts.toString() + '</td></tr>' );
				} else {
				$("#messages").append( '<tr class=\"msgItem oddRow\"><td><img class=mesgSymbol src=symbol/' + data[idx].symbol + '></td><td class=msgLeft>' + data[idx].callsign + ' &rarr; ' + data[idx].dest + '<br>' + data[idx].tactical + '</td><td class=msgCenter> ' + data[idx].message + '</td><td class=msgRight>' + ts.toString() + '</td></tr>' );
				}
				$rowNum++;
			})
		})
		$("#mesgLoad").innerHTML = "🔄";
		setTimeout(getMessages, 60000);
	} // end getMessages

	$(document).ready( function() { 
		$("#mesgLoad").click( function() { getMessages(); } );
		getMessages();
	});

