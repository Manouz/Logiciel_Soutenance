<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue - Université Félix Houphouët-Boigny</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a5490;
            --secondary-color: #f8b500;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
            --light-bg: #f8f9fa;
            --gradient-primary: linear-gradient(135deg, #1a5490 0%, #2980b9 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gradient-primary);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .landing-page {
            text-align: center;
            color: white;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 4rem 2.5rem;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
        }

        .landing-page h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .landing-page p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .landing-page .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 10px;
            transition: background-color 0.3s ease;
        }

        .landing-page .btn-primary:hover {
            background-color: #2980b9;
        }

        .landing-page .university-logo {
            margin-bottom: 2rem;
        }

        .landing-page .university-logo i {
            font-size: 3.5rem;
        }

        /* Animation background */
        .bg-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .bg-shapes::before,
        .bg-shapes::after {
            content: '';
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .bg-shapes::before {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
            animation-delay: 0s;
        }

        .bg-shapes::after {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1rem;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            text-align: center;
        }

    </style>
</head>
<body>
    <div class="bg-shapes"></div>
    
    <div class="landing-page">
        <div class="university-logo">
            <i class="fas fa-graduation-cap text-primary"></i>
        </div>
        <h1>Bienvenue à l'Université Félix Houphouët-Boigny</h1>
        <p>Accédez à votre espace académique pour gérer vos soutenances, rapports et plus encore.</p>
        <a href="login.php" class="btn btn-primary">Se Connecter</a>
    </div>

    <div class="footer">
        <p>© <?= date('Y') ?> Université Félix Houphouët-Boigny de Cocody | Système de Validation Académique</p>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
