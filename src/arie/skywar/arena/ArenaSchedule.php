<?php
/*
 * Copyright (c) 2022 Arisify
 *
 * This program is freeware, so you are free to redistribute and/or modify
 * it under the conditions of the MIT License.
 *
 * @author Arisify
 * @link   https://github.com/Arisify
 * @license https://opensource.org/licenses/MIT MIT License
 *
 * \    /\
 *  )  ( ') ᵐᵉᵒʷˢ
 * (  /  )
 *  \(__)|
 *
*/

namespace arie\skywar\arena;

use pocketmine\scheduler\Task;

class ArenaSchedule extends Task{
	protected int $countdown_time;
	protected int $opencage_time;
    protected int $game_time;
    protected int $restart_time;
	protected bool $forceStart = false;

    public function __construct(
	    private Arena $arena
    ){
	    $this->countdown_time = 45;
	    $this->opencage_time = 15;
	    $this->game_time = 20 * 60;
	    $this->restart_time = 15;
    }

    public function onRun() : void{
	    if ($this->forceStart || $this->arena->getPlayerAmount() / $this->arena->getMaxSlot() > 3 / 4) {
		    $this->arena->game_state = Arena::STATE_COUNTDOWN;
	    }
	    if ($this->countdown_time === 0) {
		    $this->arena->poststart();
		    $this->arena->game_state = Arena::STATE_OPEN_CAGE;
	    }
	    if ($this->opencage_time === 0) {
		    $this->arena->start();
		    $this->arena->game_state = Arena::STATE_IN_GAME;
	    }
	    if ($this->arena->canRestart() || $this->game_time === 0) {
		    $this->arena->restart();
		    $this->arena->game_state = Arena::STATE_RESTART;
	    }
	    if ($this->restart_time === 0) {
		    $this->arena->restart();
		    $this->arena->game_state = Arena::STATE_WAITING;
	    }

	    switch ($this->arena->game_state) {
		    case Arena::STATE_COUNTDOWN:
			    --$this->countdown_time;
			    break;
		    case Arena::STATE_OPEN_CAGE:
			    --$this->opencage_time;
			    break;
		    case Arena::STATE_IN_GAME:
			    --$this->game_time;
			    break;
		    case Arena::STATE_RESTART:
				--$this->restart_time;
				break;
			default:
		}
    }
}