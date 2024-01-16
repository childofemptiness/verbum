import { sendWebScoketMessage, onWebSocketMessage } from "./server.js";


// Функция отправки приглашения в друзья
function sendFriendRequest(userTagToInvite) {
    sendWebScoketMessage({
        type: 'friend-request',
        userTagToInvite: userTagToInvite
    });
}

function handleMessage(data) {
    if (data.type == 'frined_request') {
        addInvitation(data);
    }
}

// Подписываемся на WebSocket сообщения
onWebSocketMessage(handleMessage);

document.addEventListener('DOMContentLoaded', function() {

    // Использование функции getUserid
    getUserId().then((id) => {
        document.querySelector('.user-friend-id').innerHTML = "Ваш тэг: " + (666666 + id);
        }).catch((error) => {
        console.error('Failed to fetch user id:', error);
    });
    
    var dropdown = document.querySelector('.dropdown');
    var dropdownContent = document.querySelector('.dropdown-menu-content');

    dropdown.addEventListener('click', function(event) {
        // Этот код переключает видимость выпадающего меню
        dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
        event.stopPropagation();
    });

    // Закрывает выпадающее меню, когда кликают вне его
    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            if (dropdownContent.classList.contains('show')) {
                dropdownContent.classList.remove('show');
            }
        }
    };
});


document.querySelector('.friends-list-button').addEventListener('click', function() {
    // Переключает видимость списка друзей
    var friendsList = document.querySelector('.friends-list');
    friendsList.classList.toggle('hidden');
});

document.querySelector('.search-form').addEventListener('submit', function(event) {
    event.preventDefault();
    var userTagToInvite = document.getElementById('user-search').value;
    
    sendFriendRequest(userTagToInvite);
});


function getUserId() {
    return fetch('/friends/getuserid') // Здесь используется метод GET по умолчанию
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json(); // Или response.json(), если сервер возвращает JSON
      });
}


// Функция для добавления нового приглашения в список
function addInvitation(invitationData) {
    // Получаем элемент <template>
    const template = document.getElementById('invitations-template');
    
    // Клонируем содержимое шаблона
    const clone = template.content.cloneNode(true);
    
    // Заполняем данные о приглашении
    clone.querySelector('.invitation-name').textContent = invitationData.name;
    
    // Обработка кликов на кнопках
    clone.querySelector('.accept-btn').addEventListener('click', () => acceptInvitation(invitationData.id));
    clone.querySelector('.decline-btn').addEventListener('click', () => declineInvitation(invitationData.id));
    
    // Добавляем приглашение в DOM
    const invitationsList = document.getElementById('invitations-list');
    invitationsList.appendChild(clone);
  }
  

  
 
