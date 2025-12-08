<?php

declare(strict_types=1);

namespace Drush\Commands\marvin_phpstan_product;

use Drupal\marvin\CommandEvent as BaseCommandEvent;
use Drupal\marvin\Lint\CommandEvent as LintCommandEvent;
use Drupal\marvin\MarvinTaskDefinitionCommandTrait;
use Drupal\marvin\Utils;
use Drupal\marvin_git\GitHook\CommandEvent as GitHookCommandEvent;
use Drupal\marvin_phpstan\PhpstanAnalyzeCommandTrait;
use Drupal\marvin_product\CommandsBaseTrait;
use Drush\Boot\DrupalBootLevels;
use Drush\Attributes\Bootstrap as CliBootstrap;
use Drush\Commands\AutowireTrait;
use Drush\Config\DrushConfig;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerInterface;
use Robo\Collection\Tasks as ForEachTaskLoader;
use Robo\Contract\BuilderAwareInterface;
use Robo\TaskAccessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
  name: self::NAME,
  description: 'Runs "phpstan analyze".',
)]
#[CliBootstrap(level: DrupalBootLevels::NONE)]
class MarvinLintPhpstanCommand extends Command implements BuilderAwareInterface, ContainerAwareInterface {

  use ContainerAwareTrait;
  use AutowireTrait {
    create as protected autowireCreate;
  }
  use TaskAccessor;
  use ForEachTaskLoader;
  use CommandsBaseTrait;
  use PhpstanAnalyzeCommandTrait;
  use MarvinTaskDefinitionCommandTrait;

  public const string NAME = 'marvin:lint:phpstan';

  public function __construct(
    #[Autowire(Filesystem::class)]
    protected Filesystem $fs,
    #[Autowire('config')]
    protected DrushConfig $drushConfig,
    #[Autowire('eventDispatcher')]
    protected EventDispatcherInterface $eventDispatcher,
    #[Autowire(Utils::class)]
    protected Utils $utils,
    #[Autowire(LoggerInterface::class)]
    protected LoggerInterface $logger,
  ) {
    parent::__construct();
    $this->eventDispatcher->addListener(
      LintCommandEvent::EVENT_RUN_TASKS_COLLECT,
      $this->onEventMarvinLintTasksCollect(...),
    );

    $this->eventDispatcher->addListener(
      GitHookCommandEvent::EVENT_PRE_COMMIT_TASKS_COLLECT,
      $this->onEventMarvinGitHookPreCommitTasksCollect(...),
    );
  }

  protected function getLinterInfo(): array {
    return [
      'id' => 'phpstan',
    ];
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public function execute(InputInterface $input, OutputInterface $output): int {
    $gitHookName = NULL;
    $event = new LintCommandEvent(
      $input,
      $output,
      $gitHookName,
      $this->collectionBuilder(),
      [],
    );
    $event->taskDefinitions += $this->getTaskDefsInitStateDataBase($event);
    $event->taskDefinitions += $this->getTaskDefsRunPhpstanAnalyze($event);

    return $this->mtdRun(
      self::NAME,
      $event->collectionBuilder,
      $event->taskDefinitions,
    );
  }

  public function onEventMarvinLintTasksCollect(LintCommandEvent $event): void {
    $event->taskDefinitions += $this->getTaskDefsRunPhpstanAnalyze($event);
  }

  public function onEventMarvinGitHookPreCommitTasksCollect(GitHookCommandEvent $event): void {
    $event->taskDefinitions += $this->getTaskDefsRunPhpstanAnalyze($event);
  }

  protected function getTaskDefsRunPhpstanAnalyze(BaseCommandEvent $event): array {
    $reportsDir = $this->drushConfig->get('marvin.reportsDir');

    $task = $this->getTaskLintPhpstanAnalyzeExtension(
      '.',
      'vendor/bin',
      $reportsDir,
      $event->gitHookName,
    );

    return [
      'Invoke-Phpstan.marvin_phpstan_product' => [
        'weight' => 40,
        'description' => 'Runs "phpstan analyze".',
        'task' => $task,
      ],
    ];
  }

}
