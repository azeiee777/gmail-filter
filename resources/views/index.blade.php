<!DOCTYPE html>
<html>
<head>
    <title>Filtered Emails</title>
</head>
<body>
    <h1>Emails with "Job Update" in the Subject</h1>
    <ul>
        @foreach($emails as $email)
            <li>{{ $email->id }}</li>
        @endforeach
    </ul>
</body>
</html>
