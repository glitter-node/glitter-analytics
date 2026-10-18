<?php

namespace Modules\Glitter\Analytics;

use App\Extension\AbstractModule;

class Module extends AbstractModule
{
    /**
     * 모듈 권한 목록을 반환합니다.
     *
     * @return array<string, mixed>
     */
    public function getPermissions(): array
    {
        return [
            'name' => ['ko' => '사이트 분석', 'en' => 'Site Analytics'],
            'description' => [
                'ko' => '사이트 분석 관리자 권한',
                'en' => 'Site analytics administration permissions',
            ],
            'categories' => [
                $this->permissionCategory(
                    'dashboard',
                    ['ko' => '분석 대시보드', 'en' => 'Analytics Dashboard'],
                    ['ko' => '분석 대시보드 접근 권한', 'en' => 'Analytics dashboard access'],
                    [['action' => 'view', 'name' => ['ko' => '대시보드 조회', 'en' => 'View Dashboard'], 'description' => ['ko' => '분석 대시보드를 조회합니다.', 'en' => 'View the analytics dashboard.']]],
                ),
                $this->permissionCategory(
                    'visits',
                    ['ko' => '방문 기록', 'en' => 'Visits'],
                    ['ko' => '방문 기록 접근 권한', 'en' => 'Visit records access'],
                    [['action' => 'view', 'name' => ['ko' => '방문 기록 조회', 'en' => 'View Visits'], 'description' => ['ko' => '방문 기록을 조회합니다.', 'en' => 'View visit records.']]],
                ),
                $this->permissionCategory(
                    'settings',
                    ['ko' => '분석 설정', 'en' => 'Analytics Settings'],
                    ['ko' => '분석 설정 관리 권한', 'en' => 'Analytics settings access'],
                    [
                        ['action' => 'view', 'name' => ['ko' => '설정 조회', 'en' => 'View Settings'], 'description' => ['ko' => '분석 설정을 조회합니다.', 'en' => 'View analytics settings.']],
                        ['action' => 'update', 'name' => ['ko' => '설정 수정', 'en' => 'Update Settings'], 'description' => ['ko' => '분석 설정을 수정합니다.', 'en' => 'Update analytics settings.']],
                    ],
                ),
            ],
        ];
    }

    /**
     * 모듈 설정 기본값을 반환합니다.
     *
     *  array<string, mixed>
     */
    public function getChannels(): array
    {
        return ["module.glitter-analytics.dashboard" => ["permission" => "glitter-analytics.dashboard.view"]];
    }

    public function getSchedules(): array
    {
        return [["command" => "glitter-analytics:prune", "schedule" => "daily", "description" => "Analytics retention cleanup"]];
    }

    public function getConfigValues(): array
    {
        $path = $this->getSettingsDefaultsPath();
        if ($path === null) {
            return [];
        }

        $config = json_decode((string) file_get_contents($path), true);

        return is_array($config['defaults'] ?? null) ? $config['defaults'] : [];
    }

    /**
     * 관리자 메뉴를 반환합니다.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAdminMenus(): array
    {
        return [[
            'name' => ['ko' => '사이트 분석', 'en' => 'Site Analytics'],
            'slug' => 'glitter-analytics',
            'url' => null,
            'icon' => 'fas fa-chart-line',
            'order' => 35,
            'children' => [
                ['name' => ['ko' => '대시보드', 'en' => 'Dashboard'], 'slug' => 'glitter-analytics-dashboard', 'url' => '/admin/analytics', 'icon' => 'fas fa-chart-line', 'order' => 1, 'permission' => 'glitter-analytics.dashboard.view'],
                ['name' => ['ko' => '방문 기록', 'en' => 'Visits'], 'slug' => 'glitter-analytics-visits', 'url' => '/admin/analytics/visits', 'icon' => 'fas fa-history', 'order' => 2, 'permission' => 'glitter-analytics.visits.view'],
                ['name' => ['ko' => '설정', 'en' => 'Settings'], 'slug' => 'glitter-analytics-settings', 'url' => '/admin/analytics/settings', 'icon' => 'fas fa-cog', 'order' => 3, 'permission' => 'glitter-analytics.settings.view'],
            ],
        ]];
    }

    /**
     * 권한 카테고리를 구성합니다.
     *
     * @param  array<string, string>  $name
     * @param  array<string, string>  $description
     * @param  array<int, array<string, mixed>>  $permissions
     * @return array<string, mixed>
     */
    private function permissionCategory(string $identifier, array $name, array $description, array $permissions): array
    {
        return [
            'identifier' => $identifier,
            'name' => $name,
            'description' => $description,
            'permissions' => array_map(static fn (array $permission): array => [
                ...$permission,
                'type' => 'admin',
                'roles' => ['admin'],
            ], $permissions),
        ];
    }
}
