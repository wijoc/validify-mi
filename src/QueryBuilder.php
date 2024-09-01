<?php

namespace Wijoc;

use Exception;
use mysqli;

class QueryBuilder
{
    private $table;
    private $select     = [];
    private $columns    = [];
    private $where      = [];
    private $orderBy;
    private $limit;
    private $offset;
    private $joins      = [];
    private $subquery;
    private $data;
    private $insertData;
    private $upsertData;
    private $upsertSetData;
    private $updateData;
    private $delete;
    private $parameters  = [];
    private $whereParameters  = [];
    private $query = '';

    private $connection;
    private $host;
    private $username;
    private $password;
    private $database;
    private $wordpress;
    private $wpdb;

    public function __construct(String $host = '', String $username = '', String $password = '', String $database = '')
    {
        $this->connection($host, $username, $password, $database);
    }

    /**
     * Create connection function
     *
     * - Check if current project is a wordpress or not.
     * - Then create a connection if not a wordpress project.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $database
     * @return self
     * @throws Exception - When failed to create connection.
     */
    private function connection(String $host = '', String $username = '', String $password = '', String $database = ''): self
    {
        if ($this->checkIfWordpress()) {
            global $wpdb;
            $this->wpdb = $wpdb;
        } else {
            $this->host = $host;
            $this->username = $username;
            $this->password = $password;
            $this->database = $database;

            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);

            if ($this->connection->connect_error) {
                throw new Exception("Failed to connect to database: " . $this->connection->connect_error);
            }
        }

