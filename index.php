<?php
  require 'player_card.php';
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

  $page;
  if(isset($_GET['page'])){
    $page = $_GET['page'];
    if($page == 'players'){//if this is the players screen will need to render the cards to be echoed later
      $card_string = render_cards();
    }else{ // if this is the timeline page need to display the row of games with their players and storytellers also need to set up datapoints variable to be used in graph 
      //
      //this will be an associative "x" to the day and "y" to the percent good win rate 
      $data_points1 = [];
      $good_wins = 0;
      $games_played = 0;
      $prev_day = '';
      $array_index = -1;

      foreach($GLOBALS['all_games'] as $idx => $game){
        $outcome = $game->good_won;
        $day = $game->date;
        $games_played++; // game has been played
        if($outcome) $good_wins++;
        $pct_win = ($good_wins / $games_played)*100;
        
        // if the previous day is the same as this day
        // need to overwrite the previous pct_win with a new one 
        if($day != $prev_day){
          $array_index++;
          $data_points1[$array_index] = array("x" => strtotime($day)* 1000, "y" => $pct_win); 
        }else{
          $data_points1[$array_index] = array("x" => strtotime($day)* 1000, "y" => $pct_win);
        }
        $prev_day = $day;
      }


      //this will be a second associative array with "x" associated with the date and
      //and "y" associated with the games played
      $data_points2 = [];
      $array_index = -1;
      $games_played = 0;
      $prev_day = '';
      foreach($GLOBALS['all_games'] as $idx => $game){
        
        $day = $game->date;
        $games_played++; // game has been played
        // if the previous day is the same as this day
        // need to overwrite the previous pct_win with a new one 
        if($day != $prev_day){
          $array_index++;
          $data_points2[$array_index] = array("x" => strtotime($day)* 1000, "y" => $games_played); 
        }else{
          $data_points2[$array_index] = array("x" => strtotime($day)* 1000, "y" => $games_played);
        }
        $prev_day = $day;
      }
    }
  }else{
    $page = 'players';
    $card_string = render_cards();
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
        if($card->games_played > 10){
          $players[$count] = $card;
          $count++;
        }
      }
      return $players;
    }

    function render_cards(){
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

      return $card_string;
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
  <form method='get'>
    <button class="nav-btn <?php if(isset($_GET['page'])){echo ($_GET['page'] == 'players') ? 'active' : '';}?>" value='players' name='page'>💀 Players</button>
    <button class="nav-btn <?php if(isset($_GET['page'])){echo ($_GET['page'] == 'timeline') ? 'active' : '';}?>" value='timeline' name='page'>📈 Timeline</button>
  </form>
</nav>
<?php if($page == 'players'):?>
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
            
            // function showPage(id){
            //   document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
            //   document.querySelectorAll('.nav-btn').forEach((b,i)=>b.classList.toggle('active',PAGE_KEYS[i]===id));
            //   $('page-'+id).classList.add('active');
            //   if(id==='timeline'&&!window._tlBuilt){window._tlBuilt=true;buildTLChart();renderTimeline();}
            // }
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

<?php elseif ($page == 'timeline'):?>
<!-- TIMELINE PAGE -->
<div id="page-timeline" class="page active">
  <div class="tlwrap">
    <div class="tl-chart-wrap">
       <div id="chart_container" style="height: 500px; width: 100%; display:block!important;"></div>
          <script>  
            window.addEventListener('load', () =>  {
                  
              var chart = new CanvasJS.Chart("chart_container", {
                animationEnabled: true,
                theme: "light3",
                title:{
                  text: "Good Win % And Games Played Over Time",
                  fontFamily: "Cinzel",
                  fontSize: 15
                },
                axisX:{
                  valueFormatString: "DD MMM",
                  titleFontFamily: "Cinzel",
                  titleFontSize: 15,
                  labelFontFamily: "Cinzel",
                  crosshair: {
                    enabled: true,
                    snapToDataPoint: true
                  }
                },
                axisY:{
                  title: "Win Rate",
                  titleFontFamily: "Cinzel",
                  titleFontSize: 15,
                  labelFontFamily: "Cinzel",
                  suffix: "%",
                  maximum: 100,
                  includeZero: true,
                  crosshair: {
                    enabled: true,
                    snapToDataPoint: true,
                    labelFontFamily: "Cinzel",
                    valueFormatString: "##.#'%'"
                  }
                },
                axisY2:{
                  title: "Games Played",
                  titleFontFamily: "Cinzel",
                  titleFontSize: 15,
                  labelFontFamily: "Cinzel",
                  crosshair: {
                    enabled: true,
                    snapToDataPoint: false,
                    labelFontFamily: "Cinzel",
                  }
                },
                toolTip:{
                  enabled: false
                },
                legend:{
                  cursor: "pointer",
                  fontFamily: "Cinzel",
                  dockInsidePlotArea: true,
                },
                data: [{
                  type: "area",
                  name: "Good Win Rate",
                  titleFontFamily: "Cinzel",
                  xValueType: "dateTime",
                  showInLegend: true,
                  dataPoints: <?php echo json_encode($data_points1, JSON_NUMERIC_CHECK); ?>
                },{
                  type: "line",
                  axisYType: "secondary",
                  name: "Games Played",
                  titleFontFamily: "Cinzel",
                  xValueType: "dateTime",
                  markerSize: 0,
                  showInLegend: true,
                  dataPoints: <?php echo json_encode($data_points2, JSON_NUMERIC_CHECK); ?>
                }]
              });
            chart.render();
          });
          </script>
     
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
<?php endif;?>
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
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script> -->
<script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
<!-- <script type="module" src="./api.js"></script>
<script type="module" src="./app.js"></script> -->
</body>
</html>
