<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Login Form</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            overflow: hidden;
        }

        .container {
            position: relative;
            width: 380px;
            padding: 40px 30px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .container h2 {
            text-align: center;
            color: #fff;
            margin-bottom: 30px;
            font-size: 2em;
        }

        .form-group {
            position: relative;
            margin-bottom: 30px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: none;
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: #fff;
            font-size: 1em;
            transition: 0.5s;
        }

        .form-group label {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            pointer-events: none;
            transition: 0.5s;
        }

        .form-group input:focus ~ label,
        .form-group input:valid ~ label {
            top: -10px;
            left: 10px;
            font-size: 0.8em;
            color: #fff;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: 0.5s;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .submit-btn:hover {
            background: linear-gradient(45deg, #ff8e8e, #ff6b6b);
            transform: translateY(-2px);
        }

        .social-login {
            margin-top: 30px;
            text-align: center;
        }

        .social-login p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 15px;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-icons a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            transition: 0.5s;
        }

        .social-icons a:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
        }

        @media (max-width: 480px) {
            .container {
                width: 90%;
                margin: 0 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome Back</h2>
        <form action="login.php" method="POST">
            <div class="form-group">
                <input type="email" name="email" required>
                <label>Email</label>
            </div>
            <div class="form-group">
                <input type="password" name="password" required>
                <label>Password</label>
            </div>
            <button type="submit" class="submit-btn">Login</button>
            <div class="social-login">
                <p>Or login with</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-google"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </form>
    </div>

    <!-- Font Awesome for social icons -->
    <script src="https://kit.fontawesome.com/your-kit-code.js"></script>
</body>
</html>