const socket = new WebSocket('ws://localhost:8081');


export function sendWebScoketMessage(data) {
    socket.send(JSON.stringify(data));
}

export function onWebSocketMessage(callback) {
    socket.onmessage = function(event) {
        const data = JSON.parse(event.data);
        callback(data);
    }
}