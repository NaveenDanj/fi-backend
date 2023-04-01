<!DOCTYPE html>
<html>
<head>
    <title>YOU HAVE PENDING SUBMISSIONS</title>
    <style>
        * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: "Inter", sans-serif;
  color: #343a40;
  line-height: 1;
  display: flex;
  justify-content: center;
}
h3{
    margin-top: 20px;
    margin-bottom: 10px;
}
table {
  width: 1000px;
  margin-top: 50px;
  font-size: 14px;
  border-collapse: collapse;
}

td,
th {
  padding: 0px 10px;
  text-align: left;
}

thead tr {
  background-color: #005188;
  color: #fff;
}

thead th {
  width: 25%;
}

tbody tr:nth-child(odd) {
  background-color: #f8f9fa;
}

tbody tr:nth-child(even) {
  background-color: #e9ecef;
}

    </style>
</head>
<body>
    <h1>YOU HAVE PENDING SUBMISSIONS </h1>
    <h3>Dear Introducer</h3>
    <p>Please note that the below leads under your supervision have not been updated since it’s submission. Please ensure you update it today and avoid any further delay.</p>
    
    
    <table>
    <thead>
  <tr>
    <th>Name</th>
    <th>Contact No</th>
    <th>Create</th>
    <th>Last Update</th>
    <th>Status</th>
    <th>Referee Remarks</th>
    <th>Introducer</th>
    <th>Introducer Remarks</th>
  </tr>
  </thead>
  <tbody>
  @foreach ($submissions as $submission)
  <tr>
   <td>{{ $submission->name }} </td>
   <td>{{ $submission->contact }}</td>
   <td>{{ $submission->created_at }}</td>
   <td>{{ $submission->updated_at }}</td>
   <td>{{ $submission->status }}</td>
   <td>{{ $submission->remarks }}</td>
   <th>{{ $submission->introducer->fullname }}</th>
   <td>{{ $submission->introducerRemarks }}</td>
  </tr>
  @endforeach
  </tbody>
</table>
</body>
</html>
