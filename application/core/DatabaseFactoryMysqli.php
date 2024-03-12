<?php

class DatabaseFactoryMysqli
{
    private static $factory;
    private $database;

    public static function getFactory()
    {
        if (!self::$factory) {
            self::$factory = new DatabaseFactoryMysqli();
        }
        return self::$factory;
    }

    public function getConnection()
    {
        if (!$this->database) {
            try {
                $this->database = new MysqliWrapper(
                    new mysqli(
                        Config::get('DB_HOST'),
                        Config::get('DB_USER'),
                        Config::get('DB_PASS'),
                        Config::get('DB_NAME'),
                        Config::get('DB_PORT')
                    )
                );

                if ($this->database->connect_error) {
                    throw new Exception('Database connection can not be established. Please try again later.');
                }

                $this->database->set_charset(Config::get('DB_CHARSET'));

            } catch (Exception $e) {
                echo $e->getMessage();
                echo 'Error code: ' . $e->getCode();
                exit;
            }
        }
        return $this->database;
    }
}
