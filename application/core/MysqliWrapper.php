<?php
class MysqliWrapper
{
    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'prepare':
                $query = $arguments[0];
                $query = preg_replace('/:[a-z_]+/', '?', $query);
                $stmt = $this->mysqli->prepare($query);
                return new self($stmt);

            case 'execute':
                $stmt = $this->mysqli;
                if (isset($arguments[0]) && is_array($arguments[0])) {
                    $types = str_repeat('s', count($arguments[0]) + 1); // assuming all parameters are strings
                    $params = array_merge([$types], $arguments[0]);
                    $params_ref = [];
                    foreach ($params as $key => $value) {
                        $params_ref[$key] = &$params[$key];
                    }
                    array_unshift($params_ref, $types);
                    call_user_func_array([$stmt, 'bind_param'], array_values($params_ref));
                }
                $stmt->execute();
                return new self($stmt);

            case 'fetchAll':
                $stmt = $this->mysqli;
                $result = $stmt->get_result();
                $data = $result->fetch_all(MYSQLI_ASSOC);
                return array_map(function ($item) {
                    return (object) $item;
                }, $data);

            case 'fetch':
                $result = $this->mysqli->get_result();
                if ($result) {
                    return $result->fetch_object();
                } else {
                    return null;
                }


            default:
                $result = call_user_func_array([$this->mysqli, $name], $arguments);

                // If the result is a mysqli_stmt object, wrap it in a MysqliWrapper
                if ($result instanceof mysqli_stmt) {
                    return new self($result);
                }

                // If the result is a mysqli_result object, fetch all rows
                if ($result instanceof mysqli_result) {
                    return $result->fetch_all(MYSQLI_ASSOC);
                }

                return $result;
        }
    }

    public function __get($name)
    {
        return $this->mysqli->$name;
    }
}