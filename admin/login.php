<?php
require_once 'includes/config.php';

$error = '';

if ($_POST) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Mary Mother of Mercy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Color Variables - Matching School Website */
        :root {
            --primary-blue: rgb(44, 172, 238);
            --primary-maroon: maroon;
            --white: white;
            --light-blue: rgba(44, 172, 238, 0.1);
            --light-maroon: rgba(128, 0, 0, 0.1);
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        /* Add subtle pattern overlay */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%), 
                        linear-gradient(-45deg, rgba(255,255,255,0.1) 25%, transparent 25%), 
                        linear-gradient(45deg, transparent 75%, rgba(255,255,255,0.1) 75%), 
                        linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.1) 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            opacity: 0.3;
        }
        
        .login-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
            position: relative;
            z-index: 1;
            border: 3px solid rgba(255,255,255,0.2);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon));
            color: var(--white);
            padding: 35px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Add shine effect to header */
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s ease-in-out infinite;
        }
        
        @keyframes shine {
            0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); opacity: 0; }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); opacity: 1; }
        }
        
        .login-body {
            padding: 35px 30px;
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
        }
        
        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 14px 18px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
        }
        
        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(44,172,238,0.25);
            transform: translateY(-2px);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-label i {
            color: var(--primary-blue);
            margin-right: 8px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon));
            border: none;
            border-radius: 12px;
            padding: 14px 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(44,172,238,0.4);
            background: linear-gradient(135deg, var(--primary-maroon), var(--primary-blue));
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .school-logo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 3px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        
        .school-logo:hover {
            transform: scale(1.05);
            border-color: rgba(255,255,255,0.5);
        }
        
        .login-header h4 {
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 5px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
            position: relative;
            z-index: 2;
        }
        
        .login-header p {
            font-weight: 400;
            opacity: 0.9;
            font-size: 1rem;
            position: relative;
            z-index: 2;
        }
        
        .alert-danger {
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: var(--white);
            border-left: 4px solid #a71e2a;
        }
        
        .text-muted {
            color: #6c757d !important;
            font-weight: 500;
        }
        
        .text-muted i {
            color: var(--primary-blue) !important;
            margin-right: 5px;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .login-container {
                max-width: 95%;
                margin: 20px;
            }
            
            .login-header {
                padding: 25px 20px;
            }
            
            .login-body {
                padding: 25px 20px;
            }
            
            .school-logo {
                width: 60px;
                height: 60px;
            }
            
            .login-header h4 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="../images/marry.webp" alt="School Logo" class="school-logo">
            <h4 class="mb-0">Admin Panel</h4>
            <p class="mb-0">Mary Mother of Mercy School</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                </button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt"></i> Secure Admin Access
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>