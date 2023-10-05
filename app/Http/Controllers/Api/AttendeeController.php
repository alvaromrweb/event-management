<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['user'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('throttle:60,1')
            ->only(['store', 'destroy']);
        $this->authorizeResource(Attendee::class, 'attendee');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        $attendees = $this->loadRelationships(
            $event->attendees()->latest()
        );

        return AttendeeResource::collection(
            $attendees->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Event $event, Request $request)
    {
        $attendee = $event->attendees()->create([
            'user_id' => $request->user()->id
        ]);

        return new AttendeeResource($this->loadRelationships($attendee));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $event, Attendee $attendee)
    {
        return new AttendeeResource($this->loadRelationships($attendee));
    }

    /**
     * Remove the specified resource from storage.
     */
    // Here the event is string because we dont want to fetch it by typing it with Event
    public function destroy(Event $event, Attendee $attendee)
    {
        $attendee->delete();

        return response(status: 204);
    }
}
