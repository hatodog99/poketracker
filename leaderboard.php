<?php
session_start();
include 'includes/connection.php';

$sql = "SELECT pokemon.*, users.username, 
        pokemon_dex.name AS species_name, pokemon_dex.sprite AS species_sprite,
        pokemon_dex.type1, pokemon_dex.type2,
        pokemon_dex.hp, pokemon_dex.attack, pokemon_dex.defense, 
        pokemon_dex.sp_attack, pokemon_dex.sp_defense, pokemon_dex.speed
        FROM pokemon
        JOIN users ON pokemon.user_id = users.id
        LEFT JOIN pokemon_dex ON pokemon.species_id = pokemon_dex.id AND (pokemon_dex.form = '' OR pokemon_dex.form IS NULL)
        WHERE upvotes > 0
        ORDER BY upvotes DESC
        LIMIT 10";

$result = mysqli_query($conn, $sql);

$type_colors = [
    'Normal' => '#A8A878', 'Fire' => '#F08030', 'Water' => '#6890F0', 'Electric' => '#F8D030',
    'Grass' => '#78C850', 'Ice' => '#98D8D8', 'Fighting' => '#C03028', 'Poison' => '#A040A0',
    'Ground' => '#E0C068', 'Flying' => '#A890F0', 'Psychic' => '#F85888', 'Bug' => '#A8B820',
    'Rock' => '#B8A038', 'Ghost' => '#705898', 'Dragon' => '#7038F8', 'Dark' => '#705848',
    'Steel' => '#B8B8D0', 'Fairy' => '#EE99AC'
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pokémon Hall of Fame</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="hof-page">

<div class="navbar">
    <h1>PokéTracker</h1>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])) { ?>
            <a href="dashboard.php">Trainer Card</a>
            <a href="collection.php">My PC Box</a>
            <a href="add-pokemon.php">Add PKMN</a>
            <a href="leaderboard.php">Leaderboard</a>
            <a href="auth/logout.php">Logout</a>
        <?php } else { ?>
            <a href="auth/login.php">Login</a>
            <a href="auth/register.php">Register</a>
        <?php } ?>
    </div>
</div>

