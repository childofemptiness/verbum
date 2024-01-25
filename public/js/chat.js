// // Отслеживаем установление соединения
// socket.addEventListener('open', function(event) {
//     console.log('Connected to server.');
//     updateConnectionStatus('Connected'); // добавляем функцию для обновления статуса
// });

// // Отслеживаем получение сообщения
// socket.addEventListener('message', function(event) {
//     const messages = document.getElementById('messages');
//     const message = document.createElement('div');
//     message.innerHTML = event.data;
//     console.log(event.data);
//     messages.appendChild(message);
// });

// // Обработка закрытия соединения
// socket.addEventListener('close', function(event) {
//     console.log('Disconnected from server');
//     updateConnectionStatus('Disconnected'); // обновляем статус при закрытии соединения
// });

// // Обработка ошибок WebSocket соединения
// socket.addEventListener('error', function(event) {
//     console.log('Error: connection failed');
//     updateConnectionStatus('Error'); // обновляем статус при ошибке соединения
// });

// const form = document.querySelector('form');
// const input = document.querySelector('input');

// // Отправка сообщений формы
// form.addEventListener('submit', function(event) {
//     event.preventDefault();

//     // Проверяем, если соединение открыто перед отправкой
//     if (socket.readyState === WebSocket.OPEN) {
//         const message = input.value;
//         socket.send(message);
//         input.value = ''; // Очищаем поле ввода после отправки
//     } else {
//         console.log('Cannot send message: WebSocket is not connected.');
//         updateConnectionStatus('Not Connected'); // обновляем статус, если соединение не установлено
//     }
// });

// // Функция для обновления статуса соединения на странице
// function updateConnectionStatus(status) {
//     console.log(status);
// }