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
        if(!file_exists($this->getDataFolder() . 'players')){
            @mkdir($this->getDataFolder() . 'players');
        }
        //チームのリストを作成
        $this->teamlist = new Config($this->getDataFolder() . 'teamlist.yml', Config::YAML);
        if(!$this->teamlist->exists('red' || 'blue')){
            $this->teamlist->set('red','1');
            $this->teamlist->set('blue','1');
            $this->teamlist->save();
            $this->getLogger()->info('チームリストが作成されチームblueとredが有効になりました');
        }
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

                $exists_team = array($this->teamlist->getAll(true));
                $sender->sendMessage('§3＝＝チーム一覧＝＝');
                $team_array = $exists_team[0];
                foreach($team_array as $teamname){
                    $sender->sendMessage("§f$teamname");
                }
                $sender->sendMessage('§3＝＝＝＝＝＝＝＝＝');

            break;

            case 'join' :

                if(isset($args[0])){
                    $this->teamname = strtolower($args[0]);
                        //チームが存在しないとき
                        if(!$this->teamlist->exists($this->teamname)){
                            $sender->sendMessage('チーム：' . $this->teamname . 'は存在しません');
                            break;
                        }
                        //プレイヤーのコンフィグ準備
                        $this->current_config = new Config($this->getDataFolder() . 'players/' . $sender->getName() . '.yml', Config::YAML); 
                }
                else{
                    $sender->sendMessage('§4チーム名を正しく指定してください');
                    break ;
                }

                //今入っているチームを確認
                if($this->current_config->get('team') !== $this->teamname){
                    //すでにチームに所属していればそのチームを抜けることを通知
                    if($this->current_config->get('team') !== ''){
                        $sender->sendMessage('チーム' . $this->current_config->get('team') . 'から抜けます');
                    }
                    //コンフィグに参加するチーム名をセット
                    $this->current_config->set('team',$this->teamname);
                    $this->current_config->save();

                    //チームごとの色の指定
                    switch($this->teamname){

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
                $this->current_config = new Config($this->getDataFolder() . 'players/' . $sender->getName() . '.yml', Config::YAML); 
                if($this->current_config->exists('team')){
                    $this->current_team = $this->current_config->get('team');
                    $this->current_config->set('team','');
                    $this->current_config->save();
                    //プレイヤーのネームタグを白色にする
                    $sender->setNameTag("§f$sender->getName()");
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