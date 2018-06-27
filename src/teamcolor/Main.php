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
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\ServerCommandEvent;

##TODO##
/*
sendTip()というのとスケジューラーを組み合わせれば画面に現在のチーム情報を表示できるかも
*/

class Main extends PluginBase implements Listener{

    /**赤チームのコンフィグ*/
    private static $red;

    /**青チームのコンフィグ*/
    private static $blue;

    /**黃チームのコンフィグ*/
    private static $yellow;

    /**緑チームのコンフィグ*/
    private static $green;

    /**チーム名の配列*/
    private static $teams = array('red','blue','yellow','green');

    /**操作中のチームコンフィグ*/
    private $team_config;

    /**操作中のチームの色*/
    private $team_color;

    /**操作中のチームの人数*/
    private static $nmember;

    /**このクラスを格納*/
    private static $plugin;

    /**リロード検出用コンフィグ*/
    private $reload;

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

        //リロード検出用ファイル作成
        $this->reload = new Config($this->getDataFolder() . 'reload.yml', Config::YAML);
        
        //コマンド処理クラスの指定
        $class = '\\teamcolor\\command\\TeamCommand'; //作成したクラスの場所(srcディレクトリより相対)
        $this->getServer()->getCommandMap()->register('TeamCommand', new $class);

        //コマンドクラスでgetDatafolderを使うため
        self::$plugin = $this;

        $this->getLogger()->info('初期化完了');
    }

    //pluginが有効になった時に実行
    public function onEnable(){

        //それぞれのチームコンフィグを読み込み・作成(リロード時以外)
        if($this->reload->get('reload') != true){
            $this->loadTeamConfig();
        }
        else{
            $this->reload->set('reload',false);
            $this->reload->save();
        }

        $this->getServer()->getPluginManager()->registerEvents($this,$this); //イベント登録
        $this->getLogger()->info('プラグインは有効になりました');
    }

    public function onDisable(){
        $this->getLogger()->info('プラグインは無効になりました');
    }

    //サーバーリロードの検出
    public function onServerCommand(ServerCommandEvent $event){

        $this->command = $event->getCommand();
        if ($this->command == 'reload'){
            $this->reload->set('reload',true);
            $this->reload->save();
            $this->getLogger()->info('リロードを検知');
        }
    }

    //プレイヤーが入ったらconfigの生成
    public function onPlayerJoin(PlayerJoinEvent $event){

        //プレイヤー情報の取得・コンフィグを準備
        $player = $event->getPlayer();
        $player_config = self::getPlayerConfig($player);
        //チーム名の表示
        if($player_config->exists('team')){

            $this->player_team = $player_config->get('team');

            if($this->player_team != ''){
                $this->team_color = self::getTeamColor($this->player_team);        
                $player->setNameTag($this->team_color . $player->getName());
                $player->setNameTagVisible(true);

                //チーム人数をコンフィグに反映
                $this->team_config = self::getTeamConfig($this->player_team);
                $this->team_config->set('member',$this->team_config->get('member') + 1);
            }

        }
        else{
            $player_config->set('team',NULL);
        }
        //TODO初期参加プレイヤーのチームを指定する必要あり
    }

    //TODO プレイヤーが鯖から抜けた時チーム人数の変更
    public function onPlayerQuit(PlayerQuitEvent $event){

        $player = $event->getPlayer();
        $player_config = self::getPlayerConfig($player);

        //チームの人数に反映
        if($player_config->exists('team')){

            $this->player_team = $player_config->get('team');

            if($this->player_team != ''){      
                //チーム人数をコンフィグに反映
                $this->team_config = self::getTeamConfig($this->player_team);
                $this->team_config->set('member',$this->team_config->get('member') - 1);
            }

        }

    }

    //チーム名の配列を取得
    public static function getTeamAllay(){
        return self::$teams;
    }

    //チームのコンフィグを読み込み・準備
    private function loadTeamConfig(){

        foreach(self::$teams as $team_name){
            //チームのコンフィグを読み込み・作成
            self::$$team_name = new Config($this->getDataFolder() . 'teams/' . $team_name . '.yml', Config::YAML);
            //人数の項目に0をセット
            self::$$team_name->set('member','0');
            self::$$team_name->save();
            
        }
    }

    //指定したチームのコンフィグを取得
    public static function getTeamConfig(string $teamname) : config{

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

    //指定したチームの色を取得
    public static function getTeamColor(string $team) : string{

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
        }

        return $team_color;
    }

    //チームの現在人数を格納した配列を取得
    public static function getNumber0member() : array{

        foreach(self::$teams as $team_name){
            $team_config = self::getTeamConfig($team_name);
            self::$nmember[$team_name] = $team_config->get('member');
        }
        return self::$nmember;
    }

    public static function getPlayerConfig(player $player) : config{

        if($player instanceof player){

            $player_name = $player->getName();
            $player_config = new Config(self::getPlugin()->getDataFolder() . 'players/' . $player_name . '.yml', Config::YAML);
            return $player_config;
        }
        else{
            return false;
        }

    }

    //このクラスのインスタンス取得
    public static function getPlugin(){
        return self::$plugin;
    }
}