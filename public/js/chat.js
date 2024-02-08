var interlocutorData;
var userId;
var chatId;
let messageData = new Map();
let originalMessageId = null;
let editMessageId = null;
let groupMembers = new Array();

const path = window.location.pathname;
const segments = path.split('/');
const dialogIndex = segments.indexOf('dialog');
const groupIndex = segments.indexOf('group');
const chatIndex = dialogIndex != -1 ? dialogIndex : groupIndex;
chatId = (chatIndex !== -1 && segments[chatIndex + 1]) ? segments[chatIndex + 1] : null;
document.addEventListener('DOMContentLoaded', function() {
    
    
   if (segments[2] == 'dialog' || segments[2] == 'group') {
    getUserId().then((id) => {
        localStorage.setItem('userId', id);
    });

    userId = localStorage.getItem('userId');

   }

   if (path.includes('dialog')) {
        getInterlocutorInfo(chatId, setInterlocutorInfo).then((data) => {
            interlocutorData = data;
        });
   }


    if (socket) {
        socket.addEventListener('open', function(event) {
            console.log('Connected to server.');
    
            if (segments[2] == 'dialog' || segments[2] == 'group') {
                fetchChatHistory(chatId);
            }
        });
    }

    if(path.includes('groups')) {
        getFriendsList();
    }

    if(segments[2] == 'group') {
        getGroupName();
    }
});


var token = localStorage.getItem('userToken');
var encodedToken = encodeURIComponent(token);
var encodedSessionId = encodeURIComponent(document.cookie.replace(/(?:(?:^|.*;\s*)PHPSESSID\s*\=\s*([^;]*).*$)|^.*$/, "$1"));
const socket = new WebSocket(`ws://localhost:8081/ws?token=${encodedToken}&phpsessid=${encodedSessionId}&chatId=${chatId}`);

socket.addEventListener('message', function(event) {
    const data = JSON.parse(event.data);
    handleSocketMessage(data);
});

socket.addEventListener('close', function(event) {
    console.log('Disconnected from server');
});

socket.addEventListener('error', function(event) {
    console.log('Error: connection failed');
});

const inputElement = document.querySelector('.chat-input');
const sendButton = document.querySelector('.chat-send');
// Отправка сообщения по нажатию на кнопку    
if (segments[2] == 'dialog' || segments[2] == 'group') {
    sendButton.addEventListener('click', function() {
        if (socket.readyState === WebSocket.OPEN) {
            handleMessageSend(segments[2]);
        } else {
            console.log('Cannot send message: WebSocket is not connected.');
        }
    });
    // Отправка сообщения по нажатию на клавишу Enter
    document.addEventListener('keydown', function(event) {
        if (inputElement.value != '' && event.code == 'Enter' && socket.readyState === WebSocket.OPEN) {
           handleMessageSend();
        }
    });


    // Нажатие правой кнопкой по мыши
    const chatContainer = document.querySelector('.chat-messages');
    chatContainer.addEventListener('contextmenu', (event) => {
        event.preventDefault();

    removeExistingMenu();

        const messageDiv = event.target.closest('.message');
        if(!messageDiv) return;
        if(document.getElementById('contextMenu')) {
        }
        //Создаем контекстное меню на основе шаблона
        const contextMenu = createContextMenu(Number(messageDiv.dataset.id));
        
        positionContextMenu(event.pageX, event.pageY, contextMenu);

        document.body.appendChild(contextMenu);
    });

    document.addEventListener('click', function(event) {
        // проверить, что клик был сделан вне элемента .message
        if (!event.target.matches('.message') && !event.target.matches('.context-menu')) {
            // получить возможное существующее контекстное меню
            const contextMenu = document.querySelector('.context-menu');
            if (contextMenu) {
                // вызвать функцию для удаления контекстного меню
                removeExistingMenu();
            }
        }
    });
}




var dropdownMenu = document.querySelector('.menu-dropbtn');
var dropdownMenuContent = document.querySelector('.dropdown-menu-content');

dropdownMenu.addEventListener('click', function(event) {
    // Этот код переключает видимость выпадающего меню
    dropdownMenuContent.style.display = dropdownMenuContent.style.display === 'block' ? 'none' : 'block';
    
    // Предотвращаем дальнейшее распространение события вверх по DOM дереву
    event.stopPropagation();
});

// Закрывает выпадающее меню, когда кликают вне его
window.addEventListener('click', function(event) {    
    if (!event.target.matches('.menu-dropbtn') && !event.target.matches('.dropdown *')) {
        dropdownMenuContent.style.display = 'none';
    }
});

