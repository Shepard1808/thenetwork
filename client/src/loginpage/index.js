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
window.onload  = () => {
    document.getElementById('submitButton').onclick = () => {
        let uname = document.getElementById('username').value;
        let pw = document.getElementById('password').value;
        if (pw !== null && uname !== null) {
            socket.send(JSON.stringify({
                from: "client",
                to: "server",
                type: "login",
                payload: {uname: uname, password: pw},
                timestamp: new Date()
            }));
        } else {
            alert("fill all fields");
        }
    }
}

setInterval(function(){
    if(socket.readyState > 1){
        alert("Connection lost");
    }
},5000);