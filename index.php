<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/classes/Student.php";
require_once __DIR__ . "/core/Response.php";

header("Content-Type: application/json; charset=UTF-8");

// Connect to database
try {
    $database = new Database();
    $pdo = $database->connect();
} catch (Throwable $e) {
    Response::json(500, [
        "success" => false,
        "message" => "error: " . $e->getMessage()
    ]);
}

$studentModel = new Student($pdo);

// HTTP method
$method = $_SERVER["REQUEST_METHOD"];

// Read and validate student ID
$id = null;

if (isset($_GET["id"])) {
    $id = filter_var(
        $_GET["id"],
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if ($id === false) {
        Response::json(400, [
            "success" => false,
            "message" => "Invalid student ID"
        ]);
    }
}

// Read JSON request body
function getJsonBody(): array
{
    $rawBody = file_get_contents("php://input");

    try {
        $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        Response::json(400, [
            "success" => false,
            "message" => "Invalid JSON body"
        ]);
    }

    if (!is_array($data)) {
        Response::json(400, [
            "success" => false,
            "message" => "JSON body must be a JSON object"
        ]);
    }

    return $data;
}

// Validate name, email, course (password is validated separately)
function validateStudentFields(array $data): array
{
    $name   = $data["name"]   ?? "";
    $email  = $data["email"]  ?? "";
    $course = $data["course"] ?? "";

    if (!is_string($name) || !is_string($email) || !is_string($course)) {
        Response::json(422, [
            "success" => false,
            "message" => "Name, email and course must be strings"
        ]);
    }

    $name   = trim($name);
    $email  = trim($email);
    $course = trim($course);

    if ($name === "" || $email === "" || $course === "") {
        Response::json(422, [
            "success" => false,
            "message" => "Name, email and course are required"
        ]);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Response::json(422, [
            "success" => false,
            "message" => "Invalid email address"
        ]);
    }

    return [
        "name"   => $name,
        "email"  => $email,
        "course" => $course
    ];
}

// Validate password
function validatePassword($password): string
{
    if (!is_string($password) || strlen($password) < 8) {
        Response::json(422, [
            "success" => false,
            "message" => "Password must be at least 8 characters"
        ]);
    }

    return $password;
}

try {

    switch ($method) {

        // GET: retrieve students
        case "GET":

            if ($id !== null) {
                $student = $studentModel->getById($id);

                if ($student === null) {
                    Response::json(404, [
                        "success" => false,
                        "message" => "Student not found"
                    ]);
                }

                Response::json(200, [
                    "success" => true,
                    "data" => $student
                ]);
            }

            $students = $studentModel->getAll();

            Response::json(200, [
                "success" => true,
                "count" => count($students),
                "data" => $students
            ]);

        // POST: create student
        case "POST":

            $body = getJsonBody();
            $data = validateStudentFields($body);

            if (!array_key_exists("password", $body)) {
                Response::json(422, [
                    "success" => false,
                    "message" => "Password is required"
                ]);
            }

            $password = validatePassword($body["password"]);

            $newId = $studentModel->create(
                $data["name"],
                $data["email"],
                $data["course"],
                $password
            );

            Response::json(201, [
                "success" => true,
                "message" => "Student created successfully",
                "data" => [
                    "id" => $newId,
                    "name" => $data["name"],
                    "email" => $data["email"],
                    "course" => $data["course"]
                ]
            ]);

        // PUT: update student
        case "PUT":

            if ($id === null) {
                Response::json(400, [
                    "success" => false,
                    "message" => "Student ID is required"
                ]);
            }

            $body = getJsonBody();
            $data = validateStudentFields($body);

            $existingStudent = $studentModel->getById($id);

            if ($existingStudent === null) {
                Response::json(404, [
                    "success" => false,
                    "message" => "Student not found"
                ]);
            }

            // Password is optional during update
            $password = null;

            if (array_key_exists("password", $body)) {
                $password = validatePassword($body["password"]);
            }

            $studentModel->update(
                $id,
                $data["name"],
                $data["email"],
                $data["course"],
                $password
            );

            Response::json(200, [
                "success" => true,
                "message" => "Student updated successfully",
                "data" => [
                    "id" => $id,
                    "name" => $data["name"],
                    "email" => $data["email"],
                    "course" => $data["course"],
                    "created_at" => $existingStudent["created_at"] ?? null
                ]
            ]);

        // DELETE: delete student
        case "DELETE":

            if ($id === null) {
                Response::json(400, [
                    "success" => false,
                    "message" => "Student ID is required"
                ]);
            }

            if ($studentModel->getById($id) === null) {
                Response::json(404, [
                    "success" => false,
                    "message" => "Student not found"
                ]);
            }

            $studentModel->delete($id);

            Response::json(200, [
                "success" => true,
                "message" => "Student deleted successfully"
            ]);

        default:

            header("Allow: GET, POST, PUT, DELETE");

            Response::json(405, [
                "success" => false,
                "message" => "Method not allowed"
            ]);
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    if ($e->getCode() === "23000") {
        Response::json(409, [
            "success" => false,
            "message" => "Email already exists or violates a database constraint"
        ]);
    }

    Response::json(500, [
        "success" => false,
        "message" => "A database error occurred"
    ]);

} catch (Throwable $e) {

    error_log($e->getMessage());

    Response::json(500, [
        "success" => false,
        "message" => "An internal server error occurred"
    ]);
}
