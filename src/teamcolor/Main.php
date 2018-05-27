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

    private static $red;
    private static $blue;
    private static $yellow;
    private static $green;
    private static $teams = array('red','blue','yellow','green');
    private $team_config;
    private $team_color;
    private static $nmember;

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
        self::$red = new Config($this->getDataFolder() . 'teams/red.yml', Config::YAML, array('member' => 0));
        self::$blue = new Config($this->getDataFolder() . 'teams/blue.yml', Config::YAML, array('member' => 0));
        self::$yellow = new Config($this->getDataFolder() . 'teams/yellow.yml', Config::YAML, array('member' => 0));
        self::$green = new Config($this->getDataFolder() . 'teams/green.yml', Config::YAML, array('member' => 0));
        
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
        self::get_teamcolor($new_player_config->get('team'));        
        $player->setNameTag($this->team_color . $player->getName());
        $player->setNameTagVisible(true);
    }

    public static function get_team_array(){
        return self::$teams;
    }

    public static function get_team_config(string $teamname){

        if($teamname !== ''){

            switch($teamname){
        
                case 'red' : 
                    $config = self::$red;  
                break;

                case 'blue' : 
                    $config = self::$blue;
                break;

                case 'yellow' : 
                    $config = self::$yellow;
                break;

                case 'green' : 
                    $config = self::$green;
                break;
            }

            return $config;
        }
    }

    public static function get_team_color(string $team){

        switch($team){
            
            case 'red' : 
                $team_color = '§4';
            break;

            case 'blue' : 
                $team_color = '§1';
            break;

            case 'yellow' : 
                $team_color = '§6';
            break;

            case 'green' : 
                $team_color = '§2';
            break;

            default :
                $team_color = '§f';
            break;

            return $team_color;
        }
    }

    public static function set_nmember2array(){

        foreach(self::$teams as $team_name ){
            $team_config = self::get_team_config($team_name);
            $nmember[$team_name] = $team_config->get('member');
        }
        return $nmember;
    }
}