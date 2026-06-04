<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
include 'includes/connection.php';
$user_id = $_SESSION['user_id'];

// 1. Fetch all species for the Search Dropdown
$dex_result = mysqli_query($conn, "SELECT id, name, form FROM pokemon_dex ORDER BY id ASC");
$dex_pokemon = [];
while ($row = mysqli_fetch_assoc($dex_result)) {
    $dex_pokemon[] = $row;
}

// 2. Handle Customization Form Submission
if (isset($_POST['save_customization'])) {
    $new_theme = mysqli_real_escape_string($conn, $_POST['card_theme']);
    $new_sticker = mysqli_real_escape_string($conn, $_POST['sticker_id']);
    $new_bio = mysqli_real_escape_string($conn, trim($_POST['bio']));

    mysqli_query($conn, "UPDATE users SET card_theme='$new_theme', sticker_id='$new_sticker', bio='$new_bio' WHERE id='$user_id'");
}

// 3. Fetch User Profile Info, Theme, Sticker, and Bio
$user_query = "SELECT username, card_theme, sticker_id, bio FROM users WHERE id='$user_id'";
$user_res = mysqli_query($conn, $user_query);
$user_row = mysqli_fetch_assoc($user_res);
$username = $user_row['username'];
$card_theme = $user_row['card_theme'] ?: 'blue';
$sticker_id = $user_row['sticker_id'] ?: 0;
$bio = $user_row['bio'] ?: '';

// 4. Fetch the species name of your active sticker for the search input pre-population
$sticker_name = '';
if ($sticker_id > 0) {
    $sticker_query = mysqli_query($conn, "SELECT name FROM pokemon_dex WHERE id='$sticker_id'");
    if ($st_row = mysqli_fetch_assoc($sticker_query)) {
        $sticker_name = $st_row['name'];
    }
}

// 5. Count how many Pokémon this trainer has caught
$count_sql = "SELECT COUNT(*) AS total FROM pokemon WHERE user_id='$user_id'";
$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total_pokemon = $count_row['total'];

$trainer_id = str_pad($user_id * 143, 5, "0", STR_PAD_LEFT); 
$started_date = date("M. d, Y");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Trainer Card</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php $active_page = basename($_SERVER['PHP_SELF']); ?>
<div class="navbar">
    <h1>PokéTracker</h1>
    <div class="nav-links">
        <a href="index.php" class="<?php echo $active_page == 'index.php' ? 'active' : ''; ?>">Home</a>
        <a href="dashboard.php" class="<?php echo $active_page == 'dashboard.php' ? 'active' : ''; ?>">Trainer Card</a>
        <a href="collection.php" class="<?php echo $active_page == 'collection.php' ? 'active' : ''; ?>">My PC Box</a>
        <a href="add-pokemon.php" class="<?php echo $active_page == 'add-pokemon.php' ? 'active' : ''; ?>">Add PKMN</a>
        <a href="leaderboard.php" class="<?php echo $active_page == 'leaderboard.php' ? 'active' : ''; ?>">Leaderboard</a>
        <a href="auth/logout.php" onclick="return confirm('Logout?')">Logout</a>
    </div>
</div>

