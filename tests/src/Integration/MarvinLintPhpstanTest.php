<?php

declare(strict_types = 1);

namespace Drupal\Tests\marvin_phpstan_product\Integration;

/**
 * @group marvin
 * @group marvin_product
 * @group marvin_phpstan
 * @group marvin_phpstan_product
 * @group drush-command
 *
 * @covers \Drush\Commands\marvin_phpstan_product\PhpstanAnalyzeCommands
 */
class MarvinLintPhpstanTest extends UnishIntegrationTestCase {

  public function testMarvinLintPhpstanHelp(): void {
    $expected = [
      'stdError' => '',
      'stdOutput' => 'Runs PHPStan analyze.',
      'exitCode' => 0,
    ];

    $args = [];
    $options = $this->getCommonCommandLineOptions();
    $options['help'] = NULL;

    $this->drush(
      'marvin:lint:phpstan',
      $args,
      $options,
      NULL,
      NULL,
      $expected['exitCode'],
      NULL,
      $this->getCommonCommandLineEnvVars(),
    );

    $actualStdError = $this->getErrorOutput();
    $actualStdOutput = $this->getOutput();

    static::assertStringContainsString($expected['stdError'], $actualStdError, 'StdError');
    static::assertStringContainsString($expected['stdOutput'], $actualStdOutput, 'StdOutput');
  }

  public function testMarvinLintPhpstanRun(): void {
    $expected = [
      'stdError' => '',
      'stdOutput' => '[OK] No errors',
      'exitCode' => 0,
    ];

    $args = [];
    $options = $this->getCommonCommandLineOptions();

    $this->drush(
      'marvin:lint:phpstan',
      $args,
      $options,
      NULL,
      $this->getProjectRootDir(),
      $expected['exitCode'],
      NULL,
      $this->getCommonCommandLineEnvVars(),
    );

    $actualStdError = $this->getErrorOutput();
    $actualStdOutput = $this->getOutput();

    static::assertStringContainsString($expected['stdError'], $actualStdError, 'StdError');
    static::assertStringContainsString($expected['stdOutput'], $actualStdOutput, 'StdOutput');
  }

}
