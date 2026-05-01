<?php
/**
 * Logout Page
 * Destroy session and show a styled confirmation screen
 */
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - Phone Shop Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            color: #e2e8f0;
        }
        .logout-container {
            width: 100%;
            max-width: 420px;
            background: rgba(15, 23, 42, 0.98);
            border: 1px solid rgba(148, 163, 184, 0.12);
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.45);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }
        .logout-header {
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }
        .logout-header .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .logout-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.75rem;
            color: #f8fafc;
        }
        .logout-header p {
            color: #cbd5e1;
            line-height: 1.7;
            font-size: 0.98rem;
        }
        .logout-actions {
            padding: 1.5rem 2rem 2rem;
            display: grid;
            gap: 1rem;
        }
        .btn-primary {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 0.95rem 1rem;
            border-radius: 0.75rem;
            border: none;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.25);
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        }
        .logout-note {
            color: #94a3b8;
            font-size: 0.92rem;
            text-align: center;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-header">
            <div class="icon">👋</div>
            <h1>You're Logged Out</h1>
            <p>You have successfully signed out of Techsham Shop Management. You will be redirected to the login page shortly.</p>
        </div>
        <div class="logout-actions">
            <a href="login.php" class="btn-primary">Go to Login</a>
            <div class="logout-note">If the page does not redirect automatically, click the button above.</div>
        </div>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 2800);
    </script>
</body>
</html>