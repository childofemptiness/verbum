document.addEventListener('DOMContentLoaded', function() {
   
    // Использование функции getUserName
    getUserName().then((name) => {
        document.querySelector('.user-name').innerHTML = name;
        }).catch((error) => {
        console.error('Failed to fetch user name:', error);
    });
    // Вызываем для отображения списка чатов на сайдбаре
    getChatsList();

    var dropdown = document.querySelector('.dropdown');
    var dropbtn = document.querySelector('.menu-dropbtn');
    var dropdownContent = document.querySelector('.dropdown-menu-content');

    dropbtn.addEventListener('click', function(event) {
        // Переключаем видимость меню
        dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
        event.stopPropagation();
    });

    // Закрыть выпадающее меню, если пользователь кликнет вне его
    window.addEventListener('click', function(event) {
        if (!dropdown.contains(event.target)) {
            dropdownContent.style.display = 'none';
        }
    });
});

function getUserName() {
    return fetch('/main/getusername') // Здесь используется метод GET по умолчанию
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json(); // Или response.json(), если сервер возвращает JSON
      });
  }


async function getChatsList() {
    try {
        const response = await fetch(`/chats/sendchatslist`);
        if (!response.ok) { // Проверка на успешный ответ от сервера
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        displayChatsList(data);
    } catch (error) {
        console.error('Произошла ошибка:', error);
    }
}


function displayChatsList(chats) {
    // Находим родительский контейнер для списка
    const container = document.getElementById('chats-list-container');
    
    const header = document.createElement('div');
    header.innerHTML = 'Чаты:';
    header.classList.add('chats-list-header');
    // Создаем элемент ul, который будет содержать список друзей
    const ul = document.createElement('ul');
    ul.className = 'chats-list';
    
    // Для каждого друга создаем элемент списка и добавляем в ul
    chats.forEach(chat => {
        const li = document.createElement('li');
        li.className = 'chat-item';
        
        const nameSpan = document.createElement('span');
        nameSpan.textContent = chat.chatName;
        nameSpan.className = 'chat-name';
        
        li.appendChild(nameSpan);
        li.addEventListener('click', function() {
            window.location.href = `/chats/${chat.chatType}/${chat.chatId}`
        })
        // Добавляем элемент списка в ul
        ul.appendChild(li);
    });
    
    // Очищаем ранее добавленные друзей и добавляем новый список
    container.innerHTML = '';
    container.appendChild(header);
    container.appendChild(ul);
}
