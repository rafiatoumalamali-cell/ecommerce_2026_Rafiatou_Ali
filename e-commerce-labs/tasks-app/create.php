<?php

require "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $status = $_POST["status"];

    $stmt = $conn->prepare(
        "INSERT INTO tasks (title, description, status)
         VALUES (?, ?, ?)"
    );

    $stmt->bind_param(
        "sss",
        $title,
        $description,
        $status
    );

    $stmt->execute();

    $stmt->close();

    $conn->close();

    header("Location: index.php");

    exit;
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

    <title>Add Task | Task Manager</title>

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

                <h1>Add New Task</h1>

                <p class="form-description">
                    Create a task and track its progress.
                </p>


                <form method="POST" action="create.php">


                    <div class="form-group">

                        <label for="title">
                            Task Title
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            placeholder="Enter task title"
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
                            placeholder="Describe your task..."
                        ></textarea>

                    </div>


                    <div class="form-group">

                        <label for="status">
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                        >

                            <option value="pending">
                                Pending
                            </option>

                            <option value="in_progress">
                                In Progress
                            </option>

                            <option value="done">
                                Done
                            </option>

                        </select>

                    </div>


                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Save Task
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