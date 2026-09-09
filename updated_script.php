Refactoring `mysql_*` functions to `mysqli_*` is a critical security and maintenance upgrade, as `mysql_*` functions are deprecated and removed in modern PHP versions.

`mysqli` (MySQL Improved Extension) offers two interfaces:

1.  **Procedural:** Similar to `mysql_*` functions, using `mysqli_` prefixes.
2.  **Object-Oriented (OO):** Using `new mysqli()` and calling methods on the connection object. This is generally recommended for its clarity and consistency.

This guide will focus primarily on the **Object-Oriented (OO) interface** as it's the modern and preferred approach, but will also touch upon the procedural where relevant.

---

**Before You Start (Crucial Steps!):**

1.  **BACKUP ALL YOUR FILES AND DATABASE!** This is a significant change, and you want to be able to revert if something goes wrong.
2.  **Understand Your Code:** Go through your existing PHP files and identify all instances of `mysql_*` functions.
3.  **Error Reporting:** Enable verbose error reporting in your development environment (`error_reporting(E_ALL); ini_set('display_errors', 1);`).
4.  **Character Set:** Explicitly set the character set for your connection (e.g., `utf8mb4`). This prevents encoding issues.

---

## Step-by-Step Refactoring Guide

Here's how to refactor common `mysql_*` operations to `mysqli_*`:

### 1. Database Connection

**Old `mysql_*`:**

```php
<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "mydatabase";

$conn = mysql_connect($db_host, $db_user, $db_pass);
if (!$conn) {
    die("Database connection failed: " . mysql_error());
}
mysql_select_db($db_name, $conn);
mysql_set_charset("utf8", $conn); // If you used this
?>
```

**New `mysqli` (Object-Oriented Recommended):**

```php
<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "mydatabase";
$db_port = 3306; // Default MySQL port, specify if different

// Enable error reporting for mysqli (recommended for development)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    $mysqli->set_charset("utf8mb4"); // Always set character set!
} catch (mysqli_sql_exception $e) {
    // Log the error for debugging, but show a user-friendly message
    error_log("Failed to connect to MySQL: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// $mysqli is now your connection object
?>
```

**New `mysqli` (Procedural):**

```php
<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "mydatabase";
$db_port = 3306;

$mysqli = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (mysqli_connect_errno()) {
    error_log("Failed to connect to MySQL: " . mysqli_connect_error());
    die("Database connection failed. Please try again later.");
}

mysqli_set_charset($mysqli, "utf8mb4");
?>
```

### 2. Simple SELECT Queries (No User Input)

**Old `mysql_*`:**

```php
<?php
$sql = "SELECT id, name FROM users";
$result = mysql_query($sql, $conn);

if (!$result) {
    die("Query failed: " . mysql_error($conn));
}

while ($row = mysql_fetch_assoc($result)) {
    echo "ID: " . $row['id'] . ", Name: " . $row['name'] . "<br>";
}
echo "Total users: " . mysql_num_rows($result);
mysql_free_result($result);
?>
```

**New `mysqli` (Object-Oriented):**

```php
<?php
$sql = "SELECT id, name FROM users";
$result = $mysqli->query($sql); // No need for connection object in query()

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Name: " . $row['name'] . "<br>";
    }
    echo "Total users: " . $result->num_rows;
    $result->free(); // Free result set
} else {
    // This block might not be reached if MYSQLI_REPORT_STRICT is enabled,
    // as it would throw an exception directly.
    error_log("Query failed: " . $mysqli->error);
    die("Error retrieving data.");
}
?>
```

**Key changes:**
*   `mysql_query($sql, $conn)` becomes `$mysqli->query($sql)`
*   `mysql_fetch_assoc($result)` becomes `$result->fetch_assoc()`
*   `mysql_num_rows($result)` becomes `$result->num_rows`
*   `mysql_free_result($result)` becomes `$result->free()`

### 3. INSERT, UPDATE, DELETE Queries (No User Input)

**Old `mysql_*`:**

```php
<?php
$sql = "INSERT INTO products (name, price) VALUES ('Book', 25.99)";
$result = mysql_query($sql, $conn);

if (!$result) {
    die("Insert failed: " . mysql_error($conn));
} else {
    echo "New record created successfully. ID: " . mysql_insert_id($conn);
}

$sql_update = "UPDATE products SET price = 27.50 WHERE id = 1";
$result_update = mysql_query($sql_update, $conn);
if (!$result_update) {
    die("Update failed: " . mysql_error($conn));
} else {
    echo "Records updated: " . mysql_affected_rows($conn);
}
?>
```

**New `mysqli` (Object-Oriented):**

```php
<?php
$sql = "INSERT INTO products (name, price) VALUES ('Book', 25.99)";
$mysqli->query($sql); // Query execution

if ($mysqli->affected_rows > 0) { // Check affected rows for success
    echo "New record created successfully. ID: " . $mysqli->insert_id;
} else {
    // If no exception, but no rows affected, could mean duplicate key or other issue
    error_log("Insert failed or no rows affected: " . $mysqli->error);
    die("Error creating record.");
}

$sql_update = "UPDATE products SET price = 27.50 WHERE id = 1";
$mysqli->query($sql_update);

if ($mysqli->affected_rows > 0) {
    echo "Records updated: " . $mysqli->affected_rows;
} else {
    // If no exception, but no rows affected, could mean no record found or same data
    error_log("Update failed or no rows affected: " . $mysqli->error);
    die("Error updating record.");
}
?>
```

