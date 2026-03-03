<?php

declare(strict_types=1);

class User
{
    private PDO $db;
    private bool $profileTableRepairAttempted = false;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->ensureProfileTable();
        $this->migrateLegacyProfileData();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare(
            'SELECT
                u.id,
                u.username,
                u.email,
                u.password_hash,
                up.goal_preference,
                up.height_cm,
                up.weight_kg,
                up.bmi,
                up.age
             FROM users u
             LEFT JOIN user_profiles up ON up.user_id = u.id
             WHERE u.email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT
                u.id,
                u.username,
                u.email,
                up.goal_preference,
                up.height_cm,
                up.weight_kg,
                up.bmi,
                up.age
             FROM users u
             LEFT JOIN user_profiles up ON up.user_id = u.id
             WHERE u.id = :id
             LIMIT 1'
        );
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

        $createdUserId = (int)$this->db->lastInsertId();
        $this->ensureProfileRow($createdUserId);

        return $createdUserId;
    }

    public function updateGoalPreference(int $userId, string $goal): bool
    {
        $this->ensureProfileRow($userId);
        $statement = $this->db->prepare('UPDATE user_profiles SET goal_preference = :goal WHERE user_id = :id');

        return $statement->execute([
            'goal' => $goal,
            'id' => $userId,
        ]);
    }

    public function emailExistsForOther(string $email, int $userId): bool
    {
        $statement = $this->db->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
        $statement->execute([
            'email' => $email,
            'id' => $userId,
        ]);

        return $statement->fetch() !== false;
    }

    public function updateAccount(int $userId, string $username, string $email, ?string $passwordHash = null): bool
    {
        if ($passwordHash !== null) {
            $statement = $this->db->prepare(
                'UPDATE users SET username = :username, email = :email, password_hash = :password_hash WHERE id = :id'
            );

            return $statement->execute([
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash,
                'id' => $userId,
            ]);
        }

        $statement = $this->db->prepare('UPDATE users SET username = :username, email = :email WHERE id = :id');

        return $statement->execute([
            'username' => $username,
            'email' => $email,
            'id' => $userId,
        ]);
    }

    public function updateProfileData(int $userId, ?float $heightCm, ?float $weightKg, ?float $bmi, ?int $age, string $goal): bool
    {
        $this->ensureProfileRow($userId);
        $statement = $this->db->prepare(
            'UPDATE user_profiles
             SET height_cm = :height_cm,
                 weight_kg = :weight_kg,
                 bmi = :bmi,
                 age = :age,
                 goal_preference = :goal
             WHERE user_id = :id'
        );

        return $statement->execute([
            'height_cm' => $heightCm,
            'weight_kg' => $weightKg,
            'bmi' => $bmi,
            'age' => $age,
            'goal' => $goal,
            'id' => $userId,
        ]);
    }

    public function deleteById(int $userId): bool
    {
        $statement = $this->db->prepare('DELETE FROM users WHERE id = :id');

        return $statement->execute(['id' => $userId]);
    }

    private function ensureProfileTable(): void
    {
        try {
            $this->createOrUpdateProfileTable();
        } catch (PDOException $exception) {
            if (!$this->shouldRebuildProfileTable($exception) || $this->profileTableRepairAttempted) {
                throw $exception;
            }

            $this->profileTableRepairAttempted = true;
            $this->rebuildProfileTable();

            try {
                $this->createOrUpdateProfileTable();
            } catch (PDOException $retryException) {
                if (!$this->shouldRebuildProfileTable($retryException)) {
                    throw $retryException;
                }

                $cleanupAttempted = $this->cleanupOrphanProfileTablespaceFiles();

                if (!$cleanupAttempted) {
                    throw $retryException;
                }

                $this->createOrUpdateProfileTable();
            }
        }
    }

    private function createOrUpdateProfileTable(): void
    {
        $userIdType = $this->resolveUsersIdColumnType();

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS user_profiles (
                user_id ' . $userIdType . ' NOT NULL,
                age INT NULL,
                goal_preference VARCHAR(20) NULL,
                height_cm DECIMAL(5,2) NULL,
                weight_kg DECIMAL(5,2) NULL,
                bmi DECIMAL(5,2) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        if (!$this->profileForeignKeyExists('fk_user_profiles_user')) {
            try {
                $this->db->exec(
                    'ALTER TABLE user_profiles
                     ADD CONSTRAINT fk_user_profiles_user
                     FOREIGN KEY (user_id) REFERENCES users(id)
                     ON DELETE CASCADE'
                );
            } catch (PDOException $exception) {
            }
        }

        if (!$this->profileColumnExists('age')) {
            $this->db->exec('ALTER TABLE user_profiles ADD COLUMN age INT NULL AFTER user_id');
        }

        if (!$this->profileColumnExists('goal_preference')) {
            $this->db->exec('ALTER TABLE user_profiles ADD COLUMN goal_preference VARCHAR(20) NULL AFTER age');
        }

        if (!$this->profileColumnExists('height_cm')) {
            $this->db->exec('ALTER TABLE user_profiles ADD COLUMN height_cm DECIMAL(5,2) NULL AFTER goal_preference');
        }

        if (!$this->profileColumnExists('weight_kg')) {
            $this->db->exec('ALTER TABLE user_profiles ADD COLUMN weight_kg DECIMAL(5,2) NULL AFTER height_cm');
        }

        if (!$this->profileColumnExists('bmi')) {
            $this->db->exec('ALTER TABLE user_profiles ADD COLUMN bmi DECIMAL(5,2) NULL AFTER weight_kg');
        }

        $this->db->exec(
            'UPDATE user_profiles
             SET bmi = ROUND(weight_kg / POW(height_cm / 100, 2), 2)
             WHERE height_cm IS NOT NULL
             AND weight_kg IS NOT NULL
             AND height_cm > 0
             AND (bmi IS NULL OR bmi = 0)'
        );

        $this->db->exec(
            'INSERT INTO user_profiles (user_id)
             SELECT u.id
             FROM users u
             LEFT JOIN user_profiles up ON up.user_id = u.id
             WHERE up.user_id IS NULL'
        );
    }

    private function shouldRebuildProfileTable(PDOException $exception): bool
    {
        $errorCode = $exception->errorInfo[1] ?? null;

        if ((int)$errorCode === 1932 || (int)$errorCode === 1813) {
            return true;
        }

        $message = strtolower($exception->getMessage());
        $isMissingInEngine = str_contains($message, 'user_profiles') && str_contains($message, "doesn't exist in engine");
        $isTablespaceConflict = str_contains($message, 'user_profiles') && str_contains($message, 'tablespace for table') && str_contains($message, 'exists');

        return $isMissingInEngine || $isTablespaceConflict;
    }

    private function rebuildProfileTable(): void
    {
        try {
            $this->db->exec('SET FOREIGN_KEY_CHECKS=0');
            $this->db->exec('DROP TABLE IF EXISTS user_profiles');
        } finally {
            $this->db->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function cleanupOrphanProfileTablespaceFiles(): bool
    {
        $statement = $this->db->query('SELECT @@datadir AS datadir, DATABASE() AS database_name');
        $result = $statement->fetch();

        $datadir = is_array($result) ? (string)($result['datadir'] ?? '') : '';
        $databaseName = is_array($result) ? (string)($result['database_name'] ?? '') : '';

        if ($datadir === '' || $databaseName === '') {
            return false;
        }

        $basePath = rtrim($datadir, "\\/") . DIRECTORY_SEPARATOR . $databaseName . DIRECTORY_SEPARATOR;
        $candidateFiles = [
            $basePath . 'user_profiles.ibd',
            $basePath . 'user_profiles.cfg',
            $basePath . 'user_profiles.frm',
        ];

        $removedAnyFile = false;

        foreach ($candidateFiles as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }

            if (@unlink($filePath)) {
                $removedAnyFile = true;
                continue;
            }

            if (!file_exists($filePath)) {
                $removedAnyFile = true;
            }
        }

        return $removedAnyFile;
    }

    private function resolveUsersIdColumnType(): string
    {
        $statement = $this->db->prepare(
            'SELECT COLUMN_TYPE
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = :table_name
             AND COLUMN_NAME = :column_name
             LIMIT 1'
        );
        $statement->execute([
            'table_name' => 'users',
            'column_name' => 'id',
        ]);
        $column = $statement->fetch();

        if (!is_array($column) || !isset($column['COLUMN_TYPE'])) {
            return 'INT';
        }

        return strtoupper((string)$column['COLUMN_TYPE']);
    }

    private function migrateLegacyProfileData(): void
    {
        if (!$this->usersColumnExists('goal_preference') && !$this->usersColumnExists('height_cm') && !$this->usersColumnExists('weight_kg') && !$this->usersColumnExists('bmi')) {
            return;
        }

        $selectGoal = $this->usersColumnExists('goal_preference') ? 'u.goal_preference' : 'NULL';
        $selectHeight = $this->usersColumnExists('height_cm') ? 'u.height_cm' : 'NULL';
        $selectWeight = $this->usersColumnExists('weight_kg') ? 'u.weight_kg' : 'NULL';
        $selectBmi = $this->usersColumnExists('bmi') ? 'u.bmi' : 'NULL';

        $this->db->exec(
            'UPDATE user_profiles up
             JOIN users u ON u.id = up.user_id
             SET up.goal_preference = COALESCE(up.goal_preference, ' . $selectGoal . '),
                 up.height_cm = COALESCE(up.height_cm, ' . $selectHeight . '),
                 up.weight_kg = COALESCE(up.weight_kg, ' . $selectWeight . '),
                 up.bmi = COALESCE(up.bmi, ' . $selectBmi . ')'
        );
    }

    private function ensureProfileRow(int $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO user_profiles (user_id)
             VALUES (:user_id)
             ON DUPLICATE KEY UPDATE user_id = user_id'
        );
        $statement->execute(['user_id' => $userId]);
    }

    private function usersColumnExists(string $columnName): bool
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) AS count
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = :table_name
             AND COLUMN_NAME = :column_name'
        );

        $statement->execute([
            'table_name' => 'users',
            'column_name' => $columnName,
        ]);

        $result = $statement->fetch();
        return (int)($result['count'] ?? 0) > 0;
    }

    private function profileColumnExists(string $columnName): bool
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) AS count
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = :table_name
             AND COLUMN_NAME = :column_name'
        );

        $statement->execute([
            'table_name' => 'user_profiles',
            'column_name' => $columnName,
        ]);

        $result = $statement->fetch();
        return (int)($result['count'] ?? 0) > 0;
    }

    private function profileForeignKeyExists(string $constraintName): bool
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) AS count
             FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
             AND CONSTRAINT_NAME = :constraint_name'
        );

        $statement->execute([
            'constraint_name' => $constraintName,
        ]);

        $result = $statement->fetch();
        return (int)($result['count'] ?? 0) > 0;
    }
}
