<?php
namespace App\Models;
use PDO;
use PDOException;

class DbModel {
  protected $rows = array();  
  protected $conx;
  protected $new_id;
  private $pdo;

  protected function __construct($dbName = null) {
    try {
      $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
      $this->pdo = new PDO('mysql:host='.DBHOST.';dbname=' .($dbName == null ? DBNAME : $dbName), DBUSER, DBPASS, $options);
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
      error_log("Database test failed: " . $e->getMessage());
      echo "Failed to connect to MySQL: " . $e->getMessage();      
      exit();
    }
  }  

  # Отправить SQL запрос для INSERT, UPDATE or DELETE
  protected function setQuery($sql, $params = []) {
    try {
      $statement = $this->pdo->prepare($sql);
      // Привязываем значения параметров
      foreach ($params as $key => $value) {
        // Привязка параметров к вашему запросу
        $statement->bindValue(':' . $key, $value); // Легкая модификация для прямого использования $value

    }
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

  protected function setMultyquery($sql) {
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

  protected function commitTransaction(){
    $this->pdo->commit();
  }

  public function rollBack(){
    $this->pdo->rollBack();
  }

  # Submit SELECT SQL query
  protected function getQuery($sql, $params = [], $flag = 0) {
    try {
        $statement = $this->pdo->prepare($sql);
        if ($flag === 1) {
          foreach($params as $key => $value) {
            $statement->bindValue(($key+1), $value);
          }
        }
        else {
          foreach ($params as $key => &$value) {  // Обратите внимание на ссылку & перед $value
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
  protected function getRows($sql, $params=[]) {
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

  public function getUserIdFromSession() {
    return 18;
  }

  protected function lastInsertId() {
    return $this->pdo->lastInsertId();
  }
  
}
