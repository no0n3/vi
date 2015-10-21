<?php
namespace components\db;

class Query {

    private $query = [];

    public function select($columns) {
        if (is_string($columns)) {
            $this->query['select'] = $columns;
        }

        return $this;
    }

    public function from($columns) {
        if (is_string($columns)) {
            $this->query['from'] = $columns;
        }

        return $this;
    }

    public function limit($limit) {
        $this->query['limit'] = (int) $limit;

        return $this;
    }

    public function where($condition, $params = null) {
        $this->query['where'] = $this->_where($condition);
        $this->query['params'] = $params;

        return $this;
    }

    public function andWhere($condition, $params = null) {
        $this->query['where'] = (empty($this->query['where']) ? '' : ("({$this->query['where']}) AND ")) . $this->_where($condition);
        $this->query['params'] = $params;

        return $this;
    }

    public function orWhere($condition, $params = null) {
        $this->query['where'] = (empty($this->query['where']) ? '' : ("({$this->query['where']}) OR ")) . $this->_where($condition);
        $this->query['params'] = $params;

        return $this;
    }

    private function _where($condition) {
        if (is_string($condition)) {
            return $condition;
        } elseif (is_array($condition)) {
            $res = '';
            $i = 0;
            $c = count($condition);

            foreach ($condition as $key => $value) {
                if(null === $value) {
                    $res .= "$key IS NOT NULL";
                } elseif(is_array($value)) {
                    $_i = 0;
                    $_c = count($value);
                    $res .= "$key IN (";

                    foreach ($value as $item) {
                        if(is_string($item)) {
                            $res .= "'$item'";
                        } else {
                            $res .= $item;
                        }

                        if($_i++ < $_c - 1) {
                            $res .= ', ';
                        }
                    }

                    $res .= ')';
                } elseif(is_string($value)) {
                    $res .= "$key = '$value'";
                } else {
                    $res .= "$key = $value";
                }

                if($i++ < $c - 1) {
                    $res .= ' AND ';
                }
            }

            return $res;
        }
    }

    public function update($tableName, $values, $params = null) {
        $this->query['update'] = $tableName;
        $this->query['values'] = $values;
        $this->query['params'] = $params;

        return $this;
    }

    public function insert($tableName, $values, $params = null) {
        $this->query['insert'] = $tableName;
        $this->query['values'] = $values;
        $this->query['params'] = $params;

        return $this;
    }

    public function asAssoc() {
        $this->query['fetch_type'] = \PDO::FETCH_ASSOC;
        return $this;
    }

    public function asArray() {
        $this->query['fetch_type'] = \PDO::FETCH_NUM;
        return $this;
    }

    public function asObject() {
        $this->query['fetch_type'] = \PDO::FETCH_OBJ;
        return $this;
    }

    public function asClass($className) {
        $this->query['fetch_type'] = \PDO::FETCH_CLASS;
        $this->query['fetch_class'] = $className;
        return $this;
    }

    public function indexBy($columns) {
        $r = preg_split("/,\s*/", $columns);
        $this->query['index_by'] = $r;

        return $this;
    }

    public function all() {
        return $this->_getResults(false);
    }

    public function column() {
        return $this->_getResults(true);
    }

    private function _getResults($firstColumn = false) {
        $q = $this->buildQuery();

        if (empty($this->query['params'])) {
            if ($q['update']) {
                
            } else {
                $fetchType = isset($this->query['fetch_type']) ? $this->query['fetch_type'] : \PDO::FETCH_ASSOC;
                $c = \Vi::$app->db;
                $stmt = $c->executeQuery($q['query']);

                if ($fetchType === \PDO::FETCH_CLASS) {
                    $result = $stmt->fetchAll($fetchType, $this->query['fetch_class']);
                } else {
                    $result = $stmt->fetchAll($fetchType);
                }

                if(isset($this->query['index_by'])) {
                    $ib = $this->query['index_by'];
                    $_r = [];

                    foreach($result as $i) {
                        $index = $ib[0];
                        foreach($ib as $colName) {
                            $index .= $i->$colName;
                        }

                        $_r[$i[$index]] = $i;
                    }

                    $result = $_r;
                }
                
                if (true === $firstColumn && 0 < count($result)) {
                    $firstColumnName = array_keys($result[0])[0];

                    return array_column($result, $firstColumnName);
                }

                return $result;
            }
        } else {
            $ps = $c->prepare($q['query']);

            $fetchType = isset($this->query['fetch_type']) ? $this->query['fetch_type'] : \PDO::FETCH_ASSOC;

            if ($ps->execute($this->query['params'])) {
                $result = $ps->fetchAll($fetchType);
                
                if(isset($this->query['index_by'])) {
                    $ib = $this->query['index_by'];

                    foreach($result as $i) {
                        $index = '';

                        foreach($ib as $colName) {
                            $index .= $i->$colName;
                        }

                        $result[$index] = $i;
                    }
                }
            } else {
                return [];
            }
            
            return $result;
        }
    }

    public function execute() {
        $q = $this->buildQuery();
        
        $ps = \Vi::$app->db->prepare($q['query']);

        return $ps->execute($this->query['params']);
    }

    private function buildQuery() {
        if (isset($this->query['select'])) {
            return [
                'query' => sprintf(
                    "SELECT %s FROM %s %s %s",
                    $this->query['select'],
                    $this->query['from'],
                    (isset($this->query['where']) ? ('WHERE ' . $this->query['where']) : ''),
                    (isset($this->query['limit']) ? ('LIMIT ' . strval($this->query['limit'])) : '')
                ),
                'update' => false
            ];
        } elseif (isset($this->query['update'])) {
            $values = '';
            $c = count($this->query['values']);
            $i = 0;

            foreach($this->query['values'] as $k => $v) {
                $values .= "$k = $v";
                if($i++ < $c - 1) {
                    $values .= ', ';
                }
            }

            return [
                'query' => sprintf("UPDATE %s SET %s %s",
                    $this->query['update'],
                    $values,
                    (isset($this->query['where']) ? ('WHERE ' . $this->query['where']) : '')
                ),
                'update' => true
            ];
        } elseif (isset($this->query['insert'])) {
            $columns = '';
            $values = '(';
            $c = count($this->query['values']);
            $i = 0;

            foreach($this->query['values'] as $k => $v) {
                $values .= is_string($v) ? (empty($this->query['params']) ? "'$v'" : $v) : (null === $v ? 'NULL' : $v);
                $columns .= "$k";

                if($i++ < $c - 1) {
                    $values .= ', ';
                    $columns .= ', ';
                }
            }

            $values .= ')';

            return [
                'query' => sprintf("INSERT INTO %s (%s) VALUES %s %s",
                    $this->query['insert'],
                    $columns,
                    $values,
                    (isset($this->query['where']) ? ('WHERE ' . $this->query['where']) : '')
                ),
                'update' => true
            ];
        }
    }

    public function generate($query) {
        $this->query = $query;
        return $this;
    }

}