        return $this;
    }

    /**
     * Check if current project is wordpress function
     *
     * @return bool
     */
    private function checkIfWordpress(): bool
    {
        $directory = $_SERVER['DOCUMENT_ROOT'];
        if (file_exists($directory . '/wp-config.php')) {
            if (is_dir($directory . '/wp-includes')) {
                if (is_dir($directory . '/wp-admin')) {
                    if (is_dir($directory . '/wp-content')) {
                        $this->wordpress = true;
                        return $this->wordpress;
                    }
                }
            }
        }

        $this->wordpress = false;

        return $this->wordpress;
    }

    /**
     * Set table function
     *
     * @param String $table
     * @return self
     */
    public function table(String $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Select from table function
     *
     * @param Mixed $columns - Array|String of field name.
     * @return self
     */
    public function select(Mixed $columns = NULL): self
    {
        if (!empty($columns) && $columns !== NULL) {
            if (is_array($columns)) {
                $select = $columns;
            } else if (is_string($columns)) {
                $select = explode(',', $columns);
                $select = array_map(function ($column) {
                    return trim($column);
                }, $select);
            }
        } else {
            $select = ['*'];
        }

        if (isset($this->select) && !empty($this->select) && $this->select !== NULL) {
            $this->select = array_unique(array_merge($this->select, $select));
        } else {
            $this->select = $select;
        }

        return $this;
    }

    /**
     * Set field to use function
     *
     * @param array $fields
     * @return self
     */
    public function column(array $fields): self
    {
        $this->columns = $fields;
        return $this;
    }

    /**
     * Grouping Where function
     *
     * @param string $type
     * @return self
     */
    public function groupWhere(String $type = 'start'): self
    {
        if ($type == 'start') {
            $this->where[]  = "(";
        } else {
            $this->where[]  = ")";
        }

        return $this;
    }

    /**
     * Where Condition function
     *
     * @param String $column - String of field name.
     * @param String $operator
     * @param Mixed $value
     * @param Mixed $relation - value oneOf : 'AND' | 'OR'
     * @param array $cast - value must be an array with key : by and into. By to indicate the the column or value to cast, into is casting format
     * @return self
     */
    public function where(String $column, String $operator, Mixed $value, String $relation = 'AND', array $cast = [], bool $strict = true): self
    {
        /** Prepare column that need a strict query and/or need to cast */
        $needCastValue = false;
        if ($strict) {
            if (!empty($cast) && isset($cast['by']) && isset($cast['into'])) {
                if (strtolower($cast['by']) == 'column' || strtolower($cast['by']) == 'coloumn' || strtolower($cast['by']) == 'field') {
                    $column = $this->_prepareCast($column, $cast['into'], $strict);
                } else if (strtolower($cast['by']) == 'value') {
                    $needCastValue = true;
                }
            } else {
                $column = "`{$column}`";
            }
        }

        /** Check if last where is a group bracket */
        $lastIsGroupBracket = false;
        if (isset($this->where) && is_array($this->where)) {
            $lastIsGroupBracket = (end($this->where) == "(");
        }

        $whereQuery = null;
        switch (strtolower($operator)) {
            case 'isnull':
            case 'is null':
                $whereQuery  = "{$column} IS NULL";
                break;
            case 'isnotnull':
            case 'is not null':
                $whereQuery  = "{$column} IS NULL";
                break;
            case 'like':
                $parameters = $this->_prepareParameters("%$value%");
                $this->whereParameters[] = $parameters;

                /** Prepare cast value */
                $castedParametersKey = [];
                if ($needCastValue) {
                    foreach ($parameters as $key => $value) {
                        $castedParametersKey[] = $this->_prepareCast($key, $cast['into'], $strict);
                    }
                } else {
                    $castedParametersKey = array_keys($parameters);
                }

                if ($this->wordpress) {
                    $whereQuery = "{$column} LIKE '" . implode("', '", $castedParametersKey) . "'"; // not neccesaraly using implode cause this will always
                } else {
                    $whereQuery = "{$column} LIKE ?";
                }
                break;
            case 'in':
                $parameterPlaceholders  = [];

                if (is_array($value)) {
                    foreach ($value as $val) {
                        $parameters = $this->_prepareParameters($val, false);
                        $parameterPlaceholders = array_merge($parameterPlaceholders, array_keys($parameters));
                        $this->whereParameters[] = $parameters;
                    }
                } else {
                    $values = $this->_prepareQueryValue('where', $value);
                    $values = explode(",", $values);

                    foreach ($values as $val) {
                        $parameters = $this->_prepareParameters($val, false);
                        $parameterPlaceholders = array_merge($parameterPlaceholders, array_keys($parameters));
                        $this->whereParameters[] = $parameters;
                    }
                }

                /** Prepare cast value */
                $castedParametersKey = [];
                if ($needCastValue) {
                    foreach ($parameterPlaceholders as $key => $value) {
                        $castedParametersKey[] = $this->_prepareCast($key, $cast['into'], $strict);
                    }
                } else {
                    $castedParametersKey = array_keys($parameterPlaceholders);
                }

                if ($this->wordpress) {
                    $whereQuery = "{$column} IN ('" . implode("', '", $castedParametersKey) . "')";
                } else {
                    $whereQuery = "{$column} IN ('" . implode("', '", $castedParametersKey) . "')";
                }
                break;
            case 'notin':
            case 'not in':
                $parameterPlaceholders  = [];

                if (is_array($value)) {
                    foreach ($value as $val) {
                        $parameters = $this->_prepareParameters($val, false);
                        $parameterPlaceholders = array_merge($parameterPlaceholders, array_keys($parameters));
                        $this->whereParameters[] = $parameters;
                    }
                } else {
                    $values = $this->_prepareQueryValue('where', $value);
                    $values = explode(",", $values);

                    foreach ($values as $val) {
                        $parameters = $this->_prepareParameters($val, false);
                        $parameterPlaceholders = array_merge($parameterPlaceholders, array_keys($parameters));
                        $this->whereParameters[] = $parameters;
                    }
                }

                /** Prepare cast value */
                $castedParametersKey = [];
                if ($needCastValue) {
                    foreach ($parameterPlaceholders as $key => $value) {
                        $castedParametersKey[] = $this->_prepareCast($key, $cast['into'], $strict);
                    }
                } else {
                    $castedParametersKey = array_keys($parameterPlaceholders);
                }

                if ($this->wordpress) {
                    $whereQuery = "{$column} NOT IN ('" . implode("', '", $castedParametersKey) . "')";
                } else {
                    $whereQuery = "{$column} NOT IN ('" . implode("', '", $castedParametersKey) . "')";
                }
                break;
            case '!=':
            case '<>':
                $parameters  = $this->_prepareParameters($value, false);
                $this->whereParameters[] = $parameters;

                /** Prepare cast value */
                $castedParametersKey = [];
                if ($needCastValue) {
                    foreach ($parameters as $key => $value) {
                        $castedParametersKey[] = $this->_prepareCast($key, $cast['into'], $strict);
                    }
                } else {
                    $castedParametersKey = array_keys($parameters);
                }

                if ($this->wordpress) {
                    $whereQuery = "{$column} != '" . implode("', '", $castedParametersKey) . "'"; // not neccesaraly using implode cause this will always
                } else {
                    $whereQuery = "{$column} != ?";
                }
                break;
            case '>':
            case '>=':
            case '<=':
                $parameters  = $this->_prepareParameters($value, false);
                $this->whereParameters[] = $parameters;

                /** Prepare cast value */
                $castedParametersKey = [];
                if ($needCastValue) {
                    foreach ($parameters as $key => $value) {
                        $castedParametersKey[] = $this->_prepareCast($key, $cast['into'], $strict);
                    }
                } else {
                    $castedParametersKey = array_keys($parameters);
                }

                if ($this->wordpress) {
                    $whereQuery = "{$column} {$operator} '" . implode("', '", $castedParametersKey) . "'"; // not neccesaraly using implode cause this will always
                } else {
                    $whereQuery = "{$column} {$operator} ?";
                }
                break;
            case '=':
            default:
                $parameters  = $this->_prepareParameters($value, false);
                $this->whereParameters[] = $parameters;

                /** Prepare cast value */
                $castedParametersKey = [];
                if ($needCastValue) {
                    foreach ($parameters as $key => $value) {
                        $castedParametersKey[] = $this->_prepareCast($key, $cast['into'], $strict);
                    }
                } else {
                    $castedParametersKey = array_keys($parameters);
                }

                if ($this->wordpress) {
                    $whereQuery = "{$column} = '" . implode("', '", $castedParametersKey) . "'"; // not neccesaraly using implode cause this will always
                } else {
                    $whereQuery = "{$column} = ?";
                }
                break;
        }

        /** Add start of group bracket
         * end of group bracket will imploded in build where query
         * that will run in function "build()"
         */
        if ($lastIsGroupBracket) {
            $whereQuery = '(' . $whereQuery;
        }

        /** Add relation to query */
        if (!in_array(strtolower($relation), ['and', 'or'])) {
            $relation = 'AND';
        }

        if (isset($this->where) && is_array($this->where) && count($this->where) >= 1) {
            if ($lastIsGroupBracket) {
                /** Add relation if $lastIsGroupBracket and $lastIsGroupBracket is not in first index */
                $this->where[count($this->where) - 1] = (count($this->where) - 1) > 0 ? " {$relation} {$whereQuery}" : $whereQuery;
            } else {
                $this->where[] = " {$relation} {$whereQuery}";
            }
        } else {
            $this->where[] = $whereQuery;
        }

        return $this;
    }

    /**
     * Where In condition function
     *
     * @param String $column
     * @param Mixed $value
     * @return self
     */
    public function whereIn(String $column, Mixed $value): self
    {
        $parameterPlaceholders  = [];

        if (is_array($value)) {
            foreach ($value as $val) {
                $parameters         = $this->_prepareParameters($val);
                $parameterPlaceholders  = array_merge($parameterPlaceholders, array_keys($parameters));
                $this->whereParameters[]     = $parameters;
            }
        } else {
            $value = $this->_prepareQueryValue('where', $value);
            $parameters         = $this->_prepareParameters($value);
            $this->whereParameters[] = $parameters;
        }

        if ($this->wordpress) {
            if (is_array($value)) {
                if (end($this->where) == "(") {
                    if ((count($this->where) - 1) > 0) {
                        $this->where[count($this->where) - 1]  = " ( `{$column}` IN ('" . implode("', '", $parameterPlaceholders) . "')";
                    } else {
                        $this->where[]  = " `{$column}` IN ('" . implode("', '", $parameterPlaceholders) . "')";
                    }
                } else {
                    $this->where[]  = " `{$column}` IN ('" . implode("', '", $parameterPlaceholders) . "')";
                }
            } else {
                $this->where[]  = " `{$column}` IN ('" . array_keys($parameters) . "')";
            }
        } else {
            if (is_array($value)) {
                $placeholders = [];
                foreach ($parameterPlaceholders as $param) {
                    $placeholders[] = '?';
                }
                $this->where[]  = " `{$column}` IN ('" . implode("', '", $placeholders) . "')";
            } else {
                $this->where[]  = " `{$column}` IN ('" . array_keys($parameters) . "')";
            }
        }

        return $this;
    }

    /**
     * Order By query function
     *
     * @param String $column
     * @param string $direction
     * @return self
     */
    public function orderBy(String $column, ?String $direction = 'ASC'): self
    {
        $this->orderBy = "`{$column}` {$direction}";
        return $this;
    }

    /**
     * Set Limit function
     *
     * @param Int|null $limit
     * @param Int|null $offset
     * @return self
     */
    public function limit(?Int $limit, ?Int $offset): self
    {
        if (isset($limit) && is_numeric($limit) && $limit > 0) {
            $this->limit = $limit;

            if (isset($offset) && is_numeric($offset)) {
                $this->offset = $offset;
            }
        }

        return $this;
    }

    /**
     * Join query function
     *
     * @param String $table - table to join
     * @param String $firstColumn
     * @param String $operator
     * @param String $secondColumn
     * @param Array $conditions - can be array associative or list with structure each element : 'column', 'operator', and 'value'
     * @param String $type - accepted value : self | left | right | inner | full or full-outer | cross
     * @return self
     */
    public function join(String $table, String $firstColumn, String $operator, String $secondColumn, array $conditions = [], ?String $type = 'self'): self
    {
        if (!in_array(strtolower($type), ["self", "left", "right", "inner", "full", "full-outer", "cross"])) {
            throw new Exception("Join type must be one of : self | left | right | inner | full or full-outer | cross!");
        }

        if (array_is_list($conditions)) {
            foreach ($conditions as $condition) {
                if (count(array_diff_key(array_flip(['column', 'operator', 'value']), $condition)) > 0) {
                    throw new Exception("Join condition must array-associative or list with structure each element : 'column', 'operator', and 'value'!");
                }
            }
        } else {
            if (count(array_diff_key(array_flip(['column', 'operator', 'value']), $conditions)) > 0) {
                throw new Exception("Join condition must array-associative or list with structure each element : 'column', 'operator', and 'value'!");
            }
        }

        $this->joins[] = [
            'type'  => strtolower($type),
            'table' => $table,
            'firstColumn'   => $firstColumn,
            'secondColumn'  => $secondColumn,
            'operator'      => $operator,
            'conditions'    => array_is_list($conditions) ? $conditions : [$conditions]
        ];

        return $this;
    }

    /**
     * Add string query as subquery function
     *
     * @param String $subquery
     * @return self
     */
    public function subquery($subquery): self
    {
        $this->subquery = "({$subquery})";
        return $this;
    }

    /**
     * Set data for insert / save function
     *
     * @param array $data
     * @return self
     */
    public function data(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Insert function
     *
     * @param array $data
     * @param String|null $execution
     * @return mixed
     */
    public function insert(array $data = [], ?String $execution = 'execute'): mixed
    {
        if (!empty($data) && $data !== NULL) {
            $this->insertData = $data;
        } else {
            $this->insertData = isset($this->data) ? $this->data : [];
        }

        if (!isset($this->insertData) || empty($this->insertData)) {
            throw new Exception('Please provide data to insert!');
        }

        if (strtolower($execution) == 'self') {
            return $this;
        } else if (strtolower($execution) == 'build') {
            $query = $this->build();

            $query = $this->wpdb->prepare($query, $this->_prepareQueryParameter());

            /** Reset property */
            $this->_reset();

            return $query;
        } else {
            $query = $this->build();

            if ($this->wordpress) {
                // $result = $this->wpdb->insert($this->wpdb->prepare($query, $this->_prepareQueryParameter()), 'ARRAY_A');
                $result = $this->wpdb->query($this->wpdb->prepare($query, $this->_prepareQueryParameter()), 'ARRAY_A');
            } else {
                $result = $this->execute($query);
            }

            /** Reset property */
            $this->_reset();

            return $result;
        }
    }

    /**
     * Undocumented function
     *
     * @param array $data
     * @param array $condition
     * @param String|null $execution
     * @return mixed
     */
    public function update(array $data = [], array $condition = [], String $execution = 'execute'): mixed
    {
        if ((!isset($this->where) || empty($this->where)) && (empty($condition) || $condition == NULL)) {
            throw new Exception('Please provide condition!');
        } else if (!empty($condition)) {
            // if (!is_array($this->where) || $this->where == NULL) {
            //     $this->where = [];
            // }

            $this->where = array_unique(array_merge($this->where, $condition));
        }

        if (!empty($data) && $data !== NULL) {
            $this->updateData = $data;
        } else {
            $this->updateData = isset($this->data) ? $this->data : [];
        }

        if (!isset($this->updateData) || empty($this->updateData)) {
            throw new Exception('Please provide data to update!');
        }

        if (strtolower($execution) == 'self') {
            return $this;
        } else if (strtolower($execution) == 'build') {
            $query = $this->build();
            $query = $this->wpdb->prepare($query, $this->_prepareQueryParameter());

            /** Reset property */
            $this->_reset();

            return $query;
        } else {
            $query = $this->build($this->wordpress);

            if ($this->wordpress) {
                $result = $this->wpdb->query($this->wpdb->prepare($query, $this->_prepareQueryParameter()), 'ARRAY_A');
            } else {
                $result = $this->execute($query);
            }

            /** Reset property */
            $this->_reset();

            return $result;
        }
    }

    /**
     * Insert / update using update on duplicate key update function
     *
     * @param array $data
     * @param array $set
     * @param String|null $execution
     * @return mixed
     */
    public function upsert(array $data = [], array $set = [], String $execution = 'execute'): mixed
    {
        if (!empty($data) && $data !== NULL) {
            $this->upsertData = $data;
        } else {
            $this->upsertData = isset($this->data) ? $this->data : [];
        }

        if (!empty($set) && $set !== NULL) {
            $this->upsertSetData = $set;
        } else {
            $this->upsertSetData = isset($this->updateData) ? $this->updateData : [];
        }


        if (!isset($this->upsertData) || empty($this->upsertData)) {
            throw new Exception('Please provide data to insert or update!');
        }

        if (!isset($this->upsertSetData) || empty($this->upsertSetData)) {
            throw new Exception('Please provide data to update!');
        }

        if (strtolower($execution) == 'self') {
            return $this;
        } else if (strtolower($execution) == 'build') {
            $query = $this->wpdb->prepare($this->build(), $this->_prepareQueryParameter());

            /** Reset property */
            $this->_reset();

            return $query;
        } else {
            $query = $this->build();

            if ($this->wordpress) {
                $result = $this->wpdb->query($this->wpdb->prepare($query, $this->_prepareQueryParameter()), 'ARRAY_A');
            } else {
                $result = $this->execute($query);
            }

            /** Reset property */
            $this->_reset();

            return $result;
        }

        $this->updateData = $data;
        return $this;
    }

    public function delete(array $condition = [], String $execution = 'execute'): mixed
    {
        if ((!isset($this->where) || empty($this->where)) && (empty($condition) || $condition == NULL)) {
            throw new Exception('Please provide condition!');
        } else if (!empty($condition)) {
            $this->where = array_unique(array_merge($this->where, $condition));
        }

        $this->delete = true;

        if (strtolower($execution) == 'self') {
            return $this;
        } else if (strtolower($execution) == 'build') {
            $query = $this->build();
            $query = $this->wpdb->prepare($query, $this->_prepareQueryParameter());

            /** Reset property */
            $this->_reset();

            return $query;
        } else {
            $query = $this->build($this->wordpress);

            if ($this->wordpress) {
                $result = $this->wpdb->query($this->wpdb->prepare($query, $this->_prepareQueryParameter()), 'ARRAY_A');
            } else {
                $result = $this->execute($query);
            }

            /** Reset property */
            $this->_reset();

            return $result;
        }
    }

    /**
     * Build string query function
     *
     * @return String
     */
    public function build(): String
    {
        if (is_array($this->select)) {
            $this->select = implode(', ', $this->select);
        } else {
            $this->select = '*';
        }

        if (!isset($this->query) || empty($this->query) || $this->query == '') {
            $this->query = "SELECT {$this->select} FROM {$this->table}";
        }

        if ($this->delete) {
            $this->query = $this->buildDeleteQuery();
        }

        if ($this->updateData) {
            $this->query = $this->_buildUpdateQuery();
        }

        if ($this->insertData) {
            $this->query = $this->_buildInsertQuery();
        }

        if ($this->upsertData) {
            $this->query = $this->_buildUpsertQuery();
        }

        if (!empty($this->joins)) {
            $this->query .= $this->_buildJoinQuery();
        }

        if (!empty($this->where)) {
            // $this->query .= " WHERE " . implode(' AND ', $this->where);
            $this->query .= " WHERE " . implode($this->where);
        }

        if ($this->subquery) {
            $this->query .= ' ' . $this->subquery;
        }

        if (!empty($this->orderBy)) {
            $this->query .= " ORDER BY {$this->orderBy}";
        }

        if (!empty($this->limit)) {
            $this->query .= " LIMIT {$this->limit}";
            if (!empty($this->offset)) {
                $this->query .= " OFFSET {$this->offset}";
            }
        }

        return $this->query;
    }

    /**
     * Execute select function
     *
     * @return mixed
     */
    public function get(String $execution = 'execution'): mixed
    {
        $query = $this->build();

        if (strtolower($execution) == 'build') {
            $query = $this->wpdb->prepare($query, $this->_prepareQueryParameter());

            /** Reset property */
            $this->_reset();

            return $query;
        }

        if ($this->wordpress) {
            $result = $this->wpdb->get_results($this->wpdb->prepare($query, $this->_prepareQueryParameter()), 'ARRAY_A');
        } else {
            $result = $this->execute($query);
        }

        /** Reset property */
        $this->_reset();

        return $result;
    }

    /**
     * Pluck first data from result function
     *
     * @return mixed
     */
    public function pluck(): mixed
    {
        $query = $this->build();

        if ($this->wordpress) {
            $result = $this->wpdb->get_results($this->wpdb->prepare($query, $this->_prepareQueryParameter()), 'ARRAY_A');
        } else {
            $result = $this->execute($query);
        }

        /** Reset property */
        $this->_reset();

        if (is_array($result) && !empty($result)) {
            return $result[0];
        } else {
            return $result;
        }
    }

    /**
     * Insert execution function
     *
     * @param String|null $execution
     * @return mixed
     */
    public function save(): mixed
    {
        $query = $this->build();

        if ($this->wordpress) {
            $result = $this->wpdb->get_results($this->wpdb->prepare($query, $this->_prepareQueryParameter()), 'ARRAY_A');
        } else {
            $result = $this->execute($query);
        }

        /** Reset property */
        $this->_reset();

        return $result;
    }

    private function execute(String $query)
    {
        return $this->connection->query($query);
    }

    /**
     * Build insert query function
     *
     * @return String
     */
    private function _buildInsertQuery(): String
    {
        if (array_is_list($this->insertData)) {
            if (isset($this->columns) && !empty($this->columns)) {
                if (is_array($this->columns)) {
                    $columns = implode(', ', $this->columns);
                } else {
                    $columns = $this->columns;
                }
            } else {
                $columns = [];
                foreach ($this->insertData[0] as $field => $value) {
                    $columns[] = $field;
                }
            }

            $columns    = implode(', ', $columns);
            $values     = '';
            for ($i = 0; $i < count($this->insertData); $i++) {
                $eachDataParameter      = [];
                $parameterPlaceholders  = [];

                foreach ($this->insertData[$i] as $data) {
                    $parameter  = $this->_prepareParameters($data);
                    $eachDataParameter[]    = $parameter;
                    $parameterPlaceholders  = array_merge($parameterPlaceholders, array_keys($parameter));
                }

                /** Flatten the data parameter */
                foreach ($eachDataParameter as $parameter) {
                    $this->parameters[]         = $parameter;
                }

                $values .= "('" . implode("', '", $parameterPlaceholders) . "')";
                if ($i < (count($this->insertData) - 1)) {
                    $values .= ', ';
                }
            }
        } else {
            if (isset($this->columns) && !empty($this->columns)) {
                if (is_array($this->columns)) {
                    $columns = implode(', ', $this->columns);
                } else {
                    $columns = $this->columns;
                }
            } else {
                $columns = implode(', ', array_keys($this->insertData));
            }

            $values = "";
            $insertParameters = [];
            foreach ($this->insertData as $data) {
                $parameter = $this->_prepareParameters($data);
                $insertParameters = array_merge($insertParameters, array_keys($parameter));
                $this->parameters[] = $parameter;
            }

            $values .= "('" . implode("', '", array_values($insertParameters)) . "')";
        }

        return "INSERT INTO {$this->table} ({$columns}) VALUES {$values}";
    }

    /**
     * Build insert on duplicate key update query function
     *
     * @return String
     */
    private function _buildUpsertQuery(): String
    {
        if (array_is_list($this->upsertData)) {
            if (isset($this->columns) && !empty($this->columns)) {
                $columns = $this->columns;
            } else {
                $columns = [];
                foreach ($this->upsertData[0] as $field => $value) {
                    $columns[] = $field;
                }
            }

            $columns    = implode(', ', $columns);
            $values     = '';
            for ($i = 0; $i < count($this->upsertData); $i++) {
                $eachDataParameter      = [];
                $parameterPlaceholders  = [];

                foreach ($this->upsertData[$i] as $data) {
                    $parameter  = $this->_prepareParameters($data);
                    $eachDataParameter[]    = $parameter;
                    $parameterPlaceholders  = array_merge($parameterPlaceholders, array_keys($parameter));
                }

                /** Flatten the data parameter */
                foreach ($eachDataParameter as $parameter) {
                    $this->parameters[]         = $parameter;
                }

                $values .= "('" . implode("', '", $parameterPlaceholders) . "')";
                if ($i < (count($this->upsertData) - 1)) {
                    $values .= ', ';
                }
            }

            $update = '';
            foreach ($this->upsertSetData as $field => $setValues) {
                if ($setValues[0] == ':' || strpos($setValues, ':') === 0 || strpos($setValues, ':') === "0") {
                    $update .= "{$field} = VALUES($field)" . substr($setValues, 1);
                } else {
                    $update .= "{$field} = VALUES($field)";
                }

                if ($field == array_key_last($this->upsertSetData)) {
                    $update .= ';';
                } else {
                    $update .= ', ';
                }
            }
        } else {
            if (isset($this->columns) && !empty($this->columns)) {
                $columns = $this->columns;
            } else {
                $columns = implode(', ', array_keys($this->upsertData));
            }

            $values = "";
            $dataParameter = [];
            foreach ($this->upsertData as $data) {
                $dataParameter  = array_merge($dataParameter, $this->_prepareParameters($data));
            }

            $values .= "('" . implode("', '", array_keys($dataParameter)) . "')";
        }

        $update = '';
        foreach ($this->upsertSetData as $field => $setValues) {
            if ($setValues[0] == ':' || strpos($setValues, ':') === 0 || strpos($setValues, ':') === "0") {
                $update .= "{$field} = VALUES($field)" . substr($setValues, 1);
            } else {
                $update .= "{$field} = VALUES($field)";
            }

            if ($field == array_key_last($this->upsertSetData)) {
                $update .= ';';
            } else {
                $update .= ', ';
            }
        }

        return "INSERT INTO {$this->table} ({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$update}";
    }

    /**
     * Delete query function
     *
     * @return string
     */
    private function buildDeleteQuery(): string
    {
        return "DELETE FROM {$this->table}";
    }

    /**
     * Build update query function
     *
     * @param boolean $isWordpress
     * @return string
     */
    private function _buildUpdateQuery(Bool $isWordpress = false): string
    {
        $setClause  = [];

        foreach ($this->updateData as $column => $value) {
            $parameter  = $this->_prepareParameters($value);
            if ($this->wordpress) {
                $setClause[] = "`{$column}` = '" . implode(', ', array_keys($parameter)) . "'";
            } else {
                $setClause[] = "`{$column}` = ?";
            }

            if (!isset($this->parameters) || $this->parameters == NULL) {
                $this->parameters = [];
            }

            // $this->parameters[] = array_merge($this->parameters, $parameter);
            $this->parameters[] = $parameter;
        }

        return "UPDATE {$this->table} SET " . implode(', ', $setClause);
    }

    /**
     * Join query function
     *
     * @return String
     * @throws Exception - If join conditions is not an array list
     */
    private function _buildJoinQuery(): String
    {
        $query = '';
        foreach ($this->joins as $join) {
            switch (strtolower($join['type'])) {
                case "left":
                    $query .= " LEFT JOIN";
                    break;
                case "right":
                    $query .= " RIGHT JOIN";
                    break;
                case "inner":
                    $query .= " INNER JOIN";
                    break;
                case "full":
                case "full-outer":
                    $query .= " FULL JOIN";
                    break;
                case "cross":
                    $query .= " CROSS JOIN";
                    break;
                case "self":
                default:
                    $query .= " JOIN";
                    break;
            }

            $query .= " ON {$join['firstColumn']} {$join['operator']} {$join['secondColumn']}";

            if (array_key_exists('conditions', $join)) {
                if (array_is_list($join['conditions'])) {
                    if (isset($join['conditions']) && !empty($join['conditions'])) {
                        foreach ($join['conditions'] as $condition) {
                            $condition['value'] = $this->_prepareQueryValue('where', $condition['value']);
                            $parameter = $this->_prepareParameters($condition['value']);
                            $this->parameters = $parameter;
                            $query .= " AND {$condition['column']} {$condition['operator']} " . array_keys($parameter);
                        }
                    }
                } else {
                    throw new Exception("join conditions must be array of list!");
                }
            }
        }

        return $query;
    }

    /**
     * Prepare parameter for mysql prepare statement function
     *
     * @param Mixed $value
     * @return array
     */
    private function _prepareParameters(Mixed $value, bool $bindType = true): array
    {
        /** Determine the data type of the value */
        switch (true) {
            case is_int($value):
                if ($this->wordpress) {
                    return ['%d' => $value];
                } else if ($bindType) {
                    return ['i' => $value];
                } else {
                    return ['?' => $value];
                }
                break;
            case is_bool($value):
                $value = $value ? 1 : 0;
                if ($this->wordpress) {
                    return ['%d' => $value];
                } else if ($bindType) {
                    return ['i' => $value];
                } else {
                    return ['?' => $value];
                }
                break;
            case is_float($value):
                if ($this->wordpress) {
                    return ['%f' => $value];
                } else if ($bindType) {
                    return ['d' => $value];
                } else {
                    return ['?' => $value];
                }
                break;
            case is_string($value):
            case $value === NULL:
                if ($this->wordpress) {
                    return ['%s' => $value];
                } else if ($bindType) {
                    return ['s' => $value];
                } else {
                    return ['?' => $value];
                }
                break;
            default:
                throw new Exception("Unsupported data type for parameter");
        }
    }

    /**
     * Setup query value function
     *
     * @param String $type
     * @param Mixed $value
     * @return mixed
     */
    private function _prepareQueryValue(String $type, Mixed $value): mixed
    {
        switch (strtolower($type)) {
            case 'where':
                if (is_array($value)) {
                    $value = implode(',', $value);
                } else if (is_string($value)) {
                    if (strpos($value, '(', 0) !== false && strpos($value, ')', -1) !== false) {
                        $strlen = strlen($value);
                        $value = substr($value, 1, $strlen);

                        $strlen = strlen($value);
                        $value = substr($value, 0, $strlen - 1);
                    }

                    if (strpos($value, '\'', 0) !== false && strpos($value, '\'', -1) !== false) {
                        $strlen = strlen($value);
                        $value = substr($value, 0, $strlen);

                        $strlen = strlen($value);
                        $value = substr($value, 0, $strlen - 1);
                    }
                } else if ($value == NULL) {
                    $value = 'NULL';
                }

                return $value;
                break;
            default:
                return $value;
        }
    }

    /**
     * Get parameter value for prepare query statement function
     *
     * @return array
     */
    private function _prepareQueryParameter(): array
    {
        $parameterValue = [];
        if (is_array($this->parameters)) {
            foreach ($this->parameters as $parameter) {
                $parameterValue = array_merge($parameterValue, array_values($parameter));
            }
        }

        if (is_array($this->whereParameters)) {
            foreach ($this->whereParameters as $parameter) {
                $parameterValue = array_merge($parameterValue, array_values($parameter));
            }
        }

        return $parameterValue;
    }

    /**
     * Prepare cast query function
     *
     * @param String $source
     * @param String $into
     * @param boolean $strict
     * @return string
     */
    private function _prepareCast(String $source, String $into, bool $strict = true): string
    {
        switch (strtolower($into)) {
            case 'date':
                return ($strict ? "CAST(`{$source}` AS DATE)" : "CAST({$source} AS DATE)");
                break;
            case 'datetime':
                return ($strict ? "CAST(`{$source}` AS DATETIME)" : "CAST({$source} AS DATETIME)");
                break;
            case 'time':
                return ($strict ? "CAST(`{$source}` AS TIME)" : "CAST({$source} AS TIME)");
                break;
            case 'decimal':
                return ($strict ? "CAST(`{$source}` AS DECIMAL)" : "CAST({$source} AS DECIMAL)");
                break;
            case 'char':
            case 'character':
                return ($strict ? "CAST(`{$source}` AS CHAR)" : "CAST({$source} AS CHAR)");
                break;
            case 'nchar':
            case 'ncharacter':
                return ($strict ? "CAST(`{$source}` AS NCHAR)" : "CAST({$source} AS NCHAR)");
                break;
            case 'signed':
            case 'signedinteger':
                return ($strict ? "CAST(`{$source}` AS SIGNED)" : "CAST({$source} AS SIGNED)");
                break;
            case 'unsigned':
            case 'unsignedinteger':
                return ($strict ? "CAST(`{$source}` AS UNSIGNED)" : "CAST({$source} AS UNSIGNED)");
                break;
            case 'binary':
                return ($strict ? "CAST(`{$source}` AS BINARY)" : "CAST({$source} AS BINARY)");
                break;
            default:
                return $source;
                break;
        }
    }

    /**
     * Reset property value function
     *
     * @return void
     */
    private function _reset()
    {
        foreach ($this as $key => $value) {
            if (!in_array($key, ['connection', 'host', 'username', 'password', 'database', 'wordpress', 'wpdb'])) {
                $this->$key = NULL;
            }
        }
    }
}