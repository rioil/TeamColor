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

//コマンド処理部分の読み込み
//use team\command\TeamCommand;

class TeamCommand extends Command{

    private $teamlist;

    public function __construct(){

        $name = 'team';
        $description = 'Team Plugin'; //プラグインの説明
        $usageMessage = '/team [操作]'; //使い方の説明
        $aliases = array('tm'); //コマンドエイリアス
        parent::__construct($name, $description, $usageMessage, $aliases);

        $permission = 'teamplugin.command'; //パーミッションノード
        $this->setPermission($permission);

    }
    
    public function execute(CommandSender $sender, string $label, array $args) : bool {

        if(isset($args[0])){

            switch (strtolower($args[0])){
    
                case 'info':
    
                    $sender->sendMessage('§3＝チーム一覧＝');
                    $sender->sendMessage('§4red');
                    $sender->sendMessage('§1blue');
                    $sender->sendMessage('§6yellow');
                    $sender->sendMessage('§2green');           
                    $sender->sendMessage('§3＝＝＝＝＝＝＝');
    
                break;
    
                case 'join' :
    
                    if(isset($args[1])){
                        $this->teamname = strtolower($args[1]);
                            //チームが存在しないとき
                            if(!($this->teamname == 'red' || 'blue' || 'yellow' || 'green')){
                                $sender->sendMessage('チーム：' . $this->teamname . 'は存在しません');
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
                    if($this->player_config->get('team') !== $this->teamname){
                        //すでにチームに所属していればそのチームを抜けることを通知
                        if($this->player_config->get('team') !== ''){
                            $sender->sendMessage('チーム' . $this->player_config->get('team') . 'から抜けます');
                        }
                        //コンフィグに参加するチーム名をセット
                        $this->player_config->set('team',$this->teamname);
                        $this->player_config->save();
    
                        //チームのコンフィグファイルと色を指定
                        switch($this->teamname){
    
                            case 'red' : 
                                $this->team_config = parent::$red;  
                                $this->color = '§4';
                            break;
    
                            case 'blue' : 
                                $this->team_config = parent::$blue;
                                $this->color = '§1';
                            break;
    
                            case 'yellow' : 
                                $this->team_config = parent::$yellow;
                                $this->color = '§6';
                            break;
    
                            case 'green' : 
                                $this->team_config = parent::$green;
                                $this->color = '§2';
                            break;
    
                        }
                        //コンフィグに書き込み
                        $this->team_config->set($sender->getName(),'0');
                        $this->team_config->set('member',(int)$this->team_config->get('member') + 1);
                        $this->team_config->save();
    
                        //プレイヤーのネームタグの色をチームカラーに変更
                        $sender->setNameTag($this->color . $sender->getName());
                        $sender->setNameTagVisible(true);
                        //完了メッセージ
                        $sender->sendMessage('チーム' . $this->teamname . 'に参加しました');
                        $this->getLogger()->info($sender->getName() . 'がチーム' . $this->teamname . 'に参加しました');
                    }
                    else{
                        $sender->sendMessage('§6すでにチーム' . $this->teamname . 'に所属しています');
                    }
    
                break;
    
                case 'leave' :
    
                    //プレイヤーのコンフィグ準備
                    $this->player_config = new Config($this->getDataFolder() . 'players/' . $sender->getName() . '.yml', Config::YAML); 
                    //所属チームの確認
                    if($this->player_config->exists('team')){
                        $this->teamname = $this->player_config->get('team');
    
                        //チームのコンフィグを指定
                        switch($this->teamname){
    
                            case 'red' : 
                                $this->team_config = parent::$red;
                            break;
    
                            case 'blue' : 
                                $this->team_config = parent::$blue;
                            break;
    
                            case 'yellow' : 
                                $this->team_config = parent::$yellow;
                            break;
    
                            case 'green' : 
                                $this->team_config = parent::$green;
                            break;
                        }
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