
function commandDeop(player, btn) {
	var _ = $(btn);
	console.log("command deop from", _);
	sendCommand("deop "+player, function() {
//		_.closest("li").addClass("kicked");
	});
}

function commandOp(player, btn) {
	var _ = $(btn);
	console.log("command op from", _);
	sendCommand("op "+player, function() {
//		_.closest("li").addClass("kicked");
	});
}

function commandKick(player, btn) {
	var _ = $(btn);
	console.log("command kick from", _);
	sendCommand("kick "+player, function() {
		_.closest("li").addClass("kicked");
	});
}

function commandBan(player, btn) {
	var _ = $(btn);
	sendCommand("kick "+player, function() {
		_.closest("li").addClass("banned");
	});
}

function sendCommand(command, success, fail) {
	console.log("Send command => "+command);
	$.post(CONSOLE_INPUT, {"command":command}, function(data) {
		console.log("Command success", data);
		if( success ) {
			success(data);
		}
		
	}).fail( function(xhr, textStatus, errorThrown) {
		console.log("Command fail", xhr.responseJSON);
		if( fail ) {
			fail(xhr.responseJSON);
		}
    });
}
	
$(function() {
	//https://developer.mozilla.org/fr/docs/Server-sent_events/Using_server-sent_events
// 	$(".consolestream")
	var consoleTitle = $(".consolestream").closest(".panel").find(".panel-title").first();
	if( !consoleTitle.length ) {
		return;
	}
// 	var consolePing = $('<small class="ml6"></small>');
	var consoleIcon = $('<i class="fa fa-fw fa-power-off ml6" style="color: #808080;" data-offline="#808080" data-online="#2FCF2E"></i>');
	var consoleStartButton = $('<button class="btn btn-primary btn-xs pull-right" type="button">Connecter</button>');
	var consoleStopButton = $('<button class="btn btn-primary btn-xs pull-right" type="button">Déconnecter</button>');
	var consoleList = $(".consolestream").first();
	var consoleMaxRows = 200;
	var scrollMax = consoleList.height();
	var attachedScroll = true;
	
	var playerCount = $(".playercount");
	var playersList = $(".playerslist").first();
	var playersNone = $(".noplayer");
	
	consoleTitle.append(consoleIcon);
// 	consoleTitle.append(consolePing);
	consoleTitle.append(consoleStartButton);
	consoleTitle.append(consoleStopButton.hide());
	
	var source;
	function startConsole() {
		consoleStartButton.hide();
		consoleList.empty();
		source = new EventSource(CONSOLE_STREAM);
// 		source = new EventSource('http://flo.mcm.sowapps.com/user/server/8/console.html');
	// 	console.log('consoleList.height', consoleList.height());
	// 	console.log('consoleList.innerHeight', consoleList.innerHeight());
	// 	console.log('consoleList.outerHeight', consoleList.outerHeight());
	// 	console.log("source", source);
		source.addEventListener('message', function(e) {
// 			console.log(e.data);
			consoleList.append('<li class="list-group-item">'+e.data+'</li>');
			consoleList.children("li").slice(0, -consoleMaxRows).remove();
// 			if( consoleList.children("li").length > consoleMaxRows ) {

// 			}
			if( attachedScroll ) {
				consoleList.scrollTop(consoleList[0].scrollHeight);
			}
		}, false);
		
		source.addEventListener('process', function(e) {
			var process = JSON.parse(e.data);
			process.mem_res = Math.ceil(process.mem_res/1024)+'Mo';
			process.mem_virt = Math.ceil(process.mem_virt/1024)+'Mo';
			$("body").fill("process-", process);
		});
		
		source.addEventListener('players', function(e) {
// 			console.log(e.data, e);
			var players = JSON.parse(e.data);
// 			console.log("players", players);
			var c = 0;
			playersList.empty();
			for( var k in players ) {
				var player = players[k];
// 				console.log("isString(player) ", typeof player, player);
				if( !isString(player) ) {
					continue;
				}
// 					console.log("Append "+player);
				var playerRow = $('<li class="list-group-item">'+player+'</li>');
				var buttons = $('<div class="btn-group btn-group-xs pull-right" role="group"></div>');
				buttons.append('<button class="btn btn-default" onClick="commandDeop(\''+player+'\', this);" title="Rétrograder"><i class="fa fa-fw fa-star-o"></i></button>');
				buttons.append('<button class="btn btn-default" onClick="commandOp(\''+player+'\', this);" title="Promouvoir"><i class="fa fa-fw fa-star"></i></button>');
				buttons.append('<button class="btn btn-default" onClick="commandKick(\''+player+'\', this);" title="Éjecter"><i class="fa fa-fw fa-times"></i></button>');
				buttons.append('<button class="btn btn-default"onClick="commandBan(\''+player+'\', this);" title="Bannir"><i class="fa fa-fw fa-ban"></i></button>');
				playerRow.append(buttons);
				playersList.append(playerRow);
				c++;
			}
// 			console.log("Number fo players online ? "+c);
			if( c ) {
				playersNone.filter(":visible").hide();
				playersList.filter(":hidden").show();
			} else {
				playersNone.filter(":hidden").show();
				playersList.filter(":visible").hide();
			}
			playerCount.text(c);
			
		}, false);
		
// 		source.addEventListener('ping', function(e) {
// 			// Connection was opened.
// 			var now = Date.now();
// 	// 		console.log("Ping - result", e);
// 			consolePing.text((now-e.data)+"ms");
// 	// 		console.log("Ping - ping", (e.data-now)*1000);
// 		}, false);
	
		source.addEventListener('open', function(e) {
			// Connection was opened.
	// 		console.log("Open");
			consoleIcon.css("color", consoleIcon.data("online"));
		}, false);
	
		source.addEventListener('error', function(e) {
			console.log("Error", e);
			if( e.readyState == EventSource.CLOSED ) {
				// Connection was closed.
				consoleIcon.css("color", consoleIcon.data("offline"));
// 				consolePing.text("");
			}
		}, false);
		
		consoleStopButton.show();
	}
	
	function stopConsole() {
		if( !source ) {
			return;
		}
		consoleStopButton.hide();
		source.close();
// 		consoleList.empty();
// 		consolePing.text("");
		consoleIcon.css("color", consoleIcon.data("offline"));
// 		console.log('consoleStartButton', consoleStartButton);
		consoleStartButton.show();
	}
	
	consoleStartButton.click(startConsole);
	consoleStopButton.click(stopConsole);
	
	consoleList.scroll(function(e) {
		var scrollDelta = consoleList[0].scrollHeight-consoleList.height()-consoleList.scrollTop();
// 		console.log("scrollDelta => "+scrollDelta);
// 		console.log("scrollMax => "+scrollMax);
		attachedScroll = scrollDelta < scrollMax;
// 		console.log("attachedScroll ? "+attachedScroll);
	});
	
	var consoleInput = $("#ConsoleInput");
	var consoleSendBtn = $("#ConsoleSendButton");
	consoleInput.pressEnter(function() {
		consoleSendBtn.click();
	});
	var sendingCommand;
	consoleSendBtn.click(function() {
		if( sendingCommand ) {
			return;
		}
		var command = consoleInput.val();
		if( command.length < 2 ) {
			return;
		}
		sendingCommand = true;
		consoleInput.prop("disabled", true);
		$(".rcon_alert").remove();
		sendCommand(command, function(data) {
			sendingCommand = false;
//			console.log("Command success", data);
			consoleInput.val("");
			consoleInput.prop("disabled", false);
			consoleInput.focus();
			if( !data ) {
				return;
			}
// 			console.log("data ", data);
// 			console.log("data.length => "+data.length);
			consoleInput.parent().after('<div class="rcon_alert alert alert-info mt20 mb0" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Fermer"><span aria-hidden="true">&times;</span></button>'+data+'</div>');
		}, function(response) {
			sendingCommand = false;
// 			console.log(xhr, textStatus, errorThrown);
//			console.log("Command fail", response);
			// There is result and translated one
			var error = response && response.code != response.description ? response.description : 'Une erreur est survenue.';
			consoleInput.parent().after('<div class="rcon_alert alert alert-danger mt20 mb0" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Fermer"><span aria-hidden="true">&times;</span></button>'+error+'</div>');
			consoleInput.prop("disabled", false);
			consoleInput.focus();
	    });
	});
// 	source.onmessage = function (e) {
// 		console.log("Message", e);
// // 		var message = JSON.parse(e.data);
// 		// handle message
// 	};
	
	$(".showpassword").each(function() {
		var input = $(this);
		var showBtn = input.next().find(".showbtn");
		var hideBtn = input.next().find(".hidebtn");
// 		console.log("showpassword", showBtn, input);
		showBtn.click(function() {
			input.attr("type", "text");
			showBtn.hide();
			hideBtn.show();
		});
		hideBtn.click(function() {
			input.attr("type", "password");
			showBtn.show();
			hideBtn.hide();
		});
	});
	
	$(".consolewrapper").click(function() {
// 		console.log("Console click, selection ["+getSelection()+"]");
		if( getSelection() != "" ) { return; }
		consoleInput.focus();
	});
});