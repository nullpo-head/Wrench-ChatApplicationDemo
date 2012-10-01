$(document).ready ->
	escapeHTML = (str) ->
		return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
	logobj = (msg) ->
		$('#log').append("#{key} : #{value}<br />") for key, value of msg
		$('#log').append("--------<br />")
	serverUrl = 'ws://127.0.0.1:8000/chat'
	if window.MozWebSocket
		socket = new MozWebSocket serverUrl
	else if window.WebSocket
		socket = new WebSocket serverUrl

	socket.onopen = (msg) ->
		$('#status').removeClass().addClass('online').html('connected')
	
	socket.onmessage = (msg) ->
		data = JSON.parse msg.data
		#logobj data #debug
		switch data['type']
			when "login accept"
				$('<li>').attr('id', escapeHTML username).text(username).appendTo $('#userList') for username in data['usernames']
				$('#onlinePanel>h2').text "users -- #{$('#roomId').val()}"
				$('#offlinePanel').fadeToggle "normal", -> $('#onlinePanel').fadeToggle()
			when "login notify"
				$('<li>').attr('id', escapeHTML data['username']).attr('style', 'display:none').text(data['username']).appendTo $('#userList')
				$("##{escapeHTML data['username']}").fadeToggle()
			when "logout notify"
				$("##{escapeHTML data['username']}").fadeToggle "normal", -> $("##{escapeHTML data['username']}").remove()
			when "message"
				$('#log').append "#{escapeHTML data['username']} : #{escapeHTML data['body']}<br />"
			when "error"
				$('#log').append "エラー：#{escapeHTML data['message']}<br />"
	
	socket.onclose = (msg) ->
		$('#status').removeClass().addClass('offline').html('disconnected')
	
	$('#status').click ->
		socket.close()

	$('#login').click ->
		socket.send JSON.stringify {
			type: "participate"
			userId: $('#userId').val()
			roomId: $('#roomId').val()
		}
	$('#logout').click ->
		socket.send JSON.stringify {
			type: "logout"
		}
		$('#userList>li').remove()
		$('#onlinePanel').fadeToggle "normal", -> $('#offlinePanel').fadeToggle()
	$('#send').click ->
		data = {
			type: "message"
			body: $('#mes').val()
		}
		socket.send JSON.stringify data
		