**Key changes:**
*   `mysql_insert_id($conn)` becomes `$mysqli->insert_id`
*   `mysql_affected_rows($conn)` becomes `$mysqli->affected_rows`
*   Error checking now often relies on `try/catch` with `MYSQLI_REPORT_STRICT` or checking `$mysqli->affected_rows`.

### 4. **CRITICAL: Queries with User Input (Prepared Statements!)**

This is the most important security improvement. **NEVER use `mysqli_real_escape_string()` for user input if you can use prepared statements.** Prepared statements completely prevent SQL injection.

**Old `mysql_*` (Vulnerable!):**

```php
<?php
$username = $_POST['username'];
$password = $_POST['password']; // Storing plaintext passwords is also bad!

// DANGEROUS: SQL Injection possible if $username or $password are not properly escaped
$sql = "SELECT id FROM users WHERE username = '" . mysql_real_escape_string($username) . "' AND password = '" . mysql_real_escape_string($password) . "'";
$result = mysql_query($sql, $conn);

if (!$result) {
    die("Query failed: " . mysql_error($conn));
}

if (mysql_num_rows($result) > 0) {
    echo "Login successful!";
} else {
    echo "Invalid credentials.";
}
?>
```

**New `mysqli` (Object-Oriented with Prepared Statements - SECURE!):**

```php
<?php
$username = $_POST['username'];
$password = $_POST['password']; // Assume password is hashed for real apps!

// 1. Prepare the statement
$stmt = $mysqli->prepare("SELECT id, username FROM users WHERE username = ? AND password = ?"); // ? are placeholders

if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("Database error during preparation.");
}

// 2. Bind parameters
// 'ss' means two string parameters (s=string, i=integer, d=double, b=blob)
$stmt->bind_param("ss", $username, $password);

// 3. Execute the statement
$stmt->execute();

// 4. Get results (for SELECT queries)
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "Login successful! Welcome, " . htmlspecialchars($user['username']) . "!";
} else {
    echo "Invalid credentials.";
}

// 5. Close the statement (important!)
$stmt->close();
?>
```

---

#### Prepared Statements for INSERT/UPDATE

**Old `mysql_*` (Vulnerable!):**

```php
<?php
$product_name = $_POST['name'];
$product_price = $_POST['price'];

$sql = "INSERT INTO products (name, price) VALUES ('" . mysql_real_escape_string($product_name) . "', " . (float)$product_price . ")";
$result = mysql_query($sql, $conn);
if (!$result) {
    die("Insert failed: " . mysql_error($conn));
}
echo "Product added. ID: " . mysql_insert_id($conn);
?>
```

**New `mysqli` (Object-Oriented with Prepared Statements - SECURE!):**

```php
<?php
$product_name = $_POST['name'];
$product_price = $_POST['price'];

// 1. Prepare
$stmt = $mysqli->prepare("INSERT INTO products (name, price) VALUES (?, ?)");

if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("Database error during preparation.");
}

// 2. Bind parameters ('sd' means string, double)
$stmt->bind_param("sd", $product_name, $product_price);

// 3. Execute
$stmt->execute();

// 4. Check for success (no result set for INSERT/UPDATE)
if ($stmt->affected_rows > 0) {
    echo "Product added. ID: " . $stmt->insert_id;
} else {
    error_log("Product insert failed: " . $stmt->error);
    die("Error adding product.");
}

// 5. Close
$stmt->close();
?>
```

### 5. Closing the Database Connection

**Old `mysql_*`:**

```php
<?php
mysql_close($conn);
?>
```

**New `mysqli` (Object-Oriented):**

```php
<?php
$mysqli->close();
?>
```

### 6. Common `mysql_*` to `mysqli` Mappings

