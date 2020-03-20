<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Controllers;

use Ebess\AdvancedNovaMediaLibrary\Http\Requests\MediaRequest;
use Ebess\AdvancedNovaMediaLibrary\Http\Resources\MediaResource;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\Models\Media;

class MediaController extends Controller
{

    /**
     * @param MediaRequest $request
     *
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    public function index(MediaRequest $request)
    {
        if (!config('nova-media-library.enable-existing-media'))
        {
            throw new Exception('You need to enable the `existing media` feature via config.');
        }

        /** @var Model|Media|Searchable $mediaClass */
        $mediaClass             = config('medialibrary.media_model');
        $mediaClassIsSearchable = method_exists($mediaClass, 'search');

        $searchText =
            $request->input('search_text')
                ?: null;
        $perPage    =
            $request->input('per_page')
                ?: 18;

        $collection = $request->input('collection', null);

        /** @var Builder $query */
        $query = null;

        if ($searchText && $mediaClassIsSearchable)
        {
            $query = $mediaClass::search($searchText);
        }
        else
        {
            $query = $mediaClass::query();

            if ($searchText)
            {
                $query->where(
                    function ($query) use ($searchText)
                    {
                        /** @var $query Builder */
                        $query->where('name', 'LIKE', '%' . $searchText . '%');
                        $query->orWhere('file_name', 'LIKE', '%' . $searchText . '%');
                    }
                );
            }

            $query->latest();
        }

        if ($collection)
        {
            $query->where('collection_name', '=', $collection);
        }

        $relAttr = $request->input('relAttr', null);
        if ($relAttr)
        {
            $query->with('model');
        }

        $results = $query->paginate($perPage);

        return MediaResource::collection($results);
    }
}
