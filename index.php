<?php
session_start();
include 'includes/connection.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$search  = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$sort    = isset($_GET['sort']) ? $_GET['sort'] : "recent";

// 1. Process dynamic sorting selection
$order_by = "pokemon.created_at DESC"; // Default
if ($sort === 'pokedex') {
    $order_by = "CAST(pokemon_dex.id AS UNSIGNED) ASC, pokemon.created_at DESC";
} elseif ($sort === 'level') {
    $order_by = "pokemon.level DESC, pokemon.created_at DESC";
} elseif ($sort === 'upvotes') {
    $order_by = "pokemon.upvotes DESC, pokemon.created_at DESC";
}

// 2. Updated SQL query to handle both searching and sorting
$sql = "SELECT pokemon.*, users.username, 
        pokemon_dex.id AS pokedex_number, pokemon_dex.name AS species_name, pokemon_dex.sprite AS species_sprite,
        pokemon_dex.type1, pokemon_dex.type2,
        pokemon_dex.hp, pokemon_dex.attack, pokemon_dex.defense, 
        pokemon_dex.sp_attack, pokemon_dex.sp_defense, pokemon_dex.speed,
        (SELECT COUNT(*) FROM pokemon_votes WHERE pokemon_votes.pokemon_id = pokemon.id AND pokemon_votes.user_id = '$user_id') AS has_voted
        FROM pokemon
        JOIN users ON pokemon.user_id = users.id
        LEFT JOIN pokemon_dex ON pokemon.species_id = pokemon_dex.id AND (pokemon_dex.form = '' OR pokemon_dex.form IS NULL)
        WHERE (pokemon.nickname LIKE '%$search%' OR pokemon_dex.name LIKE '%$search%')
        ORDER BY $order_by";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>PokéTracker - Community Box</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="index-page">

<?php $active_page = basename($_SERVER['PHP_SELF']); ?>
<div class="navbar">
    <h1>PokéTracker</h1>
    <div class="nav-links">
    <?php if (isset($_SESSION['user_id'])) { ?>
        <a href="index.php" class="<?php echo $active_page == 'index.php' ? 'active' : ''; ?>">Home</a>
        <a href="dashboard.php" class="<?php echo $active_page == 'dashboard.php' ? 'active' : ''; ?>">Trainer Card</a>
        <a href="collection.php" class="<?php echo $active_page == 'collection.php' ? 'active' : ''; ?>">My PC Box</a>
        <a href="add-pokemon.php" class="<?php echo $active_page == 'add-pokemon.php' ? 'active' : ''; ?>">Add PKMN</a>
        <a href="leaderboard.php" class="<?php echo $active_page == 'leaderboard.php' ? 'active' : ''; ?>">Leaderboard</a>
        <a href="auth/logout.php" onclick="return confirm('Logout?')">Logout</a>
    <?php } else { ?>
        <a href="index.php" class="<?php echo $active_page == 'index.php' ? 'active' : ''; ?>">Home</a>
        <a href="auth/login.php" class="<?php echo $active_page == 'login.php' ? 'active' : ''; ?>">Login</a>
        <a href="auth/register.php" class="<?php echo $active_page == 'register.php' ? 'active' : ''; ?>">Register</a>
    <?php } ?>
    </div>
</div>

