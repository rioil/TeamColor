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

class Main extends PluginBase implements Listener{

    public $red;
    public $blue;
    public $yellow;
    public $green;
    public static $teams = array('red','blue','yellow','green');

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
        $red = new Config($this->getDataFolder() . 'teams/red.yml', Config::YAML, array('member' => 0));
        $blue = new Config($this->getDataFolder() . 'teams/blue.yml', Config::YAML, array('member' => 0));
        $yellow = new Config($this->getDataFolder() . 'teams/yellow.yml', Config::YAML, array('member' => 0));
        $green = new Config($this->getDataFolder() . 'teams/green.yml', Config::YAML, array('member' => 0));
        
        //コマンド処理クラスの指定
        $class = '\\teamcolor\\command\\TeamCommand'; //作成したクラスの場所(srcディレクトリより相対)
        $this->getServer()->getCommandMap()->register('TeamCommand', new $class);

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

    public static function get_team_array(){
        return $teams;
    }

    public static function get_teamconfig(string $teamname){

        if($teamname !== ''){

            switch($teamname){
        
                case 'red' : 
                    $team_config = $red;  
                break;

                case 'blue' : 
                    $team_config = $blue;
                break;

                case 'yellow' : 
                    $team_config = $yellow;
                break;

                case 'green' : 
                    $team_config = $green;
                break;
            }

            return $team_config;
        }
    }
}