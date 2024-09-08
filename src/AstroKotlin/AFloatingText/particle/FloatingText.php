<?php

declare(strict_types=1);

namespace AstroKotlin\AFloatingText\particle;

use AstroKotlin\AFloatingText\AFloatingText;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\world\World;

class FloatingText extends FloatingTextParticle {

    public function __construct(public readonly int|float|string $id, protected string $text) {
        parent::__construct($text);
    }
    
    public function getText(): string {
        $text = AFloatingText::getInstance()->getCfg()->getNested($this->getId().".text");
        return $text;
    }
    
    public function getId(): string {
        return $this->id;
    }
    
    public function existsId(): bool {
        return AFloatingText::getInstance()->getCfg()->exists($this->id);
    }
    
    public function getWorldName(): string {
        return AFloatingText::getInstance()->getCfg()->getNested($this->getId().".world");
    }
    
    public function getWorld(): ?World {
        return AFloatingText::getInstance()->getServer()->getWorldManager()->getWorldByName($this->getWorldName());
    }

    public function remove(Player $player) : void {
        $pk = new RemoveActorPacket();
        $pk->actorUniqueId = $this->entityId;
        $player->getNetworkSession()->sendDataPacket($pk);
    }
    
    public function getX(): float {
        return AFloatingText::getInstance()->getCfg()->getNested($this->getId().".x");
    }
    
    public function getY(): float {
        return AFloatingText::getInstance()->getCfg()->getNested($this->getId().".y");
    }
    
    public function getZ(): float {
        return AFloatingText::getInstance()->getCfg()->getNested($this->getId().".z");
    }
}
