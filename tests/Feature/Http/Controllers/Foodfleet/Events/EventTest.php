<?php

namespace Tests\Feature\Http\Controllers\Foodfleet\Events;

use App\Enums\EventStatus as EventStatusEnum;
use App\Models\Foodfleet\Document;
use App\Models\Foodfleet\Event;
use App\Models\Foodfleet\EventMenuItem;
use App\Models\Foodfleet\EventSchedule;
use App\Models\Foodfleet\EventStatus;
use App\Models\Foodfleet\EventTag;
use App\Models\Foodfleet\EventType;
use App\Models\Foodfleet\Location;
use App\Models\Foodfleet\Store;
use App\Models\Foodfleet\Venue;
use App\User;
use FreshinUp\FreshBusForms\Models\Company\Company;
use Illuminate\Foundation\Testing\Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithoutMiddleware;

    public function testGetList()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $events = factory(Event::class, 5)->create();

        $data = $this
            ->json('GET', "/api/foodfleet/events")
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(5, count($data));
        foreach ($events as $idx => $event) {
            $this->assertArraySubset([
                'uuid' => $event->uuid,
                'name' => $event->name
            ], $data[$idx]);
        }
    }

    public function testGetListWithFilters()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        factory(Event::class, 5)->create([
            'name' => 'Not visibles'
        ]);

        $eventsToFind = factory(Event::class, 5)->create([
            'name' => 'To find'
        ]);

        $data = $this
            ->json('GET', "/api/foodfleet/events")
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(10, count($data));


        $data = $this
            ->json('GET', "/api/foodfleet/events?filter[name]=find")
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(5, count($data));

        foreach ($eventsToFind as $idx => $event) {
            $this->assertArraySubset([
                'uuid' => $event->uuid,
                'name' => $event->name
            ], $data[$idx]);
        }

        $event = $eventsToFind->first();
        $data = $this
            ->json('GET', "/api/foodfleet/events?filter[uuid]=".$event->uuid)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(1, count($data));

        $this->assertArraySubset([
            'uuid' => $event->uuid,
            'name' => $event->name
        ], $data[0]);
    }

    public function testGetListFilteredByType()
    {
        $user = factory(User::class)->create();
        factory(EventType::class, 2)->create();
        Passport::actingAs($user);

        factory(Event::class, 5)->create([
            'type_id' => 1
        ]);
        $eventsToFind = factory(Event::class, 3)->create([
            'type_id' => 2
        ]);

        $response = $this
            ->json('GET', "/api/foodfleet/events?filter[type_id]=". 2)
            ->assertStatus(200);
        $this->assertNotExceptionResponse($response);
        $data = $response
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(3, count($data));
        foreach ($eventsToFind as $index => $event) {
            $this->assertArraySubset([
                'uuid' => $event->uuid,
                'name' => $event->name
            ], $data[$index]);
        }
    }

    public function testGetListIncludingType()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $events = factory(Event::class, 5)->create();

        $data = $this
            ->json('GET', "/api/foodfleet/events?include=type")
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(5, count($data));
        foreach ($events as $index => $event) {
            $e = EventType::find($event->type_id);
            $this->assertArraySubset([
                'uuid' => $event->uuid,
                'name' => $event->name,
                'type' => [
                    'id' => $e->id,
                    'name' => $e->name
                ]
            ], $data[$index]);
        }
    }

    public function testGetListWithHostUuidFilter()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $nonhost = factory(Company::class)->create();

        factory(Event::class, 5)->create([
            'name' => 'Not Visibles',
            'host_uuid' => $nonhost->uuid
        ]);

        $hosts = factory(Company::class, 2)->create();

        $eventToFind1 = factory(Event::class)->create([
            'name' => 'To find 1',
            'host_uuid' => $hosts->first()->uuid
        ]);

        $eventToFind2 = factory(Event::class)->create([
            'name' => 'To find 2',
            'host_uuid' => $hosts->last()->uuid
        ]);

        $hostUuid = $hosts->map(function ($host) {
            return $host->uuid;
        })->join(',');

        $data = $this
            ->json('get', "/api/foodfleet/events?filter[host_uuid]=".$hostUuid)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');
        $this->assertNotEmpty($data);
        $this->assertCount(2, $data);
        $this->assertEquals($eventToFind1->uuid, $data[0]['uuid']);
        $this->assertEquals($eventToFind2->uuid, $data[1]['uuid']);
    }

    public function testGetListWithManagerUuidFilter()
    {
        $user = factory(User::class)->create([
            'type' => 1,
            'level' => 5
        ]);

        Passport::actingAs($user);

        $nonuser = factory(User::class)->create();

        factory(Event::class, 5)->create([
            'name' => 'Not visibles',
            'manager_uuid' => $nonuser->uuid
        ]);

        $usersToFind = factory(User::class, 2)->create();

        $eventToFind1 = factory(Event::class)->create([
            'name' => 'To find',
            'manager_uuid' => $usersToFind->first()->uuid
        ]);

        $eventToFind2 = factory(Event::class)->create([
            'name' => 'To find',
            'manager_uuid' => $usersToFind->last()->uuid
        ]);

        $eventToFind3 = factory(Event::class)->create([
            'name' => 'To find',
            'manager_uuid' => $user->uuid
        ]);

        $userUuid = $usersToFind->map(function ($user) {
            return $user->uuid;
        })->join(',');

        $data = $this
            ->json('get', "/api/foodfleet/events?filter[manager_uuid]=".$userUuid)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertCount(3, $data);
        $this->assertEquals($eventToFind1->uuid, $data[0]['uuid']);
        $this->assertEquals($eventToFind2->uuid, $data[1]['uuid']);
        $this->assertEquals($eventToFind3->uuid, $data[2]['uuid']);
    }

    public function testGetListWithStatusIdFilter()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $nonstatus = factory(EventStatus::class)->create();

        factory(Event::class, 5)->create([
            'name' => 'Not visibles',
            'status_id' => $nonstatus->id
        ]);

        $statuses = factory(EventStatus::class, 2)->create();

        $eventToFind1 = factory(Event::class)->create([
            'name' => 'To find 1',
            'status_id' => $statuses->first()->id
        ]);

        $eventToFind2 = factory(Event::class)->create([
            'name' => 'To find 2',
            'status_id' => $statuses->last()->id
        ]);

        $statusId = $statuses->map(function ($status) {
            return $status->id;
        })->join(',');

        $data = $this
            ->json('get', "/api/foodfleet/events?filter[status_id]=".$statusId)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(2, count($data));
        $this->assertEquals($eventToFind1->uuid, $data[0]['uuid']);
        $this->assertEquals($eventToFind2->uuid, $data[1]['uuid']);
    }

    public function testGetListWithEventTagUuidFilter()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        factory(Event::class, 5)->create([
            'name' => 'Not visibles'
        ]);

        $eventTags = factory(EventTag::class, 2)->create();

        $eventToFind1 = factory(Event::class)->create([
            'name' => 'To find 1'
        ]);
        $eventToFind1->eventTags()->save($eventTags->first());

        $eventToFind2 = factory(Event::class)->create([
            'name' => 'To find 2'
        ]);
        $eventToFind2->eventTags()->save($eventTags->last());

        $eventTagUuid = $eventTags->map(function ($eventTag) {
            return $eventTag->uuid;
        })->join(',');

        $data = $this
            ->json('get', "/api/foodfleet/events?filter[event_tag_uuid]=".$eventTagUuid)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(2, count($data));
        $this->assertEquals($eventToFind1->uuid, $data[0]['uuid']);
        $this->assertEquals($eventToFind2->uuid, $data[1]['uuid']);
    }

    public function testGetListWithStoreUuidFilter()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        factory(Event::class, 5)->create([
            'name' => 'Not visibles'
        ]);

        $stores = factory(Store::class, 2)->create();

        $eventToFind1 = factory(Event::class)->create([
            'name' => 'To find 1'
        ]);
        $eventToFind1->stores()->save($stores->first());

        $eventToFind2 = factory(Event::class)->create([
            'name' => 'To find 2'
        ]);
        $eventToFind2->stores()->save($stores->last());

        $storeUuid = $stores->map(function ($store) {
            return $store->uuid;
        })->join(',');

        $data = $this
            ->json('get', "/api/foodfleet/events?filter[store_uuid]=".$storeUuid)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(2, count($data));
        $this->assertEquals($eventToFind1->uuid, $data[0]['uuid']);
        $this->assertEquals($eventToFind2->uuid, $data[1]['uuid']);
    }

    public function testGetListWithStartAtFilter()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        Carbon::setTestNow(Carbon::createFromTimeString('2019-10-01 01:03:40.930965'));

        factory(Event::class, 5)->create([
            'name' => 'Not visibles',
            'start_at' => Carbon::now()->subDays(5),
            'end_at' => Carbon::now()->subDays(10)
        ]);

        $eventToFind = factory(Event::class)->create([
            'name' => 'To find',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->subDays(1)
        ]);

        $startAt = Carbon::now()->subDays(2)->toDateTimeString();
        $endAt = Carbon::now()->addDays(2)->toDateTimeString();

        $data = $this
            ->json('get', "/api/foodfleet/events?filter[start_at]=".$startAt.'&filter[end_at]='.$endAt)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(1, count($data));
        $this->assertEquals($eventToFind->uuid, $data[0]['uuid']);
        Carbon::setTestNow();
    }

    public function testGetListWithInclude()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $eventTag = factory(EventTag::class)->create();
        $status = factory(EventStatus::class)->create();
        $location = factory(Location::class)->create();
        $host = factory(Company::class)->create();
        $eventType = factory(EventType::class)->create();
        $venue = factory(Venue::class)->create();

        $event = factory(Event::class)->create([
            'manager_uuid' => $user->uuid,
            'status_id' => $status->id,
            'location_uuid' => $location->uuid,
            'host_uuid' => $host->uuid,
            'type_id' => $eventType->id,
            'venue_uuid' => $venue->uuid
        ]);

        $event->eventTags()->save($eventTag);
        $data = $this->json('GET', '/api/foodfleet/events?include=status,host,location,manager,event_tags,type,venue')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [],
            ])
            ->json('data');

        $this->assertArraySubset([
            'uuid' => $event->uuid,
            'name' => $event->name,
        ], $data[0]);

        $this->assertArraySubset([
            'uuid' => $eventTag->uuid,
            'name' => $eventTag->name,
        ], $data[0]['event_tags'][0]);

        $this->assertArraySubset([
            'id' => $eventType->id,
            'name' => $eventType->name,
        ], $data[0]['type']);

        $this->assertArraySubset([
            'uuid' => $location->uuid,
            'name' => $location->name,
        ], $data[0]['location']);

        $this->assertArraySubset([
            'uuid' => $user->uuid,
            'name' => $user->name,
        ], $data[0]['manager']);

        $this->assertArraySubset([
            'uuid' => $host->uuid,
            'name' => $host->name,
        ], $data[0]['host']);

        $this->assertArraySubset([
            'uuid' => $venue->uuid,
            'name' => $venue->name,
        ], $data[0]['venue']);
    }

    public function testGetListWithAllowedSorts()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $event1 = factory(Event::class)->create([
            'name' => 'A event1',
            'start_at' => Carbon::now(),
            'host_uuid' => factory(Company::class)->create(['name' => 'A host1'])->uuid,
            'manager_uuid' => factory(User::class)->create(['first_name' => 'A manager1'])->uuid
        ]);
        $event2 = factory(Event::class)->create([
            'name' => 'Z event2',
            'start_at' => Carbon::now()->subDays(1),
            'host_uuid' => factory(Company::class)->create(['name' => 'Z host1'])->uuid,
            'manager_uuid' => factory(User::class)->create(['first_name' => 'Z manager2'])->uuid
        ]);

        $eventTags1 = factory(EventTag::class)->create([
            'name' => 'A tag1'
        ]);
        $eventTags2 = factory(EventTag::class)->create([
            'name' => 'Z tag2'
        ]);
        $event1->eventTags()->save($eventTags1);
        $event2->eventTags()->save($eventTags2);

        // 1. sort by name
        $response = $this->json('get', '/api/foodfleet/events?sort=name');
        $data = $response->assertStatus(200)->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals($event1->uuid, $data[0]['uuid']);

        $response = $this->json('get', '/api/foodfleet/events?sort=-name');
        $data = $response->assertStatus(200)->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals($event2->uuid, $data[0]['uuid']);

        // 2. sort by start_at
        $response = $this->json('get', '/api/foodfleet/events?sort=start_at');
        $data = $response->assertStatus(200)->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals($event2->uuid, $data[0]['uuid']);

        $response = $this->json('get', '/api/foodfleet/events?sort=-start_at');
        $data = $response->assertStatus(200)->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals($event1->uuid, $data[0]['uuid']);

        // 3. sort by host
        $response = $this->json('get', '/api/foodfleet/events?sort=host');
        $data = $response->assertStatus(200)->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals($event1->uuid, $data[0]['uuid']);

        $response = $this->json('get', '/api/foodfleet/events?sort=-host');
        $data = $response->assertStatus(200)->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals($event2->uuid, $data[0]['uuid']);

        // // 4. sort by manager
        $response = $this->json('get', '/api/foodfleet/events?sort=manager');
        $data = $response->assertStatus(200)->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals($event1->uuid, $data[0]['uuid']);

        $response = $this->json('get', '/api/foodfleet/events?sort=-manager');
        $data = $response->assertStatus(200)->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals($event2->uuid, $data[0]['uuid']);

        // 5. sort by event_tags
        $response = $this->json('get', '/api/foodfleet/events?sort=event_tags');
        $data = $response->assertStatus(200)->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals($event1->uuid, $data[0]['uuid']);

        $response = $this->json('get', '/api/foodfleet/events?sort=-event_tags');
        $data = $response->assertStatus(200)->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals($event2->uuid, $data[0]['uuid']);
    }

    public function testGetItem()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $company = factory(Company::class)->create();
        $location = factory(Location::class)->create();
        $eventTag = factory(EventTag::class)->create();
        $eventType = factory(EventType::class)->create();

        $event = factory(Event::class)->create([
            'host_uuid' => $company->uuid,
            'location_uuid' => $location->uuid,
            'manager_uuid' => $user->uuid,
            'type_id' => $eventType->id
        ]);

        $event->eventTags()->save($eventTag);

        $data = $this
            ->json('GET', 'api/foodfleet/events/'.$event->uuid.'?include=manager,host,location,event_tags,type')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertArraySubset([
            'uuid' => $event->uuid,
            'name' => $event->name,
            'manager' => [
                'uuid' => $user->uuid
            ],
            'host' => [
                'uuid' => $company->uuid
            ],
            'location' => [
                'uuid' => $location->uuid
            ],
            'event_tags' => [
                [
                    'uuid' => $eventTag->uuid,
                    'name' => $eventTag->name
                ]
            ],
            'type_id' => $eventType->id,
            'type' => [
                'id' => $eventType->id,
                'name' => $eventType->name
            ]
        ], $data);
    }

    public function testDuplicateItemWhenNotFound()
    {

        $admin = factory(User::class)->create([
            'level' => 1
        ]);
        Passport::actingAs($admin);
        $payload = ['basicInformation' => true];
        /** @var Event $event */
        $count = Event::count();
        $this->json('POST', "api/foodfleet/events/abc123/duplicate", $payload)
            ->assertStatus(404);
        $this->assertEquals($count, Event::count());
    }

    public function getDuplicateEmptyProvider()
    {
        return [
            [ [], ],
            [
                ['basicInformation' => false]
            ],
            [
                ['venue' => false]
            ],
            [
                ['customer' => false]
            ],
            [
                ['fleetMember' => false]
            ],
            [
                [
                'basicInformation' => false,
                'venue' => false,
                'customer' => false,
                'fleetMember' => false
                ]
            ]
        ];
    }

    /**
     * @dataProvider getDuplicateEmptyProvider
     * @param $payload
     */
    public function testDuplicateItemWhenNoneSelectedOrEmpty($payload)
    {

        $admin = factory(User::class)->create([
            'level' => 1
        ]);
        Passport::actingAs($admin);
        /** @var Event $event */
        $event = factory(Event::class)->create();
        $count = Event::count();
        $this->json('POST', "api/foodfleet/events/{$event->uuid}/duplicate", $payload)
            ->assertStatus(422);
        $this->assertEquals($count, Event::count());
    }

    public function getDuplicateProvider()
    {
        return [
            [
                ['basicInformation' => true]
            ],
            [
                [
                    'basicInformation' => true,
                    'venue' => true
                ]
            ],
            [
                [
                    'basicInformation' => true,
                    'customer' => true
                ]
            ],
            [
                [
                    'basicInformation' => true,
                    'fleetMember' => true
                ]
            ],
            [
                [
                    'basicInformation' => true,
                    'venue' => true,
                    'customer' => true,
                    'fleetMember' => true
                ]
            ]
        ];
    }

    /**
     * @dataProvider getDuplicateProvider
     * @param $payload
     */
    public function testDuplicateItem($payload)
    {
        $admin = factory(User::class)->create([
            'level' => 1
        ]);
        Passport::actingAs($admin);

        /** @var Event $event */
        $event = factory(Event::class)->create();
        $eventTags = factory(EventTag::class, 5)->create();
        $event->eventTags()->saveMany($eventTags);
        $this->assertEquals(5, $event->eventTags()->count());
        /* The following fields are not duplicated until explicitly requested by client
        * @property Document[] documents
        * @property MenuItem[] menuItems
        * @property EventSchedule schedule
         */
        $count = Event::count();
        $data = $this
            ->json('POST', "api/foodfleet/events/{$event->uuid}/duplicate", $payload)
            ->assertStatus(201)
            ->json('data');

        /** @var Event $duplicate */
        $duplicate = Event::where('uuid', $data['uuid'])->firstOrFail();
        $this->assertEquals($count + 1, Event::count());
        $this->assertNotEquals($event->id, $duplicate->id);
        $this->assertNotEquals($event->uuid, $duplicate->uuid);

        // Basic Information
        if (Arr::get($payload, 'basicInformation', false)) {
            $this->assertEquals("Copy of $event->name", $duplicate->name);
            $this->assertEquals($event->type_id, $duplicate->type_id);
            $this->assertEquals($event->start_at->format('Y-m-d H:i:s'), $duplicate->start_at->format('Y-m-d H:i:s'));
            $this->assertEquals($event->end_at->format('Y-m-d H:i:s'), $duplicate->end_at->format('Y-m-d H:i:s'));
            $this->assertEquals($event->host_uuid, $duplicate->host_uuid);
            $this->assertEquals($event->host_status, $duplicate->host_status);
            $this->assertEquals($event->manager_uuid, $duplicate->manager_uuid);
            $this->assertEquals($event->status_id, $duplicate->status_id);
            $this->assertEquals($event->budget, $duplicate->budget);
            $this->assertEquals($event->attendees, $duplicate->attendees);
            $this->assertEquals($event->commission_rate, $duplicate->commission_rate);
            $this->assertEquals($event->commission_type, $duplicate->commission_type);
            $this->assertEquals($event->staff_notes, $duplicate->staff_notes);
            $this->assertEquals($event->member_notes, $duplicate->member_notes);
            $this->assertEquals($event->customer_notes, $duplicate->customer_notes);
            $this->assertEquals(5, $duplicate->eventTags()->count());
            $this->assertEquals(
                $event->eventTags()->pluck('name')->toArray(),
                $duplicate->eventTags()->pluck('name')->toArray()
            );
        }

        // Venue
        if (Arr::get($payload, 'venue', false)) {
            $this->assertEquals($duplicate->venue_uuid, $event->venue_uuid);
            $this->assertEquals($duplicate->location_uuid, $event->location_uuid);
        }

        // Fleet member
        if (Arr::get($payload, 'fleetMember', false)) {
            $this->assertEquals($event->stores()->count(), $duplicate->stores()->count());
        }

        // Customer
        if (Arr::get($payload, 'customer', false)) {
            $this->assertEquals($duplicate->host_uuid, $event->host_uuid);
        }
    }

    public function testCreatedItem()
    {
        $admin = factory(User::class)->create([
            'level' => 1
        ]);
        Passport::actingAs($admin);
        $company = factory(Company::class)->create();
        $location = factory(Location::class)->create();
        $eventTags = factory(EventTag::class, 5)->create();
        $eventTagNames = $eventTags->map(function ($item) {
            return $item->name;
        });

        $payload = [
            'name' => 'test event',
            'manager_uuid' => $admin->uuid,
            'host_uuid' => $company->uuid,
            'location_uuid' => $location->uuid,
            'event_tags' => $eventTagNames,
            'host_status' => 1,
            'status_id' => 1,
            'start_at' => '2050-09-18',
            'end_at' => '2050-09-20',
            'staff_notes' => 'test staff notes',
            'member_notes' => 'test member notes',
            'customer_notes' => 'test customer notes',
            'commission_rate' => 30,
            'commission_type' => 1,
            'type_id' => 1
        ];
        $data = $this
            ->json('POST', 'api/foodfleet/events', $payload)
            ->assertStatus(201)
            ->json('data');

        $url = 'api/foodfleet/events/'.$data['uuid'].'?include=manager,host,location,event_tags';
        $returnedEvent = $this->json('GET', $url)
            ->assertStatus(200)
            ->json('data');

        $this->assertArraySubset([
            'name' => $payload['name'],
            'manager_uuid' => $payload['manager_uuid'],
            'host_uuid' => $payload['host_uuid'],
            'location_uuid' => $payload['location_uuid'],
            'host_status' => $payload['host_status'],
            'status_id' => $payload['status_id'],
            'start_at' => '2050-09-18T00:00:00.000000Z',
            'end_at' => '2050-09-20T00:00:00.000000Z',
            'staff_notes' => $payload['staff_notes'],
            'member_notes' => $payload['member_notes'],
            'customer_notes' => $payload['customer_notes'],
            'commission_rate' => $payload['commission_rate'],
            'commission_type' => $payload['commission_type'],
            'type_id' => $payload['type_id'],
        ], $returnedEvent);
        $this->assertArraySubset($eventTags->map(function ($item) {
            return [
                'uuid' => $item->uuid,
                'name' => $item->name
            ];
        }), $returnedEvent['event_tags']);
    }

    public function testCreatedItemWithSchedule()
    {
        $admin = factory(User::class)->create([
            'level' => 1
        ]);

        Passport::actingAs($admin);

        $company = factory(Company::class)->create();
        $repeatOn = array();
        $repeatOn[] = (object) ["id" => 1, "text" => "First Monday on each following month"];
        $data = $this
            ->json('POST', 'api/foodfleet/events', [
                'name' => 'test event',
                'manager_uuid' => $admin->uuid,
                'host_uuid' => $company->uuid,
                'status_id' => 1,
                'start_at' => '2050-09-18',
                'end_at' => '2050-09-20',
                'staff_notes' => 'test staff notes',
                'member_notes' => 'test member notes',
                'customer_notes' => 'test customer notes',
                'commission_rate' => 30,
                'commission_type' => 1,
                'schedule' => [
                    'interval_unit' => 'Month(s)',
                    'interval_value' => 3,
                    'occurrences' => 4,
                    'ends_on' => 'after',
                    'repeat_on' => $repeatOn,
                    'description' => 'First Monday on each following month, util December 13th, 2020'
                ]
            ])
            ->assertStatus(201)
            ->json('data');

        $returnedEvent = $this->json('GET', 'api/foodfleet/events/'.$data['uuid'])
            ->assertStatus(200)
            ->json('data');

        $this->assertEquals('Month(s)', $returnedEvent['schedule']['interval_unit']);
        $this->assertEquals(3, $returnedEvent['schedule']['interval_value']);
        $this->assertEquals(4, $returnedEvent['schedule']['occurrences']);
        $this->assertEquals('after', $returnedEvent['schedule']['ends_on']);
        $this->assertEquals(
            'First Monday on each following month, util December 13th, 2020',
            $returnedEvent['schedule']['description']
        );
    }

    public function testCreatedDraftItem()
    {
        $admin = factory(User::class)->create([
            'level' => 1
        ]);

        Passport::actingAs($admin);

        $data = $this
            ->json('POST', 'api/foodfleet/events', [
                'name' => 'test event',
                'status_id' => 1
            ])
            ->assertStatus(201)
            ->json('data');

        $url = 'api/foodfleet/events/'.$data['uuid'];
        $returnedEvent = $this->json('GET', $url)
            ->assertStatus(200)
            ->json('data');
        $expectations = $data;
        $this->assertArraySubset([
            'uuid' => $returnedEvent['uuid'],
            'status_id' => $returnedEvent['status_id'],
            'name' => $returnedEvent['name'],
            'host_status' => 1,
            'start_at' => null,
            'end_at' => null,
            'staff_notes' => null,
            'member_notes' => null,
            'customer_notes' => null,
            'budget' => null,
            'attendees' => 0,
            'commission_rate' => 0,
            'commission_type' => 1,
            'type_id' => null,
            'manager_uuid' => null,
            'host_uuid' => null,
            'location_uuid' => null
        ], $expectations);
    }

    public function testUpdateItem()
    {
        $event = factory(Event::class)->create();

        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $payload = factory(Event::class)->make()->toArray();
        $eventTag = factory(EventTag::class)->create();
        $eventTag2 = factory(EventTag::class)->create();
        $payload['event_tags'] = [
            [
                'uuid' => $eventTag2->uuid
            ]
        ];
        $event->eventTags()->save($eventTag);

        $data = $this
            ->json('PUT', 'api/foodfleet/events/'.$event->uuid, $payload)
            ->assertStatus(200)
            ->json('data');

        $event->refresh();

        $this->assertEquals($data['name'], $event->name);
        $this->assertEquals($data['manager_uuid'], $event->manager_uuid);
        $this->assertEquals($data['host_uuid'], $event->host_uuid);
        $this->assertEquals($data['location_uuid'], $event->location_uuid);
        $this->assertEquals($data['venue_uuid'], $event->venue_uuid);
        $this->assertEquals($data['status_id'], $event->status_id);
        $this->assertEquals(1, $event->eventTags()->where('uuid', $eventTag2->uuid)->count());
    }

    public function testUpdateItemWithSchedule()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $company = factory(Company::class)->create();
        $event = factory(Event::class)->create([
            'manager_uuid' => $user->uuid,
            'host_uuid' => $company->uuid
        ]);

        $schedule = factory(EventSchedule::class)->create([
            'event_uuid' => $event->uuid,
            'interval_unit' => 'Week(s)',
            'interval_value' => 1,
            'occurrences' => 1,
            'ends_on' => 'on',
            'description' => 'testing'
        ]);

        $repeatOn = array();
        $repeatOn[] = (object) ["id" => 1, "text" => "First Monday on each following month"];
        $data = $this
            ->json('PUT', 'api/foodfleet/events/'.$event->uuid, [
                'name' => 'test event',
                'schedule' => [
                    'interval_unit' => 'Month(s)',
                    'interval_value' => 3,
                    'occurrences' => 4,
                    'ends_on' => 'after',
                    'repeat_on' => $repeatOn,
                    'description' => 'First Monday on each following month, util December 13th, 2020'
                ]
            ])
            ->assertStatus(200)
            ->json('data');

        $returnedEvent = $this->json('GET', 'api/foodfleet/events/'.$event->uuid)
            ->assertStatus(200)
            ->json('data');

        $this->assertEquals('test event', $returnedEvent['name']);
        $this->assertEquals('Month(s)', $returnedEvent['schedule']['interval_unit']);
        $this->assertEquals(3, $returnedEvent['schedule']['interval_value']);
        $this->assertEquals(4, $returnedEvent['schedule']['occurrences']);
        $this->assertEquals('after', $returnedEvent['schedule']['ends_on']);
        $this->assertEquals(
            'First Monday on each following month, util December 13th, 2020',
            $returnedEvent['schedule']['description']
        );
    }

    public function testAssignStores()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $company = factory(Company::class)->create();
        $location = factory(Location::class)->create();
        $eventTag = factory(EventTag::class)->create();
        $stores = factory(Store::class, 3)->create();
        $storeUuids = $stores->map(function ($item) {
            return $item->uuid;
        });

        $event = factory(Event::class)->create([
            'status_id' => 1,
            'manager_uuid' => $user->uuid,
            'host_uuid' => $company->uuid,
            'location_uuid' => $location->uuid
        ]);
        $event->eventTags()->save($eventTag);

        $data = $this
            ->json('PUT', 'api/foodfleet/events/'.$event->uuid, [
                'store_uuids' => $storeUuids
            ])
            ->assertStatus(200)
            ->json('data');

        $url = 'api/foodfleet/events/'.$event->uuid.'?include=stores';
        $returnedEvent = $this->json('GET', $url)
            ->assertStatus(200)
            ->json('data');

        $this->assertNotEmpty($returnedEvent);
        $this->assertEquals(3, count($returnedEvent['stores']));
        foreach ($stores as $idx => $store) {
            $this->assertArraySubset([
                'uuid' => $store->uuid,
                'name' => $store->name,
                'square_id' => $store->square_id
            ], $returnedEvent['stores'][$idx]);
        }
    }

    public function testDeleteItem()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $company = factory(Company::class)->create();
        $location = factory(Location::class)->create();
        $eventTag = factory(EventTag::class)->create();

        $event = factory(Event::class)->create([
            'host_uuid' => $company->uuid,
            'location_uuid' => $location->uuid,
            'manager_uuid' => $user->uuid
        ]);

        $event->eventTags()->save($eventTag);

        $data = $this
            ->json('GET', 'api/foodfleet/events/'.$event->uuid)
            ->assertStatus(200)
            ->json('data');

        $this->assertEquals($event->uuid, $data['uuid']);
        $this->assertDatabaseHas('events_event_tags', [
            'event_uuid' => $event->uuid,
            'event_tag_uuid' => $eventTag->uuid,
        ]);

        $this->json('DELETE', 'api/foodfleet/events/'.$event->uuid)
            ->assertStatus(204);

        $this->json('GET', 'api/foodfleet/events/'.$event->uuid)
            ->assertStatus(404);
    }

    public function testGetNewItemRecommendation()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $data = $this->json('GET', 'api/foodfleet/events/new')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [],
            ])
            ->json('data');

        $this->assertEquals($data['status_id'], EventStatusEnum::DRAFT);
    }

    public function testEventSummaryWithEventCommissionRate()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $company = factory(\FreshinUp\FreshBusForms\Models\Company\Company::class)->create([
            'users_id' => $user->id
        ]);

        $event = factory(Event::class)->create([
            'host_uuid' => $company->uuid,
            'commission_rate' => 12,
            'commission_type' => 1
        ]);

        $stores = factory(Store::class, 2)->create();
        $storeUuids = $stores->map(function ($store) {
            return $store->uuid;
        });
        $event->stores()->sync($storeUuids);

        factory(EventMenuItem::class, 2)->create([
            'cost' => 5,
            'event_uuid' => $event->uuid,
            'store_uuid' => $storeUuids[0]
        ]);

        factory(EventMenuItem::class, 3)->create([
            'cost' => 10,
            'event_uuid' => $event->uuid,
            'store_uuid' => $storeUuids[1]
        ]);

        factory(Document::class, 5)->create([
            'type_id' => 2,
            'status_id' => 2,
            'assigned_uuid' => $user->uuid,
            'assigned_type' => \App\User::class
        ]);

        $data = $this
            ->json('get', "/api/foodfleet/event-summary/".$event->uuid)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals($data['customer']['owner'], $user->first_name.' '.$user->last_name);
        $this->assertEquals($data['customer']['signed_contracts'], 5);
        $this->assertEquals($data['customer']['phone'], $user->mobile_phone);
        $this->assertEquals($data['customer']['email'], $user->email);
        $this->assertEquals($data['financial']['total_fleet'], 2);
        $this->assertEquals($data['financial']['total_cost'], 40);
        $this->assertEquals($data['financial']['amount_due'], 64); //10*3+12 + 2*5+12
    }

    public function testEventSummaryWithOneOfOverrideCommissionRate()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $company = factory(\FreshinUp\FreshBusForms\Models\Company\Company::class)->create([
            'users_id' => $user->id
        ]);

        $event = factory(Event::class)->create([
            'host_uuid' => $company->uuid,
            'commission_rate' => 12,
            'commission_type' => 1
        ]);

        $stores = factory(Store::class, 2)->create();
        $storeUuids = $stores->map(function ($store) {
            return $store->uuid;
        });
        $event->stores()->sync($storeUuids);

        factory(EventMenuItem::class, 2)->create([
            'cost' => 5,
            'event_uuid' => $event->uuid,
            'store_uuid' => $storeUuids[0]
        ]);

        factory(EventMenuItem::class, 3)->create([
            'cost' => 10,
            'event_uuid' => $event->uuid,
            'store_uuid' => $storeUuids[1]
        ]);

        factory(Document::class, 5)->create([
            'type_id' => 2,
            'status_id' => 2,
            'assigned_uuid' => $user->uuid,
            'assigned_type' => \App\User::class
        ]);

        $this->json('PUT', 'api/foodfleet/stores/'.$storeUuids[0], [
            'event_uuid' => $event->uuid,
            'commission_rate' => 2,
            'commission_type' => 2,
            'name' => $stores->first()->name
        ])
            ->assertStatus(200)
            ->json('data');

        $data = $this
            ->json('get', "/api/foodfleet/event-summary/".$event->uuid)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals($data['customer']['owner'], $user->first_name.' '.$user->last_name);
        $this->assertEquals($data['customer']['signed_contracts'], 5);
        $this->assertEquals($data['customer']['phone'], $user->mobile_phone);
        $this->assertEquals($data['customer']['email'], $user->email);
        $this->assertEquals($data['financial']['total_fleet'], 2);
        $this->assertEquals($data['financial']['total_cost'], 40);
        $this->assertEquals($data['financial']['amount_due'], 52.2); // 10*3+12 + 2*5+(2*5*2/100)
    }

    public function testGetListWithVenueIncluded()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $events = factory(Event::class, 5)->create();
        $data = $this->json('get', "/api/foodfleet/events?include=venue")
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(5, count($data));

        foreach ($events as $idx => $event) {
            $venue = $event->venue;
            $this->assertArraySubset([
                'uuid' => $event->uuid,
                'name' => $event->name,
                'venue' => [
                    'uuid' => $venue->uuid,
                    'name' => $venue->name,
                    'address' => $venue->address,
                ]
            ], $data[$idx]);
        }
    }

    public function testGetListWithLocationsIncluded()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $events = factory(Event::class, 5)->create();
        $data = $this
            ->json('get', "/api/foodfleet/events?include=location")
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals(5, count($data));

        foreach ($events as $idx => $event) {
            $location = $event->location;
            $this->assertArraySubset([
                'uuid' => $event->uuid,
                'name' => $event->name,
                'location' => [
                    "uuid" => $location->uuid,
                    "name" => $location->name,
                    "venue_uuid" => $location->venue_uuid,
                    "spots" => $location->spots,
                    "capacity" => $location->capacity,
                    "details" => $location->details
                ]
            ], $data[$idx]);
        }
    }

    public function testValidationToCreateEventDoesNotRejectIncompleteDatesFormat()
    {
        //Given
        //exists an admin
        $company = factory(Company::class)->create();

        $user = factory(User::class)->create([
            'company_id' => $company->id,
        ]);

        Passport::actingAs($user);

        // with venue and locations
        $venue =  factory(Venue::class)->create([
            'uuid' => 'cd1e36c1-426c-376a-a881-b91f2ce33d31',
        ]);

        $location = factory(Location::class)->create([
            'venue_uuid' => $venue->uuid,
            'uuid' => 'b4b34d44-c3ff-3494-8201-73b67b2263fe',
        ]);

        //When

        //the admin decides to create a new event, with incomplete date formats

        $dates = [
            '2020-12-26T08:20:00.000000Z', '2020-12-27T08:20:00.000000Z', '2020-12-24 00:00', '2020-12-25 08:00',
        ];

        for ($i = 0; $i < count($dates); $i += 2) {
            $event_payload = [
                'name' => 'My Event',
                'status_id' => 1,
                'host_status' => 0,
                'start_at' => $dates[$i], //Y-m-d H:i or in ISO format
                'end_at' => $dates[$i + 1], //Y-m-d H:i
                'staff_notes' => 'only visible to you people',
                'member_notes' => 'only fleet members aye',
                'customer_notes' => 'nothing much to be said again',
                'attendees' => '70',
                'commission_rate' => 5,
                'commission_type' => 1,
                'type_id' => 1,
                'location_uuid' => $location->uuid,
                'venue_uuid' => $venue->uuid,
            ];

            $response = $this->postJson("/api/foodfleet/events", $event_payload)
                ->assertStatus(201)->json('data');

            //same goes for an update with date in such format
            //include the host
            $event_payload = array_merge($event_payload, [
                'host_uuid' => $company->uuid,
                'manager_uuid' => $user->uuid,
            ]);

            $updated = $this->putJson("/api/foodfleet/events/" . $response['uuid'], $event_payload);

            $final = $updated->assertStatus(200)->assertJsonStructure(['data'])->json('data');
        }
    }

    public function testUpdatingAnEventWithoutTagsRemovesExistingTagsAsWell()
    {
        $company = factory(Company::class)->create();
        $user = factory(User::class)->create([
            'company_id' => $company->id,
        ]);

        $tagNames = ['food', 'working', 'code'];
        /** @var Event $event */
        $event = factory(Event::class)->create([
            'host_uuid' => $company->uuid,
        ]);
        $tags = array_map(function ($name) {
            return EventTag::firstOrCreate(['name' => $name])->uuid;
        }, $tagNames);
        $event->eventTags()->sync($tags);
        $tags = $event->eventTags()->get();
        $payload = array_merge($event->toArray(), [
            'event_tags' => []
        ]);

        Passport::actingAs($user);
        $data = $this->json('PUT', 'api/foodfleet/events/' . $event->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');


        //assert event (refreshed) no longer has tags
        foreach ($tags as $tag) {
            $this->assertDatabaseMissing('events_event_tags', [
                'event_uuid' => $event->uuid,
                'event_tag_uuid' => $tag->uuid,
            ]);

            //also assert that, the actual tags are not deleted
            $this->assertDatabaseHas('event_tags', [
                'uuid' => $tag->uuid,
            ]);
        }

        $this->assertEquals(0, $event->eventTags()->count());
    }

    public function testAssignStoreToEvent()
    {
        $user = factory(User::class)->create();
        $event = factory(Event::class)->create();
        $store = factory(Store::class)->create();
        Passport::actingAs($user);

        $this->assertEquals(0, $event->stores()->count());
        $data = $this->json('POST', "/api/foodfleet/events/{$event->uuid}/stores/{$store->uuid}")
            ->assertStatus(201)
            ->assertJsonStructure([
                'data'
            ])
            ->json('data');
        $this->assertEquals($event->uuid, $data['uuid']);
        $this->assertEquals(1, $event->stores()->where('uuid', $store->uuid)->count());
    }

    public function testUnassignStoreToEvent()
    {
        $user = factory(User::class)->create();
        /** @var Event $event */
        $event = factory(Event::class)->create();
        $store = factory(Store::class)->create();
        Passport::actingAs($user);

        $event->stores()->attach($store->uuid);

        $this->assertEquals(1, $event->stores()->count());
        $this->json('DELETE', "/api/foodfleet/events/{$event->uuid}/stores/{$store->uuid}")
            ->assertStatus(204);
        $this->assertEquals(0, $event->stores()->count());
    }
}
