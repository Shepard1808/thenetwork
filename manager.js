import {Client} from "./client.js";

const currentUrl = new URL(document.location);
const mId = currentUrl.searchParams.get('manager-id');

if(!mId) {
    console.error("Missing a manager id");
}

let clientStatus = [];

console.log(mId);

const onError = (error) => console.error(error);
const onClose = () => console.log("Disconnected from Server");

const socket = new WebSocket('ws://127.0.0.1:8080');
socket.binaryType = "blob";



socket.onopen = function () {
    console.log("Connected to Server");
    console.log(socket.readyState);
    socket.send(JSON.stringify({
        from: mId,
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

    if(msg.type === "verifyName"){
        response = JSON.stringify({
            from: mId,
            to: "server",
            payload: {Identity: mId},
            type: "verify",
            timestamp: new Date()
        });
        console.log("sent: " + response);
        socket.send(response);
    }else if(msg.type === "activeUpdate"){
        let entryexists = false;
        for(let i = 0; i < clientStatus.length; i++){
            if(clientStatus[i].getUsername() === msg.from){
                entryexists = true;
                clientStatus[i].update(true);
                break;
            }
        }
        if(!entryexists){
            clientStatus.push(new Client(true, msg.from));
        }
    }

    if(msg.type === "heartbeat"){
        socket.send(JSON.stringify({
            from: mId,
            to: "server",
            type: "heartbeat",
            payload: {},
            timestamp: new Date()
            }
        ));
    }

}

export function sendRefresh(){

    for(let i = 0; i< clientStatus.length; i++){
        let circle = document.getElementById(clientStatus[i].getUsername()+ "circle");
        circle.style.backgroundColor = "yellow";
    }

    socket.send(JSON.stringify({
        from: mId,
        to: "clients",
        payload: {target: "all"},
        type: "refreshAll",
        timestamp: new Date()
    }));
    for(let i = 0; i < clientStatus.length; i++){
        clientStatus[i].setStatus(false);
    }
    console.log("Refresh request send for every Client");
}

setInterval(function (){
    for(let i = 0; i < clientStatus.length; i++) {
        if(!clientStatus[i].getIsAppended()) {
            newTableRow(clientStatus[i], clientStatus[i].getStatus());
            clientStatus[i].append();
        }
        updateCss(clientStatus[i].getUsername(),clientStatus[i].getStatus());
    }
},5000);

setInterval(function(){
    if (socket.readyState > 1){
       location.reload();
    }
},5000);

export function disconnect(){
    if(socket.readyState < 2){
        console.log("closing connection to server");
    }
    socket.close();
}


function sendSingleRefresh(client) {
    client.setStatus(false)
    console.log("Refreshing: " + client.getUsername());
    socket.send(JSON.stringify({
        from: mId,
        to: client.getUsername(),
        type: "refresh",
        payload: {target: client.getUsername},
        timestamp: new Date()
    }));
}

document.getElementById("refAll").onclick = sendRefresh;
document.getElementById("disconnect").onclick = disconnect;

function newTableRow(client, status) {
    let tr = document.createElement("tr");

    let text = document.createTextNode(client.getUsername());
    let td1 = document.createElement("td").appendChild(text);
    tr.appendChild(td1);
    let div = document.createElement("div");
    div.id= client.getUsername() + "circle";
    div.style.borderRadius = "10px";
    div.style.height = "20px";
    div.style.width = "20px";
    if(status){
        div.style.backgroundColor = "lime";
    }else{
        div.style.backgroundColor = "yellow";
    }
    let td2 = document.createElement("td").appendChild(div);
    tr.appendChild(td2);

    if(status){
        let text = document.createTextNode("refresh");
        let rButton = document.createElement("button");
        rButton.appendChild(text);
        rButton.onclick = function (){
            div.style.backgroundColor = "yellow";
            sendSingleRefresh(client)
        }
        let td3 = document.createElement("td").appendChild(rButton);
        tr.appendChild(td3);
    }

    document.getElementById("clientbody").appendChild(tr);
}
function updateCss(clientname, status){
    if(document.getElementById(clientname + "circle").style.backgroundColor === "yellow" && status){
        document.getElementById(clientname + "circle").style.backgroundColor = "lime";
    }
}