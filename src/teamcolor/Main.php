<?php

namespace teamcolor;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\Utils;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

//コマンド処理部分の読み込み
//use team\command\TeamCommand;

class Main extends PluginBase implements Listener{

    public $teamlist;

  //plugin読み込み時に実行
    public function onLoad(){
        //設定ファイル保存場所作成
        if(!file_exists($this->getDataFolder())){
            @mkdir($this->getDataFolder());
        }
        //プレイヤーファイルの保存場所作成
        if(!file_exists($this->getDataFolder() . 'players')){
            @mkdir($this->getDataFolder() . 'players');
        }
        //チームファイルの保存場所作成
        if(!file_exists($this->getDataFolder() . 'teams')){
            @mkdir($this->getDataFolder() . 'teams');
        }
        //それぞれのチーム管理ファイルを作成
        $this->red = new Config($this->getDataFolder() . 'teams/red.yml', Config::YAML, array('member' => 0));
        $this->blue = new Config($this->getDataFolder() . 'teams/blue.yml', Config::YAML, array('member' => 0));
        $this->yellow = new Config($this->getDataFolder() . 'teams/yellow.yml', Config::YAML, array('member' => 0));
        $this->green = new Config($this->getDataFolder() . 'teams/green.yml', Config::YAML, array('member' => 0));
        $this->getLogger()->info('初期化完了');
    }
    //pluginが有効になった時に実行
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this,$this); //イベント登録
        $this->getLogger()->info('プラグインは有効になりました');
    }

    public function onDisable(){
        $this->getLogger()->info('プラグインは無効になりました');
    }

    //プレイヤーが入ったらconfigの生成
    public function onPlayerJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $player_name = $event->getPlayer()->getName();
        $new_player_config = new Config($this->getDataFolder() . 'players/' . $player_name . '.yml', Config::YAML, array('team' => ''));
        //チーム名の表示
        switch($new_player_config->get('team')){
            
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

            default :
                $this->color = '§f';
            break;
        }
        $player->setNameTag($this->color . $player->getName());
        $player->setNameTagVisible(true);
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) : bool {

        switch (strtolower($command->getName())){
    
            case 'team':

                $sender->sendMessage('§3＝チーム一覧＝');
                $sender->sendMessage('§4red');
                $sender->sendMessage('§1blue');
                $sender->sendMessage('§6yellow');
                $sender->sendMessage('§2green');           
                $sender->sendMessage('§3＝＝＝＝＝＝＝');

            break;

            case 'join' :

                if(isset($args[0])){
                    $this->teamname = strtolower($args[0]);
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
                            $this->team_config = $this->red;  
                            $this->color = '§4';
                        break;

                        case 'blue' : 
                            $this->team_config = $this->blue;
                            $this->color = '§1';
                        break;

                        case 'yellow' : 
                            $this->team_config = $this->yellow;
                            $this->color = '§6';
                        break;

                        case 'green' : 
                            $this->team_config = $this->green;
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

                $this->send_player = $sender->getName();
                //プレイヤーのコンフィグ準備
                $this->player_config = new Config($this->getDataFolder() . 'players/' . $sender->getName() . '.yml', Config::YAML); 
                //所属チームの確認
                if($this->player_config->exists('team')){
                    $this->teamname = $this->player_config->get('team');

                    //チームのコンフィグを指定
                    switch($this->teamname){

                        case 'red' : 
                            $this->team_config = $this->red;
                        break;

                        case 'blue' : 
                            $this->team_config = $this->blue;
                        break;

                        case 'yellow' : 
                            $this->team_config = $this->yellow;
                        break;

                        case 'green' : 
                            $this->team_config = $this->green;
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

        return true;

    }
}