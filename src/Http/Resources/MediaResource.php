<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Resources;

use App\Media;
use Ebess\AdvancedNovaMediaLibrary\Fields\HandlesConversionsTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MediaResource
 * @package Ebess\AdvancedNovaMediaLibrary\Http\Resources
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    use HandlesConversionsTrait;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $relAtt = $request->input('relAttr', null);
        $title  = null;

        /**
         * This is incompatible with following settings on the Field.
         * - conversionOnIndexView
         * - conversionOnDetailView
         * - conversionOnForm
         * - conversionOnPreview
         * - serializeMediaUsing
         *
         */
        $tr = array_merge(
            $this->resource->toArray(),
            [
                '__media_urls__' => $this->getConversionUrls($this->resource)
            ],

        );
        if ($relAtt)
        {
            $title = $this->model->{$relAtt};
            $tr    = array_merge($tr, [ 'title' => $title, ]);
        }

        return $tr;
    }
}
