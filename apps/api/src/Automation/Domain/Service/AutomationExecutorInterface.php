<?php

declare(strict_types=1);

namespace App\Automation\Domain\Service;

use App\Automation\Domain\Model\Automation;

interface AutomationExecutorInterface
{
    /**
     * Execute an automation and return the invested amount
     */
    public function execute(Automation $automation): float;

    /**
     * Check if the automation can be executed
     */
    public function canExecute(Automation $automation): bool;
}
