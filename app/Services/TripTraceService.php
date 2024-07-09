<?php

namespace App\Services;

use App\Enums\NotificationActions;
use App\Events\SendMulticastNotification;
use App\Models\TripDates;
use App\Models\TripTrace;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Class TripTraceService.
 */
class TripTraceService
{
    public function getTripTraces($request)
    {
        $date = $request->start_date ?? Carbon::today();
        $isMonth = strlen($date) == 7;
        $trips =  TripDates::with(['trip.salesman', 'tripTrace'])
            ->whereHas('tripTrace', function ($query) use ($request) {
                $query->whereNotNull('status');
            });

        if($isMonth){
            $date = Carbon::make($date);
            return $trips->whereYear('start_date','=', $date->year)
                ->whereMonth('start_date', '=', $date->month)
                ->get()
                ->toArray();
        }else{
            logger('noohere');
            return $trips->whereDate('start_date', '=', $date)
                ->get()
                ->toArray();
        }
    }

    public function updateTripTrace($request)
    {

        if ($request->status == 'resume') {

            $trace = TripTrace::query()
                ->where('trip_date_id', '=', $request->trip_date_id)
                ->first();
            $trace->update(
                [
                    'status' => $request->status,
                ]
            );
            $delay = Carbon::make('0:0:0')->diffInMinutes($request->duration);
            $trace['tripDate']->increment('delay', $delay);
        } else {
            $trace = TripTrace::query()
                ->where('trip_date_id', '=', $request->trip_date_id)
                ->first();
            $trace->update(
                [
                    'duration' => $request->duration,
                    'status' => $request->status,
                ]
            );
        }
        if ($request->status == 'resume' || $request->status == 'start') {
            $customers = $trace['tripDate']['trip']['customerTimes'];
            foreach ($customers as $customer) {

                event(new SendMulticastNotification(
                   20,//todo make auth for authentication user
                    [$customer->customer->id],
                    NotificationActions::TRACE->value,
                    $customer,
                    true
                ));
            }
            unset($trace['tripDate']);
        }
        return $trace;
    }


    public function currentTrip(User $salesman)
    {
        return $salesman->todayTripsDates()
            ->whereHas('tripTrace', function ($query) {
                $query->whereNotNull('status');
            })
            ->with('tripTrace')
            ->orderBy('start_time', 'desc')
            ->first();
    }

    public function next(User $salesman)
    {
        return $salesman->todayTripsDates()
            ->whereHas('tripTrace', function ($query) {
                $query->whereNull('status');
            })
            ->with('tripTrace')
            ->orderBy('start_time')
            ->first();
    }

    public function startTrip(TripDates $tripDate): void
    {
        $trace = $tripDate['tripTrace'];
        $trace['status'] = 'start';
        $trace->save();
        $delay = Carbon::now()->diffInMinutes($tripDate['start_time']);
        if($delay < 0) $delay = 0;
        $tripDate['delay'] = $delay;
        $this->pushNotification($tripDate);
    }

    private function pushNotification(TripDates $trip): void
    {
        $customers = $trip['trip']['customerTimes'];
        foreach ($customers as $customer) {
            event(new SendMulticastNotification(
                auth()->id(),
                [$customer->customer->id],
                NotificationActions::TRACE->value,
                $trip->trip->branch_id,
                $customer,
                true
            ));
        }
    }

    public function stop(TripDates $tripDate): void
    {

        $trace = $tripDate['tripTrace'];

        if($trace['status'] == 'stop')
            return;

        $trace['status'] = 'stop';
        $trace['duration'] = request()->input('duration');
        $trace->save();
    }

    public function nextTrip(User $salesman): void
    {
        $current = $this->currentTrip($salesman);
        $next = $this->next($salesman);
        if($current){
            $this->stop($current);
        }
        if($next){
            $this->startTrip($next);
        }else{
            throw new \Exception('No trip to go');
        }
    }

    public function stopTrip(User $salesman): void
    {
        $current = $this->currentTrip($salesman);
        if($current){
            $this->stop($current);
        }
    }

    public function pauseTrip(User $salesman): void
    {
        $current = $this->currentTrip($salesman);
        if($current){
            $trace = $current['tripTrace'];
            $trace['status'] = 'pause';
            $trace->save();
        }
    }

    public function resumeTrip(User $salesman): void
    {
        $current = $this->currentTrip($salesman);
        if($current){
            $trace = $current['tripTrace'];
            $trace['status'] = 'resume';
            $delay = Carbon::make('0:0:0')->diffInMinutes(request('duration'));
            $trace->save();
            $current['delay'] = $delay;
            $current->save();
            $this->pushNotification($current);
        }
    }


    public function endTrip(User $salesman): void
    {
        $trips = $salesman->todayTripsDates;
        foreach ($trips as $trip) {
            $this->stop($trip);
        }
    }
}
