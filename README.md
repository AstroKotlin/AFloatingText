# AFloatingText
[![](https://poggit.pmmp.io/shield.state/AFloatingText)](https://poggit.pmmp.io/p/AFloatingText)
<br>
A custom FloatingText plugin in the PocketMine-MP server.
## 🛠️|Commands
`/ft create {id}` - Create a Floating Text

`/ft remove {id}` - Remove a Floating Text

`/ft list` - List of Floating Text ids

`/ft info` - Floating Text Information

`/ft plugin` - View plugin description

`/ft tp {id} {x} {y} {z} {world}` - Moves a FloatingText to a specified coordinate

`/ft tphere` - Move FloatingText to where you are standing
## 🧑‍💻|Developer
Import AFloatingText path:
```php
use AstroKotlin\AFloatingText\AFloatingText;
```

Register a Floating Text tag:
```php
$search = "{tag}";
$replace = "Hello world";
AFloatingText::getInstance()->register($search, $replace);
```

Thanks for using :3
