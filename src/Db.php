<?php

namespace AuthTest;

use Faker\Factory as FakeFactory;

class Db
{
    protected $db;
    protected $dbPath;
    protected $config;

    /**
     * Db constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $path = rtrim('/', (@$config['path'] ?: realpath(dirname(__DIR__).'/data')));
        $name = basename((@$config['name'] ?: 'users.db'));
        $this->dbPath = "{$path}/{$name}";
        $this->config = $config;

        if (!file_exists($this->dbPath)) {
            $this->initialise();
        } else {
            $this->connect();
        }
    }

    /**
     * @return \SQLite3
     */
    public function __invoke()
    {
        return $this->db;
    }

    /**
     * @return $this
     */
    protected function connect()
    {
        if (empty($this->db)) {
            $this->db = new \SQLite3($this->dbPath);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function initialise()
    {
        $this->connect();
        $this->db->exec('
            CREATE TABLE users
            (
                id INTEGER PRIMARY KEY,
                email TEXT NOT NULL,
                password TEXT NOT NULL,
                firstname TEXT NOT NULL,
                surname TEXT NOT NULL,
                added TEXT NOT NULL,
                updated TEXT,
                lastlogin TEXT
            )
        ');
        $this->db->exec('CREATE INDEX email_tindex ON users (email)');
        $this->seed();

        return $this;
    }

    /**
     * @return $this
     */
    protected function seed()
    {
        // yes, these will all be the same, but it makes testing the login easier!
        $password = password_hash('password', 'SH256');
        $f = new FakeFactory();

        $this->db->exec('DELETE from users');
        for ($i = 0; $i < ($this->config['seed_length'] ?: 10); $i++) {
            $this->db->exec(sprintf("
                INSERT INTO users (`email`, `password`, `firstname`, `surname`, `added`) 
                VALUES ('%s', '%s', '%s', '%s', '%s')",
                $this->db->escapeString($f->safeEmail()),
                $this->db->escapeString($password),
                $this->db->escapeString($f->firstName()),
                $this->db->escapeString($f->lastName()),
                $this->db->escapeString($f->iso8601())
            ));
        }

        return $this;
    }
}