<div class="container">

    <h1 class="hof-title">HALL OF FAME</h1>
    <p class="hof-subtitle">Top Pokémon ranked by community UPVOTES.</p>

    <div class="hof-list">
        <?php
        $rank = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            
            $rank_class = ($rank <= 3) ? "rank-" . $rank : "rank-other";
            $display_name = $row['nickname'] ? $row['nickname'] : $row['species_name'];
            $species_name = $row['species_name'] ? $row['species_name'] : 'Unknown';
            $gender_icon = $row['gender'] === 'Female' ? '♀' : '♂';
            $sprite_url = htmlspecialchars($row['species_sprite'] ?? '');
            
            $t1 = $row['type1'] ?? '';
            $t2 = $row['type2'] ?? '';

            $album_images = array_filter([$row['image1'], $row['image2'], $row['image3'], $row['image4'], $row['image5']]);
            $has_multiple_images = count($album_images) > 1;
        ?>

        <div class="hof-card rank-card-<?php echo $rank; ?>" 
             data-id="<?php echo $row['id']; ?>" 
             data-owner="<?php echo $row['user_id']; ?>" 
             data-img1="<?php echo htmlspecialchars($row['image1'] ?? ''); ?>"
             data-img2="<?php echo htmlspecialchars($row['image2'] ?? ''); ?>"
             data-img3="<?php echo htmlspecialchars($row['image3'] ?? ''); ?>"
             data-img4="<?php echo htmlspecialchars($row['image4'] ?? ''); ?>"
             data-img5="<?php echo htmlspecialchars($row['image5'] ?? ''); ?>"
             data-index="0"
             onclick="toggleHofCard(this, event)">
            
            <!-- HEADER WRAPPER: Contains Rank, Image, and Info -->
            <div class="hof-card-header">
                <div class="hof-rank <?php echo $rank_class; ?>">#<?php echo $rank; ?></div>
                
                <div class="hof-sprite-box">
                    <img src="uploads/<?php echo htmlspecialchars($row['image1']); ?>" alt="Portrait">
                </div>

                <div class="hof-details">
                    <h2><?php echo htmlspecialchars($display_name); ?> <?php echo $gender_icon; ?></h2>
                    
                    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
                        <div class="hof-species-tag" style="margin-bottom: 0;">
                            <?php if ($sprite_url) { ?>
                                <img src="<?php echo $sprite_url; ?>" alt="Sprite">
                            <?php } ?>
                            <span><?php echo htmlspecialchars($species_name); ?></span>
                        </div>

                        <div style="display: flex; gap: 6px; margin: 4px 0 10px 0;">
                            <?php if ($t1 && trim($t1) !== '' && strtolower($t1) !== 'null' && strtolower($t1) !== 'none') { ?>
                                <span class="type-badge" style="background-color: <?php echo $type_colors[$t1] ?? '#777'; ?>;"><?php echo $t1; ?></span>
                            <?php } ?>
                            <?php if ($t2 && trim($t2) !== '' && strtolower($t2) !== 'null' && strtolower($t2) !== 'none') { ?>
                                <span class="type-badge" style="background-color: <?php echo $type_colors[$t2] ?? '#777'; ?>;"><?php echo $t2; ?></span>
                            <?php } ?>
                        </div>
                    </div>

                    <p>Level: <?php echo htmlspecialchars($row['level']); ?></p>
                    <p class="hof-trainer" onclick="event.stopPropagation();">
                        Trainer: <a href="profile.php?username=<?php echo urlencode($row['username']); ?>" style="text-decoration: underline; color: var(--gba-blue);"><?php echo htmlspecialchars($row['username']); ?></a>
                    </p>
                </div>

                <!-- Upvote Badge moved into header -->
                <div class="hof-upvote-badge">▲ <?php echo $row['upvotes']; ?></div>
            </div>

            <!-- FULL WIDTH EXPANDABLE AREA -->
            <div class="hof-expandable">
                
                <div class="hof-section-divider"></div>

                <!-- 1. Description Box -->
                <?php if ($row['description'] && trim($row['description']) !== '') { ?>
                    <div class="hof-desc-box">
                        <?php echo htmlspecialchars($row['description']); ?>
                    </div>
                <?php } ?>

                <div class="hof-section-divider" style="border-top:none; height:0;"></div>

                <!-- 2. Album Box -->
                <div class="hof-album-box" onclick="event.stopPropagation()">
                    <button class="carousel-btn carousel-prev" onclick="changeHofImage(this, -1, event)" style="<?php echo $has_multiple_images ? 'display:block' : 'display:none'; ?>">◀</button>
                    <img class="hof-carousel-img" src="uploads/<?php echo htmlspecialchars($row['image1']); ?>">
                    <button class="carousel-btn carousel-next" onclick="changeHofImage(this, 1, event)" style="<?php echo $has_multiple_images ? 'display:block' : 'display:none'; ?>">▶</button>
                    
                    <div class="carousel-dots" style="<?php echo $has_multiple_images ? 'display:flex' : 'display:none'; ?>">
                        <?php for($i=0; $i<count($album_images); $i++) { ?>
                            <div class="carousel-dot <?php echo $i===0 ? 'active' : ''; ?>"></div>
                        <?php } ?>
                    </div>
                </div>

                <!-- 3. Toggleable Stats -->
                <div class="hof-stats-toggle" onclick="toggleHofStats(this, event)">▶ SHOW BASE STATS</div>
                <div class="stats-grid" style="display:none;">
                    <div class="stat-inner-row"><span class="label">HP</span><span class="val"><?php echo htmlspecialchars($row['hp'] ?? '0'); ?></span></div>
                    <div class="stat-inner-row"><span class="label">ATK</span><span class="val"><?php echo htmlspecialchars($row['attack'] ?? '0'); ?></span></div>
                    <div class="stat-inner-row"><span class="label">DEF</span><span class="val"><?php echo htmlspecialchars($row['defense'] ?? '0'); ?></span></div>
                    <div class="stat-inner-row"><span class="label">SPA</span><span class="val"><?php echo htmlspecialchars($row['sp_attack'] ?? '0'); ?></span></div>
                    <div class="stat-inner-row"><span class="label">SPD</span><span class="val"><?php echo htmlspecialchars($row['sp_defense'] ?? '0'); ?></span></div>
                    <div class="stat-inner-row"><span class="label">SPE</span><span class="val"><?php echo htmlspecialchars($row['speed'] ?? '0'); ?></span></div>
                </div>

                <div class="hof-section-divider"></div>

                <!-- 4. Comments Section -->
                <div class="comments-section" style="margin-top: 0; border-top: none; padding-top: 0;" onclick="event.stopPropagation()">
                    <h4 class="comments-header">COMMENTS:</h4>
                    <div id="comments-list-<?php echo $row['id']; ?>" class="comments-list" style="max-height: 120px;"></div>
                    
                    <?php if (isset($_SESSION['user_id'])) { ?>
                        <div class="comment-input-row">
                            <input type="text" id="new-comment-text-<?php echo $row['id']; ?>" placeholder="Write a comment..." maxlength="100">
                            <button onclick="postComment(<?php echo $row['id']; ?>, <?php echo $row['user_id']; ?>)">POST</button>
                        </div>
                    <?php } else { ?>
                        <div style="font-size:0.55rem; color:#888; text-align:center;">Log in to post comments.</div>
                    <?php } ?>
                </div>

            </div>

            <span class="hof-toggle-arrow">▼ details</span>
        </div>

        <?php $rank++; } ?>
    </div>

