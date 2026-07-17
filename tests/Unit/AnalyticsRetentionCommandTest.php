<?php
namespace Modules\Glitter\Analytics\Tests\Unit;
use Carbon\CarbonImmutable;
use Modules\Glitter\Analytics\Repositories\Contracts\AnalyticsEventRepositoryInterface;
use Modules\Glitter\Analytics\Services\AnalyticsSettingsService;
use Modules\Glitter\Analytics\Console\Commands\PruneAnalyticsEventsCommand;
use PHPUnit\Framework\TestCase;
class AnalyticsRetentionCommandTest extends TestCase {
 public function test_command_declares_dry_run_and_chunk_options():void {
  $command=new PruneAnalyticsEventsCommand();
  $definition=$command->getDefinition();
  $this->assertTrue($definition->hasOption("dry-run")); $this->assertTrue($definition->hasOption("chunk"));
 }
}
