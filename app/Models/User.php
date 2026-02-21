<?php

declare(strict_types=1);

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT id, username, email, password_hash, goal_preference FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT id, username, email, goal_preference FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function create(string $username, string $email, string $passwordHash): ?int
    {
        $statement = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)'
        );

        $created = $statement->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        if (!$created) {
            return null;
        }

        return (int)$this->db->lastInsertId();
    }

    public function updateGoalPreference(int $userId, string $goal): bool
    {
        $statement = $this->db->prepare('UPDATE users SET goal_preference = :goal WHERE id = :id');

        return $statement->execute([
            'goal' => $goal,
            'id' => $userId,
        ]);
    }
}
