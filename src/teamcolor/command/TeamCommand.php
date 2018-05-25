<?php

namespace teamcolor\command;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\Utils;
use pocketmine\utils\Config;
use teamcolor\Main;

class TeamCommand extends Command{

    private $teamlist;
    private $team;

    public function __construct(){

        $name = 'team';
        $description = 'Team Plugin'; //プラグインの説明
        $usageMessage = '/team [操作]'; //使い方の説明
        $aliases = array('tm'); //コマンドエイリアス
        parent::__construct($name, $description, $usageMessage, $aliases);

        $permission = 'teamplugin.command'; //パーミッションノード
        $this->setPermission($permission);

    }

    public function get_teamconfig(string $team){

        if($team !== ''){

            $this->team = $team;
            //configのセット
            $this->team_config = Main::get_teamconfig($this->team);

            switch($this->team){
        
                case 'red' :    
                    $this->color = '§4';
                break;

                case 'blue' : 
                    $this->color = '§1';
                break;

                case 'yellow' : 
                    $this->color = '§6';
                break;

                case 'green' : 
                    $this->color = '§2';
                break;
            }
        }
    }

    public function set_array_nmember(){

        $this->team_array = Main::get_team_array();
        foreach($this->team_array as $this->tn ){
            $this->get_teamconfig($this->tn);
            $this->nmember[$this->tn] = $this->team_config->get('member');
        }
    }
    
    public function execute(CommandSender $sender, string $label, array $args) : bool {

        if(isset($args[0])){

            switch (strtolower($args[0])){
    
                case 'info':
    
                    $this->set_array_nmember(); //各チームの人数を取得
                    $sender->sendMessage('§3＝チーム一覧＝');
                    $sender->sendMessage('§4red     §f' . $this->nmember['red'] .'人');
                    $sender->sendMessage('§1blue    §f' . $this->nmember['blue'].'人');
                    $sender->sendMessage('§6yellow  §f' . $this->nmember['yellow'].'人');
                    $sender->sendMessage('§2green   §f' . $this->nmember['green'].'人');           
                    $sender->sendMessage('§3＝＝＝＝＝＝＝');
    
                break;
    
                case 'join' :
    
                    if(isset($args[1])){
                        $this->join_team = strtolower($args[1]);
                            //チームが存在しないとき
                            if(!($this->join_team == 'red' || 'blue' || 'yellow' || 'green')){
                                $sender->sendMessage('チーム：' . $this->join_team . 'は存在しません');
                                break;
                            }
                            //プレイヤーのコンフィグ準備
                            $this->player_config = new Config($this->getDataFolder() . 'players/' . $sender->getName() . '.yml', Config::YAML); 
                    }
                    else{
                        $sender->sendMessage('§4チーム名を正しく指定してください');
                        break ;
                    }
    
                    //今入っているチームを確認
                    $this->current_team = $this->player_config->get('team');

                    if($this->current_team !== $this->join_team){

                        //すでにチームに所属していればそのチームを抜けることを通知
                        if($this->current_team !== ''){

                            $sender->sendMessage('チーム' . $this->current_team . 'から抜けます');
                            //configに書き込み
                            get_teamconfig($this->current_team);
                            $this->team_config->remove($sender->getName());
                            $this->team_config->set('member',(int)$this->team_config->get('member') - 1);
                            $this->team_config->save();
                            
                        }
                        //コンフィグに参加するチーム名をセット
                        $this->player_config->set('team',$this->join_team);
                        $this->player_config->save();
    
                        //チームのコンフィグファイルと色を指定
                        get_teamconfig($this->join_team);
                        //コンフィグに書き込み
                        $this->team_config->set($sender->getName(),'0');
                        $this->team_config->set('member',(int)$this->team_config->get('member') + 1);
                        $this->team_config->save();
    
                        //プレイヤーのネームタグの色をチームカラーに変更
                        $sender->setNameTag($this->color . $sender->getName());
                        $sender->setNameTagVisible(true);
                        //完了メッセージ
                        $sender->sendMessage('チーム' . $this->join_team . 'に参加しました');
                        $this->getLogger()->info($sender->getName() . 'がチーム' . $this->join_team . 'に参加しました');
                    }
                    else{
                        $sender->sendMessage('§6すでにチーム' . $this->join_team . 'に所属しています');
                    }
    
                break;
    
                case 'leave' :
    
                    //プレイヤーのコンフィグ準備
                    $this->player_config = new Config($this->getDataFolder() . 'players/' . $sender->getName() . '.yml', Config::YAML); 
                    //所属チームの確認
                    if($this->player_config->exists('team')){
                        $this->leave_team = $this->player_config->get('team');
    
                        //チームのコンフィグを指定
                        get_teamconfig($this->leave_team);
                        //コンフィグに書き込み
                        $this->team_config->remove($sender->getName());
                        $this->team_config->set('member',(int)$this->team_config->get('member') - 1);
                        $this->team_config->save();
    
                        $this->player_config->set('team','');
                        $this->player_config->save();
                        //プレイヤーのネームタグを白色にする
                        $sender->setNameTag('§f' . $sender->getName());
                        //完了メッセージ
                        $sender->sendMessage('チーム' . $this->current_team . 'から抜けました');
                        $this->getLogger()->info($sender->getName() . 'がチーム' . $this->current_team . 'から抜けました');
                    }
                    else{
                        $sender->sendMessage('§6現在どのチームにも属していません');
                    }
    
                break;
    
            }
        }
        else{
            //引数がなかったとき使い方の表示
            $sender->sendMessage('：：：：：使い方：：：：：');
            $sender->sendMessage('info:チーム情報の表示');
            $sender->sendMessage('join:チームに参加');
            $sender->sendMessage('leave:チームから抜ける');
        }
        

        return true;

    }
}