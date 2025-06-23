<?php
include '../includes/db.php';

// Default filters
$filter = $_GET['filter'] ?? 'month';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// Build SQL filter
$whereClause = "1";
$dateGroup = "DATE(created_at)";
if ($filter === 'today') {
    $whereClause = "DATE(created_at) = CURDATE()";
} elseif ($filter === 'week') {
    $whereClause = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter === 'month') {
    $whereClause = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
} elseif ($filter === 'range' && $from && $to) {
    $whereClause = "DATE(created_at) BETWEEN '$from' AND '$to'";
}

// Fetch income
$incomeRes = $conn->query("SELECT SUM(amount) AS total_income FROM shipments WHERE $whereClause");
$income = $incomeRes->fetch_assoc()['total_income'] ?? 0;

// Fetch expenses
$expenseRes = $conn->query("SELECT SUM(amount) AS total_expense FROM expenses WHERE $whereClause");
$expenses = $expenseRes->fetch_assoc()['total_expense'] ?? 0;

$profit = $income - $expenses;

// Fetch detailed expense breakdown
$expenseDetails = $conn->query("SELECT type, SUM(amount) as total FROM expenses WHERE $whereClause GROUP BY type");
$expenseData = [];
while($row = $expenseDetails->fetch_assoc()) {
    $expenseData[] = $row;
}

// Fetch daily totals for line chart
$lineDataQuery = "
    SELECT d.report_date,
           IFNULL(s.total_income, 0) AS income,
           IFNULL(e.total_expense, 0) AS expense
    FROM (
        SELECT DATE(created_at) AS report_date FROM shipments WHERE $whereClause
        UNION
        SELECT DATE(created_at) AS report_date FROM expenses WHERE $whereClause
    ) AS d
    LEFT JOIN (
        SELECT DATE(created_at) AS date, SUM(amount) AS total_income FROM shipments WHERE $whereClause GROUP BY DATE(created_at)
    ) AS s ON d.report_date = s.date
    LEFT JOIN (
        SELECT DATE(created_at) AS date, SUM(amount) AS total_expense FROM expenses WHERE $whereClause GROUP BY DATE(created_at)
    ) AS e ON d.report_date = e.date
    GROUP BY d.report_date
    ORDER BY d.report_date ASC
";
$lineResult = $conn->query($lineDataQuery);
$lineDates = [];
$lineIncome = [];
$lineExpense = [];
while($row = $lineResult->fetch_assoc()) {
    $lineDates[] = $row['report_date'];
    $lineIncome[] = $row['income'];
    $lineExpense[] = $row['expense'];
}

// Fetch shipment status breakdown
$statusResult = $conn->query("SELECT status, COUNT(*) as total FROM shipments WHERE $whereClause GROUP BY status");
$statusSummary = [
    'Total Orders' => 0,
    'Pending' => 0,
    'In Progress' => 0,
    'Delivered' => 0
];
while ($row = $statusResult->fetch_assoc()) {
    $statusSummary[$row['status']] = $row['total'];
    $statusSummary['Total Orders'] += $row['total'];
}

// Fetch top routes
$topRoutesResult = $conn->query("SELECT CONCAT(from_location, ' → ', to_location) AS route, COUNT(*) as trips FROM shipments WHERE $whereClause GROUP BY route ORDER BY trips DESC LIMIT 5");
$topRoutes = [];
while($row = $topRoutesResult->fetch_assoc()) {
    $topRoutes[] = $row;
}

// Fetch most profitable vehicle
$vehicleResult = $conn->query("SELECT vehicle_reg, SUM(amount) as total_income FROM shipments WHERE $whereClause GROUP BY vehicle_reg ORDER BY total_income DESC LIMIT 1");
$topVehicle = $vehicleResult->fetch_assoc();

