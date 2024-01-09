<?php
if (session_id() == '') session_start();
class DbModel {

  protected $rows = array();  
  protected $conx;
  protected $new_id;
  private $pdo;
  private $statement;

  public function __construct() {
    try {
      $options = [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES   => false,
    ];
      $this->pdo = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPASS, $options);
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
      error_log("Database test failed: " . $e->getMessage());
      echo "Failed to connect to MySQL: " . $e->getMessage();      
      exit();
    }
  }  

  # Отправить SQL запрос для INSERT, UPDATE or DELETE
  protected function set_query($sql, $params = []) {
    try {
      $statement = $this->pdo->prepare($sql);
      // Привязываем значения параметров
      //print_r($params);
      foreach ($params as $key => $value) {
        // Привязка параметров к вашему запросу
        $statement->bindValue(':' . $key, $value); // Легкая модификация для прямого использования $value
        // echo $key;
        // echo "  =>  ";
        // echo $value;
    }
 //  echo "   ";
        // Выполняем запрос
        $executionResult = $statement->execute();
        
        // Получаем ID последней вставленной записи, если это был INSERT запрос
        if ($executionResult) {
            $this->new_id = $this->pdo->lastInsertId();
        }
        
        // Возвращаем результат выполнения запроса
        return $executionResult;
    } catch (PDOException $e) {
        // Логгируем ошибку, чтобы не потерять информацию об исключении
        error_log("Query failed: " . $e->getMessage());

        // Добавляем ошибку в сессию, если нужно отобразить ее пользователю
        $_SESSION["error"][] = "Query error: " . $e->getMessage();
        
        // Возвращаем false, так как запрос не был успешно выполнен
        return false;
    }
}

  public function set_multyquery($sql) {
    try {
      $this->pdo->exec($sql);
    } catch(PDOException $e) {
      error_log("Query failed: " . $sql);
      $_SESSION["error"][] = "Query error: " . $e->getMessage();      
    }
  }

  public function beginTransaction(){
    $this->pdo->beginTransaction();
  }

  public function commitTransaction(){
    $this->pdo->commit();
  }

  public function rollBack(){
    $this->pdo->rollBack();
  }

  # Submit SELECT SQL query
  public function get_query($sql, $params = []) {
    try {
        $statement = $this->pdo->prepare($sql);
        foreach ($params as $key => &$value) {  // Обратите внимание на ссылку & перед $value
          if ($key == 'booksPerPage' || $key == 'offset') {
              // Поскольку LIMIT и OFFSET должны быть целыми числами, приводим их к нужному типу
              $value = (int) $value;
              // Используйте bindParam для привязки параметра по ссылке
              $statement->bindParam(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
          } else {
              $statement->bindValue(':' . $key, $value); // Оставляем как есть для других параметров
          }
      }
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC); // Вот здесь изменение - установим режим выборки ассоциативного массива
        $statement->closeCursor();
        return $result;
    } catch(PDOException $e) {
        error_log("Query failed: " . $sql);
        $_SESSION["error"][] = "Query error: " . $e->getMessage();
        return false;
    }
}
  # Submit SELECT SQL query - get row count if matches found
  public function get_rows($sql, $params=[]) {
    try {
      $statement = $this->pdo->prepare($sql);

      foreach ($params as $key => &$value) { // Используем ссылку на переменную $value
        $statement->bindParam(':' . $key, $value);
      }
      $statement->execute();

      $rows = $statement->rowCount();
    } catch(PDOException $e) {
      error_log("Query failed: " . $sql);
      $_SESSION["error"][] = "Query error: " . $e->getMessage();
      return false;
    }

    return $rows;    
}

  public function catchJson()
  {
      return json_decode(file_get_contents('php://input'), true);
}
  

  public function getUserID()
  {
    send_json(['id' => $_SESSION['id']]);
}

  public function lastInsertId()
  {
    return $this->pdo->lastInsertId();
}
}


