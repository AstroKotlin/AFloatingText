<?php

declare(strict_types=1);

namespace AstroKotlin\AFloatingText;

use pocketmine\utils\Config;

class Data extends Config {
    
    public function createTextID(string $id, string $text, string $world, float $x, float $y, float $z) {
        $this->setNested($id, [
            "text" => $text,
            "world" => $world,
            "x" => $x,
            "y" => $y,
            "z" => $z
        ]);
        $this->save();
    }
}
