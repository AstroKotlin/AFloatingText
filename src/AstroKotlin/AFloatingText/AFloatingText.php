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
    
    public function onLoad(): void{
        self::setInstance($this);
    }
    
    public function onEnable(): void {
        VapmPMMP::init($this);
        
        $this->cfg = new Data($this->getDataFolder()."data.yml", Data::YAML);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
        $this->register("{line}", "\n");
        
        foreach ($this->cfg->getAll() as $k => $v) {
            $particle = new FloatingText($k, $v["text"]);
            if ($this->getServer()->getWorldManager()->getWorldByName($v["world"]) === null) continue;
            $this->getServer()->getWorldManager()->getWorldByName($v["world"])->addParticle(new Vector3($v["x"], $v["y"], $v["z"]), $particle);
            $this->update($particle);
        }
    }
    
    public function getCfg(): Data {
        return $this->cfg;
    }
    
    public function register(string $search, mixed $replace): bool {
        if (in_array($search, $this->search) or $search === "{rainbow}") {
            throw new \Exception("Tag $search has been registered! Registration is cancelled!");
            return false;
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
            $sender->sendMessage("§3--------AFloatingText--------\n§cAuthor: §fAstroKotlin\n§cVersion: ".$this->getDescription()->getVersion()."\nDescription: ".$this->getDescription()->getDescription()."\n Thanks for using!");
            return false;
        }
        
        if (!isset($arg[0])) return false;
        
        switch ($arg[0]) {
            case 'help':
                $sender->sendMessage("§eAFloatingText Commands:\n/ft create {id} - create a floating text\n/ft remove {id} - remove a floating text\n/ft info {id} - see info floating text\n/ft list - see list floating text");
            case 'plugin':
                $sender->sendMessage("§3--------AFloatingText--------\n§cAuthor: §fAstroKotlin\n§cVersion: ".$this->getDescription()->getVersion()."\nDescription: ".$this->getDescription()->getDescription()."\n Thanks for using!");
            break;
            case 'info':
                if (!isset($arg[1])) {
                    $sender->sendMessage("§cPlease input id Floating Text!");
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
                    $sender->sendMessage("§c Floating text id ".$arg[1]." already exists, please choose another id!");
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
                $pos->getWorld()->addParticle(new Vector3($pos->getX(), $pos->getY() + $player->getEyeHeight(), $pos->getZ()), $particle);
                
                $player->sendMessage("§aCreated new floating text with id §e".(string)$this->dataCreate[$player->getName()]["id"]."§a success!");
                $this->update($particle);
                
                $ev->cancel();
            }
        }
    }
    
    public function update(FloatingText $particle) {
        Task::setInterval(function() use($particle) {
            if (!$particle->existsId() or is_null($particle->getWorld())) {
                foreach ($this->getServer()->getOnlinePlayers() as $player) {
                    $particle->remove($player);
                    continue;
                }
            }
            
            $text = str_replace(
                array_merge($this->search, ["{rainbow}"]),
                array_merge($this->replace, [rainbowText()]), $particle->getText());
            
            $particle->setText($text);
        
            $particle->getWorld()->addParticle(new Vector3($particle->getX(), $particle->getY(), $particle->getZ()), $particle);
        }, 1000);
    }
}
