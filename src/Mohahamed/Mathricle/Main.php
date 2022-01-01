<?php

declare(strict_types=1);

namespace Mohahamed\Mathricle;

use GalactixPE\CityCore\modules\EzRegions\math\Circle;
use GalactixPE\CityCore\modules\EzRegions\math\ParticleEngine;
use GalactixPE\CityCore\modules\EzRegions\math\Shape;
use pocketmine\color\Color;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\Position;

class Main extends PluginBase implements Listener {

    public function onEnable(): void
    {
        $this->saveResource("fff.png");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if(!$sender instanceof Player) return false;
        if($command->getName() == "text")
        {

            if(!isset($args[0])) return false;



            $file = imagecreatetruecolor(100, 30);
            $bgColor = imagecolorallocate($file, 255,255,255);
            imagefill($file, 0, 0, $bgColor);
            imagestring($file, 5,10, 7, $args[0], 0);

            $startPoint = $sender->getPosition();

            $particlePositions = [$startPoint];
            for ($x = 0; $x < 100; $x++)
            {
                for($y = 0; $y < 30; $y++)
                {
                    $rgb = imagecolorat($file, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    $vector = clone $startPoint;
                    if($r + $g + $b == 0)
                    {
                        $particlePositions[] = $vector->add($x/2.5, $sender->getPosition()->getY() - 1, $y/2.5);
                    }
                }
            }
            $color = new Color(0, 255, 255);
            $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use($particlePositions, $sender, $color){
                foreach ($particlePositions as $particlePosition)
                {
                    $sender->getWorld()->addParticle($particlePosition, new DustParticle($color));
                }
            }), 15);


            return true;
        }
        return false;
    }

    public function test(BlockBreakEvent $event, $test)
    {
        $sender = $event->getPlayer();

        $file = imagecreatefrompng($this->getDataFolder() . "fff.png");
        $startPoint = $sender->getPosition();

        $particlePositions = [$startPoint];
        for ($x = 0; $x < 200; $x++)
        {
            for($y = 0; $y < 200; $y++)
            {
                $rgb = imagecolorat($file, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $vector = clone $startPoint;
                if($rgb== 0)
                {
                    $particlePositions[] = $vector->add($x/2.5, $sender->getPosition()->getY() - 1, $y/2.5);
                    $colors = [$r, $g, $b];
                }
            }
        }
        $color = new Color(0, 255, 255);
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use($particlePositions, $sender, $color){
            foreach ($particlePositions as $particlePosition)
            {
                $sender->getWorld()->addParticle($particlePosition, new DustParticle($color));
            }
        }), 15);
    }

    public function backUp(BlockBreakEvent $event)
    {
        if(!VanillaItems::STICK()->equals($event->getItem())) return;

        $event->cancel();
        $player = $event->getPlayer();
        $playerVec = $event->getBlock()->getPosition()->asVector3();

        $color = new Color(0, 255, 255);
        for ($theta = 0; $theta < 150;$theta += 0.05)
        {
            $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $theta, $playerVec, $color){
                if($theta != null){
                    $cartesianCoords = $this->polarToCartesian( 10 + sin(2*pi()*$theta), $theta);
                    $newVec = $playerVec->add($cartesianCoords->getX(), 0, $cartesianCoords->getY());
                    $player->getWorld()->addParticle($newVec, new DustParticle($color));
                }
            }), intval(40 + 2.5 * $theta));
        }
    }

    public function polarToCartesian(float $r, float $theta)
    {
        return new Vector2($r * cos($theta), $r * sin($theta));
    }



    public function calculateDirectionVector(Vector3 $begin, Vector3 $end)
    {
        return $end->subtractVector($begin);
    }


}
