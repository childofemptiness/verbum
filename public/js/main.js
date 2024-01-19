document.addEventListener('DOMContentLoaded', function() {
   
    // Использование функции getUserName
    getUserName().then((name) => {
        document.querySelector('.user-name').innerHTML = name;
        }).catch((error) => {
        console.error('Failed to fetch user name:', error);
    });

    var dropdown = document.querySelector('.dropdown');
    var dropbtn = document.querySelector('.dropbtn');
    var dropdownContent = document.querySelector('.dropdown-content');

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
