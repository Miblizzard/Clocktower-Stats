<?php


    function set_sort($array, $string, $sort_type){
  

        if($string == null){
            $var_to_card = [];
            foreach($array as $index => $card){ // need variables games_played, percent_games_won(), good_won / good_games, evil_won / evil_games, survived, dont know how to get demon_won and demon_played in O(1) time
                
                switch($sort_type){
                    case 'games': 
                        $var = $card->games_played;
                        break;
                    case 'overall':
                        $var = ($card->games_played == 0) ? 0 : number_format($card->percent_games_won(), 0);
                        break;
                    case 'good':
                        $var = ($card->games_won == 0) ? 0 : number_format(($card->good_won / $card->games_won)*100, 0);
                        break;
                    case 'evil':
                        $var = ($card->games_won == 0) ? 0 : number_format(($card->evil_won / $card->games_won)*100, 0);
                        break;
                    case 'surv':
                        $var = ($card->games_played == 0) ? 0 : number_format(($card->survived / $card->games_played)*100, 0);
                        break;
                    // case 'demon':
                    //     $var = $card->games_played;
                    //     break;
                    default: 
                        $var = $card->games_played;
                        break;
                }
                
                if(isset($var_to_card[$var])) { // if there is already a player with that same number then increase the number by one and add them in
                    $var_to_card[$var+1] = $card;
                }else{
                    $var_to_card[$var] = $card; // creates an associative array with the specified variable to the card
                } 
                
            }   
        }else{ //if the string in the searchbar is not null will need to check if any part of the given string matches 
            $var_to_card = [];
            foreach($array as $index => $card){ // need variables games_played, percent_games_won(), good_won / good_games, evil_won / evil_games, survived, dont know how to get demon_won and demon_played in O(1) time
                if(str_contains(strtolower($card->name), strtolower($string))){
                    $var = $card->games_played;
                    switch($sort_type){
                        case 'games': 
                            $var = $card->games_played;
                            break;
                        case 'overall':
                            $var = ($card->games_played == 0) ? 0 : number_format($card->percent_games_won(), 0);
                            break;
                        case 'good':
                            $var = ($card->games_won == 0) ? 0 : number_format(($card->good_won / $card->games_won)*100, 0);
                            break;
                        case 'evil':
                            $var = ($card->games_won == 0) ? 0 : number_format(($card->evil_won / $card->games_won)*100, 0);
                            break;
                        case 'surv':
                            $var = ($card->games_played == 0) ? 0 : number_format(($card->survived / $card->games_played)*100, 0);
                            break;
                        // case 'demon':
                        //     $var = $card->games_played;
                        //     break;
                        default: 
                            $var = $card->games_played;
                            break;
                    }

                    if(isset($var_to_card[$var])) { // if there is already a player with that same number then increase the number by one and add them in
                        $var_to_card[$var+1] = $card;
                    }else{
                        $var_to_card[$var] = $card; // creates an associative array with the specified variable to the card
                    } 
                }
            }

        }

        if($var_to_card == null) return null;

        

        krsort($var_to_card); // sorts the array of variables by their keys which is the specified variable

        

        return $var_to_card;
    }

function mbar($label, $color, $pct){
  $fill= $pct == null ? 0: $pct;
  $txt= $pct == null ? '—' : $pct .'%';
  return '<div class="brow" style="margin-bottom:5px"><div class="blbl">'.$label.'</div><div class="btrack" style="height:8px"><div class="bfill" style="width:'.$fill.'%;background:'. $color .'"></div></div><div class="bpct" style="color:'.$color.'">'.$txt.'</div></div>';
}

if(isset($_GET['q']) && $_GET['q'] != ''){
    require 'player_card.php';
    $sheet_data = get_data();
    $sheet = json_decode($sheet_data, true);
    format_data($sheet);
    // foreach($GLOBALS['players'] as $key => $value){
    //     echo $key;
    // }
    
    $string = show_full_card($GLOBALS['players'][$_GET['q']]);
    echo $string;
}



