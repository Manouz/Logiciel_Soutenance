<?php
require_once '../config/database.php';

function getAllRecords($table) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM " . $table . " ORDER BY id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertRecord($table, $data) {
    $database = new Database();
    $db = $database->getConnection();
    
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $query = "INSERT INTO " . $table . " (" . $columns . ") VALUES (" . $placeholders . ")";
    $stmt = $db->prepare($query);
    
    foreach ($data as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    
    return $stmt->execute();
}

function updateRecord($table, $data, $id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $setClause = '';
    foreach ($data as $key => $value) {
        $setClause .= $key . ' = :' . $key . ', ';
    }
    $setClause = rtrim($setClause, ', ');
    
    $query = "UPDATE " . $table . " SET " . $setClause . " WHERE id = :id";
    $stmt = $db->prepare($query);
    
    foreach ($data as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':id', $id);
    
    return $stmt->execute();
}

function deleteRecord($table, $id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "DELETE FROM " . $table . " WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $id);
    
    return $stmt->execute();
}

function getRecordById($table, $id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM " . $table . " WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>