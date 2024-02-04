let invitationsList;

document.addEventListener('DOMContentLoaded', function() {
    
    getFriendsList();
    // Использование функции getUserid
    getUserId().then((id) => {
        document.querySelector('.user-friend-id').innerHTML = "Ваш тэг: " + (666666 + id);
        }).catch((error) => {
        console.error('Failed to fetch user id:', error);
    });

    const sentButton = document.getElementById("sent-button");
    const receivedButton = document.getElementById("recevied-button");
    invitationsList = document.querySelector('.invitations-list');
    
    sentButton.addEventListener("click", function() {
        fetchInvitations('sent');
    });

    receivedButton.addEventListener("click", function() {
        fetchInvitations('received');
    });

    
});

// Выпадающее меню левого сайдбара

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


// Выпадающиее меню правого сайдбара

var dropdownInvitation = document.querySelector('.invitation-dropdown');
var dropdownInvitationContent = document.querySelector('.invitation-dropdown-content');


dropdownInvitation.addEventListener('click', function(event) {
    // Этот код переключает видимость выпадающего меню
    dropdownInvitationContent.style.display = dropdownInvitationContent.style.display === 'block' ? 'none' : 'block';
    event.stopPropagation();
});

// Закрывает выпадающее меню, когда кликают вне его
window.onclick = function(event) {
    if (!event.target.matches('invitation-dropbtn')) {
        if (!dropdownInvitation.contains(event.target)) {
            dropdownInvitationContent.style.display = 'none';
        }
    }
};


// Отправка приглашений

var formElement = document.getElementById('search-form');

// Назначаем обработчик события submit на форму
formElement.addEventListener('submit', function(event) {
    event.preventDefault(); // Отменяем стандартное поведение формы (отправку)

    var input = formElement.querySelector('input[type="text"]');
    var userTag = input.value;

    getUserId().then((id) => {
        if (id != userTag) {
        sendFriendRequest(userTag);
        }
        }).catch((error) => {
        console.error('Failed to fetch user id:', error);
    });


    input.value = '';
});

async function sendFriendRequest(tag) {
    const response = await fetch('/friends/setfriendrequest', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ tag: tag }) // Тело запроса с тэгом в формате JSON
    });

    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    // Поскольку обратных данных нет, мы не ожидаем ответ в формате JSON.
}


// Получение id пользователя для тэга

async function getUserId() {
    const response = await fetch('/friends/senduserid'); // Используем await для ожидания ответа от fetch
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    return await response.json(); // Ожидаем резолва Promise от response.json()
}


// Получение/обработка приглашений
async function fetchInvitations(type) {
     try {
        // Предполагается, что переменная url содержит правильный путь к вашему API
        const response = await fetch(`/friends/getall` + type + `requests`);
        if (!response.ok) { // Проверка на успешный ответ от сервера
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        if (data != null) renderInvitations(data, type); // Вызов функции отрисовки приглашений
     } catch (error) {
         console.error('Error fetching invitations:', error);
     }
}

async function sendResponseToRequest(flag, userId) {
    let data = {
        flag: flag,
        userId: userId
    };
    try {
        const response = await fetch(`/friends/getresponsetorequest`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application-json',
            },
            body: JSON.stringify(data)
        });
    } catch (error) {
        console.error('Произошла ошибка:', error);
    }
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
// РЕФАКТОРИНГ: ПОМЕНЯТЬ POST НА GET, тэг передавать через GET -------------------------------------------------------------------------------------------------------
async function sendFriendTagToDelete(tag) {
    try {
        const response = await fetch(`/friends/getfriendtagtodelete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application-json',
            },
            body: JSON.stringify({tag: tag})
        });
    }   catch (error) {
        console.error('Произошла ошибка:', error);
    }
}

async function getDialogId(tag) {
    try {
        const response = await fetch(`/chats/senddialogid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application-json',
            },
            body: JSON.stringify({tag: tag})
        });

        if (!response.ok) { // Проверка на успешный ответ от сервера
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();

        return data;

    }   catch (error) {
        console.log('Произошла ошибка', error);
    }
}


function createInvitationItem(invitation, type) {
    const item = document.createElement("div");
    item.classList.add('invitation-item'); // Применяем класс для стилизации
    const basicInfo = `<strong>${invitation.userName}</strong> (${invitation.fullName}) - UserId: ${invitation.userId}`;
    item.innerHTML = basicInfo;

    if (type !== 'sent') {
        const buttonContainer = createButtonContainer(invitation);
        item.appendChild(buttonContainer); // Добавляем контейнер с кнопками в элемент приглашения
    } else {
        // Добавьте статус для отправленных приглашений, если необходимо
    }

    return item;
}

