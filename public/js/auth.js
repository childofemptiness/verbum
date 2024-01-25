document.addEventListener('DOMContentLoaded', function() {
    
    const myForm = document.getElementById('myForm');

    // Предотвращаем стандартную отправку формы
    myForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const path = window.location.pathname;
        if (path.includes('login')) {
        sendLogDataAndGetResponse();
        } else {
          sendRegDataAndGetResponse();
        }
    });


});

// Обертка данных формы
const getFormData = (form) => {
    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
      data[key] = value;
    }
    return data;
}
  // Делаем так, чтобы страница авторизации не перезагружалась при нажатии на кнпоку
  
  // Функция отправки запроса для авторизации
  const sendLogDataAndGetResponse = async () => {
    const form = document.getElementById('myForm');
    const data = getFormData(form);
  
    try {
      const response = await fetch('/auth/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      });
  
      if (!response.ok) {
        throw new Error('Failed to fetch');
      }
  
      const responseData = await response.json();
      const status = responseData.status;
      const message = responseData.message;
      // Токен для websocket авторизации
      console.log(responseData.token);
      localStorage.setItem('userToken', responseData.token);
      if (status === 200) {
        console.log(555);
        // Если статус 200, показываем сообщение об успешном входе
        window.location.href = '/main/home';
        showFlashMessage(message, 'green');
      } else {
        window.location.href = '/auth/loginpage';
        showFlashMessage(message, 'red');

      }
        
      
    } catch (error) {
      console.error('Произошла ошибка:', error);
      showFlashMessage('Error occurred', 'violet');
    }
  }
  

  const sendRegDataAndGetResponse = async () => {
    const form = document.getElementById('myForm');
    const data_r = getFormData(form);

   try { const response = await fetch('/auth/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(data_r)
      });
      if (!response.ok) {
        throw new Error('Failed to fetch');
      }
     // console.log(await response.text());
      const responseData = await response.json();
     // console.log(response);
      const status = responseData.status;
      const message = responseData.message;
  
      if (status === 200) {
        // Если статус 200, показываем сообщение об успешном входе
       // window.location.href = 'loginpage';
        showFlashMessage(message, 'green');
      } else {
        //window.location.href = 'registerpage';
        console.log(message);
        showFlashMessage(message, 'red');
      }
    } catch (error) {
        console.error('Произошла ошибка:', error);
        showFlashMessage('Error occurred', 'violet');
      }
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
    }, 300);
  }
  