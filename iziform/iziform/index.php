<?php
session_start();
require_once "includes/db.php";

// Si déjà connecté, rediriger selon rôle
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin': header("Location: admin/dashboard.php"); exit;
        case 'formateur': header("Location: formateur/dashboard.php"); exit;
        case 'student': header("Location: student/dashboard.php"); exit;
    }
}

$error = "";

if (isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Vérifier l'utilisateur
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Login réussi
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nom'] = $user['nom'];

        // Redirection selon rôle
        switch ($user['role']) {
            case 'admin': header("Location: admin/dashboard.php"); exit;
            case 'formateur': header("Location: formateur/dashboard.php"); exit;
            case 'student': header("Location: student/dashboard.php"); exit;
        }
    } else {
        $error = "Email ou mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IZiForM | Connexion</title>

<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* ======= TON DESIGN (inchangé) ======= */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Open Sans', sans-serif;
}

body, html {
    height: 100%;
    background-color: #fff;
}

.main-container {
    display: flex;
    height: 100vh;
    width: 100%;
}

/* --- SECTION GAUCHE --- */
.left-panel {
    flex: 1.2;
    padding: 70px 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
    background: url("img/imm1.jpg") no-repeat center center;
    background-size: cover;
}

.left-panel::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        135deg,
        rgb(61, 183, 185),
        rgba(240, 254, 255, 0.88)
    );
    z-index: 0;
}

.left-panel > * {
    position: relative;
    z-index: 1;
}

.portal-badge {
    display: inline-block;
    background: rgba(0, 68, 171, 0.1);
    color: #0044ab;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 20px;
}

.left-panel h1 {
    font-size: 28px;
    color: #444;
    font-weight: 400;
}

.left-panel h1 span {
    display: block;
    font-size: 38px;
    font-weight: 800;
    color: #0044ab;
    margin-top: 5px;
}

.title-line {
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, #0044ab, #4fc3f7);
    border-radius: 10px;
    margin: 18px 0 30px 0;
}

.left-panel p {
    font-size: 15px;
    color: #555;
    line-height: 1.7;
    margin-bottom: 18px;
}

/* --- SECTION DROITE --- */
.right-panel {
    flex: 0.5;
    background-color: whitesmoke;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 50px;
}

.logo-container {
    margin-bottom: 40px;
}

.logo-container img {
    max-width: 180px;
    height: auto;
}

.right-panel h2 {
    font-size: 18px;
    color: #333;
    margin-bottom: 30px;
    font-weight: 600;
}

form {
    width: 100%;
    max-width:400px;
}

.input-group {
    display: flex;
    margin-bottom: 15px;
    border: 1px solid #d1d1d1;
    border-radius: 4px;
    background-color: #e8f0fe;
    overflow: hidden;
}

.input-icon {
    background-color: #f0f0f0;
    padding: 30px;
    min-width: 55px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-right: 1px solid #d1d1d1;
    color: #666;
}

.input-group input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 12px 15px;
    font-size: 14px;
    outline: none;
    color: #333;
}

.footer-note {
    font-size: 11px;
    color: #d32f2f;
    text-align: center;
    margin-top: 15px;
    line-height: 1.4;
}

.btn-submit {
    width: 100%;
    padding: 14px;
    background-color: #0044ab;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 20px;
}

.btn-submit:hover {
    background-color: #00358a;
}

/* Message d'erreur stylé */
.alert-error {
    background: #fdecea;
    color: #842029;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    text-align: center;
    font-size: 13px;
}

@media (max-width: 900px) {
    .main-container { flex-direction: column; }
    .left-panel { padding: 40px 20px; }
    .right-panel { padding: 40px 20px; }
}
/* ===== ANIMATION LOGO ===== */
.logo-anim {
    transition: transform 0.4s ease, filter 0.4s ease;
    cursor: pointer;
}

/* Effet au survol */
.logo-anim:hover {
    transform: scale(1.08) rotate(2deg);
    filter: drop-shadow(0 8px 15px rgba(0, 68, 171, 0.35));
}

/* Petite animation d’entrée au chargement */
.logo-container {
    animation: fadeInZoom 1s ease forwards;
}

@keyframes fadeInZoom {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

</style>
</head>
<body>
    

<div class="main-container">

<!-- GAUCHE -->
<div class="left-panel">
    <div class="portal-badge">
        <i class="fa-solid fa-graduation-cap"></i> Portail numérique
    </div>

    <h1>Bienvenue dans <span>MY IZIFORM</span></h1>
    <div class="title-line"></div>

    <p>
        <strong>My IZiForM</strong> est la porte d'entrée vers un ensemble
        d'informations, de ressources et de services numériques personnalisés.
    </p>

    <p>
        Toute personne appartenant à <strong>IZiForM</strong>
        (Étudiants, Enseignants, Administratifs) peut accéder à
        <strong>My IZiForM</strong> moyennant son identifiant et son mot de passe.
    </p>
</div>

<!-- DROITE (FORMULAIRE) -->
<div class="right-panel">
   <div class="logo-container">
    <a href="../index.html">
        <img src="../img/log1.png" alt="Logo IZIFORM" class="logo-anim">
    </a>
</div>


    <h2>Vous avez déjà un compte ?</h2>

    <?php if (!empty($error)) : ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="input-group">
            <div class="input-icon">
                <i class="fa-regular fa-id-card"></i>
            </div>
            <!-- IMPORTANT : name="email" pour garder ton PHP -->
            <input type="email" name="email" placeholder="Votre email" required>
        </div>

        <div class="input-group">
            <div class="input-icon">
                <i class="fa-solid fa-lock"></i>
            </div>
            <input type="password" name="password" placeholder="••••" required>
        </div>

        <div class="footer-note">
            Pour votre premier accès à My IZIFORM, utilisez comme mot de passe la
            date de livraison de votre Carte d'Identité Nationale (JJ/MM/AAAA).
        </div>

        <!-- IMPORTANT : name="login" pour ton PHP -->
        <button type="submit" name="login" class="btn-submit">
            Se connecter
        </button>
    </form>
</div>

</div>
</body>
</html>
