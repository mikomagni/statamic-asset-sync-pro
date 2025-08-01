<?php
// statamic-asset-sync-pro/src/Traits/HandlesProgressBar.php

namespace MikoMagni\RsyncCommands\Traits;
use Laravel\Prompts\Progress;
use Illuminate\Support\Facades\Log;
use function Laravel\Prompts\progress;

trait HandlesProgressBar
{
    protected Progress $progress;
    protected int $currentStep = 0;
    protected int $totalSteps = 100;
    protected int $progressThrottle = 0;

    protected function initializeProgressBar(int $totalSteps = 100): void
    {
        $this->totalSteps = max($totalSteps, 1);
        $this->currentStep = 0;
        $this->progressThrottle = 0;
        $this->output->newLine();
        $this->progress = progress(
            label: trans('rsync::commands.rsync_progress_starting'),
            steps: $this->totalSteps
        );
        $this->progress->start();
    }

    protected function updateProgressBar(string $buffer): void
    {
        $this->progressThrottle++;

        $shouldAdvance = false;

        if ($this->isFileTransfer($buffer) || $this->isFileOperation($buffer)) {
            if ($this->currentStep < $this->totalSteps) {
                $shouldAdvance = true;
            }
        } else if ($this->progressThrottle % 50 === 0 && $this->currentStep < $this->totalSteps) {
            $shouldAdvance = true;
        }

        if ($shouldAdvance) {
            $this->currentStep++;
            $this->progress->advance();
        }

        // Show hints if enabled in config
        if (config('rsync.display_progress_hints', false)) {
            $hint = $this->extractProgressHint($buffer);
            if ($hint) {
                $this->progress->hint($hint);
            }
        }

        if ($this->currentStep > $this->totalSteps) {
            $this->currentStep = $this->totalSteps;
        }
    }

    protected function finishProgressBar(): void
    {
        if ($this->currentStep < $this->totalSteps) {
            $remaining = $this->totalSteps - $this->currentStep;
            $this->progress->advance($remaining);
            $this->currentStep = $this->totalSteps;
        }
        $this->progress->finish();
    }

    protected function clearProgressBar(): void
    {
        if (isset($this->progress)) {
            $this->progress->finish();
        }
    }

    protected function isFileTransfer(string $buffer): bool
    {
        $defaultPattern = '/\s+\d+[,.]?\d*\s+\d+%\s+[\d.]+[KMGT]?B\/s\s+\d+:\d+:\d+/';
        $pattern = config('rsync.file_transfer_regex', $defaultPattern);

        if (@preg_match($pattern, '') === false) {
            if (config('rsync.assets_log') === true) {
                Log::warning('Invalid regex pattern in config, using default', [
                    'invalid_pattern' => $pattern,
                    'default_pattern' => $defaultPattern
                ]);
            }
            $pattern = $defaultPattern;
        }

        return preg_match($pattern, $buffer) === 1;
    }

    protected function isFileOperation(string $buffer): bool
    {
        return preg_match('/^(>f|cd|\*deleting|sending incremental file list|\.\/)/m', trim($buffer)) === 1;
    }

    protected function extractProgressHint(string $buffer): ?string
    {
        $lines = explode("\n", trim($buffer));
        $lastLine = trim(end($lines));

        if (preg_match('/^(.+?)\s+\d+/', $lastLine, $matches)) {
            return trim($matches[1]);
        }

        if (strlen($lastLine) > 0 && strlen($lastLine) < 100) {
            return $lastLine;
        }

        return null;
    }
}