function handleMessageSend(chatType) {

    if (editMessageId != null) {

        body = {
            type: 'edit-message',
            messageId: editMessageId,
            chatId: chatId,
            text: inputElement.value,
            chatType: chatType,
        };

        if (chatType == 'dialog') body.takerId = interlocutorData.id;

        sendSocketMessage(body);

        const messageElement = document.getElementById(`message-${editMessageId}`);
        const replyElement = messageElement.querySelector('.reply');

        if (replyElement) {
            const messageDiv = replyElement.querySelector('.reply-message');
            messageDiv.textContent = inputElement.value; 
        }
        else {
            const messageP = messageElement.querySelector('.message-text');
            messageP.textContent = inputElement.value;
        }
        // Очищаем поле ввода
        inputElement.value = '';
        // В будущем подумать на тем, чтобы добавить подпись изменено ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        // Меняем текущее время приложения
        const messageTimeP = messageElement.querySelector('.message-time');
        var currentTime = new Date();
        var hours = toTwoDigits(currentTime.getHours());
        var minutes = toTwoDigits(currentTime.getMinutes());
        messageTimeP.innerHTML = hours + ':' + minutes;

        const editQuoteElement = document.querySelector('.edit-quote');
        if (editQuoteElement) editQuoteElement.remove();

        editMessageId = null;

    } else {

        if (chatType == 'dialog') {

            body = {
                type: 'dialog-message',
                messageId: null,
                text: inputElement.value,
                takerId: interlocutorData.id,
                chatId: chatId,
                sendDate: new Date(),
                senderId: userId,
                };

        } else {

            body = {
                type: 'group-message',
                messageId: null,
                text: inputElement.value,
                chatId: chatId,
                sendDate: new Date(),
                senderId: userId,
                userName: 'Вы'
            };
        }

        body = addMessageToDOM(body, true);

        sendSocketMessage(body);

        inputElement.value = '';
        const replyQuoteElement = document.querySelector('.reply-quote');
        if (replyQuoteElement) replyQuoteElement.remove();
    }
}


// Отправка всех видов сообщений через сокет
function sendSocketMessage(body) {

    data = JSON.stringify(body);    
    socket.send(data);
}

// Запрос на получение истории сообщений
function fetchChatHistory(chatId) {
    body = {
        type: 'chat-history',
        chatId: chatId,
    };

    data = JSON.stringify(body);

    socket.send(data);
}



