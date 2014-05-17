	function getMessages() {
		$("#mesgLoad").attr("src", "/trivnet/images/spinner-small.gif");
		$(".msgItem").remove();
		$.getJSON("/trivnet/agents/messages.php?limit=5", null, function(data) { 
			$.each( data, function(idx) {
				$("#messages").append( '<tr class=msgItem><td><img class=mesgSymbol src=getAPRS.php?sym=' + data[idx].symbol + '></td><td class=msgLeft>' + data[idx].callsign + '-&gt;' + data[idx].dest + '</td><td class=msgCenter> ' + data[idx].message + '</td><td class=msgRight>' + data[idx].timestamp + '</td></tr>' );
			})
		})
		$("#mesgLoad").attr("src", "/trivnet/images/refresh.png");
		setTimeout(getMessages, 60000);
	} // end getMessages

	$(document).ready( function() { 
		$("#mesgLoad").click( function() { getMessages(); } );
		getMessages();
	});