<div class="container">
    <!-- NEW: Community Filter and Sort Form -->
    <form method="GET" class="pc-filter-form">
        <div class="filter-inputs">
            <input type="text" name="search" placeholder="Search PKMN or Species..." value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="sort" onchange="this.form.submit()">
                <option value="recent" <?php if($sort == 'recent') echo 'selected'; ?>>Sort: Recent</option>
                <option value="pokedex" <?php if($sort == 'pokedex') echo 'selected'; ?>>Sort: Pokédex No.</option>
                <option value="level" <?php if($sort == 'level') echo 'selected'; ?>>Sort: Level</option>
                <option value="upvotes" <?php if($sort == 'upvotes') echo 'selected'; ?>>Sort: Upvotes</option>
            </select>
            
            <button type="submit">SEARCH</button>
        </div>
        <div class="result-count">IN BOX: <?php echo mysqli_num_rows($result); ?></div>
    </form>

    <div class="pc-box">
        <div class="pc-info-panel" style="gap: 12px; overflow-y: auto; max-height: 600px;">
            <div class="panel-header">- PKMN DATA -</div>

            <!-- Carousel Box -->
            <div class="sprite-box" style="margin-bottom: 0;">
                <button class="carousel-btn carousel-prev" id="btn-prev-img" onclick="changeImage(-1)">◀</button>
                <img id="detail-thumbnail" class="carousel-img" src="" alt="Thumbnail" onclick="openLightbox()">
                <button class="carousel-btn carousel-next" id="btn-next-img" onclick="changeImage(1)">▶</button>
                
                <div class="carousel-dots" id="carousel-dots">
                    <div class="carousel-dot" id="dot-0"></div>
                    <div class="carousel-dot" id="dot-1"></div>
                    <div class="carousel-dot" id="dot-2"></div>
                    <div class="carousel-dot" id="dot-3"></div>
                    <div class="carousel-dot" id="dot-4"></div>
                </div>
            </div>

            <div class="info-content" id="detail-content" style="display:none; flex: none;">
                <div class="detail-header-row">
                    <img id="detail-sprite" class="detail-sprite-img" src="" alt="Sprite">
                    <div>
                        <h3 class="pkmn-name-row">
                            <span id="detail-nickname"></span> 
                            <span id="detail-gender"></span>
                        </h3>
                        <p class="pkmn-species-info">
                            <span id="detail-pokedex"></span> 
                            <span id="detail-species"></span>
                        </p>
                    </div>
                </div>
                
                <p id="detail-level" class="pkmn-level"></p>
                
                <div id="detail-types" class="detail-types-container"></div>

                <div class="stat-row"><span class="label">TRAINER</span><a id="detail-trainer" class="val" href="" style="text-decoration: underline; color: var(--gba-blue);"></a></div>
                <div class="stat-row"><span class="label">UPVOTES</span><span id="detail-upvotes" class="val"></span></div>

                <p id="detail-description" class="pkmn-description" style="display:none;"></p>

                <div class="stats-toggle-btn" id="btn-toggle-stats" onclick="toggleStats()">BASE STATS ▼</div>

                <div class="stats-grid" id="stats-container" style="display:none;">
                    <div class="stat-inner-row"><span class="label">HP</span><span id="stat-hp" class="val"></span></div>
                    <div class="stat-inner-row"><span class="label">ATK</span><span id="stat-atk" class="val"></span></div>
                    <div class="stat-inner-row"><span class="label">DEF</span><span id="stat-def" class="val"></span></div>
                    <div class="stat-inner-row"><span class="label">SPA</span><span id="stat-spa" class="val"></span></div>
                    <div class="stat-inner-row"><span class="label">SPD</span><span id="stat-spd" class="val"></span></div>
                    <div class="stat-inner-row"><span class="label">SPE</span><span id="stat-spe" class="val"></span></div>
                </div>

                <div class="action-buttons" style="margin-top: 14px;">
                    <a id="btn-upvote" href="#" class="btn-pc">▲ UPVOTE</a>
                </div>
            </div>

            <!-- COMMENTS DIALOGUE BOX -->
            <div class="info-content" id="detail-comments" style="display:none; flex: none; margin-top: 0;">
                <div class="comments-section" style="margin-top: 0; border-top: none; padding-top: 0;">
                    <h4 class="comments-header">COMMENTS:</h4>
                    <div id="comments-list" class="comments-list" style="max-height: 90px;"></div>
                    <?php if (isset($_SESSION['user_id'])) { ?>
                        <div class="comment-input-row">
                            <input type="text" id="new-comment-text" placeholder="Write a comment..." maxlength="100">
                            <button onclick="postComment()">POST</button>
                        </div>
                    <?php } else { ?>
                        <div style="font-size:0.55rem; color:#888; text-align:center;">Log in to post comments.</div>
                    <?php } ?>
                </div>
            </div>

            <div class="info-content empty-state" id="empty-state" style="flex: 1;">
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
                <?php while ($row = mysqli_fetch_assoc($result)) {
                    $gender_icon = $row['gender'] === 'Female' ? '♀' : '♂';
                    $gender_class = $row['gender'] === 'Female' ? 'gender-female' : 'gender-male';
                    $sprite_url = htmlspecialchars($row['species_sprite'] ?? '');
                    $pokedex_num = $row['pokedex_number'] ? 'No. ' . str_pad($row['pokedex_number'], 3, '0', STR_PAD_LEFT) : '';
                ?>
                    <div class="pc-pokemon-slot"
                        onclick="updateDetails(this)"
                        data-img1="<?php echo htmlspecialchars($row['image1'] ?? ''); ?>"
                        data-img2="<?php echo htmlspecialchars($row['image2'] ?? ''); ?>"
                        data-img3="<?php echo htmlspecialchars($row['image3'] ?? ''); ?>"
                        data-img4="<?php echo htmlspecialchars($row['image4'] ?? ''); ?>"
                        data-img5="<?php echo htmlspecialchars($row['image5'] ?? ''); ?>"
                        data-sprite="<?php echo $sprite_url; ?>"
                        data-nickname="<?php echo htmlspecialchars($row['nickname']); ?>"
                        data-species="<?php echo htmlspecialchars($row['species_name'] ?? '???'); ?>"
                        data-pokedex="<?php echo $pokedex_num; ?>"
                        data-level="Lv<?php echo htmlspecialchars($row['level']); ?>"
                        data-trainer="<?php echo htmlspecialchars($row['username']); ?>"
                        data-gendericon="<?php echo $gender_icon; ?>"
                        data-genderclass="<?php echo $gender_class; ?>"
                        data-type1="<?php echo htmlspecialchars($row['type1'] ?? ''); ?>"
                        data-type2="<?php echo htmlspecialchars($row['type2'] ?? ''); ?>"
                        data-hp="<?php echo htmlspecialchars($row['hp'] ?? '0'); ?>"
                        data-atk="<?php echo htmlspecialchars($row['attack'] ?? '0'); ?>"
                        data-def="<?php echo htmlspecialchars($row['defense'] ?? '0'); ?>"
                        data-spa="<?php echo htmlspecialchars($row['sp_attack'] ?? '0'); ?>"
                        data-spd="<?php echo htmlspecialchars($row['sp_defense'] ?? '0'); ?>"
                        data-spe="<?php echo htmlspecialchars($row['speed'] ?? '0'); ?>"
                        data-upvotes="▲ <?php echo htmlspecialchars($row['upvotes']); ?>"
                        data-voted="<?php echo $row['has_voted'] > 0 ? 'true' : 'false'; ?>"
                        data-description="<?php echo htmlspecialchars($row['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        data-id="<?php echo $row['id']; ?>">
                        <img src="uploads/<?php echo htmlspecialchars($row['image1']); ?>" alt="<?php echo htmlspecialchars($row['nickname']); ?>">
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div id="lightbox-modal" class="lightbox-modal" onclick="closeLightbox(event)">
    <span class="lightbox-close" onclick="closeLightbox(event)">&times;</span>
    <img id="lightbox-img" class="lightbox-content" src="">
</div>

<script>
const typeColors = {
    'Normal': '#A8A878', 'Fire': '#F08030', 'Water': '#6890F0', 'Electric': '#F8D030',
    'Grass': '#78C850', 'Ice': '#98D8D8', 'Fighting': '#C03028', 'Poison': '#A040A0',
    'Ground': '#E0C068', 'Flying': '#A890F0', 'Psychic': '#F85888', 'Bug': '#A8B820',
    'Rock': '#B8A038', 'Ghost': '#705898', 'Dragon': '#7038F8', 'Dark': '#705848',
    'Steel': '#B8B8D0', 'Fairy': '#EE99AC'
};

let activeDescription = "";
let currentAlbum = [];
let currentImageIndex = 0;
let activePokemonId = null;

function updateDetails(element) {
    document.querySelectorAll('.pc-pokemon-slot').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');

    activePokemonId = element.getAttribute('data-id');

    document.getElementById('empty-state').style.display = 'none';
    document.getElementById('detail-content').style.display = 'block';
    document.getElementById('detail-comments').style.display = 'block';

    const thumb = document.getElementById('detail-thumbnail');
    thumb.classList.remove('slide-in-right', 'slide-in-left');

    document.getElementById('stats-container').style.display = 'none';
    document.getElementById('btn-toggle-stats').innerText = 'BASE STATS ▼';

    const sprite = document.getElementById('detail-sprite');
    sprite.src = element.getAttribute('data-sprite');
    sprite.style.display = 'inline-block';
    
    currentAlbum = [];
    for(let i=1; i<=5; i++) {
        let imgUrl = element.getAttribute('data-img' + i);
        if(imgUrl && imgUrl.trim() !== '') {
            currentAlbum.push(imgUrl);
        }
    }
    currentImageIndex = 0;
    renderCarousel();
    
    const nickname = element.getAttribute('data-nickname');
    const species  = element.getAttribute('data-species');
    const pokedex  = element.getAttribute('data-pokedex');
    
    document.getElementById('detail-pokedex').innerText = pokedex;

    if (nickname) {
        document.getElementById('detail-nickname').innerText = nickname;
        document.getElementById('detail-species').innerText  = species;
    } else {
        document.getElementById('detail-nickname').innerText = species;
        document.getElementById('detail-species').innerText  = '';
    }
    
    const genderEl = document.getElementById('detail-gender');
    genderEl.innerText = element.getAttribute('data-gendericon');
    genderEl.className = element.getAttribute('data-genderclass');

    document.getElementById('detail-level').innerText     = element.getAttribute('data-level');
    document.getElementById('detail-upvotes').innerText   = element.getAttribute('data-upvotes');

    activeDescription = element.getAttribute('data-description') || "";
    renderDescription(activeDescription, true);

    const typesContainer = document.getElementById('detail-types');
    typesContainer.innerHTML = ''; 
    const type1 = element.getAttribute('data-type1');
    const type2 = element.getAttribute('data-type2');
    
    if (type1 && type1.trim() !== '' && type1.toLowerCase() !== 'null' && type1.toLowerCase() !== 'none') {
        typesContainer.innerHTML += `<span class="type-badge" style="background-color: ${typeColors[type1] || '#777'};">${type1}</span>`;
    }
    if (type2 && type2.trim() !== '' && type2.toLowerCase() !== 'null' && type2.toLowerCase() !== 'none') {
        typesContainer.innerHTML += `<span class="type-badge" style="background-color: ${typeColors[type2] || '#777'};">${type2}</span>`;
    }

    document.getElementById('stat-hp').innerText  = element.getAttribute('data-hp');
    document.getElementById('stat-atk').innerText = element.getAttribute('data-atk');
    document.getElementById('stat-def').innerText = element.getAttribute('data-def');
    document.getElementById('stat-spa').innerText = element.getAttribute('data-spa');
    document.getElementById('stat-spd').innerText = element.getAttribute('data-spd');
    document.getElementById('stat-spe').innerText = element.getAttribute('data-spe');

    // DYNAMIC UPVOTE BUTTON COLOR TOGGLE
    const voted = element.getAttribute('data-voted') === 'true';
    const upvoteBtn = document.getElementById('btn-upvote');
    upvoteBtn.setAttribute('data-id', element.getAttribute('data-id'));
    upvoteBtn.href = '#';
    
    if (voted) {
        upvoteBtn.innerText = '▼ REMOVE VOTE';
        upvoteBtn.className = 'btn-pc btn-danger';
    } else {
        upvoteBtn.innerText = '▲ UPVOTE';
        upvoteBtn.className = 'btn-pc';
    }

    // Dynamic profile link binding
    const trainerName = element.getAttribute('data-trainer');
    const trainerLink = document.getElementById('detail-trainer');
    trainerLink.innerText = trainerName;
    trainerLink.href = 'profile.php?username=' + encodeURIComponent(trainerName);

    loadComments();
}

// --- COMMENTS LOGIC ---

function loadComments() {
    const list = document.getElementById('comments-list');
    list.innerHTML = '<div style="text-align:center; color:#888;">Loading...</div>';

    fetch(`api_comments.php?action=get&pokemon_id=${activePokemonId}`)
    .then(res => res.json())
    .then(data => {
        list.innerHTML = '';
        if (data.comments.length === 0) {
            list.innerHTML = '<div style="text-align:center; color:#a0a0a0; padding:10px 0;">No comments yet.</div>';
            return;
        }

        data.comments.forEach(c => {
            list.innerHTML += `
                <div class="comment-item">
                    <span class="comment-author">
                        <a href="profile.php?username=${encodeURIComponent(c.username)}">${c.username}</a>:
                    </span>
                    <span class="comment-text">${c.text}</span>
                </div>
            `;
        });
        
        list.scrollTop = list.scrollHeight;
    });
}

function postComment() {
    const input = document.getElementById('new-comment-text');
    const text = input.value.trim();
    if (text === '') return;

    const formData = new FormData();
    formData.append('action', 'post');
    formData.append('pokemon_id', activePokemonId);
    formData.append('text', text);

    fetch('api_comments.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadComments();
        } else {
            alert(data.error);
        }
    });
}

