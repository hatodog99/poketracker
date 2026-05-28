<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
include 'includes/connection.php';

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $_GET['search'] : "";
$type_filter = isset($_GET['type']) ? $_GET['type'] : "";

$sql = "SELECT * FROM pokemon WHERE user_id='$user_id' AND name LIKE '%$search%'";
if ($type_filter != "") { $sql .= " AND type='$type_filter'"; }
$sql .= " ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Collection - PC Box</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="navbar">
    <h1>PokéTracker</h1>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="dashboard.php">Trainer Card</a>
        <a href="collection.php">My PC Box</a>
        <a href="add-pokemon.php">Add PKMN</a>
        <a href="auth/logout.php" onclick="return confirm('Logout?')">Logout</a>
    </div>
</div>

<div class="container">
    <form method="GET" class="pc-filter-form">
        <div class="filter-inputs">
            <input type="text" name="search" placeholder="Search PKMN..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="type">
                <option value="">All Types</option>
                <option value="Fire" <?php if($type_filter=='Fire') echo 'selected'; ?>>Fire</option>
                <option value="Water" <?php if($type_filter=='Water') echo 'selected'; ?>>Water</option>
                <option value="Grass" <?php if($type_filter=='Grass') echo 'selected'; ?>>Grass</option>
                <option value="Electric" <?php if($type_filter=='Electric') echo 'selected'; ?>>Electric</option>
                <option value="Psychic" <?php if($type_filter=='Psychic') echo 'selected'; ?>>Psychic</option>
            </select>
            <button type="submit">SEARCH</button>
        </div>
        <div class="result-count">IN BOX: <?php echo mysqli_num_rows($result); ?></div>
    </form>

    <div class="pc-box">
        <div class="pc-info-panel">
            <div class="panel-header">- PKMN DATA -</div>
            <div class="sprite-box">
                <img id="detail-img" src="" alt="Sprite" style="display:none;">
            </div>
            
            <div class="info-content" id="detail-content" style="display:none;">
                <h3 id="detail-name" class="pkmn-name"></h3>
                <p id="detail-level" class="pkmn-level"></p>
                <div class="stat-row"><span class="label">TYPE</span><span id="detail-type" class="val"></span></div>
                <div class="stat-row"><span class="label">STATUS</span><span id="detail-status" class="val"></span></div>
                <div class="stat-row"><span class="label">RATING</span><span id="detail-rating" class="val"></span></div>
                
                <div class="action-buttons">
                    <a id="btn-edit" href="#" class="btn-pc">EDIT</a>
                    <a id="btn-release" href="#" class="btn-pc btn-danger" onclick="return confirm('Release this Pokémon?')">RELEASE</a>
                </div>
            </div>
            
            <div class="info-content empty-state" id="empty-state">
                <?php if (mysqli_num_rows($result) == 0) { ?>
                    <p>Box is empty.<br>Go catch some!</p>
                <?php } else { ?>
                    <p>Select a Pokémon<br>to view data.</p>
                <?php } ?>
            </div>
        </div>

        <div class="pc-box-main">
            <div class="pc-box-header">
                <span class="arrow">◀</span>
                <h2>MY PC BOX 1</h2>
                <span class="arrow">▶</span>
            </div>
            <div class="pc-box-grid">
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="pc-pokemon-slot" 
                         onclick="updateDetails(this)"
                         data-img="uploads/<?php echo htmlspecialchars($row['image']); ?>"
                         data-name="<?php echo htmlspecialchars($row['name']); ?>"
                         data-level="Lv<?php echo htmlspecialchars($row['level']); ?>"
                         data-type="<?php echo htmlspecialchars($row['type']); ?>"
                         data-status="<?php echo htmlspecialchars($row['status']); ?>"
                         data-rating="<?php echo htmlspecialchars($row['rating']); ?>/10"
                         data-edit="edit-pokemon.php?id=<?php echo $row['id']; ?>"
                         data-delete="delete-pokemon.php?id=<?php echo $row['id']; ?>">
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
    document.getElementById('detail-type').innerText = element.getAttribute('data-type');
    document.getElementById('detail-status').innerText = element.getAttribute('data-status');
    document.getElementById('detail-rating').innerText = element.getAttribute('data-rating');
    
    document.getElementById('btn-edit').href = element.getAttribute('data-edit');
    document.getElementById('btn-release').href = element.getAttribute('data-delete');
}
</script>
</body>
</html>