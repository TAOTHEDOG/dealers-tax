<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SJ Dealer App</title>
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.ico">
    <!-- <link rel="stylesheet" href="./style.css"> -->
    <link rel="icon" href="./favicon.ico" type="image/x-icon">

    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
  </head>
  <body>
    <main>
        <!-- <h1>Welcome to Dealertax </h1>   -->
   
		<section class="h-100">
		<div class="container h-100">
			<div class="row justify-content-sm-center h-100">
				<div class="frm_login col-xxl-6 col-xl-6 col-lg-6 col-md-7 col-sm-9" style="padding-top: 40px;">
					
					<div class="card shadow-lg">
						
						<div class="card-body p-6" style="border-radius: 10px; background-color: #FFF2F2;">
							<div class="text-center my-5">
								<h1><img src="assets/images/LINE_ALBUM_LOGO_230921_1.png" alt="logo" style="width: 90%; height: auto;"></h1>  
								
							</div>
							<!-- <h1 class="fs-4 card-title fw-bold mb-4">เข้าสู่ระบบ</h1> -->
							<form action="checkLogin.php" method="POST" class="needs-validation" novalidate="">
								<div class="mb-3">
									<label class="mb-2 text-muted" for="username">ชื่อผู้ใช้งาน</label>
									<input id="username" type="text" class="form-control" name="username" required="" autofocus="" maxlength="8">
									<div class="invalid-feedback">
										ชื่อผู้ใช้งานไม่ถูกต้อง
									</div>
								</div>

								<div class="mb-3">
									<div class="mb-2 w-100">
										<label class="text-muted" for="password">รหัสผ่าน</label>
										<!-- <a href="forgot.html" class="float-end">
											ลืมรหัสผ่าน?
										</a> -->
									</div>
									<input id="password" type="password" class="form-control" name="password" required>
								    <div class="invalid-feedback">
								    	กรุณากรอกพาสเวิร์ด
							    	</div>
								</div>

								<!-- <div class="d-flex align-items-center">
									<div class="form-check">
										<input type="checkbox" name="remember" id="remember" class="form-check-input">
										<label for="remember" class="form-check-label">Remember Me</label>
									</div>
									
								</div> -->
								<div class="mb-12" style="text-align: center;">
									<button type="submit" class="btn btn-primary btn-lg ms-auto">
										เข้าสู่ระบบ
									</button>
								</div>
							</form>
						</div>
						<div class="card-footer py-3 border-0">
						
						</div>
					</div>
					<div class="text-center mt-5 text-muted">
						Copyright © 2017-2021 — CjK
					</div>
				</div>
			</div>
		</div>

	</section>
	
    </main>
	<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
  </body>
</html>