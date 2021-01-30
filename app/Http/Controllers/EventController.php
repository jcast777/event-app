<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event;
use Carbon\Carbon;
use App\Http\Resources\Event as EventResource;

class EventController extends Controller
{

    public function index() {
        $events = Event::get();
        return EventResource::collection($events);
    }

    public function store(Request $request) {

        Event::truncate();

        $start = Carbon::parse($request->from);
        $end = Carbon::parse($request->to);

        for($i = 0; $i < $end->diffInDays($start); $i++){
            $dates[] = (clone $start)->addDays($i + 1)->format('Y-m-d');
        }
        
        foreach($request->days as $day) {

            foreach($dates as $date) {
                if (Carbon::parse($date)->is($day) == true) {
                    $eventDate = (Carbon::parse($date)->format('Y-m-d'));

                    $event = new Event;

                    $event->event = $request->input('event');
                    $event->date = $eventDate;
                    
                    $exist = Event::get();

                    $event->save();
                }
            }
            
        }

        $events = Event::all();
        return EventResource::collection($events);
        
    }
}
