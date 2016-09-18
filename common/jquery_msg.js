	function getMessages() {
		$("#mesgLoad").attr("src", "/trivnet/images/spinner-small.gif");
		$(".msgItem").remove();
		$rowNum = 0;
		$.getJSON("/trivnet/agents/messages.php?limit=25", null, function(data) { 
		$.each( data, function(idx) {
				if ( data[idx].dest == callsign ) {
				$("#messages").append( '<tr class=\"msgItem tome\"><td><img class=mesgSymbol src=getAPRS.php?sym=' + data[idx].symbol + '></td><td class=msgLeft>' + data[idx].callsign + ' &rarr; ' + data[idx].dest + '<br>' + data[idx].tactical + '</td><td class=msgCenter> ' + data[idx].message + '</td><td class=msgRight>' + data[idx].timestamp + '</td></tr>' );
				} else if ( $rowNum % 2 == 0 ) {
				$("#messages").append( '<tr class=\"msgItem evenRow\"><td><img class=mesgSymbol src=getAPRS.php?sym=' + data[idx].symbol + '></td><td class=msgLeft>' + data[idx].callsign + ' &rarr; ' + data[idx].dest + '<br>' + data[idx].tactical + '</td><td class=msgCenter> ' + data[idx].message + '</td><td class=msgRight>' + data[idx].timestamp + '</td></tr>' );
				} else {
				$("#messages").append( '<tr class=\"msgItem oddRow\"><td><img class=mesgSymbol src=getAPRS.php?sym=' + data[idx].symbol + '></td><td class=msgLeft>' + data[idx].callsign + ' &rarr; ' + data[idx].dest + '<br>' + data[idx].tactical + '</td><td class=msgCenter> ' + data[idx].message + '</td><td class=msgRight>' + data[idx].timestamp + '</td></tr>' );
				}
				$rowNum++;
			})
		})
		$("#mesgLoad").attr("src", "/trivnet/images/refresh.png");
		setTimeout(getMessages, 60000);
	} // end getMessages

	$(document).ready( function() { 
		$("#mesgLoad").click( function() { getMessages(); } );
		getMessages();
	});

