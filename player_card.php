  <?php
  require 'api.php';

   /*
    going to hold all of the games in an associative array (hashmap) where the key is the index and the value is the game class 
  */
  $all_games = [];
  $num_games = 0;
  $num_sessions = 1; 

  /*
    holds all the playercards created in associative array where the key is the name and the value is the card
  */
  $players = [];
  $num_players = 0; 

  /**
   * random color is picked from these for each player
   */
  $colors = ['b-gold', 'b-pink', 'b-teal', 'b-purple', 'b-blue', 'b-gray', 'b-blood', 'b-you'];
  $colors_available = 7;

    class Player{
        public $name;
        public $isgood;
        public $role_start;
        public $role_end;
        public $type;
        public $isalive = false;
        public $team_won;

        public function set_details($name, $isgood, $type, $role_start, $role_end, $isalive, $team_won){
            $this->name = $name;
            $this->isgood = $isgood;
            $this->role_start = $role_start;
            $this->role_end = $role_end;
            $this->type = $type;
            $this->isalive = $isalive;
            $this->team_won = $team_won;
        }

        
    }

    class PlayerCard {
        public $name;
        public $color;
        public $games_won = 0;
        public $good_won = 0;
        public $hws = 0;
        public $hls = 0;
        public $evil_won = 0;
        public $win_streak = 0;
        public $loss_streak = 0;
        public $evil_games = 0;
        public $good_games = 0;
        public $games_played = 0;
        public $survived = 0;
        public $game_idxs = [];
        public $roles = [];
        public $unique_role_count;

        public $storyteller_games = 0;
        public $storyteller_good = 0;

        public function set_name($name){
             $this->name = $name;
        }

        public function set_details($name, $game_idx){

            $this->game_idxs[$this->games_played] = $game_idx; // is an array of indexes that is each associated with a game 
            $this->games_played++;

            $this->name = $name;
        }

        public function set_highest_win_streak($streak){
            
            if($this->hws < $streak){
                $this->hws = $streak;
            }

        }

        public function set_highest_loss_streak($streak){
            if($this->hls < $streak){
                $this->hls = $streak;
            }
        }

        public function add_evil(){
            $this->evil_games++;
        }

        public function add_good(){
            $this->good_games++;
        }

        public function add_survived(){
            $this->survived++;
        }
        public function add_evil_won(){
            $this->evil_won++;
            $this->games_won++;
        }

        public function add_good_won(){
            $this->good_won++;
            $this->games_won++;
        }

        public function add_game($idx){
            $this->game_idxs[$this->games_played] = $idx; // is an array of indexes that is each associated with a game 
            $this->games_played++;
        }

        public function set_color($color){
            $this->color = $color;
        }
        
        public function percent_games_won(){
            return ($this->games_won / $this->games_played)*100;
        }

        public function percent_good_won(){
            return ($this->good_won / $this->games_won)*100;
        }

        public function percent_evil_won(){
            return ($this->evil_won / $this->games_won)*100;
        }

        /**
         * Loops through all the games played by this player and grabs their data. If they were demon and they won it increases the counter
         */
        public function num_won_by_role(){
            
            $townsfolk = 0;
            $outsider = 0;
            $traveler = 0;
            $demon = 0;
            $minion = 0;
            $unknown = 0;

            foreach($this->game_idxs as $idx){
                if(isset($GLOBALS['all_games'][$idx])){
                    $game = $GLOBALS['all_games'][$idx];
                    if(isset($game->player[$this->name])){
                        $requested_player = $game->player[$this->name];
                        if($requested_player->team_won){
                            switch($requested_player->type){
                                case 'Townsfolk': $townsfolk++;break;
                                case 'Outsider':  $outsider++;break;
                                case 'Traveler': $traveler++;break;
                                case 'Demon': $demon++;break;
                                case 'Minion': $minion++;break;
                                default:$unknown++;
                            }
                        }
                    }

                }
            }
            $roles = [$townsfolk, $outsider, $traveler, $demon, $minion, $unknown];
            return $roles;
        }

        public function num_played_by_role(){
            $townsfolk = 0;
            $outsider = 0;
            $traveler = 0;
            $demon = 0;
            $minion = 0;
            $unknown = 0;
            foreach($this->game_idxs as $idx){
                if(isset($GLOBALS['all_games'][$idx])){
                    $game = $GLOBALS['all_games'][$idx];
                    if(isset($game->player[$this->name])){
                        $requested_player = $game->player[$this->name];
                        switch($requested_player->type){
                            case 'Townsfolk': $townsfolk++;break;
                            case 'Outsider':  $outsider++;break;
                            case 'Traveler': $traveler++;break;
                            case 'Demon': $demon++;break;
                            case 'Minion': $minion++;break;
                            default:$unknown++;
                        }
                    }

                }
            }
            $roles = [$townsfolk, $outsider, $traveler, $demon, $minion, $unknown];
            return $roles;


        }

        public function survival(){
            $alive_good_wins = 0;
            $alive_evil_wins = 0;
            $dead_evil_wins = 0;
            $dead_good_wins = 0;
            $unknown = 0;

            foreach($this->game_idxs as $idx){
                if(isset($GLOBALS['all_games'][$idx])){
                    $game = $GLOBALS['all_games'][$idx];
                    if(isset($game->player[$this->name])){
                        $req_player = $game->player[$this->name];
                        if($req_player->isalive){
                            if($req_player->team_won){
                                switch($req_player->type){
                                    case 'Townsfolk': $alive_good_wins++;break;
                                    case 'Outsider':  $alive_good_wins++;break;
                                    case 'Traveler': 
                                        if($req_player->isgood){
                                            $alive_good_wins++;
                                        }else{
                                            $alive_evil_wins++;
                                        }
                                        break;
                                    case 'Demon': $alive_evil_wins++;break;
                                    case 'Minion': $alive_evil_wins++;break;
                                    default: $unknown++;break;
                                }
                            }
                        }else{
                            switch($req_player->type){
                                    case 'Townsfolk': $dead_good_wins++;break;
                                    case 'Outsider':  $dead_good_wins++;break;
                                    case 'Traveler': 
                                        if($req_player->isgood){
                                            $dead_good_wins++;
                                        }else{
                                            $dead_evil_wins++;
                                        }
                                        break;
                                    //case 'Demon': $alive_evil_wins++;break;
                                    case 'Minion': $dead_evil_wins++;break;
                                    default: $unknown++;break;
                                }
                        }
                    }

                }
            }

            
            $surv = [$alive_good_wins, $alive_evil_wins, $dead_good_wins, $dead_evil_wins];
            return $surv;
        }

        public function game_size(){
            $sml = 0;
            $med = 0;
            $larg = 0;
            $sml_w = 0;
            $med_w = 0;
            $larg_w = 0;

            foreach($this->game_idxs as $idx){
                if(isset($GLOBALS['all_games'][$idx])){
                    $game = $GLOBALS['all_games'][$idx];
                    if(isset($game->player[$this->name])){
                        if($game->player_num <= 10){
                            switch($game->player[$this->name]->team_won){
                                case true:
                                    $sml_w++;
                                    $sml++;
                                    break;
                                default: $sml++;
                            }
                        }else if(10 < $game->player_num && $game->player_num <= 12){
                            switch($game->player[$this->name]->team_won){
                                case true:
                                    $med_w++;
                                    $med++;
                                    break;
                                default: $med++;
                            }
                        }else{
                            switch($game->player[$this->name]->team_won){
                                case true:
                                    $larg_w++;
                                    $larg++;
                                    break;
                                default: $larg++;
                            }
                        }
                    }

                }
            }

            $sizes = [$sml, $sml_w, $med, $med_w, $larg, $larg_w];
            return $sizes;
        }
    }

    class Game{
        public $game_idx;
        public $player_num = 0;
        public $player = [];
        public $good_won;
        public $script;
        public $date;
        public $storyteller;
        public $how_end;

        public function set_details($game_idx, $player_num, $good_won, $script, $date, $storyteller/*, $how_end*/){
            $this->game_idx = $game_idx;
            $this->player_num = $player_num;
            $this->good_won = $good_won;
            $this->script = $script;
            $this->date = $date;
            $this->storyteller = $storyteller;
            //$this->how_end = $how_end;
        }

        public function add_player($player){
            $this->player[$player->name] = $player;
        }


    }
  
  /*
    The json_decode function returns the json file as an associative array with a key and a value.
    The 'values' section of the array is where all the sheet data is stored. This will have all 14 columns (eventually). 
  */
    function format_data($sheet){
        $prev_date = null;
        $prev_idx = '';
        foreach($sheet as $key => $sheet_data){
            if($key == "values"){
            
                foreach($sheet_data as $row){
                    //Player Name [0],Team (End Of Game)[1],Role (Start)[2],Role (End)[3],Character Type[4],Win/Loss[5],Alive/Dead (EOG)[6],Game Index[7],Storyteller[8],Team Win[9], ->
                    // # Of Players[10],Script[11],Date[12], How End[13]]
                    if($row == null || $row[0] == "Player Name"){ // skips over the first row and ignores null rows
                        continue;
                    }

                    

                    $name = $row[0] == null ? '' : trim($row[0]);
                    $team = $row[1] == null ? '' : trim($row[1]);
                    $role_start = $row[2] == null ? '' : trim($row[2]);
                    $role_end = $row[3] == null ? '' : trim($row[3]);
                    $type = $row[4] == null ? '' : trim($row[4]);
                    $win_loss = $row[5] == null ? '' : trim($row[5]);
                    $alive_dead = $row[6] == null ? '' : trim($row[6]);
                    $idx = $row[7] == null ? '' : trim($row[7]);
                    $storyteller = $row[8] == null ? '' : trim($row[8]);
                    $team_win = $row[9] == null ? '' : trim($row[9]);
                    $num_players = $row[10] == null ? '' : trim($row[10]);
                    $script = $row[11] == null ? '' : trim($row[11]);
                    $date = $row[12] == null ? '' : trim($row[12]);
                    //$how_end =  $row[13] == null ? '' : trim($row[13]);

                    if($prev_date != null && $prev_date != $date){ // if the date is different then the session has changed I assume
                        $GLOBALS['num_sessions']++;
                    }
                    $alive = (strtolower($alive_dead) == "alive");
                    $won = (strtolower($win_loss) == "win");
                    $is_good = (strtolower($team) == "good");
                    
                    if($idx != $prev_idx){
                        $game = new Game();
                        $GLOBALS['num_games']++;
                        //need to create a game with the values in the row array

                        $good_won = (strtolower($team_win) == "good"); 
                        $game->set_details($idx, (int)$num_players, $good_won, $script, $date, $storyteller/*, $how_end*/);
                        $GLOBALS['all_games'][$idx] = $game; // add the game to the hashmap of games with the key of its index as a string

                        if(has_player_card($storyteller)){
                        $st = $GLOBALS['players'][$storyteller];
                        $st->storyteller_games++;
                        if($is_good){
                            $st->storyteller_good++;
                        }
                        }else{
                            $new_player = new PlayerCard;
                            $new_player->set_name($storyteller);
                            $GLOBALS['num_players']++;
                            $new_player->storyteller_games++;
                            $GLOBALS['players'][$storyteller] = $new_player;
                            if($is_good){
                                $new_player->storyteller_good++;
                            }
                        }

                    }
                    $pl = new Player;
                        
                    
                    $pl->set_details($name, $is_good, $type, $role_start, $role_end,  $alive, $won);
                    
                    $game->add_player($pl);
                    
                    

                    //(players cannot have the same name with this structure)
                    if(has_player_card($pl->name)){ // check if there was a card with this name created already

                        $card = $GLOBALS['players'][$name];
                        
                        $card->add_game($idx);
                        
                        if($alive){
                            $card->add_survived();
                        }

                        if($is_good){ // if the player in the match is considered good add to their good counter
                            $card->add_good();
                            if($won){ // if that player won then add to their wins
                                $card->loss_streak = 0;
                                $card->win_streak++;
                                $card->set_highest_win_streak($card->win_streak);

                                $card->add_good_won();
                            }else{
                                $card->win_streak = 0;
                                $card->loss_streak++;
                                $card->set_highest_loss_streak($card->loss_streak);
                            }
                        }else{
                            $card->add_evil();
                            if($won){ // if that player won add to wins
                                $card->loss_streak = 0; // reset loss streak
                                $card->win_streak++; // increase win streak
                                $card->set_highest_win_streak($card->win_streak); // see if win_streak is highest

                                $card->add_evil_won();
                            }else{
                                $card->win_streak = 0; // reset win streak
                                $card->loss_streak++; // increase loss streak
                                $card->set_highest_loss_streak($card->loss_streak); // see if loss_streak is highest
                            }
                        }
                        
                    }else{ // if there needs to be a new card created

                        $new_card = new PlayerCard;
                        $GLOBALS['num_players']++;
                        $new_card->set_details($pl->name, $idx);

                        $new_card->set_color($GLOBALS['colors'][rand(0, $GLOBALS['colors_available'])]);
                        if($is_good){ // if the player in the match is considered good add to their good counter
                            $new_card->add_good();
                            if($won){
                                $new_card->loss_streak = 0;
                                $new_card->win_streak++;
                                $new_card->set_highest_win_streak($new_card->win_streak);

                                $new_card->add_good_won();
                            }else{
                                $new_card->win_streak = 0;
                                $new_card->loss_streak++;
                                $new_card->set_highest_loss_streak($new_card->loss_streak);
                            }
                        }else{
                            $new_card->add_evil();
                            if($won){
                                $new_card->loss_streak = 0;
                                $new_card->win_streak++;
                                $new_card->set_highest_win_streak($new_card->win_streak);

                                $new_card->add_evil_won();
                            }else{
                                $new_card->win_streak = 0;
                                $new_card->loss_streak++;
                                $new_card->set_highest_loss_streak($new_card->loss_streak);
                            }
                        }
                        
                        if($alive){
                            $new_card->add_survived();
                        }

                        $GLOBALS['players'][$pl->name] = $new_card;
                        
                    }
                    $prev_idx = $idx;
                    $prev_date = $date;
                    
                }
            }
                
        }
    }

    /**
     * checks the global hashmap of cards for one associated with the name
     */
    function has_player_card($player_name){
        if(isset($GLOBALS['players'][$player_name])){
            return true;
        }
        return false;
    }

    function choose_color($choice){
        switch($choice){
        case 'good': return '#c9933a';
        case 'evil': return '#8b1a1a';
        case 'demon': return '#4a2d8b';
        case 'minion': return '#1a6b5a';
        case 'b-gold': return '#e8b84b';
        case 'b-pink': return '#8b2d5a';
        case 'b-teal': return '#1a6b5a';
        case 'b-purple': return '#4a2d8b';
        case 'b-blue' : return '#1a4a8b';
        case 'b-blood' : return '#8b1a1a';
        case 'b-you' : return '#7f77dd';
        default:
            return '#6b6055';

        }
    }

    function bar($label, $percent, $color){
        if($percent == '—'){
            $percent = null;
        }
        $fill= $percent == null ? 0 : $percent;
        $txt= $percent == null? '—' :number_format($percent, 0) .'%';
        return '<div class="brow"><div class="blbl">'. ucfirst($label) .'</div><div class="btrack"><div class="bfill" style="width:'. $fill .'%;background:'. $color .'"></div></div><div class="bpct" style="color:'. $color .'}">'.$txt.'</div></div>';
    }

    function create_card($card){
        //both arrays are [townsfolk, outsider, traveler, demon, minion, unknown]
        $won_by_role = $card->num_won_by_role();
        $played_by_role = $card->num_played_by_role();
        
        $towns = $played_by_role[0] == 0 ? '—' : number_format(($won_by_role[0]/$played_by_role[0])*100, 0);
        //$outsiders = $played_by_role[1] == 0 ? '—' : number_format(($won_by_role[1]/$played_by_role[1])*100, 0);
        //$travelers = $played_by_role[2] == 0 ? '—' : number_format(($won_by_role[2]/$played_by_role[2])*100, 0);
        $demons = $played_by_role[3] == 0 ? '—' : number_format(($won_by_role[3] / $played_by_role[3])*100, 0);
        $minions = $played_by_role[4] == 0 ? '—' : number_format(($won_by_role[4] / $played_by_role[4])*100, 0);

        // in the form [alive+good, alive+evil, dead+good, dead+evil]
        $survival = $card->survival();

        $good = $card->good_games == 0 ? '—' : number_format(($survival[0] / $card->good_games)*100, 0);
        $evil = $card->evil_games == 0 ? '—' : number_format(($survival[1] / $card->evil_games)*100, 0);

        // in the form [sml, sml_w, med, med_w, larg, larg_w]
        $size = $card->game_size();

        $sml_pct = $size[0] == 0 ? '—' : number_format(($size[1] / $size[0])*100, 0);
        $med_pct = $size[2] == 0 ? '—' : number_format(($size[3] / $size[2])*100, 0);
        $larg_pct = $size[4] == 0 ? '—' : number_format(($size[5] / $size[4])*100, 0);

        $percent_evil = $card->evil_games == 0 ? '—' : number_format($card->percent_evil_won(), 0);
        $percent_good = $card->good_games == 0 ? '—' : number_format($card->percent_good_won(), 0);

        
        
            
            return '
                    <div class="pcard" style="animation-delay:'. 35 .'ms" onclick="openModal(\''. $card->name .'\')">
                        <div class="cbnr '. $card->color .'"></div>
                        <div class="chead">
                        <div class="chead-top">
                            <div>
                            <div class="pname">'. ucfirst($card->name) .'</div>
                            <div class="pmeta">'. $card->games_played .' games · '. $card->games_won .'W / '. $card->games_played - $card->games_won .'L</div>
                            <!-- <div class="paward"></div> -->
                            </div>
                            <div class="owr" style="color:'. choose_color($card->color) .'">'. number_format($card->percent_games_won(), 0) .'%</div>
                        </div>
                        </div>
                        <div class="cbody">
                        <div class="sgrid">
                            <div class="sbox"><div class="sbox-l">Good</div><div class="sbox-v" style="color:'. choose_color('good') .'">'. $percent_good .'%</div><div class="sbox-s">'. $card->good_won .'/'. $card->games_won .' games</div></div>
                            <div class="sbox"><div class="sbox-l">Evil</div><div class="sbox-v" style="color:'. choose_color('evil') .'">'. $percent_evil .'%</div><div class="sbox-s">'. $card->evil_won .'/'. $card->games_won .' games</div></div>
                            <div class="sbox"><div class="sbox-l">Demon</div><div class="sbox-v" style="color:'. choose_color('demon').'">'. $demons .'%</div><div class="sbox-s">'. $won_by_role[3] .'/'. $played_by_role[3] .'</div></div>
                            <div class="sbox"><div class="sbox-l">Minion</div><div class="sbox-v" style="color:'. choose_color('minion') .'">'. $minions .'%</div><div class="sbox-s">'. $won_by_role[4] .'/'. $played_by_role[4] .'</div></div>
                        </div>
                        <div class="stitle">Role type</div>
                        <div class="brows">'.bar('Townsfolk', $towns, choose_color('good')).''.bar('Demon', $demons, choose_color('demon')).''. bar('Minion', $minions, choose_color('minion')) .'</div>
                        <div class="stitle">Survival</div>
                        <div class="brows">'.bar('Alive + Good', $good, choose_color('good')).''.bar('Alive + Evil', $evil, choose_color('evil')).'</div>
                        <div class="stitle">Game size</div>
                        <div class="brows">'.bar('Small ≤ 10',$sml_pct, choose_color('evil')).''.bar('Mid 10–12', $med_pct, choose_color('minion')).''.bar('Large ≥12', $larg_pct, choose_color('good')).'</div>
                        <div class="pills">
                            <span class="pill ps">⚡ '. $card->hws .'W / '. $card->hls .'L streak</span>
                            <span class="pill ps">☯ '. $played_by_role[0] + $played_by_role[1] + $played_by_role[2] + $played_by_role[3] + $played_by_role[4] + $played_by_role[5] .' roles</span>
                        </div>
                        <button class="view-more" onclick="openModal(\''. $card->name .'\')">CLICK TO VIEW FULL PROFILE & GAME HISTORY</button>
                        </div>
                        
                    </div>';
            
        }

?>