// --- CAROUSEL & LIGHTBOX LOGIC ---

function renderCarousel() {
    const thumb = document.getElementById('detail-thumbnail');
    const prevBtn = document.getElementById('btn-prev-img');
    const nextBtn = document.getElementById('btn-next-img');
    
    if(currentAlbum.length === 0) {
        thumb.style.display = 'none'; prevBtn.style.display = 'none'; nextBtn.style.display = 'none'; return;
    }

    thumb.style.display = 'block';
    thumb.src = 'uploads/' + currentAlbum[currentImageIndex];

    for(let i=0; i<5; i++) {
        const dot = document.getElementById('dot-' + i);
        if(i < currentAlbum.length && currentAlbum.length > 1) {
            dot.style.display = 'block'; dot.className = (i === currentImageIndex) ? 'carousel-dot active' : 'carousel-dot';
        } else {
            dot.style.display = 'none';
        }
    }

    if(currentAlbum.length > 1) {
        prevBtn.style.display = 'block'; nextBtn.style.display = 'block';
    } else {
        prevBtn.style.display = 'none'; nextBtn.style.display = 'none';
    }
}

function changeImage(direction) {
    if(currentAlbum.length <= 1) return;
    currentImageIndex += direction;
    if(currentImageIndex < 0) currentImageIndex = currentAlbum.length - 1;
    if(currentImageIndex >= currentAlbum.length) currentImageIndex = 0;
    
    const thumb = document.getElementById('detail-thumbnail');
    thumb.classList.remove('slide-in-right', 'slide-in-left');
    void thumb.offsetWidth;
    if (direction > 0) thumb.classList.add('slide-in-right'); else thumb.classList.add('slide-in-left');

    renderCarousel();
}

