<?php
// db_connection.php content
$servername = "124.6.224.121";
$username = "midhubDBuser";
$password = "2345Midh@b#4$2025";
$dbname = "midhub-db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define header and footer
function printHeader() {
    echo "<h1>Trans Report</h1>";
    echo "<p>Report generated on: " . date("Y-m-d H:i:s") . "</p>";
}

function printFooter() {
    echo "<p>End of Report</p>";
}

// Function to fetch and display data with pagination
function fetchData($conn, $page) {
    $limit = 1000;
    $offset = ($page - 1) * $limit;
    
    // Get total number of records
    $total_result = $conn->query("SELECT COUNT(*) AS count FROM bkash_send");
    $total_row = $total_result->fetch_assoc();
    $total_records = $total_row['count'];
    $total_pages = ceil($total_records / $limit);
    
    // Fetch paginated records
    $sql = "SELECT * FROM bkash_send ORDER BY submitted_date LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr>
                <th>ID</th>
                <th>User ID</th>
                
                <th>Account Number</th>
               
                <th>Balance</th>
                <th>Charge</th>
                <th>Amount</th>
                <th>Submitted Date</th>
                <th>Status</th>
                <th>Submission Time</th>
                <th>Submission Date</th>
                <th>Level1</th>
                <th>Level2</th>
                <th>Level3</th>
                <th>Level4</th>
                <th>Level5</th>
                
              </tr>";

        $page_total = 0;
        while($row = $result->fetch_assoc()) {
            $page_total += $row['amount'];
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['user_id']}</td>
                   
                    <td>{$row['ac_number']}</td>
                    
                   
                    <td>{$row['balance']}</td>
                    <td>{$row['charge']}</td>
                    <td>{$row['amount']}</td>
                    <td>{$row['submitted_date']}</td>
                    <td>{$row['status']}</td>
                    <td>{$row['s_time']}</td>
                    <td>{$row['s_date']}</td>
                    <td>{$row['level1']}</td>
                    <td>{$row['level2']}</td>
                    <td>{$row['level3']}</td>
                    <td>{$row['level4']}</td>
                    <td>{$row['level5']}</td>
                   
                  </tr>";
        }
        echo "</table>";
        echo "<p>Page Total: $page_total</p>";

        // Pagination links
        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='?page=$i'>$i</a> ";
        }


        // Date-wise totals
        $date_totals = $conn->query("SELECT s_date, SUM(amount) AS total_amount FROM bkash_send GROUP BY s_date");
        if ($date_totals->num_rows > 0) {
            echo "<h2>Date-wise Totals</h2>";
            echo "<table border='1'>";
            echo "<tr><th>Date</th><th>Total Amount</th></tr>";
            while ($date_row = $date_totals->fetch_assoc()) {
                echo "<tr><td>{$date_row['s_date']}</td><td>{$date_row['total_amount']}</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p>No records found.</p>";
    }
}





// Get current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Print header
printHeader();

// Fetch and display data with pagination
fetchData($conn, $page);

// Print footer
printFooter();

// Close database connection
$conn->close();
?>
