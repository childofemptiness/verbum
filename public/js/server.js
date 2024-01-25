var token = localStorage.getItem('userToken');

var encodedToken = encodeURIComponent(token);

var sessionId = encodeURIComponent(document.cookie.replace(/(?:(?:^|.*;\s*)PHPSESSID\s*\=\s*([^;]*).*$)|^.*$/, "$1"));

const socket = new WebSocket('ws://localhost:8081/ws?token='+encodedToken+'&phpsessid='+sessionId);

export function sendWebScoketMessage(data) {
    socket.send(JSON.stringify(data));
}

export function onWebSocketMessage(callback) {
    socket.onmessage = function(event) {
        const data = JSON.parse(event.data);
        callback(data);
    }
}
