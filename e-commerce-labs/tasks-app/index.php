<?php

require "db.php";

$result = $conn->query(
    "SELECT * FROM tasks ORDER BY created_at DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Task Manager</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<header class="header">

    <div class="container header-content">

        <div class="logo">
            Task<span>Manager</span>
        </div>

        <a href="create.php" class="btn btn-primary">
            + Add Task
        </a>

    </div>

</header>


<main class="main">

    <div class="container">

        <div class="page-header">

            <div>

                <h1>My Tasks</h1>

                <p>
                    Manage your tasks and keep track of your progress.
                </p>

            </div>

            <a href="create.php" class="btn btn-primary">
                + New Task
            </a>

        </div>


        <?php if ($result->num_rows > 0): ?>

            <div class="tasks-grid">

                <?php while ($row = $result->fetch_assoc()): ?>

                    <div class="task-card">

                        <h3>
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>

                        <p class="task-description">

                            <?php

                            if (!empty($row['description'])) {

                                echo htmlspecialchars($row['description']);

                            } else {

                                echo "No description provided.";

                            }

                            ?>

                        </p>


                        <span class="status status-<?php echo $row['status']; ?>">

                            <?php

                            echo str_replace(
                                "_",
                                " ",
                                htmlspecialchars($row['status'])
                            );

                            ?>

                        </span>


                        <div class="task-actions">

                            <a
                                href="edit.php?id=<?php echo $row['id']; ?>"
                                class="btn btn-edit"
                            >
                                Edit
                            </a>


                            <a
                                href="delete.php?id=<?php echo $row['id']; ?>"
                                class="btn btn-delete"
                                onclick="return confirm('Are you sure you want to delete this task?');"
                            >
                                Delete
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <h2>No tasks yet</h2>

                <p>
                    You haven't created any tasks. Start by adding your first task.
                </p>

                <a href="create.php" class="btn btn-primary">
                    + Create Your First Task
                </a>

            </div>

        <?php endif; ?>

    </div>

</main>


<footer class="footer">

    <div class="container">

        Task Manager &copy; <?php echo date("Y"); ?>

    </div>

</footer>


<?php

$conn->close();

?>

</body>

</html>