<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Accesso non autorizzato');
}

header('Content-Type: application/json');

function pulisci($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

$email = pulisci($_POST['email'] ?? '');
$psswd = $_POST['psswd'] ?? '';

if(empty($email) || empty($psswd)) {
    echo json_encode(['successo' => false, 'messaggio' => 'Email e password sono obbligatori']);
    exit;
}

$db_host = 'localhost';
$db_name = 'volontariato';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8",
        $db_user,
        $db_pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM registrazioni WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $utente = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$utente) {
        echo json_encode(['successo' => false, 'messaggio' => 'Email non trovata. <a href="registrazione.html">Registrati qui</a>']);
        exit;
    }

    // Controlla password (se usi password_hash nel db)
    // if(!password_verify($psswd, $utente['psswd'])) {

    // Se la password è in chiaro nel db (come nel tuo caso attuale):
    if(!password_verify($psswd, $utente['psswd'])) {
        echo json_encode(['successo' => false, 'messaggio' => 'Password errata']);
        exit;
    }

    echo json_encode([
        'successo' => true,
        'messaggio' => 'Accesso effettuato!',
        'nome' => $utente['nome']
    ]);

} catch(PDOException $e) {
    echo json_encode(['successo' => false, 'messaggio' => 'Errore del server', 'debug' => $e->getMessage()]);
}
?>