function openLightbox() {
    if(currentAlbum.length === 0) return;
    document.getElementById('lightbox-img').src = 'uploads/' + currentAlbum[currentImageIndex];
    document.getElementById('lightbox-modal').style.display = 'flex';
}

function closeLightbox(event) {
    if(event.target.id === 'lightbox-modal' || event.target.className === 'lightbox-close') {
        document.getElementById('lightbox-modal').style.display = 'none';
    }
}

function renderDescription(text, truncate) {
    const descEl = document.getElementById('detail-description');
    if (!text || text.trim() === '') { descEl.style.display = 'none'; return; }
    descEl.style.display = 'block';
    const maxChars = 55; 
    
    if (truncate && text.length > maxChars) {
        const truncatedText = text.substring(0, maxChars) + '...';
        descEl.innerHTML = htmlEscape(truncatedText) + `<span class="desc-toggle-link" onclick="toggleFullDesc(true)">[More]</span>`;
    } else if (text.length > maxChars) {
        descEl.innerHTML = htmlEscape(text) + `<span class="desc-toggle-link" onclick="toggleFullDesc(false)">[Less]</span>`;
    } else {
        descEl.innerText = text;
    }
}

function toggleFullDesc(showAll) { renderDescription(activeDescription, !showAll); }

function htmlEscape(str) { return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }

