<!DOCTYPE html>
<html>
<head>
    <title>Payment Request Slip</title>
    <style>
        /* Add some basic styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        /* Add a background color */
        .container {
            background-color: #f2f2f2;
            padding: 20px;
        }
        /* Center the text */
        h1 {
            text-align: center;
        }
        /* Style the payment details */
        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;
        }
        /* Add a header row to the table */
        thead tr {
            background-color: #dddddd;
        }
        /* Style the customer details */
        .customer-details {
            margin: 20px 0;
        }
        /* Style the button */
        .button {
            background-color: #4CAF50; /* Green */
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }
        /* Style the instructions */
        p {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payment Request Slip</h1>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Amount Due</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ $payment->amount }}</td>
                    <td>{{ $payment->due_date }}</td>
                </tr>
            </tbody>
        </table>
        <div class="customer-details">
            <h2>Customer Details</h2>
            <table>
                <tr>
                    <th>Name:</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td>{{ $user->phone }}</td>
                </tr>
            </table>
        </div>
        <p>Please make your payment at the earliest.</p>
    </div>
</body>

</html>
