<?php

declare(strict_types=1);

namespace AstroKotlin\AFloatingText;

use vennv\vapm\VapmPMMP;
use vennv\vapm\System as Task;
use AstroKotlin\AFloatingText\particle\FloatingText;
use pocketmine\utils\SingletonTrait;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\math\Vector3;

function rainbowText(): string {
    $color = ["§a", "§b", "§d", "§f", "§e", "§c", "§1", "§6"];
        
    $randomColor = mt_rand(0, count($color) - 1);
        
    return $color[$randomColor];
}

class AFloatingText extends PluginBase implements Listener {
    
    use SingletonTrait;
    
    public ?Data $cfg;
    
    public array $dataCreate = [];
    
    public array $search = [];
    
    public array $replace = [];
    
    public array $particles = [];
    
    public function onLoad(): void{
        self::setInstance($this);
    }
    
    public function onEnable(): void {
        VapmPMMP::init($this);
        
        $this->cfg = new Data($this->getDataFolder()."data.yml", Data::YAML);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
        $this->register("{line}", "\n");
        
        foreach ($this->cfg->getAll() as $k => $v) {
            if ($this->getServer()->getWorldManager()->getWorldByName($v["world"]) === null) continue;
            $particle = new FloatingText($k, $v["text"]);
            
            $this->particles[] = $particle;
        }
        $this->onRun();
    }
    
    public function getCfg(): Data {
        return $this->cfg;
    }
    