function show_full_card($card){
  
  
  
  //both arrays are [townsfolk, outsider, traveler, demon, minion, unknown]
    $won_by_role = $card->num_won_by_role();
    $played_by_role = $card->num_played_by_role();

    $towns = $played_by_role[0] == 0 ? '—' : number_format(($won_by_role[0]/$played_by_role[0])*100, 0);
    $outsiders = $played_by_role[1] == 0 ? '—' : number_format(($won_by_role[1]/$played_by_role[1])*100, 0);
    $travelers = $played_by_role[2] == 0 ? '—' : number_format(($won_by_role[2]/$played_by_role[2])*100, 0);
    $demons = $played_by_role[3] == 0 ? '—' : number_format(($won_by_role[3] / $played_by_role[3])*100, 0);
    $minions = $played_by_role[4] == 0 ? '—' : number_format(($won_by_role[4] / $played_by_role[4])*100, 0);

    // in the form [alive+good, alive+evil, dead+good, dead+evil]
    $survival = $card->survival();

    $good = $card->good_games == 0 ? 0 : number_format(($survival[0] / $card->good_games)*100, 0);
    
    $evil = $card->evil_games == 0 ? 0 : number_format(($survival[1] / $card->evil_games)*100, 0);

    $survived = $card->games_played == 0 ? '—' : number_format((($survival[0]+$survival[1]) / $card->games_played)*100, 0); 

    // in the form [sml, sml_w, med, med_w, larg, larg_w]
    $size = $card->game_size();

    $sml_pct = $size[0] == 0 ? '—' : number_format(($size[1] / $size[0])*100, 0);
    $med_pct = $size[2] == 0 ? '—' : number_format(($size[3] / $size[2])*100, 0);
    $larg_pct = $size[4] == 0 ? '—' : number_format(($size[5] / $size[4])*100, 0);

    $percent_evil = $card->evil_games == 0 ? '—' : number_format($card->percent_evil_won(), 0);
    $percent_good = $card->good_games == 0 ? '—' : number_format($card->percent_good_won(), 0);

    $storyteller_pct = $card->storyteller_games == 0 ? '—' : number_format(($card->storyteller_good / $card->storyteller_games)*100, 0);

    $good_won = 0;
    foreach($GLOBALS['all_games'] as $idx => $game){
      if(($game->good_won)){
        $good_won++;
      }
    }

    $evil_won = 0;
    foreach($GLOBALS['all_games'] as $idx => $game){
      if((!$game->good_won)){
        $evil_won++;
      }
    }

  // Build the per-game history list shown at the bottom of the modal.
  $hist = history($card);

  $stBlock = $card->storyteller_games > 0?'
    <div class="msec">Storyteller Record</div>
    <div class="mbox" style="background:linear-gradient(135deg,rgba(127,119,221,.08),rgba(201,147,58,.06));border-color:rgba(127,119,221,.3)">
      <div class="mbox-l" style="color:#7F77DD">Games Run as Storyteller</div>
      <div class="mbox-v" style="font-size:18px">'. $card->storyteller_games .' games — <span style="color:#1a4a8b">'. $storyteller_pct .'% Good win rate</span></div>
      <div class="mbox-s">'. $good_won .' Good wins / '. $evil_won .' Evil wins across the year · '. number_format(($card->storyteller_games / $GLOBALS['num_games'])*100, 0) .'% of all games run by you</div>
    </div>': '';

  //var_dump($card);
  $card_body='
    
    
    <div class="m2col">
       <div class="mbox"><div class="mbox-l">Overall Win Rate</div><div class="mbox-v" style="color:'. choose_color($card->color) .'">'. number_format($card->percent_games_won(), 0) .'%</div><div class="mbox-s">'. $card->games_won .'W / '. ($card->games_played - $card->games_won) .'L from '. $card->games_played .' games</div></div>
       <div class="mbox"><div class="mbox-l">Survival Rate</div><div class="mbox-v" style="color:'. choose_color('good') .'">'. $survived .'%</div><div class="mbox-s">'. ($survival[0] + $survival[1]) .' alive at EOG / '. ($card->evil_games+$card->good_games) .' tracked</div></div>
       <div class="mbox"><div class="mbox-l">As Good</div><div class="mbox-v" style="color:'. choose_color('good') .'">'. $percent_good .'%</div><div class="mbox-s">'. $card->good_won .'/'. $card->good_games .' games</div></div>
      <div class="mbox"><div class="mbox-l">As Evil</div><div class="mbox-v" style="color:'. choose_color('evil') .'">'. $percent_evil .'%</div><div class="mbox-s">'. $card->evil_won .'/'. $card->evil_games .' games</div></div>
      <div class="mbox"><div class="mbox-l">Demon</div><div class="mbox-v" style="color:'. choose_color('demon') .'">'. $demons .'%</div><div class="mbox-s">'. $won_by_role[3] .'/'. $played_by_role[3] .' games</div></div>
      <div class="mbox"><div class="mbox-l">Minion</div><div class="mbox-v" style="color:'. choose_color('minion') .'">'. $minions .'%</div><div class="mbox-s">'. $won_by_role[4] .'/'. $played_by_role[4] .' games</div></div>
      <div class="mbox"><div class="mbox-l">Best Win Streak</div><div class="mbox-v" style="color:#c9933a">'. $card->hws .'</div><div class="mbox-s">consecutive wins</div></div>
      <div class="mbox"><div class="mbox-l">Worst Loss Streak</div><div class="mbox-v" style="color:#8b1a1a">'. $card->hls .'</div><div class="mbox-s">consecutive losses</div></div>
    </div>

    <div class="msec">Win % By Role Type</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>'. mbar('Townsfolk', choose_color('good'), $towns).''. mbar('Outsider',choose_color('b-blue'), $outsiders).''. ($travelers == 0? mbar('Traveler',choose_color('b-grey'), $travelers):'') .'</div>
      <div>'. mbar('Demon', choose_color('demon'), $demons).''. mbar('Minion', choose_color('minion'), $minions) .'</div>
    </div>

    <div class="msec">Survival Outcomes</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>'. mbar('Alive + Good', choose_color('b-gold'), $good).''.mbar('Dead + Good', choose_color('b-you'), 100 - $good).'</div>
      <div>'. mbar('Alive + Evil', choose_color('b-blood'), $evil).''.mbar('Dead + Evil', choose_color('b-teal'), 100 - $evil) .'</div>
    </div>

    <div class="msec">By Game Size</div>'.
    mbar('Small (≤9 players)', choose_color('b-gold'), $sml_pct).''.
    mbar('Mid (10–11 players)',choose_color('evil'), $med_pct).''.
    mbar('Large (≥12 players)', choose_color('b-pink'), $larg_pct)
    .'<!--<div class="msec">Script & Role Notes</div>
     <div class="m2col">
      <div class="mbox"><div class="mbox-l">Best Script</div><div class="mbox-s" style="font-size:13px;color:var(--ink);margin-top:0">${pl.bs}</div></div>
      <div class="mbox"><div class="mbox-l">Worst Script</div><div class="mbox-s" style="font-size:13px;color:var(--ink);margin-top:0">${pl.ws2}</div></div>
      <div class="mbox mstat-full"><div class="mbox-l">Most-Played Role</div><div class="mbox-s" style="font-size:13px;color:var(--ink);margin-top:0">${pl.tr} · ${pl.roles} unique roles across ${pl.scripts} different scripts</div></div> 
    </div>

    <div class="ubox" style="margin-top:0"><div class="ulbl">✦ Unique Defining Stat</div><div class="utxt">${pl.unique}</div></div> -->
    '.

    $stBlock

    .'<div class="msec">Game-by-Game History</div>
    <div style="margin-bottom:6px;font-size:11px;color:var(--stone);font-style:italic">● = alive at EOG &nbsp; ○ = dead at EOG &nbsp; Running win % shown on right</div>
    <div class="game-hist">'. $hist .'</div>
    ';

    return $card_body;
  
}

