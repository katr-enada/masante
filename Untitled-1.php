<?php
session_start();

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'projet1');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", 
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    die("Une erreur est survenue lors de la connexion à la base de données. Veuillez réessayer plus tard.");
}

// Variables pour stocker les erreurs et les données du formulaire
$error = '';
$username = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation des données
    if (empty($username)) {
        $error = "L'identifiant est requis";
    } elseif (empty($password)) {
        $error = "Le mot de passe est requis";
    } else {
        try {
            // Recherche de l'utilisateur dans la base de données
            $stmt = $pdo->prepare("SELECT id, email, username, password, role FROM users WHERE email = ? OR username = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Authentification réussie
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();

                // Redirection vers le tableau de bord
                header("Location: dashboard.php");
                exit();
            } else {
                // Délai pour prévenir les attaques par force brute
                sleep(1);
                $error = "Identifiant ou mot de passe incorrect";
            }
        } catch (PDOException $e) {
            error_log("Erreur d'authentification: " . $e->getMessage());
            $error = "Une erreur est survenue lors de l'authentification";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion DME</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-hover: #2980b9;
            --error-color: #e74c3c;
            --text-color: #333;
            --light-text: #7f8c8d;
            --border-color: #ddd;
            --background-color: #f5f5f5;
            --white: #ffffff;
            --shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            line-height: 1.6;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 20px;
            padding: 2rem;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header img {
            width: 80px;
            margin-bottom: 1rem;
        }

        .login-header h1 {
            font-size: 1.5rem;
            color: var(--text-color);
            font-weight: 600;
        }

        .login-form-table {
            width: 100%;
            border-collapse: collapse;
        }

        .login-form-table td {
            padding: 8px 0;
            vertical-align: middle;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: var(--light-text);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group label i {
            margin-right: 10px;
            width: 16px;
            text-align: center;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 38px;
            cursor: pointer;
            color: var(--light-text);
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .login-btn:hover {
            background-color: var(--primary-hover);
        }

        .login-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.9rem;
            color: var(--light-text);
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s;
        }

        .login-footer a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .login-footer p {
            margin-top: 1rem;
        }

        .error-message {
            color: var(--error-color);
            text-align: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            padding: 10px;
            background-color: rgba(231, 76, 60, 0.1);
            border-radius: 5px;
            border-left: 4px solid var(--error-color);
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
                margin: 10px;
            }
            
            .login-header h1 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="image/1.png" alt="Logo du système de gestion médicale">
            <h1>Gestion des Dossiers Médicaux</h1>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" novalidate>
            <table class="login-form-table">
                <tr>
                    <td>
                        <div class="form-group">
                            <label for="username"><i class="fas fa-user"></i> Identifiant</label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                placeholder="Email ou nom d'utilisateur" 
                                value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" 
                                required
                                autocomplete="username"
                            >
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="••••••••" 
                                required
                                autocomplete="current-password"
                            >
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <button type="submit" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </td>
                </tr>
            </table>
            
            <div class="login-footer">
                <a href="forgot_password.php">Mot de passe oublié ?</a>
                <p>Vous n'avez pas de compte ? <a href="inscriptionA.php">Créer un compte</a></p>
            </div>
        </form>
    </div>

    <script>
        // Fonction pour afficher/masquer le mot de passe
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const passwordInput = document.querySelector('#password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            // Focus sur le champ username au chargement
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>