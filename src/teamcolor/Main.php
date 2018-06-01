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
        //それぞれのチームコンフィグを読み込み・作成
        $this->road_team_config();
        
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

        //プレイヤー情報の取得・コンフィグを準備
        $player = $event->getPlayer();
        $player_name = $event->getPlayer()->getName();
        $new_player_config = new Config($this->getDataFolder() . 'players/' . $player_name . '.yml', Config::YAML, array('team' => ''));
        
        //チーム名の表示
        if($new_player_config->exists('team')){

            $this->team_color = self::get_team_color($new_player_config->get('team'));        
            $player->setNameTag($this->team_color . $player->getName());
            $player->setNameTagVisible(true);

        }
    }

    public static function get_team_array(){
        return self::$teams;
    }

    private function road_team_config(){

        foreach(self::$teams as $team_name){
            //チームのコンフィグを読み込み・作成
            self::$$team_name = new Config($this->getDataFolder() . 'teams/' . $team_name . '.yml', Config::YAML);
            //人数の項目がなければ0をセット
            if(!self::$$team_name->exists('member')){
                self::$$team_name->set('member','0');
                self::$$team_name->save();
            }
        }
    }

    public static function get_team_config(string $teamname) : config{

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

    public static function get_team_color(string $team) : string{

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

    public static function get_number0member() : array{

        foreach(self::$teams as $team_name){
            $team_config = self::get_team_config($team_name);
            $nmember[$team_name] = $team_config->get('member');
        }
        return $nmember;
    }
}