function toggleStats() {
    const container = document.getElementById('stats-container');
    const btn = document.getElementById('btn-toggle-stats');
    if (container.style.display === 'none') { container.style.display = 'grid'; btn.innerText = 'BASE STATS ▲'; } 
    else { container.style.display = 'none'; btn.innerText = 'BASE STATS ▼'; }
}

document.getElementById('btn-upvote').addEventListener('click', function(e) {
    e.preventDefault(); 
    const pokeId = this.getAttribute('data-id');
    if(!pokeId) return;

    fetch('upvote.php?id=' + pokeId).then(r => r.json()).then(data => {
        if(data.success) {
            const txt = '▲ ' + data.new_upvotes;
            document.getElementById('detail-upvotes').innerText = txt;
            const slot = document.querySelector(`.pc-pokemon-slot[data-id="${pokeId}"]`);
            if (slot) {
                slot.setAttribute('data-upvotes', txt);
                if (data.action === 'added') {
                    this.innerText = '▼ REMOVE VOTE';
                    this.className = 'btn-pc btn-danger';
                    slot.setAttribute('data-voted', 'true');
                } else {
                    this.innerText = '▲ UPVOTE';
                    this.className = 'btn-pc';
                    slot.setAttribute('data-voted', 'false');
                }
            }
        } else if (data.error === 'Not logged in') window.location.href = 'auth/login.php'; 
    });
});
</script>
</body>
</html>