function createButtonContainer(invitation) {
    const buttonContainer = document.createElement('div');
    buttonContainer.classList.add('invitation-button-container');
 
    const acceptButton = createButton('Принять', () => sendResponseToRequest(true, invitation.userId));
    buttonContainer.appendChild(acceptButton);

    const rejectButton = createButton('Отклонить', () => sendResponseToRequest(false, invitation.userId));
    buttonContainer.appendChild(rejectButton);
 
    return buttonContainer;
}

function createButton(text, onClick) {
    const button = document.createElement('button');
    button.textContent = text;
    button.classList.add('invitation-button'); // Применяем стили для кнопки
    button.onclick = function(event) {
        // Любая пользовательская логика, переданная через параметр onClick
        if (onClick) onClick(event);
        // Удаление родителя кнопки
        event.currentTarget.parentElement.parentElement.remove();
        // Если после удаления этого приглашения других не осталось - удаляем кнопку "Скрыть"
        const parent = document.querySelector('.invitations-list');
        if (!parent.querySelector('.invitations-item')) {
            const closeButton = document.getElementById('closeButton');
            closeButton.remove();
        }
    };
    return button;
}


function renderInvitations(invitations, type) {
    if(!invitationsList.innerHTML == '') invitationsList.innerHTML = '';

    
    const closeButton = document.createElement('button');
    closeButton.textContent = 'Скрыть';
    closeButton.classList.add('invitation-button');
    closeButton.id = 'closeButton';

    closeButton.onclick = function() {
        invitationsList.innerHTML = '';
    };

    invitationsList.appendChild(closeButton);

    invitations.forEach(invitation => {
        const item = createInvitationItem(invitation, type);
        invitationsList.appendChild(item);
    });
    
    const container = document.querySelector('.invitation-list-container');
    if (container) {
        container.innerHTML = ''; // Очищаем предыдущий контент
        container.appendChild(invitationsList); // Добавляем новый список приглашений
    }
}


// Выводим список друзей
function displayFriendsList(friends) {
    // Находим родительский контейнер для списка
    const container = document.querySelector('.friends-list-section');
    
    const header = document.createElement('div');
    header.innerHTML = 'Друзья:';
    header.classList.add('friends-list-header');
    // Создаем элемент ul, который будет содержать список друзей
    const ul = document.createElement('ul');
    ul.className = 'friends-list';
    
    // Для каждого друга создаем элемент списка и добавляем в ul
    friends.forEach(friend => {
      const li = document.createElement('li');
      li.className = 'friend-item';
      
      const nameSpan = document.createElement('span');
      nameSpan.textContent = friend.fullName;
      nameSpan.className = 'friend-name';
      
      const tagSpan = document.createElement('span');
      tagSpan.textContent = `Tag: ${friend.tag}`;
      tagSpan.className = 'friend-tag';
      
      const nickSpan = document.createElement('span');
      nickSpan.textContent = `Nick: ${friend.userName}`;
      nickSpan.className = 'friend-nick';
      
      const deleteButton = document.createElement('button');
      deleteButton.textContent = 'Удалить';
      deleteButton.className = 'button button-delete';
      // Добавление обработчика для удаления
      deleteButton.addEventListener('click', () => {
        ul.removeChild(li);
        sendFriendTagToDelete(friend.tag);
      });
  
      const messageButton = document.createElement('button');
      messageButton.textContent = 'Написать';
      messageButton.className = 'button button-message';

      messageButton.onclick = function(event) {
        getDialogId(friend.tag).then((dialogId) => {
            console.log(dialogId);
            window.location.href = `/chats/dialog/${dialogId}`;
        }).catch((error) => {
            console.error('Failed to fetch  chatId:', error);
        });
      }
      
      // Собираем элемент списка с информацией и кнопками
      li.appendChild(nameSpan);
      li.appendChild(tagSpan);
      li.appendChild(nickSpan);
      li.appendChild(deleteButton);
      li.appendChild(messageButton);
  
      // Добавляем элемент списка в ul
      ul.appendChild(li);
    });
    
    // Очищаем ранее добавленные друзей и добавляем новый список
    container.innerHTML = '';
    container.appendChild(header);
    container.appendChild(ul);
  }
