<?php
class DatabaseWrapper 
{
    private $connection;
    private $connection_type; // 'pgsql' หรือ 'pdo'
    
    public function __construct($connection) 
    {
        $this->connection = $connection;
        $this->connection_type = ($connection instanceof PDO) ? 'pdo' : 'pgsql';
    }
    
    // Wrapper สำหรับ pg_query_params()
    public function query_params($query, $params = []) 
    {
        if ($this->connection_type === 'pdo') {
            try {
                $stmt = $this->connection->prepare($query);
                $stmt->execute($params);
                return $stmt;
            } catch (Exception $e) {
                error_log("Database query error: " . $e->getMessage());
                return false;
            }
        } else {
            return pg_query_params($this->connection, $query, $params);
        }
    }
    
    // Wrapper สำหรับ pg_fetch_assoc()
    public function fetch_assoc($result) 
    {
        if ($this->connection_type === 'pdo') {
            return $result->fetch(PDO::FETCH_ASSOC);
        } else {
            return pg_fetch_assoc($result);
        }
    }
    
    // Wrapper สำหรับ pg_fetch_all()
    public function fetch_all($result) 
    {
        if ($this->connection_type === 'pdo') {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return pg_fetch_all($result);
        }
    }
    
    // Wrapper สำหรับ pg_num_rows()
    public function num_rows($result) 
    {
        if ($this->connection_type === 'pdo') {
            return $result->rowCount();
        } else {
            return pg_num_rows($result);
        }
    }
    
    // ดึง connection ตัวจริง
    public function getConnection() 
    {
        return $this->connection;
    }
    
    // ดึงประเภท connection
    public function getConnectionType() 
    {
        return $this->connection_type;
    }
    
    // Close connection
    public function close() 
    {
        if ($this->connection_type === 'pdo') {
            $this->connection = null;
        } else {
            pg_close($this->connection);
        }
    }
}

// Global functions สำหรับ backward compatibility
function db_query_params($connection, $query, $params = []) 
{
    if ($connection instanceof DatabaseWrapper) {
        return $connection->query_params($query, $params);
    } elseif ($connection instanceof PDO) {
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } else {
        return pg_query_params($connection, $query, $params);
    }
}

function db_fetch_assoc($result) 
{
    if ($result instanceof PDOStatement) {
        return $result->fetch(PDO::FETCH_ASSOC);
    } else {
        return pg_fetch_assoc($result);
    }
}

function db_fetch_all($result) 
{
    if ($result instanceof PDOStatement) {
        return $result->fetchAll(PDO::FETCH_ASSOC);
    } else {
        return pg_fetch_all($result);
    }
}
?>