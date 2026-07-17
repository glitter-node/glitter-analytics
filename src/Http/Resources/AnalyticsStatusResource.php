<?php

namespace Modules\Glitter\Analytics\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;

class AnalyticsStatusResource extends BaseApiResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'identifier' => $this->getValue('identifier'),
            'version' => $this->getValue('version'),
            'enabled' => $this->getValue('enabled'),
            'status' => $this->getValue('status'),
            'tracking_enabled' => true,
            'database_ready' => true,
        ];
    }
}