| `mysql_*` Function             | `mysqli` OO Method / Property  | `mysqli` Procedural Function    | Notes                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              |
| :----------------------------- | :----------------------------- | :------------------------------ | :----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `mysql_connect()`              | `new mysqli(...)`              | `mysqli_connect(...)`           | Returns connection object/resource.                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| `mysql_select_db()`            | `$mysqli->select_db()`         | `mysqli_select_db()`            | Usually handled in `mysqli_connect()` or `new mysqli()`.                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| `mysql_query()`                | `$mysqli->query()`             | `mysqli_query()`                | For simple queries. For queries with user input, **use prepared statements** (`$mysqli->prepare()`).                                                                                                                                                                                                                                                                                                                                                                                       |
| `mysql_error()`                | `$mysqli->error`               | `mysqli_error($mysqli)`         | Returns the error string.                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| `mysql_errno()`                | `$mysqli->errno`               | `mysqli_errno($mysqli)`         | Returns the error number.                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| `mysql_fetch_array()`          | `$result->fetch_array()`       | `mysqli_fetch_array($result)`   | Fetches a row as an array (numeric and associative).                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| `mysql_fetch_assoc()`          | `$result->fetch_assoc()`       | `mysqli_fetch_assoc($result)`   | Fetches a row as an associative array.                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| `mysql_fetch_row()`            | `$result->fetch_row()`         | `mysqli_fetch_row($result)`     | Fetches a row as a numeric array.                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| `mysql_num_rows()`             | `$result->num_rows`            | `mysqli_num_rows($result)`      | Number of rows in a result set.                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| `mysql_insert_id()`            | `$mysqli->insert_id`           | `mysqli_insert_id($mysqli)`     | ID generated by the last INSERT query.                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| `mysql_affected_rows()`        | `$mysqli->affected_rows`       | `mysqli_affected_rows($mysqli)` | Number of rows affected by the last INSERT, UPDATE, REPLACE or DELETE query. Note: For prepared statements, check `$stmt->affected_rows`.                                                                                                                                                                                                                                                                                                                                                        |
| `mysql_real_escape_string()`   | `$mysqli->real_escape_string()`| `mysqli_real_escape_string()`   | **Avoid using this directly for user input; prefer prepared statements.** Only use if absolutely necessary and prepared statements are not an option (e.g., for `LIKE` clause wildcards, but even then, careful binding is better).                                                                                                                                                                                                                                                                 |
| `mysql_set_charset()`          | `$mysqli->set_charset()`       | `mysqli_set_charset()`          | Sets the default client character set. Use `utf8mb4` for modern apps.                                                                                                                                                                                                                                                                                                                                                                                                                                |
| `mysql_free_result()`          | `$result->free()`              | `mysqli_free_result($result)`   | Frees the memory associated with a result. Good practice for large result sets.                                                                                                                                                                                                                                                                                                                                                                                                                      |
| `mysql_close()`                | `$mysqli->close()`             | `mysqli_close($mysqli)`         | Closes the database connection. Not strictly necessary as PHP closes it at script end, but good practice.                                                                                                                                                                                                                                                                                                                                                                                            |

---

## Best Practices and Further Improvements

1.  **Centralize Connection:** Create a dedicated `database.php` or `config.php` file for your database connection details and connect once.
2.  **Database Wrapper Class:** For larger applications, create a simple database wrapper class. This abstracts `mysqli` calls, makes your code cleaner, and easier to switch to another database library (like PDO) in the future.

    ```php
    <?php
    // Example of a very basic DB wrapper class
    class DB {
        private $mysqli;

        public function __construct($host, $user, $pass, $db_name, $port = 3306) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                $this->mysqli = new mysqli($host, $user, $pass, $db_name, $port);
                $this->mysqli->set_charset("utf8mb4");
            } catch (mysqli_sql_exception $e) {
                error_log("Failed to connect to MySQL: " . $e->getMessage());
                throw new Exception("Database connection failed."); // Rethrow or handle gracefully
            }
        }

        public function query($sql) {
            $result = $this->mysqli->query($sql);
            return $result; // For SELECT, returns mysqli_result object. For others, true/false.
        }

        public function prepare($sql) {
            $stmt = $this->mysqli->prepare($sql);
            if (!$stmt) {
                 error_log("Prepare failed: " . $this->mysqli->error);
                 throw new Exception("Failed to prepare statement.");
            }
            return $stmt;
        }

        public function fetchAll($stmt) {
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function fetchAssoc($stmt) {
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }

        public function lastInsertId() {
            return $this->mysqli->insert_id;
        }

        public function affectedRows($stmt = null) {
            return $stmt ? $stmt->affected_rows : $this->mysqli->affected_rows;
        }

        public function close() {
            $this->mysqli->close();
        }
    }

    // Usage example:
    // require_once 'DB.php'; // assuming DB.php holds the class
    // $db = new DB($db_host, $db_user, $db_pass, $db_name);

    // // Simple query
    // $users = $db->query("SELECT * FROM users")->fetch_all(MYSQLI_ASSOC);

    // // Prepared statement
    // $stmt = $db->prepare("SELECT * FROM products WHERE price > ?");
    // $stmt->bind_param("d", $minPrice);
    // $stmt->execute();
    // $products = $db->fetchAll($stmt);
    // $stmt->close();
    ?>
    ```

3.  **Error Logging:** Instead of `die()` directly, log errors (`error_log()`) and then display a generic, user-friendly error message. Never show raw database errors to users.
4.  **Transaction Management:** `mysqli` supports transactions (`$mysqli->begin_transaction()`, `$mysqli->commit()`, `$mysqli->rollback()`), which are crucial for maintaining data integrity in multi-step operations.
5.  **Password Hashing:** **NEVER** store plaintext passwords. Use `password_hash()` to hash passwords and `password_verify()` to check them.

This comprehensive guide should help you refactor your PHP files from `mysql_*` to `mysqli_*` effectively and securely. Remember to tackle this systematically and test thoroughly!