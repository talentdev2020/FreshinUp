<?php

namespace Tests\Feature\Http\Resources\Foodfleet\Document\Template;

use App\Http\Resources\Foodfleet\Document\Template\Template as Resource;
use App\Models\Foodfleet\Document\Template\Template as Model;
use FreshinUp\FreshBusForms\Http\Resources\User\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class TemplateTest extends TestCase {

    public function testResource () {
        $item = factory(Model::class)->create();
        $resource = new Resource($item);
        $expected = [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'title' => $item->title,
            'content' => $item->content,
            'description' => $item->description,
            'status_id' => $item->status_id,
            'updated_by_uuid' => $item->updated_by_uuid
        ];
        dd($expected);
        $request = app()->make(Request::class);
        $result = $resource->toArray($request);
        $this->assertArraySubset($expected, $result);

        // relations
        // TODO: see https://github.com/FreshinUp/foodfleet/issues/500
//        $this->assertArrayHasKey('updated_by', $result);
//        $userResource = (new User($item->updatedBy))->toArray($request);
//        $this->assertArraySubset($userResource, $result['updated_by']->toArray($request));
    }
}
