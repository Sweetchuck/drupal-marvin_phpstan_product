<?php

declare(strict_types = 1);

namespace Drush\Commands\marvin_phpstan_product;

use Drush\Commands\marvin_phpstan\PhpstanAnalyzeCommandsBase;
use Robo\Contract\TaskInterface;

class PhpstanAnalyzeCommands extends PhpstanAnalyzeCommandsBase {

  /**
   * @hook on-event marvin:git-hook:pre-commit
   *
   * @phpstan-return array<string, mixed>
   */
  public function onEventMarvinGitHookPreCommit(): array {
    return [
      'marvin_phpstan_product.phpstan_analyze_extension' => [
        'weight' => -200,
        'task' => $this->getTaskLintPhpstanAnalyzeExtension($this->getProjectRootDir()),
      ],
    ];
  }

  /**
   * @hook on-event marvin:lint
   *
   * @phpstan-return array<string, mixed>
   */
  public function onEventMarvinLint(): array {
    return [
      'marvin_phpstan_product.phpstan_analyze_extension' => [
        'weight' => -200,
        'task' => $this->getTaskLintPhpstanAnalyzeExtension($this->getProjectRootDir()),
      ],
    ];
  }

  /**
   * Runs PHPStan analyze.
   *
   * @command marvin:lint:phpstan
   *
   * @bootstrap none
   *
   * @marvinInitLintReporters
   */
  public function cmdLintPhpstanExecute(): TaskInterface {
    return $this->getTaskLintPhpstanAnalyzeExtension('.');
  }

}
