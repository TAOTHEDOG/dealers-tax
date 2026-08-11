<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-customers</title>
    <link rel="icon" type="image/x-icon" href="./asset/image/favicon.ico">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="./style/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</head>

<body class="text-center">

    <main class="form-signin div-center">
        <form id="form-login" action="./index.php" method="post">
            <img class="mb-4" src="./asset/image/Somjai_logo.jpg" alt="" width="200">
            <h1 class="h3 mb-3 fw-normal">ระบบเช็คประวัติลูกค้า</h1>
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="floatingInput" name="username" required>
                <label for="floatingInput">Username</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="floatingPassword" name="password" required>
                <label for="floatingPassword">Password</label>
            </div>
            <button class="w-100 btn btn-lg btn-primary mb-3" type="submit">เข้าสู่ระบบ</button>
            <p class="mt-5 mb-3 text-muted">© Somjai Team</p>
        </form>
    </main>

</body>

</html>