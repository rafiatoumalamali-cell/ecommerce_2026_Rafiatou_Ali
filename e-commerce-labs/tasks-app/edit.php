<?php

require "db.php";

$id = intval($_GET["id"] ?? 0);


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $status = $_POST["status"];
    $post_id = intval($_POST["id"]);


    $stmt = $conn->prepare(
        "UPDATE tasks
         SET title=?, description=?, status=?
         WHERE id=?"
    );


    $stmt->bind_param(
        "sssi",
        $title,
        $description,
        $status,
        $post_id
    );


    $stmt->execute();

    $stmt->close();

    $conn->close();

    header("Location: index.php");

    exit;
}


$stmt = $conn->prepare(
    "SELECT * FROM tasks WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$task = $stmt->get_result()->fetch_assoc();

$stmt->close();


if (!$task) {

    die("Task not found.");

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Task | Task Manager</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<header class="header">

    <div class="container header-content">

        <div class="logo">
            Task<span>Manager</span>
        </div>

        <a href="index.php" class="btn btn-secondary">
            Back to Tasks
        </a>

    </div>

</header>


<main class="main">

    <div class="container">

        <div class="form-container">

            <div class="form-card">

                <h1>Edit Task</h1>

                <p class="form-description">
                    Update the task information and status.
                </p>


                <form method="POST" action="edit.php">


                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo $task['id']; ?>"
                    >


                    <div class="form-group">

                        <label for="title">
                            Task Title
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="<?php echo htmlspecialchars($task['title']); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                        ><?php echo htmlspecialchars($task['description']); ?></textarea>

                    </div>


                    <div class="form-group">

                        <label for="status">
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                        >

                            <option
                                value="pending"
                                <?php
                                echo $task['status'] === 'pending'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                Pending
                            </option>


                            <option
                                value="in_progress"
                                <?php
                                echo $task['status'] === 'in_progress'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                In Progress
                            </option>


                            <option
                                value="done"
                                <?php
                                echo $task['status'] === 'done'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                Done
                            </option>

                        </select>

                    </div>


                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Update Task
                        </button>


                        <a
                            href="index.php"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>

                    </div>


                </form>

            </div>

        </div>

    </div>

</main>


<footer class="footer">

    <div class="container">

        Task Manager &copy; <?php echo date("Y"); ?>

    </div>

</footer>


</body>

</html>