    public function register(string $search, mixed $replace): bool {
        if (in_array($search, $this->search) or $search === "{rainbow}") {
            throw new \Exception("Tag $search has been registered! Registration is cancelled!");
        }
        $this->search[] = $search;
        $this->replace[] = $replace;
        
        return true;
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $arg): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Pls use in game!");
            return false;
        }
        
        if (!$sender->hasPermission("aft.cmd")) {
            $sender->sendMessage("§3--------AFloatingText--------\n§cAuthor: §fAstroKotlin\n§cVersion: §f".$this->getDescription()->getVersion()."\n§cDescription: §f".$this->getDescription()->getDescription()."\n§eThanks for using!");
            return false;
        }
        
        if (!isset($arg[0])) return false;
        
        switch ($arg[0]) {
            case 'help':
                $sender->sendMessage("§eAFloatingText Commands:\n/ft create {id} - create a floating text\n/ft remove {id} - remove a floating text\n/ft info {id} - see info floating text\n/ft list - see list floating text");
            case 'plugin':
                $sender->sendMessage("§3--------AFloatingText--------\n§cAuthor: §fAstroKotlin\n§cVersion: §f".$this->getDescription()->getVersion()."\n§cDescription: §f".$this->getDescription()->getDescription()."\n§eThanks for using!");
            break;
            case 'info':
                if (!isset($arg[1])) {
                    $sender->sendMessage("§cPlease input id Floating Text!");
                    return false;
                }
                
                if (!$this->getCfg()->exists($arg[1])) {
                    $sender->sendMessage("§cFloating Text with id ".$arg[1]." does not exist!");
                    return false;
                }
                
                $data = $this->getCfg()->get($arg[1]);
                
                $sender->sendMessage("\n§eFloating Text id ".$arg[1]." data:\n§eText: §f".$data["text"]."\n§eWorld: §f".$data["world"]."\n§cX: §f".$data["x"]."\n§1Y: §f".$data["y"]."\n§2Z: §f".$data["z"]."\n");
            break;
            case 'create':
            case 'spawn':
                if (!isset($arg[1])) {
                    $sender->sendMessage("§cPlease input id");
                    return false;
                }
                
                if ($this->getCfg()->exists($arg[1])) {
                    $sender->sendMessage("§cFloating text id ".$arg[1]." already exists, please choose another id!");
                    return false;
                }
                
                $this->dataCreate[$sender->getName()] = [
                    "id" => $arg[1],
                    "text" => ""
                    ] ;
                $sender->sendMessage("§e§oInput text for floating text to chat");
            break;
            case 'remove':
                if (!isset($arg[1])) {
                    $sender->sendMessage("§cPlease input id Floating Text!");
                    return false;
                }
                
                if (!$this->getCfg()->exists($arg[1])) {
                    $sender->sendMessage("§cFloating Text with id ".$arg[1]." does not exist!");
                    return false;
                }
                
                $this->getCfg()->remove($arg[1]);
                $this->getCfg()->save();
                
                $sender->sendMessage("§aSuccessfully removed Floating Text id ".$arg[1]);
            break;
            case 'list':
                $ids = "";
                
                foreach ($this->getCfg()->getAll() as $id => $data) {
                    $ids .= "§e- ".$id."§r\n";
                }
                
                $sender->sendMessage("§fList of FloatingText ids in server:\n".$ids);
            break;
            case 'tp':
            case 'teleport':
                if (!isset($arg[1])) {
                    $sender->sendMessage("§cPlease input id Floating Text!");
                    return false;
                }

                if (!$this->getCfg()->exists($arg[1])) {
                    $sender->sendMessage("§cFloating Text with id ".$arg[1]." does not exist!");
                    return false;
                }

                if (!isset($arg[2]) or !isset($arg[3]) or !isset($arg[4])) {
                    $sender->sendMessage("You have recorded the wrong coordinates!");
                    return false;
                }

                if (is_null($this->getServer()->getWorldManager()->getWorldByName($arg[5]))) {
                    $sender->sendMessage("World {$arg[5]} does not exist");
                    return false;
                }

                $this->getCfg()->setNested($arg[1].".x", $arg[2]);
                $this->getCfg()->setNested($arg[1].".y", $arg[3]);
                $this->getCfg()->setNested($arg[1].".z", $arg[4]);
                $this->getCfg()->setNested($arg[1].".world", $arg[5]);
                $this->getCfg()->save();
            break;
            case 'tphere':
                if (!isset($arg[1])) {
                    $sender->sendMessage("§cPlease input id Floating Text!");
                    return false;
                }

                if (!$this->getCfg()->exists($arg[1])) {
                    $sender->sendMessage("§cFloating Text with id ".$arg[1]." does not exist!");
                    return false;
                }

                $this->getCfg()->setNested($arg[1].".x", $sender->getPosition()->getX());
                $this->getCfg()->setNested($arg[1].".y", $sender->getPosition()->getY());
                $this->getCfg()->setNested($arg[1].".z", $sender->getPosition()->getZ());
                $this->getCfg()->setNested($arg[1].".world", $sender->getPosition()->getWorld());
                $this->getCfg()->save();
        }
        return true;
    }
    
    public function chatEv(PlayerChatEvent $ev) {
        $player = $ev->getPlayer();
        $text = $ev->getMessage();
        
        if (isset($this->dataCreate[$player->getName()]["text"])) {
            if ($this->dataCreate[$player->getName()]["text"] == "") {
                $pos = $player->getPosition();
                $this->dataCreate[$player->getName()]["text"] = $text;
                $this->getCfg()->createTextID((string)$this->dataCreate[$player->getName()]["id"], $this->dataCreate[$player->getName()]["text"], $pos->getWorld()->getFolderName(), $pos->getX(), $pos->getY() + $player->getEyeHeight(), $pos->getZ());
                
                $particle = new FloatingText((string)$this->dataCreate[$player->getName()]["id"], (string)$this->dataCreate[$player->getName()]["text"]);
                $this->particles[] = $particle;
                
                $player->sendMessage("§aCreated new floating text with id §e".(string)$this->dataCreate[$player->getName()]["id"]."§a success!");
                
                $ev->cancel();
            }
        }
    }
    
    public function onRun() {
        Task::setInterval(function() {
            $i = 0;
            foreach ($this->particles as $particle) {
                if (!$particle->existsId() or $particle->getWorld() == null) {
                    foreach ($this->getServer()->getOnlinePlayers() as $player) {
                        $particle->remove($player);
                    }
                    unset($this->particles[$i]);
                    
                    $this->getCfg()->remove($particle->getId());
                    $this->getCfg()->save();
                    continue;
                }
            
                $text = str_replace(
                    array_merge($this->search, ["{rainbow}"]),
                    array_merge($this->replace, [rainbowText()]), $particle->getText());
            
                $particle->setText($text);
        
                $particle->getWorld()->addParticle(new Vector3($particle->getX(), $particle->getY(), $particle->getZ()), $particle);
                $i++;
            }
        }, 1000);
    }
}
