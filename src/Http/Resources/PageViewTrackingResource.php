<?php

namespace Modules\Glitter\Analytics\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;

class PageViewTrackingResource extends BaseApiResource
{
    /** @return array{recorded: bool} */
    public function toArray(Request $request): array
    {
        return ['recorded' => (bool) $this->getValue('recorded')];
    }
}
