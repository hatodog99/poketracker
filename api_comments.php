<?php
session_start();
header('Content-Type: application/json');
include 'includes/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// 1. FETCH COMMENTS
if ($action === 'get') {
    $pokemon_id = mysqli_real_escape_string($conn, $_GET['pokemon_id']);
    
    $sql = "SELECT comments.*, users.username 
            FROM comments 
            JOIN users ON comments.user_id = users.id 
            WHERE pokemon_id = '$pokemon_id' 
            ORDER BY created_at ASC";
            
    $result = mysqli_query($conn, $sql);
    $comments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = [
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'username' => htmlspecialchars($row['username']),
            'text' => htmlspecialchars($row['comment_text'])
        ];
    }
    echo json_encode(['success' => true, 'comments' => $comments]);
    exit();
}

// 2. POST A COMMENT
if ($action === 'post') {
    $pokemon_id = mysqli_real_escape_string($conn, $_POST['pokemon_id']);
    $text = mysqli_real_escape_string($conn, trim($_POST['text']));
    
    if ($text === '') {
        echo json_encode(['success' => false, 'error' => 'Comment cannot be empty']);
        exit();
    }

    $sql = "INSERT INTO comments (pokemon_id, user_id, comment_text) VALUES ('$pokemon_id', '$user_id', '$text')";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit();
}

// 3. DELETE A COMMENT
if ($action === 'delete') {
    $comment_id = mysqli_real_escape_string($conn, $_POST['comment_id']);
    
    // We must verify the user owns the comment OR owns the pokemon post
    $verify_sql = "SELECT comments.user_id AS comment_author, pokemon.user_id AS post_owner 
                   FROM comments 
                   JOIN pokemon ON comments.pokemon_id = pokemon.id 
                   WHERE comments.id = '$comment_id'";
                   
    $verify_res = mysqli_query($conn, $verify_sql);
    if ($row = mysqli_fetch_assoc($verify_res)) {
        if ($row['comment_author'] == $user_id || $row['post_owner'] == $user_id) {
            mysqli_query($conn, "DELETE FROM comments WHERE id = '$comment_id'");
            echo json_encode(['success' => true]);
            exit();
        }
    }
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>