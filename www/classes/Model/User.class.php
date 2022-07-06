<?php

namespace Model;

use Util\Singleton\Database;
use Util\Singleton\ErrorHandler;

/**
 * User model.
 */
class User
{
    /** @var string */
    protected const ROLE_1 = 'Schrijver';

    /** @var string */
    protected const ROLE_2 = 'Nakijker';

    /** @var string */
    protected const ROLE_3 = 'Beheerder';

    /** @var int */
    public int $id;

    /** @var string */
    public string $username;

    /** @var int */
    public int $perm_level;

    /** @var bool */
    public bool $active;

    /**
     * @param int $id
     * @param string $username
     * @param int $perm_level
     * @param bool $active
     */
    public function __construct(int $id, string $username, int $perm_level, bool $active)
    {
        $this->id = $id;
        $this->username = $username;
        $this->perm_level = $perm_level;
        $this->active = $active;
    }

    /**
     * @param int $id
     * @return User|null
     */
    public static function getById(int $id): ?User
    {
        Database::instance()->storeQuery("SELECT * FROM users WHERE id = ?");
        $stmt = Database::instance()->prepareStoredQuery();
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $user_data = $stmt->get_result()->fetch_assoc();
        if ($user_data) {
            return new User($user_data['id'], $user_data['username'], $user_data['perm_level'], $user_data['active']);
        }
        return null;
    }

    /**
     * @return User[]
     */
    protected static function getAllByQuery(string $query): array
    {
        Database::instance()->storeQuery($query);
        $stmt = Database::instance()->prepareStoredQuery();
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while (($user_data = $result->fetch_assoc())) {
            $users[$user_data['id']] = new User($user_data['id'], $user_data['username'], $user_data['perm_level'], $user_data['active']);
        }
        return $users;
    }

    /**
     * @return User[]
     */
    public static function getAll(): array
    {
        return User::getAllByQuery("SELECT * FROM users");
    }

    /**
     * @return User[]
     */
    public static function getAllActive(): array
    {
        return User::getAllByQuery("SELECT * FROM users WHERE active = 1");
    }

    /**
     * @param string $name
     * @param int $perm_level
     * @return User|null
     */
    public static function createNew(string $name, int $perm_level): ?User
    {
        Database::instance()->storeQuery("INSERT INTO `users` (username, perm_level) VALUES (?, ?)");
        $stmt = Database::instance()->prepareStoredQuery();
        $stmt->bind_param('si', $name, $perm_level);
        $stmt->execute();
        if ($stmt->insert_id) {
            return User::getById($stmt->insert_id);
        }
        return null;
    }

    /**
     * @param string $name
     * @param int $perm_level
     * @param bool $active
     * @return User|null
     */
    public function update(string $name, int $perm_level, bool $active): ?User
    {
        if ($this->id === 1) {
            if ($active === false) {
                ErrorHandler::instance()->addError(sprintf('Kan gebruiker \'%s\' niet deactiveren.', $this->username));
                return null;
            }
            if ($perm_level < 3) {
                ErrorHandler::instance()->addError(sprintf('Kan gebruiker \'%s\' geen lagere rol geven.', $this->username));
                return null;
            }
        }
        Database::instance()->storeQuery("UPDATE `users` SET username = ?, perm_level = ?, active = ? WHERE id = ?");
        $stmt = Database::instance()->prepareStoredQuery();
        $stmt->bind_param('siii', $name, $perm_level, $active, $this->id);
        $stmt->execute();
        return User::getById($this->id);
    }

    /**
     * @return string
     */
    public function getPermLevelName(): string
    {
        switch ($this->perm_level) {
            default:
            case 1:
                return self::ROLE_1;

            case 2:
                return self::ROLE_2;

            case 3:
                return self::ROLE_3;
        }
    }
}
