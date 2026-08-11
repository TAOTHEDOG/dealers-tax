<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SJ Dealer App</title>
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link href="./style/style.css?v=2" rel="stylesheet">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card card shadow-lg">
            <div class="card-header">
                <img src="assets/images/LINE_ALBUM_LOGO_230921_1.png" alt="logo">
            </div>
            <div class="card-body">
                <h5 class="text-center mb-4 fw-bold" style="color: var(--purple-700);">เข้าสู่ระบบ</h5>
                <form action="checkLogin.php" method="POST" class="needs-validation">
                    <div class="mb-3">
                        <label class="mb-2 text-muted" for="username">ชื่อผู้ใช้งาน</label>
                        <input id="username" type="text" class="form-control" name="username"
                               required autofocus maxlength="11">
                        <div class="invalid-feedback">ชื่อผู้ใช้งานไม่ถูกต้อง</div>
                    </div>
                    <div class="mb-3">
                        <label class="mb-2 text-muted" for="password">รหัสผ่าน</label>
                        <div class="input-group">
                            <input id="password" type="password" class="form-control" name="password" required>
                            <span class="input-group-text" id="basic-addon1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash" viewBox="0 0 16 16">
                                    <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"></path>
                                    <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"></path>
                                    <path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"></path>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3" style="text-align: center;">
                        <button type="submit" class="btn-login btn" name="form_submit">
                            เข้าสู่ระบบ
                        </button>
                    </div>
                </form>
            </div>
            <div class="login-footer">
                Copyright &copy; 2017–2024 — CjK
            </div>
        </div>
    </div>

    <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        const togglePassword = document.querySelector('#basic-addon1');
        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
        });
    </script>
</body>
</html>
