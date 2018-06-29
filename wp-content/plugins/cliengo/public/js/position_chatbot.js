setInterval(function(){  

	var converse_chat = document.getElementById("converse-chat");

	converse_chat.style.right = "auto";

	if (converse_chat.style.right ==  "auto") 
	{
		clearInterval();
	}

}, 1000);



