<?php
  require 'PlayerCard.php';
  require 'card_sort.php';
  /*
    the sheet_data variable now holds all of the data in the spreadsheet as a json file
    need to turn it into an array of strings via json_decode
  */
  $sheet_data = get_data();

  $sheet = json_decode($sheet_data, true);

  format_data($sheet);

  function get_games(){
    return $GLOBALS['num_games'];
  }

  function get_sessions(){
    return $GLOBALS['num_sessions'];
  }

  function get_num_players(){
    return $GLOBALS['num_players'];
  }

  function get_evil_wins(){
    $count = 0;
    foreach($GLOBALS['all_games'] as $idx => $game){
      if(!($game->good_won)){
        $count++;
      }
    }
    return $count;
  }

  function get_good_wins(){
    $count = 0;
    foreach($GLOBALS['all_games'] as $idx => $game){
      if(($game->good_won)){
        $count++;
      }
    }
    return $count;
  }

  function get_good_winrt(){
    return number_format((get_good_wins() / get_games() * 100), 0);
  }

  function get_evil_winrt(){
    return number_format((get_evil_wins() / get_games() * 100), 0);
  }

  $titlescreen = '<div class="hstat"><span class="hstat-v">'. get_games() .'</span><span class="hstat-l">Games Played</span></div>
      <div class="hstat"><span class="hstat-v">'. get_sessions() .'</span><span class="hstat-l">Sessions</span></div>
      <div class="hstat"><span class="hstat-v" style="color:#1a4a8b">'. get_good_winrt() .'%</span><span class="hstat-l">Good Win Rate '. get_good_wins() .'/'. get_games() .'</span></div>
      <div class="hstat"><span class="hstat-v" style="color:#c0392b">'. get_evil_winrt() .'%</span><span class="hstat-l">Evil Win Rate '. get_evil_wins() .'/'. get_games() .'</span></div>
      <div class="hstat"><span class="hstat-v">'. get_num_players() .'</span><span class="hstat-l">Unique Players</span></div>';
    
  
  function shown_players(){
    $players = [];
    $count = 0;
    foreach($GLOBALS['players'] as $name => $card){
      if($card->good_games > 10){
        $players[$count] = $card;
        $count++;
      }
    }
    return $players;
  }
  
  if(isset($_GET['sort-select'])){
    $sorted_card_array = set_sort(shown_players(), $_GET['search'], $_GET['sort-select']);
  }else{
    $sorted_card_array = set_sort(shown_players(), null, 'games');
  }

  $card_string = '';
  if($sorted_card_array != null){
    foreach($sorted_card_array as $var => $card){
      $card_string .= create_card($card);
    }
  }else{
    $card_string = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--stone);font-style:italic;font-size:18px">No players found.</div>';
  }

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blood on the Clocktower — 2025/26 Player Stats</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,300;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="./styles.css">
</head>
<body>

<header>
  <div class="hinner">
    <div class="heyebrow">2025 · 2026 School Year</div>
    <div class="htitle">Blood on the <span>Clocktower</span></div>
    <div class="hsub">Player Statistics & Year in Review</div>
    <div class="hstats">
    
    <?php

      echo $titlescreen;

    ?>

    </div>
    <div class="ornament"><span>🩸🕰️</span></div>
  </div>
</header>

<nav class="nav">
  <button class="nav-btn active" onclick="showPage('players')">💀 Players</button>
  <!-- <button class="nav-btn" onclick="showPage('timeline')">📈 Timeline</button>-->
</nav>

<!-- PLAYERS PAGE -->
<div id="page-players" class="page active">
  <form action="index.php" method="get">  
    <div class="controls">
      <div class="swrap">
        <span class="sico">⚔</span>
        <input class="sinput" type="text" name="search" placeholder="Search by name…">
      </div>
      <!-- <span class="flbl">Sort</span> -->
      <div class="sort-select-wrap">
        <select name="sort-select" id="cars">
          <option value='games' <?php if(isset($_GET['sort-select'])){echo ($_GET['sort-select'] == 'games') ? 'selected' : '';}?>>Games</option>
          <option value='overall' <?php if(isset($_GET['sort-select'])) echo ($_GET['sort-select'] == 'overall') ? 'selected' : '';?>>Win %</option>
          <option value='good' <?php if(isset($_GET['sort-select'])) echo ($_GET['sort-select'] == 'good') ? 'selected' : '';?>>Good</option>
          <option value='evil' <?php if(isset($_GET['sort-select'])) echo ($_GET['sort-select'] == 'evil') ? 'selected' : '';?>>Evil</option>
          <option value='surv' <?php if(isset($_GET['sort-select'])) echo ($_GET['sort-select'] == 'surv') ? 'selected' : '';?>>Survival</option>
        </select>
        <button class="sbtn">Search</button>
        <!-- <button class="sbtn" value='demon' name="sort_button">Demon</button> -->
      </div>
    <script>
            function openModal(name){
              
              if(name.length == 0){
                console.log("stupid fart");
                return;
              }else{
                
                var xml = new XMLHttpRequest();
                xml.onreadystatechange = function(){
                  if(this.status == 200){
                    document.getElementById('mbnr').className='mbnr '+ name;
                    document.getElementById('mname').innerHTML= name;
                    document.getElementById('mbody').innerHTML = this.responseText;
                    document.getElementById('modal').classList.add('open');
                    document.body.style.overflow='hidden';
                  }
                
                }

                xml.open("GET", "card_sort.php?q="+name);
                xml.send();
                
              }
              
            }
            function closeModal(e){if(e.target===document.getElementById('modal'))closeModalDirect();}
            function closeModalDirect(){document.getElementById('modal').classList.remove('open');document.body.style.overflow='';}
            document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModalDirect();});
    </script>        

    </div>
  </form>
  <div class="gwrap">
    <div class="rcount" id="rcount"></div>
    <div class="cgrid" id="cgrid">
      <?php
        echo $card_string;
      ?>
    </div>
  </div>
</div>


<!-- TIMELINE PAGE -->
<div id="page-timeline" class="page">
  <div class="tlwrap">
    <div class="tl-chart-wrap">
      <div class="tl-chart-title">Good Win Rate — Rolling 10-Game Average</div>
      <canvas id="tl-chart" height="90"></canvas>
    </div>
    <div class="tl-controls">
      <span class="flbl">Filter</span>
      <button class="tl-filter active" onclick="setTLFilter('all')">All Games</button>
      <button class="tl-filter" onclick="setTLFilter('good')">Good Wins</button>
      <button class="tl-filter" onclick="setTLFilter('evil')">Evil Wins</button>
      <button class="tl-filter" onclick="setTLFilter('notable')">Notable Only</button>
    </div>
    <div class="tl-list" id="tl-list"></div>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal" onclick="closeModal(event)">
  <div class="modal" id="modal-inner">
    <div class="mhead">
      <div class="mbnr" id="mbnr"></div>
      <button class="mclose" onclick="closeModalDirect()">✕</button>
      <div class="mname" id="mname"></div>
      <div class="mmeta" id="mmeta"></div>
    </div>
    <div class="mbody" id="mbody"></div>
  </div>
</div>

<footer>Built with ♥ by <span>Anakyn</span> & <span>Lucas (3rd Year)</span> — Storyteller, Stats Keeper & Town Architect &nbsp;·&nbsp; Nov 2025 – Apr 2026</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<!-- <script type="module" src="./api.js"></script>
<script type="module" src="./app.js"></script> -->
</body>
</html>