// Получить информацию о собеседнике
async function getInterlocutorInfo(chatId, callback) {
    try {
        const response = await fetch(`/chats/sendinterlocutorinfo/${chatId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data =  await response.json();
        callback(data);
        return data;
    }

    catch (error) {
        console.log('Произошла ошибка', error);
    }
}

async function getUserId() {
    const response = await fetch('/chats/senduserid'); // Используем await для ожидания ответа от fetch
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    return await response.json(); // Ожидаем резолва Promise от response.json()
}



// Установить информацию о собеседнике
function setInterlocutorInfo(info) {
    const name = document.querySelector('.chat-header h1');
    name.innerHTML = info.fullName + `(` + info.nick + `)`;
}

function handleSocketMessage(data) {
    const chatContainer = document.querySelector('.chat-messages');
    switch(data.type) {
        // Обработка случая, когда получено сообщение
        case 'dialog-message':
            addMessageToDOM(data, false);
            break;
        // Обработка случая, когда получена история сообщений
        case 'chat-history':
            let messageHistory = data.messages;
            messageHistory.forEach(message => {
                addMessageToDOM(message, message.senderId == userId);
            });
            break;
        // После отправки сообщения на сервер, получаем его id и кладем в dataset последнего показанного сообщения. Такой кейс приходит только отправителю сообщению, потому можно не опасаться ошибок с собеседником
        case 'messageId':
            const lastUserMessage = chatContainer.lastChild;
            lastUserMessage.dataset.id = data.messageId;
            lastUserMessage.id = `message-${data.messageId}`;
            if (messageData.has(0)) {
                let value = messageData.get(0);
                value.messageId = Number(data.messageId);
                messageData.delete(0);
                messageData.set(Number(data.messageId), value);
            }
            break;
        // Удаляем отображаем элемент сообщения у клиента
        case 'delete-message':
            const messageDiv = document.getElementById(`message-${data.messageId}`);
            messageDiv.remove();
            break;
        // Меняем содержимое нашего сообщения у клиента
        case 'edit-message':
            const messageElement = document.getElementById(`message-${data.messageId}`);
            const replyElement = messageElement.querySelector('.reply');
            if(replyElement) {
                const messageDiv = replyElement.querySelector('.reply-message');
                messageDiv.textContent = data.text;
            }
            else {
                const messageP = messageElement.querySelector('.message-text');
                messageP.textContent = data.text;
            }
            // Меняем время сообщения на текущее
            const messageTimeP = messageElement.querySelector('.message-time');
            var currentTime = new Date();
            var hours = toTwoDigits(currentTime.getHours());
            var minutes = toTwoDigits(currentTime.getMinutes());
            messageTimeP.innerHTML = hours + ':' + minutes;

            break;
        // Случай, когда было получено групповое сообщение    
        case 'group-message':
            addMessageToDOM(data, false);
    }
}

// Модификация функции addMessageToDOM для отображения ответов
function addMessageToDOM(data, isOutGoing) {
    const messagesElement = document.querySelector('.chat-messages');
    
    // Создание общего div для сообщения или ответа
    const messageWrapperDiv = document.createElement('div');
    messageWrapperDiv.classList.add('message');
    if (isOutGoing) {
        messageWrapperDiv.classList.add('message-outgoing');
    } else {
        messageWrapperDiv.classList.add('message-incoming');
    }


    // Проверяем, является ли сообщение ответом
    if (originalMessageId ||  data['parentMessageId'] != null) {
        if (originalMessageId) {

            data['parentMessageId'] = originalMessageId;
        }
        else {
            originalMessageId = data['parentMessageId'];
        }
        // Создание индикатора ответа
        const replyIndicatorDiv = document.createElement('div');
        replyIndicatorDiv.classList.add('reply-indicator');
        const replyAuthorSpan = document.createElement('span');
        replyAuthorSpan.classList.add('reply-author');
        replyAuthorSpan.textContent = data.userName; // Тут указываем имя пользователя или другую информацию
        const blockquote = document.createElement('blockquote');
        blockquote.textContent = messageData.get(originalMessageId).text; // Тут указываем цитируемый текст

        // Добавляем обработчик нажатия на блок цитаты, чтобы перейти к оригинальному сообщению
        blockquote.addEventListener('click', function() {
            const originalMessageElement = document.getElementById(`message-${data['parentMessageId']}`);
            if (originalMessageElement) {
                originalMessageElement.scrollIntoView({behavior: 'smooth', block: 'center'});
                // Добавляем класс для подсветки
                originalMessageElement.classList.add('highlight');

                // Удаляем подстветку по истечении некоторого срока
                setTimeout(() => {
                    originalMessageElement.classList.remove('highlight');
                }, 3000);

            }
        });
        // Сборка индикатора ответа
        replyIndicatorDiv.appendChild(replyAuthorSpan);
        replyIndicatorDiv.appendChild(blockquote);
        
        // Создание div для сообщения-ответа
        const replyMessageDiv = document.createElement('div');
        replyMessageDiv.classList.add('reply');

        // Создание элемента p для текста сообщения
        const replyMessageP = document.createElement('p');
        replyMessageP.classList.add('reply-message');
        replyMessageP.textContent = data.text;


        // Сборка сообщения-ответа
        replyMessageDiv.appendChild(replyIndicatorDiv);
        replyMessageDiv.appendChild(replyMessageP);


        let messagaeSendDate = new Date(data.sendDate);
        const messageTime = document.createElement('p');
        messageTime.innerHTML = messagaeSendDate.getHours() + ':' + messagaeSendDate.getMinutes();
        messageTime.classList.add('message-time');
        replyMessageDiv.appendChild(messageTime);

        // Добавление сообщения-ответа в общий div
        messageWrapperDiv.appendChild(replyMessageDiv);

        // Очистка переменной с текстом оригинального сообщения
        originalMessageId = null;

    } else {
        // Добавляем автора сообщения, если это групповое сообщение
        if (data.type == 'group-message' || typeof data.type == 'undefined') {

            const authorP = document.createElement('p');
            authorP.classList.add('author-name');
            authorP.textContent = isOutGoing ? 'Вы' : data.userName;
            messageWrapperDiv.appendChild(authorP);
        }
        // Создание элемента p для текста обычного сообщения
        const messageP = document.createElement('p');
        messageP.classList.add('message-text');
        messageP.textContent = data.text;
  
        let messageSendTime = new Date(data.sendDate);
        var hours = toTwoDigits(messageSendTime.getHours());
        var minutes = toTwoDigits(messageSendTime.getMinutes());

        const messageTimeP = document.createElement('p');
        messageTimeP.classList.add('message-time');
        messageTimeP.innerHTML = hours + ':' + minutes;

        messageWrapperDiv.appendChild(messageP);
        messageWrapperDiv.appendChild(messageTimeP);
    }

    // Назначение id сообщению
    if (data.messageId) {
        messageWrapperDiv.id = `message-${data.messageId}`;
        messageWrapperDiv.dataset.id = data.messageId;
    }


    // Добавление всего сообщения или ответа в контейнер чата
    messagesElement.appendChild(messageWrapperDiv);

    // Прокрутка к последнему сообщению
    messagesElement.scrollTop = messagesElement.scrollHeight;
    // Связываем DOM элемент сообщения с его данными, если нужно
     if (!messageData.has(data.messageId)) messageData.set(Number(data.messageId), data);

    return data;
}

// Нужна для отображения времени отправки сообщения в виде 20:05, а не 20:5
function toTwoDigits(num) {
    return num.toString().padStart(2, '0');
}

// Функция для создания контекстного меню
function createContextMenu(messageId) {

    const contextMenu = document.createElement('div');
    contextMenu.classList.add('context-menu');

    if (messageData.get(messageId).senderId == userId) {
      contextMenu.appendChild(createActionButton('delete', () => deleteMessage(messageId, segments[2])));
      contextMenu.appendChild(createActionButton('edit', () => editMessage(messageId)));
    }
  
    contextMenu.appendChild(createActionButton('reply', () => replyToMessage(messageId)));
    return contextMenu;
  }

  // Функция для позиционирования контекстного меню
  function positionContextMenu(x, y, menu) {
    menu.style.left = `${x-10}px`;
    menu.style.top = `${y-40}px`;
  }
  
  // Функция для удаления существующего контекстного меню
function removeExistingMenu() {
    const existingMenu = document.querySelector('.context-menu');
    if (existingMenu) {
      existingMenu.remove(); // Удаляем существующее контекстное меню
    }
  }

  // Функция для создания кнопки действия
function createActionButton(text, action) {
    const button = document.createElement('button');
    button.classList.add('context-menu-button');
    const icon = document.createElement('i');
  
    switch (text) {
      case 'delete':
        icon.className = 'fa fa-trash';
        button.appendChild(icon);
        button.onclick = (event) => {
          action();
          removeExistingMenu();
        };
        break;
  
      case 'reply':
        icon.className = 'fa solid fa-reply';
        button.appendChild(icon);
        button.onclick = (event) => {
          action();
          removeExistingMenu();
        };
        break;
      
      case 'edit':
        icon.className = 'fa-solid fa-pen-to-square';
        button.appendChild(icon);
        button.onclick = (event) => {
          action();
        };
        break;
    }
    return button;
  }


// Функция удаления сообщения
function deleteMessage(messageId, chatType) {

    body = {
        type: 'delete-message',
        messageId: messageId,
        chatId: chatId,
        chatType: chatType,
        senderId: userId,
    };

    if (chatType == 'dialog') body.interlocutorId = interlocutorData.id;

    sendSocketMessage(body);

    const messageDiv = document.getElementById(`message-${messageId}`);
    messageDiv.remove();
}
// Изменяем сообщение
function editMessage(messageId) {

    let originalMessageText = messageData.get(messageId).text;

    // Создаем и вставляем цитату над полем ввода
    if (!document.querySelector('.edit-quote')) {

        editQuote = document.createElement('div');
        editQuote.classList.add('edit-quote');
    }   else {
        editQuote = document.querySelector('.edit-quote');
    }

    const replyQuote = document.querySelector('.reply-quote');
    if (replyQuote) replyQuote.remove();


    // Мы предполагаем, что над .chat-footer находится .chat-input-container
    const chatInputContainer = document.querySelector('.chat-container');
    chatInputContainer.insertBefore(editQuote, chatInputContainer.querySelector('.chat-footer'));

    // Заполнение элемента цитаты текстом оригинального сообщения и кнопкой отмены ответа
    editQuote.innerHTML = `Редактирование: <span class="text-quote">${originalMessageText}</span> <button class="cancel-reply">
        <i class="fa fa-times" style="color: #2E8B57; cursor: pointer;"></i>
    </button>`;

    // Добавление обработчика на нажатие кнопки "Отмена"
    const cancelEditButton = editQuote.querySelector('.cancel-reply');
    cancelEditButton.addEventListener('click', () => {
        editQuote.remove();
    });

    // Переводим фокус на поле ввода текста
    const inputField = document.querySelector('.chat-input');
    inputField.focus();

   editMessageId = messageId;

}

// Модификация функции replyToMessage для установки контекста ответа
function replyToMessage(messageId) {

    let originalMessageText = messageData.get(messageId).text;
    originalMessageId = messageId;

    let replyQuote;
    // Создаем и вставляем цитату над полем ввода
    if (!document.querySelector('.reply-quote')) {

        replyQuote = document.createElement('div');
        replyQuote.classList.add('reply-quote');
    }

    else {
        replyQuote = document.querySelector('.reply-quote');
    }

    const editQuote = document.querySelector('.edit-quote');
    if (editQuote) editQuote.remove();
    
    // Мы предполагаем, что над .chat-footer находится .chat-input-container
    const chatInputContainer = document.querySelector('.chat-container');
    chatInputContainer.insertBefore(replyQuote, chatInputContainer.querySelector('.chat-footer'));

    // Заполнение элемента цитаты текстом оригинального сообщения и кнопкой отмены ответа
    replyQuote.innerHTML = `Ответ на: <span class="text-quote">${originalMessageText}</span> <button class="cancel-reply">
        <i class="fa fa-times" style="color: #2E8B57; cursor: pointer;"></i>
    </button>`;

    // Добавление обработчика на нажатие кнопки "Отмена"
    const cancelReplyButton = replyQuote.querySelector('.cancel-reply');
    cancelReplyButton.addEventListener('click', () => {
        replyQuote.remove();
        replyingToMessageId = null; // Очищаем контекст ответа
    });

    // Переводим фокус на поле ввода текста
    const inputField = document.querySelector('.chat-input');
    inputField.focus();
}





function addFriendToGroup(friendId) {
    if (groupMembers.includes(friendId)) groupMembers.pop(friendId);

    else groupMembers.push(friendId);
}

function displayFriendsList(friends) {
    // Находим родительский контейнер для списка
    const container = document.querySelector('.friends-list');
    // Создаем элемент ul, который будет содержать список друзей
    const ul = document.createElement('ul');
    ul.className = 'friends-list';
    
    // Для каждого друга создаем элемент списка
    friends.forEach(friend => {
      const li = document.createElement('li');
      li.className = 'friend-item';

      const nameSpan = document.createElement('span');
      nameSpan.textContent = friend.fullName;
      nameSpan.className = 'friend-name';
      
      li.appendChild(nameSpan);
      
      // Добавляем обработчик клика, чтобы добавлять друга в участники группы
      li.addEventListener('click', () => {
        addFriendToGroup(friend.tag);
      });

      // Добавляем элемент списка в ul
      ul.appendChild(li);
    });
    
    // Очищаем ранее добавленные друзей и добавляем новый список
    container.innerHTML = '';
    container.appendChild(ul);
}

function displayGroupName(groupName) {
    const groupNameP = document.querySelector('.chat-header h1');
    groupNameP.innerHTML = groupName;
}

async function getFriendsList() {
    try {
        const response = await fetch(`/friends/sendfriendslist`);
        if (!response.ok) { // Проверка на успешный ответ от сервера
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        displayFriendsList(data);
    } catch (error) {
        console.error('Произошла ошибка:', error);
    }
}

async function sendRequestToCreateGroup(groupName) {
    body = {
        groupName: groupName,
        groupMembers: groupMembers,
    };
    try { const response = await fetch('/chats/creategroup', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(body)
      });
      if (!response.ok) {
        throw new Error('Failed to fetch');
      }

    } catch (error) {
        console.error('Произошла ошибка:', error);
        showFlashMessage('Error occurred', 'violet');
      }
}

async function getGroupName() {
    body = {
        groupId: chatId,
    };
    try {
        const response = await fetch(`/chats/sendgroupname`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application-json',
            },
            body: JSON.stringify(body),
        });

        const groupName = await response.json();
        displayGroupName(groupName);
    } catch (error) {
        console.error('Произошла ошибка:', error);
        showFlashMessage('Error occurred', 'violet');
      }
}


if (path.includes(`groups`)) {
    const createGroupButton = document.getElementById('create-group-button');
    createGroupButton.addEventListener('click', function(event) {

        event.preventDefault();
        if (groupMembers.length < 2) {
            showFlashMessage('Недостаточное колличество участников', 'red');
        }
        
        const inputElement = document.getElementById('group-name');

        if (inputElement.value != '') {
            sendRequestToCreateGroup(inputElement.value);
        }
    });
}



 // flash-сообщение
 function showFlashMessage(message, color) {
    const flashMessage = document.createElement('div');
    flashMessage.textContent = message;
    flashMessage.style.backgroundColor = color;
    flashMessage.style.position = 'fixed';
    flashMessage.style.top = '10px';
    flashMessage.style.left = '10px';
    flashMessage.style.padding = '10px';
    flashMessage.style.borderRadius = '5px';
    document.body.appendChild(flashMessage);
    
    // Удалить сообщение через 3 секунды
    setTimeout(() => {
      flashMessage.remove();
    }, 600);
  }