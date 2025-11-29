<?php
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['patient_id'])) {
    header('Location: login.php');
    exit();
}

// Configuration de la base de données
$host = 'localhost';
$dbname = 'espace_patient';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Récupération des informations du patient
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$_SESSION['patient_id']]);
$patient = $stmt->fetch();

// Récupération des statistiques
$stats = [
    'next_appointment' => null,
    'documents_count' => 0,
    'prescriptions_count' => 0
];

// Prochain rendez-vous
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE patient_id = ? AND date >= NOW() ORDER BY date ASC LIMIT 1");
$stmt->execute([$_SESSION['patient_id']]);
$stats['next_appointment'] = $stmt->fetch();

// Nombre de documents
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM documents WHERE patient_id = ?");
$stmt->execute([$_SESSION['patient_id']]);
$stats['documents_count'] = $stmt->fetch()['count'];

// Nombre d'ordonnances
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM prescriptions WHERE patient_id = ? AND expiration_date >= NOW()");
$stmt->execute([$_SESSION['patient_id']]);
$stats['prescriptions_count'] = $stmt->fetch()['count'];
?>