function history($card){
    if($card->games_played == 0){
        return '<div style="padding:20px;text-align:center;color:var(--stone);font-style:italic">No game history found.</div>';
    }
    

    $hist_string = '';

    foreach($card->game_idxs as $game_idx){
        $game = $GLOBALS['all_games'][$game_idx];
        $this_player = $game->player[$card->name];
        $isWin = $this_player->team_won ? 'Win' : 'Loss';
        $isgood = $this_player->isgood ? 'Good' : 'Evil';
        $dot_color = ($isgood == 'Good')? '#1a6b5a' :  '#8b1a1a';
        $result_col = $isWin == 'Win'?($isgood == 'Good'?'#1a6b5a':'#8b1a1a'):'#7a6e5e';
        $alive = ($this_player->isalive) ? '● ':'○ ';

        $hist_string .= '<div class="gh-row">
      <div class="gh-num"></div>
      <div class="gh-dot" style="background:'. $dot_color .'"></div>
      <div class="gh-info">
        <div class="gh-role">'. $this_player->role_start .' <span style="font-size:9px;color:var(--stone);font-weight:400">('. $this_player->type .')</span></div>
        <div class="gh-sub">'. $game->script .' '. $game->player_num .'P · Date: '. $game->date .'</div>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <div class="gh-result" style="color:'. $result_col .'">'. $isWin .' <span style="color:'. ($isgood == 'Good'?'#1a6b5a':'#8b1a1a') .'">'. $isgood .'</span></div>
        <div class="gh-wr">'. $alive .'Was Alive EOG</div>
      </div>
    </div>';
    }
    return $hist_string;

}

?>