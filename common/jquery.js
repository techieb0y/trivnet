	function getMessages() {
		$("#mesgLoad").attr("src", "/trivnet/images/spinner-small.gif");
		$(".msgItem").remove();
		$.getJSON("/trivnet/agents/messages.php?limit=5", null, function(data) { 
			$.each( data, function(idx) {
				$("#messages").append( '<span class=msgItem><img class=mesgSymbol src=getAPRS.php?sym=' + data[idx].symbol + '><span class=msgLeft>' + data[idx].callsign + '-&gt;' + data[idx].dest + '</span><span class=msgCenter> ' + data[idx].message + '</span><span class=msgRight>' + data[idx].timestamp + '</span><br /></span>' );
			})
		})
		$("#mesgLoad").attr("src", "/trivnet/images/refresh.png");
		setTimeout(getMessages, 60000);
	} // end getMessages

	$(document).ready( function() { 
		$("#mesgLoad").click( function() { getMessages(); } );
		getMessages();
	});

