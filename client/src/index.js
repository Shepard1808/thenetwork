const socket = new WebSocket('ws://127.0.0.1:8080');
socket.binaryType = "blob";


socket.onopen = function (){
    console.log("connection established");
}

socket.onmessage = function (message) {
    let msg;
    try{
        msg = JSON.parse(message.data);
    }catch (e) {
        console.error(e);
    }

    if(msg.type === "login0"){
        window.location.replace("chat.html/?token=" + msg.payload.token);
    }else if(msg.type === "login1"){
        alert("error wrong username or password");
    }

}

setInterval(function(){
    if(socket.readyState > 1){
        alert("Connection lost");
    }
},5000);