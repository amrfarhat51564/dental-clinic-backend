<?php

namespace App\Services\Clinical;

use App\Enums\Dentition;

class FdiToothService
{
    public function isValid(
        Dentition|string $dentition,
        string $toothCode
    ): bool {
        $dentition = $dentition instanceof Dentition
            ? $dentition
            : Dentition::from($dentition);

        return match ($dentition) {
            Dentition::Permanent => $this->isValidPermanent($toothCode),
            Dentition::Primary => $this->isValidPrimary($toothCode),
        };
    }

    private function isValidPermanent(string $toothCode): bool
    {
        if (! preg_match('/^[1-4][1-8]$/', $toothCode)) {
            return false;
        }

        return true;
    }

    private function isValidPrimary(string $toothCode): bool
    {
        if (! preg_match('/^[5-8][1-5]$/', $toothCode)) {
            return false;
        }

        return true;
    }
}