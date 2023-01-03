<?php

declare(strict_types = 1);

namespace Drush\Commands\marvin_phpstan_product;

use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Drupal\marvin\Attributes as MarvinCLI;
use Drush\Attributes as CLI;
use Drush\Boot\DrupalBootLevels;
use Drush\Commands\marvin_phpstan\PhpstanAnalyzeCommandsBase;
use Robo\Contract\TaskInterface;

class PhpstanAnalyzeCommands extends PhpstanAnalyzeCommandsBase {

  /**
   * @phpstan-return array<string, mixed>
   */
  #[CLI\Hook(
    type: HookManager::ON_EVENT,
    target: 'marvin:git-hook:pre-commit',
  )]
  public function onEventMarvinGitHookPreCommit(): array {
    return [
      'marvin_phpstan_product.phpstan_analyze_extension' => [
        'weight' => -200,
        'task' => $this->getTaskLintPhpstanAnalyzeExtension($this->getProjectRootDir()),
      ],
    ];
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  #[CLI\Hook(
    type: HookManager::ON_EVENT,
    target: 'marvin:lint',
  )]
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
   */
  #[CLI\Command(name: 'marvin:lint:phpstan')]
  #[CLI\Bootstrap(level: DrupalBootLevels::NONE)]
  #[MarvinCLI\PreCommandInitLintReporters]
  public function cmdLintPhpstanExecute(): TaskInterface {
    return $this->getTaskLintPhpstanAnalyzeExtension('.');
  }

}
