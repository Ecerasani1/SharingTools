<?php 
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "database_name";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_name'])) {
    $item_name = $_POST['item_name'];
    
    $sql = "INSERT INTO items (name, available) VALUES ('$item_name', 1)";
    
    if ($conn->query($sql) === TRUE) {
        echo "Item added successfully";
    } else {
        echo "Error adding item: " . $conn->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['item_id'])) {
    $user_id = $_POST['user_id'];
    $item_id = $_POST['item_id'];
    
    $availability_sql = "SELECT available FROM items WHERE id = $item_id";
    $availability_result = $conn->query($availability_sql);
    
    if ($availability_result->num_rows > 0) {
        $row = $availability_result->fetch_assoc();
        if ($row["available"] == 1) {
            $update_sql = "UPDATE items SET available = 0 WHERE id = $item_id";
            $conn->query($update_sql);
            
            $insert_sql = "INSERT INTO borrow_requests (user_id, item_id) VALUES ($user_id, $item_id)";
            if ($conn->query($insert_sql) === TRUE) {
                echo "Borrow request sent successfully";
            } else {
                echo "Error sending borrow request: " . $conn->error;
            }
        } else {
            echo "Item is not available for borrowing at the moment";
        }
    } else {
        echo "Item not found";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    
    $borrow_sql = "SELECT id FROM borrow_requests WHERE item_id = $item_id AND returned = 0";
    $borrow_result = $conn->query($borrow_sql);
    
    if ($borrow_result->num_rows > 0) {
        $update_sql = "UPDATE items SET available = 1 WHERE id = $item_id";
        $conn->query($update_sql);
        
        $update_request_sql = "UPDATE borrow_requests SET returned = 1 WHERE item_id = $item_id AND returned = 0";
        if ($conn->query($update_request_sql) === TRUE) {
            echo "Item returned successfully";
        } else {
            echo "Error returning item: " . $conn->error;
        }
    } else {
        echo "Item was not found in active borrows";
    }
}

$conn->close();
?>



