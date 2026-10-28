<?php

class Student
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Get all students (never return the password)
    public function getAll(): array
    {
        $sql = "SELECT id, name, email, course, created_at
                FROM student
                ORDER BY id DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get one student by id
    public function getById(int $id): ?array
    {
        $sql = "SELECT id, name, email, course, created_at
                FROM student
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        return $student ?: null;
    }

    // Create student, returns the new id
    public function create(
        string $name,
        string $email,
        string $course,
        string $password
    ): int {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // The column in the table is called "pass"
        $sql = "INSERT INTO student (name, email, course, pass)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $email, $course, $hashedPassword]);

        return (int) $this->db->lastInsertId();
    }

    // Update student (password is optional)
    public function update(
        int $id,
        string $name,
        string $email,
        string $course,
        ?string $password = null
    ): bool {
        if ($password !== null && $password !== "") {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "UPDATE student
                    SET name = ?, email = ?, course = ?, pass = ?
                    WHERE id = ?";

            $params = [$name, $email, $course, $hashedPassword, $id];
        } else {
            $sql = "UPDATE student
                    SET name = ?, email = ?, course = ?
                    WHERE id = ?";

            $params = [$name, $email, $course, $id];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    // Verify password
    public function verifyPassword(int $id, string $password): bool
    {
        $sql = "SELECT pass FROM student WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return false;
        }

        return password_verify($password, $student["pass"]);
    }

    // Delete student
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM student WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}
