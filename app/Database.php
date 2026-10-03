<?php

class Database {
    private static $instance = null;
    private $pdo;
    private $config;

    private function __construct() {
        $this->config = require __DIR__ . '/../config/database.php';
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        $conn = $this->config['connections'][$this->config['default']];
        $dsn = "{$conn['driver']}:host={$conn['host']};port={$conn['port']};dbname={$conn['database']};charset={$conn['charset']}";
        
        try {
            $this->pdo = new PDO($dsn, $conn['username'], $conn['password'], $conn['options']);
        } catch (PDOException $e) {
            if (config('app.debug')) {
                die("Database connection failed: " . $e->getMessage());
            }
            die("Database connection failed. Please check your configuration.");
        }
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchColumn($sql, $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";
        $params = array_merge($data, $whereParams);
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function transaction(callable $callback) {
        $this->pdo->beginTransaction();
        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function table($table) {
        return new QueryBuilder($this, $table);
    }

    public function raw($sql, $params = []) {
        return $this->query($sql, $params);
    }
}

class QueryBuilder {
    private $db;
    private $table;
    private $wheres = [];
    private $joins = [];
    private $selects = ['*'];
    private $orders = [];
    private $groups = [];
    private $havings = [];
    private $limitValue = null;
    private $offsetValue = null;
    private $distinct = false;
    private $whereBindings = [];
    private $modelClass = null;

    public function __construct($db, $table) {
        $this->db = $db;
        $this->table = $table;
    }

    public function asModel($class) {
        $this->modelClass = $class;
        return $this;
    }

    private function wrap($row) {
        if ($this->modelClass && is_array($row)) {
            return new $this->modelClass($row, true);
        }
        return $row;
    }

    public function select($columns = ['*']) {
        $this->selects = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function distinct() {
        $this->distinct = true;
        return $this;
    }

    public function where($column, $operator = null, $value = null) {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => 'and'];
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null) {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => 'or'];
        return $this;
    }

    public function whereIn($column, $values) {
        if (empty($values)) {
            $this->wheres[] = ['column' => $column, 'operator' => 'IN', 'value' => [-1], 'boolean' => 'and'];
        } else {
            $this->wheres[] = ['column' => $column, 'operator' => 'IN', 'value' => $values, 'boolean' => 'and'];
        }
        return $this;
    }

    public function whereNotIn($column, $values) {
        if (empty($values)) {
            $this->wheres[] = ['column' => $column, 'operator' => 'NOT IN', 'value' => [-1], 'boolean' => 'and'];
        } else {
            $this->wheres[] = ['column' => $column, 'operator' => 'NOT IN', 'value' => $values, 'boolean' => 'and'];
        }
        return $this;
    }

    public function whereNull($column) {
        $this->wheres[] = ['column' => $column, 'operator' => 'IS', 'value' => null, 'boolean' => 'and'];
        return $this;
    }

    public function whereNotNull($column) {
        $this->wheres[] = ['column' => $column, 'operator' => 'IS NOT', 'value' => null, 'boolean' => 'and'];
        return $this;
    }

    public function whereBetween($column, $values) {
        $this->wheres[] = ['column' => $column, 'operator' => 'BETWEEN', 'value' => $values, 'boolean' => 'and'];
        return $this;
    }

    public function whereDate($column, $operator, $value) {
        $this->wheres[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => 'and', 'date' => true];
        return $this;
    }

    public function whereRaw($sql, $bindings = []) {
        $this->wheres[] = ['raw' => $sql, 'bindings' => $bindings, 'boolean' => 'and'];
        return $this;
    }

    public function when($condition, $callback) {
        if ($condition) {
            return $callback($this);
        }
        return $this;
    }

    public function join($table, $first, $operator, $second, $type = 'INNER') {
        $this->joins[] = ['type' => $type, 'table' => $table, 'first' => $first, 'operator' => $operator, 'second' => $second];
        return $this;
    }

    public function leftJoin($table, $first, $operator, $second) {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin($table, $first, $operator, $second) {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function orderBy($column, $direction = 'ASC') {
        $this->orders[] = ['column' => $column, 'direction' => $direction];
        return $this;
    }

    public function groupBy($columns) {
        $this->groups = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function having($column, $operator, $value) {
        $this->havings[] = ['column' => $column, 'operator' => $operator, 'value' => $value];
        return $this;
    }

    public function limit($value) {
        $this->limitValue = (int) $value;
        return $this;
    }

    public function offset($value) {
        $this->offsetValue = (int) $value;
        return $this;
    }

    public function forPage($page, $perPage = 15) {
        $this->offset(($page - 1) * $perPage);
        $this->limit($perPage);
        return $this;
    }

    public function get() {
        $this->whereBindings = [];
        $sql = $this->toSql();
        $bindings = $this->getWhereBindings();
        $results = $this->db->fetchAll($sql, $bindings);
        if ($this->modelClass) {
            return array_map([$this, 'wrap'], $results);
        }
        return $results;
    }

    public function first() {
        $this->limit(1);
        $results = $this->get();
        return count($results) > 0 ? $results[0] : null;
    }

    public function count($column = '*') {
        $clone = clone $this;
        $clone->modelClass = null;
        $clone->select(["COUNT({$column}) as count"]);
        $clone->orders = [];
        $clone->limitValue = null;
        $clone->offsetValue = null;
        $result = $clone->first();
        return $result['count'] ?? 0;
    }

    public function sum($column) {
        $clone = clone $this;
        $clone->modelClass = null;
        $clone->select(["SUM({$column}) as sum"]);
        $clone->orders = [];
        $clone->limitValue = null;
        $clone->offsetValue = null;
        $result = $clone->first();
        return $result['sum'] ?? 0;
    }

    public function avg($column) {
        $clone = clone $this;
        $clone->modelClass = null;
        $clone->select(["AVG({$column}) as avg"]);
        $clone->orders = [];
        $clone->limitValue = null;
        $clone->offsetValue = null;
        $result = $clone->first();
        return $result['avg'] ?? 0;
    }

    public function max($column) {
        $clone = clone $this;
        $clone->modelClass = null;
        $clone->select(["MAX({$column}) as max"]);
        $clone->orders = [];
        $clone->limitValue = null;
        $clone->offsetValue = null;
        $result = $clone->first();
        return $result['max'] ?? 0;
    }

    public function min($column) {
        $clone = clone $this;
        $clone->modelClass = null;
        $clone->select(["MIN({$column}) as min"]);
        $clone->orders = [];
        $clone->limitValue = null;
        $clone->offsetValue = null;
        $result = $clone->first();
        return $result['min'] ?? 0;
    }

    public function exists() {
        return $this->count() > 0;
    }

    public function find($id) {
        return $this->where('id', $id)->first();
    }

    public function insert(array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql, $data);
        return $this->db->getPdo()->lastInsertId();
    }

    public function update(array $data) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . $this->buildWhereClause();
        $params = array_merge($data, $this->getWhereBindings());
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount();
    }

    public function delete() {
        $sql = "DELETE FROM {$this->table}" . $this->buildWhereClause();
        $stmt = $this->db->query($sql, $this->getWhereBindings());
        return $stmt->rowCount();
    }

    public function increment($column, $amount = 1) {
        $sql = "UPDATE {$this->table} SET {$column} = {$column} + {$amount}" . $this->buildWhereClause();
        $stmt = $this->db->query($sql, $this->getWhereBindings());
        return $stmt->rowCount();
    }

    public function decrement($column, $amount = 1) {
        $sql = "UPDATE {$this->table} SET {$column} = {$column} - {$amount}" . $this->buildWhereClause();
        $stmt = $this->db->query($sql, $this->getWhereBindings());
        return $stmt->rowCount();
    }

    public function paginate($perPage = 15, $page = null) {
        $page = $page ?? (int)($_GET['page'] ?? 1);
        $total = $this->count();
        $this->forPage($page, $perPage);
        $data = $this->get();
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to' => min($page * $perPage, $total),
            'has_more_pages' => $page < ceil($total / $perPage),
        ];
    }

    private function toSql() {
        $sql = 'SELECT ';
        if ($this->distinct) {
            $sql .= 'DISTINCT ';
        }
        $sql .= implode(', ', $this->selects);
        $sql .= " FROM {$this->table}";
        
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }
        
        $sql .= $this->buildWhereClause();
        
        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
            if (!empty($this->havings)) {
                $sql .= ' HAVING ';
                $havingClauses = [];
                $hCounter = 0;
                foreach ($this->havings as $having) {
                    $name = ":having_{$hCounter}";
                    $havingClauses[] = "{$having['column']} {$having['operator']} {$name}";
                    $this->whereBindings[$name] = $having['value'];
                    $hCounter++;
                }
                $sql .= implode(' AND ', $havingClauses);
            }
        }
        
        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ';
            $orderClauses = [];
            foreach ($this->orders as $order) {
                $orderClauses[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= implode(', ', $orderClauses);
        }
        
        if ($this->limitValue !== null) {
            $sql .= ' LIMIT ' . $this->limitValue;
        }
        
        if ($this->offsetValue !== null) {
            $sql .= ' OFFSET ' . $this->offsetValue;
        }
        
        return $sql;
    }

    private function buildWhereClause() {
        if (empty($this->wheres)) {
            return '';
        }
        
        $clauses = [];
        $first = true;
        $counter = 0;
        
        foreach ($this->wheres as $where) {
            $boolean = $first ? '' : strtoupper($where['boolean']) . ' ';
            $first = false;
            
            if (isset($where['raw'])) {
                $clauses[] = "{$boolean}{$where['raw']}";
                foreach ($where['bindings'] as $key => $value) {
                    $name = is_string($key) ? ":{$key}" : ":where_{$counter}";
                    $this->whereBindings[$name] = $value;
                    $counter++;
                }
            } elseif ($where['value'] === null) {
                $clauses[] = "{$boolean}{$where['column']} {$where['operator']} NULL";
            } elseif (is_array($where['value'])) {
                $names = [];
                foreach ($where['value'] as $v) {
                    $names[] = ":where_{$counter}";
                    $this->whereBindings[":where_{$counter}"] = $v;
                    $counter++;
                }
                $placeholders = implode(', ', $names);
                $clauses[] = "{$boolean}{$where['column']} {$where['operator']} ({$placeholders})";
            } else {
                $name = ":where_{$counter}";
                $clauses[] = "{$boolean}{$where['column']} {$where['operator']} {$name}";
                $this->whereBindings[$name] = $where['value'];
                $counter++;
            }
        }
        
        return ' WHERE ' . implode(' ', $clauses);
    }

    private function getWhereBindings() {
        $bindings = $this->whereBindings;
        $this->whereBindings = [];
        return $bindings;
    }
}

function db() {
    return Database::getInstance();
}