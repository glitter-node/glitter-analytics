<?php

namespace Modules\Glitter\Analytics\Tests\Feature;

use Modules\Glitter\Analytics\Tests\ModuleTestCase;

class AnalyticsModuleManifestTest extends ModuleTestCase
{
    public function test_manifest_and_composer_metadata_are_synchronized(): void
    {
        $manifest = json_decode(file_get_contents($this->modulePath('module.json')), true, 512, JSON_THROW_ON_ERROR);
        $composer = json_decode(file_get_contents($this->modulePath('composer.json')), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('glitter-analytics', $manifest['identifier']);
        $this->assertSame('Glitter.kr', $manifest['vendor']);
        $this->assertSame('0.7.0', $manifest['version']);
        $this->assertSame('0.7.0', $composer['version']);
        $this->assertSame('resources/js/index.ts', $manifest['assets']['js']['entry']);
        $this->assertSame('global', $manifest['loading']['strategy']);
        $this->assertSame('MIT', $manifest['license']);
        $this->assertSame('>=7.0.0', $manifest['g7_version']);
        $this->assertSame('modules/glitter-analytics', $composer['name']);
        $this->assertSame('src/', $composer['autoload']['psr-4']['Modules\\Glitter\\Analytics\\']);
        $this->assertArrayNotHasKey('assets', $manifest);
        $this->assertArrayNotHasKey('loading', $manifest);
    }
}