// Fetch most frequent customer
$topCustomerRes = $conn->query("SELECT customer_source, COUNT(*) AS orders FROM shipments WHERE $whereClause GROUP BY customer_source ORDER BY orders DESC LIMIT 1");
$topCustomer = $topCustomerRes->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>📈 Reports</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card, .summary-card {
            background:rgb(117, 117, 170); padding: 20px; margin: 10px 0;
            border-radius: 8px; text-align: center;
            font-size: 18px; font-weight: bold;
        }
        .filters { margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .filters select, .filters input, .filters button {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .charts-container { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 30px; justify-content: center; }
        .chart-box {
            flex: 1; min-width: 350px; max-width: 450px; height: 360px;
            background: #fff; padding: 15px;
            border: 1px solid #ddd; border-radius: 10px;
        }
        .summary-boxes {
            display: flex; gap: 20px; flex-wrap: wrap; justify-content: space-between;
        }
        ul { padding-left: 20px; }
        h2, h3 { margin-top: 5px; }
        .report-buttons {
            margin: 20px 0;
        }
        .dropdown {
            position: relative; display: inline-block;
        }
        .dropdown-content {
            display: none; position: absolute;
            background-color: #f9f9f9; min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .dropdown-content a {
            color: black; padding: 12px 16px;
            text-decoration: none; display: block;
        }
        .dropdown-content a:hover { background-color: #f1f1f1; }
        .dropdown:hover .dropdown-content { display: block; }
        .dropdown:hover .dropbtn { background-color: #555; }
        .dropbtn {
            background-color: #333; color: white;
            padding: 8px 16px; font-size: 16px;
            border: none; cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <h2>📊 Financial Reports</h2>

    <form method="GET" action="dashboard-manager.php" class="filters">
        <input type="hidden" name="page" value="reports">
        <label>Filter:</label>
        <select name="filter" onchange="toggleRange(this.value)">
            <option value="today" <?= $filter === 'today' ? 'selected' : '' ?>>Today</option>
            <option value="week" <?= $filter === 'week' ? 'selected' : '' ?>>This Week</option>
            <option value="month" <?= $filter === 'month' ? 'selected' : '' ?>>This Month</option>
            <option value="range" <?= $filter === 'range' ? 'selected' : '' ?>>Custom Range</option>
        </select>
        <input type="date" name="from" value="<?= $from ?>" <?= $filter === 'range' ? '' : 'disabled' ?>>
        <input type="date" name="to" value="<?= $to ?>" <?= $filter === 'range' ? '' : 'disabled' ?>>
        <button type="submit">Apply</button>
    </form>

    <div class="dropdown report-buttons">
        <button class="dropbtn">📁 Generate PDF Report</button>
        <div class="dropdown-content">
            <a href="generate-reports.php?type=orders&status=Pending" target="_blank">Pending Orders</a>
            <a href="generate-reports.php?type=orders&status=In Progress" target="_blank">In Progress Orders</a>
            <a href="generate-reports.php?type=orders&status=Delivered" target="_blank">Delivered Orders</a>
            <a href="generate-reports.php?type=orders&status=All" target="_blank">All Orders</a>
            <a href="generate-reports.php?type=orders&status=Paid" target="_blank">Paid Orders</a>
            <a href="generate-reports.php?type=orders&status=Unpaid" target="_blank">Unpaid Orders</a>
            <a href="generate-reports.php?type=expenses" target="_blank">Expense Report</a>
            <a href="generate-reports.php?type=profit" target="_blank">Profit Report</a>


        </div>
    </div>

    <div class="card">💰 Income: KES <?= number_format($income, 2) ?></div>
    <div class="card">💸 Expenses: KES <?= number_format($expenses, 2) ?></div>
    <div class="card">📈 Profit: KES <?= number_format($profit, 2) ?></div>

    <div class="summary-boxes">
        <div class="summary-card">📦 Total Orders: <?= $statusSummary['Total Orders'] ?></div>
        <div class="summary-card">⏳ Pending: <?= $statusSummary['Pending'] ?></div>
        <div class="summary-card">🚚 In Progress: <?= $statusSummary['In Progress'] ?></div>
        <div class="summary-card">✅ Delivered: <?= $statusSummary['Delivered'] ?></div>
    </div>

        
    </div>

    <div class="charts-container">
        <div class="chart-box">
            <h3>Expense Breakdown</h3>
            <canvas id="expenseChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Income vs Expenses</h3>
            <canvas id="lineChart"></canvas>
        </div>
    </div>

    <script>
        function toggleRange(val) {
            const inputs = document.querySelectorAll('input[type=date]');
            inputs.forEach(input => input.disabled = val !== 'range');
        }

        const pieCtx = document.getElementById('expenseChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($expenseData, 'type')) ?>,
                datasets: [{
                    label: 'Expense Breakdown',
                    data: <?= json_encode(array_column($expenseData, 'total')) ?>,
                    backgroundColor: ['#f44336', '#2196f3', '#ffc107', '#4caf50', '#9c27b0']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($lineDates) ?>,
                datasets: [
                    {
                        label: 'Income',
                        data: <?= json_encode($lineIncome) ?>,
                        borderColor: 'green',
                        fill: false
                    },
                    {
                        label: 'Expenses',
                        data: <?= json_encode($lineExpense) ?>,
                        borderColor: 'red',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
