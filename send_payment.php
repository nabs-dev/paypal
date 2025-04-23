<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_email = $_POST['recipient_email'];
    $amount = $_POST['amount'];
    
    $conn = new mysqli('localhost', 'u8gr0sjr9p4p4', '9yxuqyo3mt85', 'dbi8xu1lmucqnx');
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if sender has sufficient balance
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();
    
    if ($balance >= $amount) {
        // Deduct from sender and add to recipient
        $stmt = $conn->prepare("SELECT id, balance FROM users WHERE email = ?");
        $stmt->bind_param("s", $recipient_email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($recipient_id, $recipient_balance);
            $stmt->fetch();
            
            // Update balances
            $new_balance = $balance - $amount;
            $new_recipient_balance = $recipient_balance + $amount;
            
            $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->bind_param("di", $new_balance, $user_id);
            $stmt->execute();
            
            $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->bind_param("di", $new_recipient_balance, $recipient_id);
            $stmt->execute();
            
            // Insert transaction records
            $stmt = $conn->prepare("INSERT INTO transactions (sender, receiver, amount, date) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iii", $user_id, $recipient_id, $amount);
            $stmt->execute();
            
            echo "Payment sent successfully!";
        } else {
            echo "Recipient not found.";
        }
        
        $stmt->close();
    } else {
        echo "Insufficient balance!";
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Payment</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Send Payment</h1>
    </header>
    <main>
        <form method="POST" action="">
            <label for="recipient_email">Recipient Email:</label>
            <input type="email" id="recipient_email" name="recipient_email" required>

            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" required>

            <button type="submit">Send Payment</button>
        </form>
    </main>
</body>
</html>
