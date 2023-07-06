const socket = new WebSocket('ws://127.0.0.1:8080');
socket.binaryType = "blob";
const onError = (error) => console.error(error);
const onClose = () => console.log("Disconnected from Server");




socket.onopen = function () {
    console.log("Connected to Server");
    console.log(socket.readyState);
    socket.send(JSON.stringify({
        from: "username",
        to: "server",
        type: "introduction",
        payload: {},
        timestamp: new Date()
    }));
};
socket.onerror = onError;
socket.onclose = onClose;

let response;

//integrate button to send refresh request
socket.onmessage = function (message){
    let msg;
    try{
        console.log("received:" + message.data);
        msg = JSON.parse(message.data);
        console.log(msg.type);
    }catch (e){
        console.log("Error: \n" + e);
    }

    if(msg.type === "msg"){
        let text = document.createTextNode(msg.from + ": " + msg.payload.msg);
        let message = document.createElement("div").appendChild(text);
        document.getElementById("container").appendChild(message);
    }

}

document.getElementById("send").onclick = sendMessage;

export function sendMessage(){
    let text = document.getElementById("msg").value;
    socket.send(JSON.stringify({
        from: "user",
        to: "server",
        type: "msg",
        payload: {msg: text},
        timestamp: new Date()
    }));
}