</div>

<!-- JavaScript -->
<script>
const sessionUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;

function toggleHofCard(card, event) {
    if (event && event.target.closest('.hof-expandable')) return;

    const isExpanded = card.classList.contains('expanded');
    const label = card.querySelector('.hof-toggle-arrow');
    const pokeId = card.getAttribute('data-id');
    const ownerId = card.getAttribute('data-owner');
    
    if (!isExpanded) {
        card.classList.add('expanded');
        label.innerText = '▲ hide';
        loadComments(pokeId, ownerId);
    } else {
        card.classList.remove('expanded');
        label.innerText = '▼ details';
    }
}

function toggleHofStats(btn, event) {
    event.stopPropagation();
    const statsGrid = btn.nextElementSibling;
    if(statsGrid.style.display === 'none') {
        statsGrid.style.display = 'grid';
        btn.innerText = '▼ HIDE BASE STATS';
    } else {
        statsGrid.style.display = 'none';
        btn.innerText = '▶ SHOW BASE STATS';
    }
}

function changeHofImage(btn, direction, event) {
    event.stopPropagation();
    const card = btn.closest('.hof-card');
    let index = parseInt(card.getAttribute('data-index') || '0');
    
    let album = [];
    for(let i=1; i<=5; i++) {
        let img = card.getAttribute('data-img' + i);
        if(img && img.trim() !== '') album.push(img);
    }
    
    if(album.length <= 1) return;
    
    index += direction;
    if(index < 0) index = album.length - 1;
    if(index >= album.length) index = 0;
    
    card.setAttribute('data-index', index);
    
    const imgEl = card.querySelector('.hof-carousel-img');
    imgEl.classList.remove('slide-in-right', 'slide-in-left');
    void imgEl.offsetWidth;
    if(direction > 0) imgEl.classList.add('slide-in-right');
    else imgEl.classList.add('slide-in-left');
    
    imgEl.src = 'uploads/' + album[index];
    
    const dots = card.querySelectorAll('.carousel-dot');
    dots.forEach((dot, i) => {
        if(i < album.length) {
            dot.className = (i === index) ? 'carousel-dot active' : 'carousel-dot';
        }
    });
}

function loadComments(pokemonId, ownerId) {
    const list = document.getElementById('comments-list-' + pokemonId);
    if(!list) return;
    list.innerHTML = '<div style="text-align:center; color:#888;">Loading...</div>';

    fetch(`api_comments.php?action=get&pokemon_id=${pokemonId}`)
    .then(res => res.json())
    .then(data => {
        list.innerHTML = '';
        if (data.comments.length === 0) {
            list.innerHTML = '<div style="text-align:center; color:#a0a0a0; padding:10px 0;">No comments yet.</div>';
            return;
        }

        data.comments.forEach(c => {
            let deleteBtn = '';
            if (sessionUserId === c.user_id || sessionUserId == ownerId) {
                deleteBtn = `<span class="comment-delete" onclick="deleteComment(${c.id}, ${pokemonId}, ${ownerId})">✖</span>`;
            }

            list.innerHTML += `
                <div class="comment-item">
                    ${deleteBtn}
                    <span class="comment-author">
                        <!-- Clickable link to commentator's public profile -->
                        <a href="profile.php?username=${encodeURIComponent(c.username)}">${c.username}</a>:
                    </span>
                    <span class="comment-text">${c.text}</span>
                </div>
            `;
        });
        list.scrollTop = list.scrollHeight;
    });
}

function postComment(pokemonId, ownerId) {
    const input = document.getElementById('new-comment-text-' + pokemonId);
    const text = input.value.trim();
    if (text === '') return;

    const formData = new FormData();
    formData.append('action', 'post');
    formData.append('pokemon_id', pokemonId);
    formData.append('text', text);

    fetch('api_comments.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadComments(pokemonId, ownerId);
        } else {
            alert(data.error);
        }
    });
}

function deleteComment(commentId, pokemonId, ownerId) {
    if (!confirm('Delete this comment?')) return;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('comment_id', commentId);

    fetch('api_comments.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) { loadComments(pokemonId, ownerId); }
    });
}
</script>

</body>
</html>