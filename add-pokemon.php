<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
include 'includes/connection.php';

$sys_message = "";
$msg_type = "";

$dex_result = mysqli_query($conn, "SELECT id, name, form FROM pokemon_dex ORDER BY id ASC");
$dex_pokemon = [];
while ($row = mysqli_fetch_assoc($dex_result)) { $dex_pokemon[] = $row; }

if (isset($_POST['add_pokemon'])) {
    $user_id = $_SESSION['user_id'];
    $nickname    = mysqli_real_escape_string($conn, trim($_POST['nickname']) ?: '');
    $level       = mysqli_real_escape_string($conn, trim($_POST['level']));
    $gender      = mysqli_real_escape_string($conn, trim($_POST['gender']));
    $species_id  = mysqli_real_escape_string($conn, trim($_POST['species_id']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']) ?: ''); 

    // Enforce GBA 12-Character Nickname limit on backend
    if (mb_strlen($nickname) > 12) {
        $sys_message = "Nickname cannot exceed 12 characters.";
        $msg_type = "error";
    } elseif ($level < 1 || $level > 100) {
        $sys_message = "Level must be between 1 and 100."; $msg_type = "error";
    } elseif (!in_array($gender, ['Male', 'Female'])) {
        $sys_message = "Invalid gender selected."; $msg_type = "error";
    } else {
        $images = ['', '', '', '', ''];
        $upload_error = false;
        $allowed_types = ['jpg', 'jpeg', 'png'];

        for ($i = 1; $i <= 5; $i++) {
            $file_key = 'image' . $i;
            if (!empty($_FILES[$file_key]['name'])) {
                if ($_FILES[$file_key]['size'] > 2000000) {
                    $sys_message = "Image $i size too large. 2MB max."; $msg_type = "error"; $upload_error = true; break;
                }
                $image_name = time() . "_" . $i . "_" . $_FILES[$file_key]['name'];
                $image_type = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

                if (!in_array($image_type, $allowed_types)) {
                    $sys_message = "Only JPG, JPEG, PNG allowed for Image $i."; $msg_type = "error"; $upload_error = true; break;
                }
                
                move_uploaded_file($_FILES[$file_key]['tmp_name'], "uploads/" . $image_name);
                $images[$i-1] = $image_name;
            }
        }

        if (!$upload_error && empty($images[0])) {
            $sys_message = "Main Thumbnail (Photo 1) is required."; $msg_type = "error";
        } elseif (!$upload_error) {
            $sql = "INSERT INTO pokemon (user_id, nickname, level, gender, species_id, upvotes, description, image1, image2, image3, image4, image5)
                    VALUES ('$user_id', '$nickname', '$level', '$gender', '$species_id', '0', '$description', '{$images[0]}', '{$images[1]}', '{$images[2]}', '{$images[3]}', '{$images[4]}')";

            if (mysqli_query($conn, $sql)) {
                $sys_message = "PKMN data registered to PC successfully!"; $msg_type = "success";
            } else {
                $sys_message = "System Error: " . mysqli_error($conn); $msg_type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Pokémon - PC System</title>
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
        <a href="leaderboard.php">Leaderboard</a>
        <a href="auth/logout.php" onclick="return confirm('Logout?')">Logout</a>
    </div>
</div>

<?php if ($sys_message != ""): ?>
    <div class="sys-message <?php echo $msg_type; ?>">▶ <?php echo $sys_message; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <h2 class="form-title">REGISTER PKMN</h2>

    <input type="hidden" name="species_id" id="species-id-input">

    <label>Species:</label>
    <div class="species-search-wrap">
        <input type="text" id="species-search" placeholder="Search Pokémon species..." autocomplete="off">
        <div id="species-dropdown"></div>
    </div>
    <div class="selected-species-row" id="selected-species-row">
        <img id="sprite-preview-img" src="" alt="">
        <span class="selected-species-name" id="selected-species-name"></span>
    </div>

    <!-- Added maxlength="12" to match classic Game Boy constraints -->
    <label>Nickname <span style="font-size:0.6rem; opacity:0.7; font-weight:normal;">(Max 12 Chars)</span>:</label>
    <input type="text" name="nickname" id="nickname-input" placeholder="Leave blank to use species name" maxlength="12">

    <label>Level (1-100):</label>
    <input type="number" name="level" min="1" max="100" placeholder="Lv." required>

    <label>Gender:</label>
    <select name="gender" required>
        <option value="">-- Select Gender --</option>
        <option value="Male">♂ Male</option>
        <option value="Female">♀ Female</option>
    </select>

    <label>Journal Entry <span style="font-size:0.6rem; opacity:0.7;">(Max 150 Chars)</span>:</label>
    <textarea name="description" placeholder="Write entry..." rows="3" maxlength="150"></textarea>

    <!-- 5 Photo Album Slots with JS Clear selection triggers -->
    <label>Photo Album (Up to 5 Photos):</label>
    
    <div class="upload-slot">
        <strong>Photo 1 (Main Thumbnail):</strong> 
        <input type="file" id="file-image1" name="image1" accept=".jpg,.jpeg,.png" required>
        <button type="button" class="btn-pc" style="padding:4px 8px; font-size:0.55rem; width:auto; margin-top:4px;" onclick="document.getElementById('file-image1').value=''">Clear selection</button>
    </div>
    
    <?php for($i=2; $i<=5; $i++): ?>
    <div class="upload-slot">
        Photo <?php echo $i; ?> (Optional): 
        <input type="file" id="file-image<?php echo $i; ?>" name="image<?php echo $i; ?>" accept=".jpg,.jpeg,.png">
        <button type="button" class="btn-pc" style="padding:4px 8px; font-size:0.55rem; width:auto; margin-top:4px;" onclick="document.getElementById('file-image<?php echo $i; ?>').value=''">Clear selection</button>
    </div>
    <?php endfor; ?>

    <button type="submit" name="add_pokemon">SAVE TO PC</button>
</form>

<script>
const dexData = <?php echo json_encode($dex_pokemon); ?>;
const searchInput  = document.getElementById('species-search');
const dropdown     = document.getElementById('species-dropdown');
const speciesIdInput = document.getElementById('species-id-input');
const spriteImg    = document.getElementById('sprite-preview-img');
const speciesLabel = document.getElementById('selected-species-name');

searchInput.addEventListener('input', function () {
    const query = this.value.trim().toLowerCase();
    dropdown.innerHTML = '';
    if (!query) { dropdown.style.display = 'none'; return; }
    const matches = dexData.filter(p => p.name.toLowerCase().includes(query) || (p.form && p.form.toLowerCase().includes(query))).slice(0, 30);
    if (matches.length === 0) { dropdown.style.display = 'none'; return; }

    matches.forEach(p => {
        const div = document.createElement('div');
        div.className = 'species-option';
        const img = document.createElement('img'); img.src = `assets/sprites/${p.id}.png`;
        const txt = document.createElement('span'); txt.textContent = p.form && p.form.trim() ? `#${p.id} ${p.name} (${p.form})` : `#${p.id} ${p.name}`;
        div.appendChild(img); div.appendChild(txt);
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
    document.getElementById('selected-species-row').style.display = 'flex';
}

document.addEventListener('click', e => { if (!e.target.closest('.species-search-wrap')) dropdown.style.display = 'none'; });
</script>
</body>
</html>