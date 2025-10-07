	function getMessages() {
		console.log("Updating chat messages");
		$("#mesgLoad").innerHTML = "📡";
		$(".msgItem").remove();
		$rowNum = 1;
		$.getJSON("/agents/messages.php?limit=5", null, function(data) { 
			$.each( data, function(idx) {
				ts = new Date( data[idx].timestamp * 1000 );
				if ( data[idx].dest == callsign ) {
				$("#messages").append( '<tr class=\"msgItem tome\"><td><img class=mesgSymbol src=symbol/' + data[idx].symbol + '></td><td class=msgLeft>' + data[idx].callsign + ' &rarr; ' + data[idx].dest + '<br>' + data[idx].tactical + '</td><td class=msgCenter> ' + data[idx].message + '</td><td class=msgRight>' + ts.toLocaleString() + '</td></tr>' );
				} else if ( $rowNum % 2 == 0 ) {
				$("#messages").append( '<tr class=\"msgItem evenRow\"><td><img class=mesgSymbol src=symbol/' + data[idx].symbol + '></td><td class=msgLeft>' + data[idx].callsign + ' &rarr; ' + data[idx].dest + '<br>' + data[idx].tactical + '</td><td class=msgCenter> ' + data[idx].message + '</td><td class=msgRight>' + ts.toLocaleString() + '</td></tr>' );
				} else {
				$("#messages").append( '<tr class=\"msgItem oddRow\"><td><img class=mesgSymbol src=symbol/' + data[idx].symbol + '></td><td class=msgLeft>' + data[idx].callsign + ' &rarr; ' + data[idx].dest + '<br>' + data[idx].tactical + '</td><td class=msgCenter> ' + data[idx].message + '</td><td class=msgRight>' + ts.toLocaleString() + '</td></tr>' );
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

