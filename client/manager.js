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
}