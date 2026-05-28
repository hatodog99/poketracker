<?php
session_start();
include 'includes/connection.php';

$sql = "SELECT pokemon.*, users.username
        FROM pokemon
        JOIN users ON pokemon.user_id = users.id
        ORDER BY pokemon.created_at DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>PokéTracker - Community Box</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="index-page">

<div class="navbar">
    <h1>PokéTracker</h1>
    <div class="nav-links">
    <?php if (isset($_SESSION['user_id'])) { ?>
        <a href="index.php">Home</a>
        <a href="dashboard.php">Trainer Card</a>
        <a href="collection.php">My PC Box</a>
        <a href="add-pokemon.php">Add PKMN</a>
        <a href="auth/logout.php" onclick="return confirm('Logout?')">Logout</a>
    <?php } else { ?>
        <a href="index.php">Home</a>
        <a href="auth/login.php">Login</a>
        <a href="auth/register.php">Register</a>
    <?php } ?>
    </div>
</div>

<div class="container">
    <div class="pc-box">
        <div class="pc-info-panel">
            <div class="panel-header">- PKMN DATA -</div>
            <div class="sprite-box">
                <img id="detail-img" src="" alt="Sprite" style="display:none;">
            </div>
            
            <div class="info-content" id="detail-content" style="display:none;">
                <h3 id="detail-name" class="pkmn-name"></h3>
                <p id="detail-level" class="pkmn-level"></p>
                <div class="stat-row"><span class="label">TRAINER</span><span id="detail-trainer" class="val"></span></div>
                <div class="stat-row"><span class="label">TYPE</span><span id="detail-type" class="val"></span></div>
                <div class="stat-row"><span class="label">STATUS</span><span id="detail-status" class="val"></span></div>
                <div class="stat-row"><span class="label">RATING</span><span id="detail-rating" class="val"></span></div>
            </div>
            <div class="info-content empty-state" id="empty-state">
                <p>Select a Pokémon<br>to view data.</p>
            </div>
        </div>

        <div class="pc-box-main">
            <div class="pc-box-header">
                <span class="arrow">◀</span>
                <h2>COMMUNITY BOX 1</h2>
                <span class="arrow">▶</span>
            </div>
            <div class="pc-box-grid">
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="pc-pokemon-slot" 
                         onclick="updateDetails(this)"
                         data-img="uploads/<?php echo htmlspecialchars($row['image']); ?>"
                         data-name="<?php echo htmlspecialchars($row['name']); ?>"
                         data-level="Lv<?php echo htmlspecialchars($row['level']); ?>"
                         data-trainer="<?php echo htmlspecialchars($row['username']); ?>"
                         data-type="<?php echo htmlspecialchars($row['type']); ?>"
                         data-status="<?php echo htmlspecialchars($row['status']); ?>"
                         data-rating="<?php echo htmlspecialchars($row['rating']); ?>/10">
                        <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
function updateDetails(element) {
    document.querySelectorAll('.pc-pokemon-slot').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');

    document.getElementById('empty-state').style.display = 'none';
    document.getElementById('detail-content').style.display = 'block';
    
    const imgElement = document.getElementById('detail-img');
    imgElement.style.display = 'block';
    imgElement.src = element.getAttribute('data-img');

    document.getElementById('detail-name').innerText = element.getAttribute('data-name');
    document.getElementById('detail-level').innerText = element.getAttribute('data-level');
    document.getElementById('detail-trainer').innerText = element.getAttribute('data-trainer');
    document.getElementById('detail-type').innerText = element.getAttribute('data-type');
    document.getElementById('detail-status').innerText = element.getAttribute('data-status');
    document.getElementById('detail-rating').innerText = element.getAttribute('data-rating');
}
</script>
</body>
</html>