<div class="container">
    <h2 style="text-align: center; text-transform: uppercase; margin: 40px 0 20px 0; text-shadow: 2px 2px 0 var(--gba-text-shadow);">My Trainer Card</h2>

    <!-- Trainer Card -->
    <div class="trainer-card theme-<?php echo $card_theme; ?>">
        <div class="tc-header">
            <span class="tc-title">TRAINER CARD</span>
        </div>
        
        <div class="tc-body">
            <div class="tc-row">
                <span class="tc-label">Name</span>
                <span class="tc-value tc-name"><?php echo htmlspecialchars($username); ?></span>
            </div>
            <div class="tc-row">
                <span class="tc-label">ID No.</span>
                <span class="tc-value"><?php echo $trainer_id; ?></span>
            </div>
            <div class="tc-row">
                <span class="tc-label">Pokédex</span>
                <span class="tc-value"><?php echo $total_pokemon; ?></span>
            </div>
            <div class="tc-row" style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;">
                <span class="tc-label">Started</span>
                <span class="tc-value"><?php echo $started_date; ?></span>
            </div>

            <!-- Polished GBA Signature Plaque Box -->
            <div class="tc-bio-row">
                <span class="tc-bio-label">Bio:</span>
                <span class="tc-bio-text"><?php echo htmlspecialchars($bio ?: 'None'); ?></span>
            </div>

            <!-- Custom Sticker -->
            <?php if ($sticker_id > 0) { ?>
                <img src="assets/sprites/<?php echo $sticker_id; ?>.png" class="trainer-sprite" alt="Sticker">
            <?php } ?>
        </div>
    </div>

    <!-- Toggle Button for Customization Settings -->
    <div class="stats-toggle-btn" id="btn-toggle-settings" onclick="toggleSettings()" style="max-width:320px; margin:24px auto 0 auto;">CUSTOMIZE PROFILE ▼</div>

    <!-- Collapsible Settings Container -->
    <div id="settings-container" style="display:none;">
        <form method="POST" style="max-width: 500px; margin: 24px auto 0 auto; padding: 24px;">
            <h3 style="font-size:0.75rem; text-align:center; margin-top:0; text-transform:uppercase; border-bottom:2px dotted var(--dialogue-border-inner); padding-bottom:12px;">Customize Profile</h3>
            
            <label>Card Color Theme:</label>
            <select name="card_theme">
                <option value="blue" <?php if($card_theme == 'blue') echo 'selected'; ?>>Sapphire Blue</option>
                <option value="red" <?php if($card_theme == 'red') echo 'selected'; ?>>Ruby Red</option>
                <option value="green" <?php if($card_theme == 'green') echo 'selected'; ?>>Emerald Green</option>
                <option value="gold" <?php if($card_theme == 'gold') echo 'selected'; ?>>Gold</option>
            </select>

            <input type="hidden" name="sticker_id" id="sticker-id-input" value="<?php echo $sticker_id; ?>">
            <label>Card Sticker (Pokémon Sprite):</label>
            <div class="species-search-wrap">
                <input type="text" id="species-search" placeholder="Search Pokémon species..." autocomplete="off" value="<?php echo htmlspecialchars($sticker_name); ?>">
                <div id="species-dropdown"></div>
            </div>
            <div class="selected-species-row" id="selected-species-row" style="margin-bottom: 20px;">
                <img id="sprite-preview-img" src="" alt="">
                <span class="selected-species-name" id="selected-species-name"></span>
            </div>

            <label>Trainer Bio / Signature:</label>
            <input type="text" name="bio" value="<?php echo htmlspecialchars($bio); ?>" placeholder="Write your signature..." maxlength="150">

            <button type="submit" name="save_customization">SAVE PROFILE</button>
        </form>
    </div>
</div>

<script>
const dexData = <?php echo json_encode($dex_pokemon); ?>;
const searchInput  = document.getElementById('species-search');
const dropdown     = document.getElementById('species-dropdown');
const speciesIdInput = document.getElementById('sticker-id-input');
const spriteImg    = document.getElementById('sprite-preview-img');
const speciesLabel = document.getElementById('selected-species-name');
const selectedRow = document.getElementById('selected-species-row');

const initialId = speciesIdInput.value;
if (initialId > 0) {
    spriteImg.src = `assets/sprites/${initialId}.png`;
    speciesLabel.textContent = `#${String(initialId).padStart(4,'0')} ${searchInput.value.toUpperCase()}`;
    selectedRow.style.display = 'flex';
}

searchInput.addEventListener('input', function () {
    const query = this.value.trim().toLowerCase();
    dropdown.innerHTML = '';
    if (!query) { dropdown.style.display = 'none'; return; }

    const matches = dexData.filter(p =>
        p.name.toLowerCase().includes(query) ||
        (p.form && p.form.toLowerCase().includes(query))
    ).slice(0, 30);

    if (matches.length === 0) { dropdown.style.display = 'none'; return; }

    matches.forEach(p => {
        const div = document.createElement('div');
        div.className = 'species-option';
        const img = document.createElement('img');
        img.src = `assets/sprites/${p.id}.png`;
        img.alt = '';
        const txt = document.createElement('span');
        txt.textContent = p.form && p.form.trim() ? `#${p.id} ${p.name} (${p.form})` : `#${p.id} ${p.name}`;
        div.appendChild(img);
        div.appendChild(txt);
        div.addEventListener('click', () => selectSpecies(p));
        dropdown.appendChild(div);
    });
    dropdown.style.display = 'block';
});

function selectSpecies(p) {
    const label = p.form && p.form.trim() ? `${p.name} (${p.form})` : p.name;
    searchInput.value    = label;
    speciesIdInput.value = p.id;
    dropdown.style.display = 'none';

    spriteImg.src = `assets/sprites/${p.id}.png`;
    speciesLabel.textContent = `#${String(p.id).padStart(4,'0')} ${label.toUpperCase()}`;
    selectedRow.style.display = 'flex';
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.species-search-wrap')) {
        dropdown.style.display = 'none';
    }
});

function toggleSettings() {
    const container = document.getElementById('settings-container');
    const btn = document.getElementById('btn-toggle-settings');
    if (container.style.display === 'none') {
        container.style.display = 'block';
        btn.innerText = 'CUSTOMIZE PROFILE ▲';
    } else {
        container.style.display = 'none';
        btn.innerText = 'CUSTOMIZE PROFILE ▼';
    }
}
</script>
</body>
</html>