<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Appointment;
use App\Queue1;
use App\Queue2;
use App\Queue3;
use App\Queue4;
use App\QueueSummary;
use App\Events\QueueStarted;

class QueueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $queue1 = Queue1::all();
        $queue2 = Queue2::all();
        $queue3 = Queue3::all();
        $queue4 = Queue4::all();
        return [$queue1, $queue2, $queue3, $queue4];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $queue;
        // Save queue data
        if($request->timeslot == "08-09"){
            $queue = new Queue1(); 
        }else if($request->timeslot == "09-10"){
            $queue = new Queue2(); 
        }else if($request->timeslot == "10-11"){
            $queue = new Queue3(); 
        }else if($request->timeslot == "11-12"){
            $queue = new Queue4(); 
        }

        $queue->number = $request->number;
        $queue->patient_id = $request->patient_id;
        $queue->save();

        // Set flag in appointment table
        Appointment::where('patient_id', $request->patient_id)->where('date', $request->date)->update(['flag' => 1]);

        event(new QueueStarted()); // update doctor's dashboard(patient list) using pusher
        return $queue;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    // This method will return the recent number of a specific queue
    public function getRecentNumber(Request $request)
    {
        $recentNumber = -1;
        if($request->timeslot == "08-09"){
            if(!Queue1::all() -> isEmpty()){
                $recentNumber = Queue1::orderBy('number', 'desc')->first()->number;
            }
        }else if($request->timeslot == "09-10"){
            if(!Queue2::all() -> isEmpty()){
                $recentNumber = Queue2::orderBy('number', 'desc')->first()->number;
            }
        }else if($request->timeslot == "10-11"){
            if(!Queue3::all() -> isEmpty()){
                $recentNumber = Queue3::orderBy('number', 'desc')->first()->number;
            }
        }else if($request->timeslot == "11-12"){
            if(!Queue4::all() -> isEmpty()){
                $recentNumber = Queue4::orderBy('number', 'desc')->first()->number;
            }
        }
        return $recentNumber;
    }

    // Get patient list of current queue (This method related to doctor role)
    public function getCurrentQueue() {
        // Get active queue 
        $activeQueueDetails = QueueSummary::where('status', 1)->get();
        
        if($activeQueueDetails->isEmpty()){
            return -1;
        }
        $activeQueue = $activeQueueDetails[0]->timeslot;

        // Get data from selected queue
        $queueNo;
        switch ($activeQueue) {
            case '08-09':
                $queueNo = 'queue1';
                break;
            case '09-10':
                $queueNo = 'queue2';
                break;
            case '10-11':
                $queueNo = 'queue3';
                break;
            case '11-12':
                $queueNo = 'queue4';
                break;
        }

        // Get patients data using query builder (join 'patients' table and one of queue table)
        return DB::table($queueNo)->join('patients', 'patients.patient_id', "$queueNo.patient_id")->get();
    }

    // This method will return active queue 
    public function getActiveQueue() {
        $activeQueue = QueueSummary::where('status', '1')->get();
        if($activeQueue->isEmpty()){
            return -1;
        }
        switch ($activeQueue[0]->timeslot) {
            case '08-09':
                return 0;
            case '09-10':
                return 1;
            case '10-11':
                return 2;
            case '11-12':
                return 3;
        }
        return $activeQueue;
    }

    // start queue [queue_summary -> status=1]
    public function startQueue(Request $request) {
        $timeslot = "";
        switch ($request[0]) {
            case 0:
                $timeslot = "08-09";
                break;
            case 1:
                $timeslot = "09-10";
                break;
            case 2:
                $timeslot = "10-11";
                break;
            case 3:
                $timeslot = "11-12";
                break;
        }
        $queueSummary = new QueueSummary();
        $queueSummary->timeslot = $timeslot;
        $queueSummary->total = $request[1];
        $queueSummary->current = $request[2];
        $queueSummary->status = $request[3];
        $queueSummary->save();

        event(new QueueStarted()); // update doctor's dashboard(patient list) using pusher
        return $queueSummary;
    }

    // stop queue [queue_summary -> status=0]
    public function stopQueue(Request $request) {
        $timeslot = "";
        switch ($request[0]) {
            case 0:
                $timeslot = "08-09";
                break;
            case 1:
                $timeslot = "09-10";
                break;
            case 2:
                $timeslot = "10-11";
                break;
            case 3:
                $timeslot = "11-12";
                break;
        }
        DB::table('queue_summary')->where('timeslot', $timeslot)->update(['status' => 0]); 
    }
}
