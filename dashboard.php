<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'u8gr0sjr9p4p4', '9yxuqyo3mt85', 'dbi8xu1lmucqnx');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();

$stmt->close();

$transactions_stmt = $conn->prepare("SELECT sender, receiver, amount, date FROM transactions WHERE sender = ? OR receiver = ?");
$transactions_stmt->bind_param("ii", $user_id, $user_id);
$transactions_stmt->execute();
$transactions_result = $transactions_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Welcome to Your Dashboard</h1>
        <p>Balance: $<?php echo number_format($balance, 2); ?></p>
    </header>
    <main>
        <h2>Recent Transactions</h2>
        <table>
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Receiver</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($transaction = $transactions_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $transaction['sender']; ?></td>
                        <td><?php echo $transaction['receiver']; ?></td>
                        <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                        <td><?php echo $transaction['date']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="send_payment.php" class="button">Send Payment</a>
    </main>
</body>
</html>
<?php
$transactions_stmt->close();
$conn->close();
?>
