<?php


namespace App\Http\Controllers\Foodfleet;

use App\Http\Resources\Foodfleet\EventType as EventTypeResource;
use App\Models\Foodfleet\EventType;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class EventTypes extends Controller
{
    public function index(Request $request)
    {
        $eventTypes = QueryBuilder::for(EventType::class, $request)
            ->allowedFilters(['name'])
            ->get();

        return EventTypeResource::collection($eventTypes);
    }
}
